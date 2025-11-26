<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookFile;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookFilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔍 Scanning NEW-BATCH folders for files...');

        $pdfPath = base_path('NEW-BATCH/PDF');
        $pngPath = base_path('NEW-BATCH/PNG');

        if (!is_dir($pdfPath) || !is_dir($pngPath)) {
            $this->command->warn('⚠️  NEW-BATCH folders not found. Skipping...');
            return;
        }

        $pdfFiles = $this->scanDirectory($pdfPath);
        $pngFiles = $this->scanDirectory($pngPath);

        $this->command->info("📄 Found {$pdfFiles->count()} PDF files");
        $this->command->info("🖼️  Found {$pngFiles->count()} PNG files");

        $matched = 0;
        $unmatched = [];

        // Match PDFs with their corresponding PNGs
        foreach ($pdfFiles as $pdfFile) {
            $baseFilename = pathinfo($pdfFile, PATHINFO_FILENAME);
            $pngFile = $pngFiles->firstWhere(function ($png) use ($baseFilename) {
                return pathinfo($png, PATHINFO_FILENAME) === $baseFilename;
            });

            $fileInfo = $this->parseFilename($pdfFile);
            if (!$fileInfo) {
                $unmatched[] = $pdfFile;
                continue;
            }

            // Find matching book
            $book = $this->findMatchingBook($fileInfo);

            if ($book) {
                $this->attachFiles($book, $pdfFile, $pngFile, $fileInfo);
                $matched++;
                $this->command->line("  ✓ Matched: {$book->title}");
            } else {
                $unmatched[] = $pdfFile;
                $this->command->warn("  ✗ No match: {$pdfFile}");
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Successfully matched {$matched} books");

        if (count($unmatched) > 0) {
            $this->command->warn("⚠️  Unmatched files: " . count($unmatched));
            foreach (array_slice($unmatched, 0, 10) as $file) {
                $this->command->line("     - {$file}");
            }
            if (count($unmatched) > 10) {
                $this->command->line("     ... and " . (count($unmatched) - 10) . " more");
            }
        }
    }

    /**
     * Scan directory for files
     */
    protected function scanDirectory(string $path): \Illuminate\Support\Collection
    {
        $files = scandir($path);
        return collect($files)->filter(fn($f) => !in_array($f, ['.', '..']));
    }

    /**
     * Parse filename to extract components
     *
     * Pattern: PALM [Collection Type] - [LANGUAGE] - [Title]
     *
     * Examples:
     * - PALM CD - Chuukese - Anapet me ewe chóón nááng.pdf
     * - PALM - Printed - YAPESE - Beaq Ni Ba Moqon Ngea Ba Raanʻ I Moongkii.pdf
     * - PALM - Printed [Trial version] - CHUUKESE - Chiechiach Kewe TCC 4.pdf
     */
    protected function parseFilename(string $filename): ?array
    {
        // Remove extension
        $name = preg_replace('/\.(pdf|png|PDF|PNG)$/', '', $filename);

        // Pattern: PALM [something] - [LANGUAGE] - [Title]
        if (preg_match('/^PALM\s+(.+?)\s+-\s+([A-Z]+)\s+-\s+(.+)$/i', $name, $matches)) {
            $collectionPart = trim($matches[1]);
            $language = ucfirst(strtolower(trim($matches[2])));
            $titlePart = trim($matches[3]);

            // Determine collection type
            $collection = $this->mapCollectionType($collectionPart);

            return [
                'collection' => $collection,
                'language' => $language,
                'title' => $titlePart,
                'original_filename' => $filename,
            ];
        }

        return null;
    }

    /**
     * Map collection type from filename pattern
     */
    protected function mapCollectionType(string $collectionPart): string
    {
        if (stripos($collectionPart, 'CD') !== false) {
            return 'PALM CD';
        }

        if (stripos($collectionPart, 'Trial') !== false) {
            return 'PALM trial';
        }

        if (stripos($collectionPart, 'Printed') !== false || stripos($collectionPart, '- Printed') !== false) {
            return 'PALM final';
        }

        return 'PALM';
    }

    /**
     * Find matching book in database
     */
    protected function findMatchingBook(array $fileInfo): ?Book
    {
        $language = Language::where('name', 'LIKE', $fileInfo['language'] . '%')->first();
        if (!$language) {
            return null;
        }

        // Clean title for matching (remove special characters, extra spaces)
        $cleanTitle = $this->cleanTitleForMatching($fileInfo['title']);

        // Try to find book by:
        // 1. Language match
        // 2. Title similarity (fuzzy matching)
        // 3. Collection match (optional, for better accuracy)

        $books = Book::whereHas('languages', function ($query) use ($language) {
            $query->where('languages.id', $language->id);
        })->get();

        // Score each book by title similarity
        $bestMatch = null;
        $bestScore = 0;

        foreach ($books as $book) {
            $score = $this->calculateSimilarity($cleanTitle, $this->cleanTitleForMatching($book->title));

            // Bonus points for collection match
            if ($book->collection && stripos($book->collection->name, $fileInfo['collection']) !== false) {
                $score += 0.2;
            }

            if ($score > $bestScore && $score > 0.6) { // Minimum 60% similarity
                $bestScore = $score;
                $bestMatch = $book;
            }
        }

        return $bestMatch;
    }

    /**
     * Clean title for matching
     */
    protected function cleanTitleForMatching(string $title): string
    {
        // Remove special characters, normalize spaces
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = strtolower(trim($clean));

        return $clean;
    }

    /**
     * Calculate similarity between two strings
     * Returns value between 0 and 1
     */
    protected function calculateSimilarity(string $str1, string $str2): float
    {
        // Use similar_text for similarity calculation
        similar_text($str1, $str2, $percent);

        return $percent / 100;
    }

    /**
     * Attach PDF and PNG files to book
     */
    protected function attachFiles(Book $book, string $pdfFilename, ?string $pngFilename, array $fileInfo): void
    {
        // Check if files already exist
        $existingPdf = BookFile::where('book_id', $book->id)
            ->where('filename', $pdfFilename)
            ->exists();

        if ($existingPdf) {
            $this->command->line("     (Files already attached, skipping)");
            return;
        }

        // Attach PDF
        BookFile::create([
            'book_id' => $book->id,
            'file_type' => 'pdf',
            'file_path' => 'books/' . $pdfFilename,
            'filename' => $pdfFilename,
            'mime_type' => 'application/pdf',
            'is_primary' => true,
            'digital_source' => "Auto-matched from NEW-BATCH import",
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // Attach PNG thumbnail if exists
        if ($pngFilename) {
            BookFile::create([
                'book_id' => $book->id,
                'file_type' => 'thumbnail',
                'file_path' => 'books/' . $pngFilename,
                'filename' => $pngFilename,
                'mime_type' => 'image/png',
                'is_primary' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        Log::info("Attached files to book", [
            'book_id' => $book->id,
            'title' => $book->title,
            'pdf' => $pdfFilename,
            'png' => $pngFilename,
        ]);
    }
}
