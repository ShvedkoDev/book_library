<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTestCsvData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:generate-test-data
                            {--count=3000 : Number of test books to generate}
                            {--output= : Output file path (default: storage/csv-templates/test-data-3000.csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test CSV data with specified number of book entries';

    // Sample data arrays
    protected array $titles = [
        'The Adventures of', 'Journey to', 'Tales from', 'Stories of', 'The Mystery of',
        'Learning About', 'Discovering', 'Exploring', 'Understanding', 'The History of',
        'Island Life in', 'Ocean Tales of', 'Mountain Stories from', 'Beach Adventures in',
        'Traditional Tales from', 'Modern Life in', 'Ancient Legends of', 'Cultural Heritage of'
    ];

    protected array $subjects = [
        'Nature', 'Ocean Life', 'Island Culture', 'Mathematics', 'Science', 'History',
        'Geography', 'Language', 'Art', 'Music', 'Dance', 'Fishing', 'Farming', 'Sailing',
        'Traditional Crafts', 'Cooking', 'Family', 'Community', 'Environment', 'Wildlife'
    ];

    protected array $locations = [
        'Chuuk', 'Pohnpei', 'Yap', 'Kosrae', 'Palau', 'Guam', 'Saipan', 'Rota', 'Tinian',
        'Majuro', 'Kwajalein', 'Ebeye', 'Tarawa', 'Kiribati', 'Nauru', 'Tuvalu'
    ];

    protected array $languages = [
        'English', 'Chuukese', 'Pohnpeian', 'Yapese', 'Kosraean', 'Marshallese',
        'Palauan', 'Chamorro', 'Carolinian', 'Kiribati'
    ];

    protected array $publishers = [
        'Pacific Resources for Education and Learning',
        'University of Hawaii Press',
        'Micronesian Language Institute',
        'Pacific Islands Education Foundation',
        'College of Micronesia Press',
        'Island Heritage Publishing',
        'Pacific Education Press',
        'Oceania Publishers',
        'Micronesian Educational Publishers',
        'Regional Educational Laboratory'
    ];

    protected array $authorFirstNames = [
        'John', 'Mary', 'David', 'Sarah', 'Michael', 'Lisa', 'Robert', 'Maria',
        'James', 'Ana', 'William', 'Rosa', 'Joseph', 'Elena', 'Daniel', 'Carmen'
    ];

    protected array $authorLastNames = [
        'Santos', 'Rodriguez', 'Mwarike', 'Alapai', 'Helgenberger', 'Pretrick',
        'Sigrah', 'Killion', 'Nena', 'Hezel', 'Rechebei', 'McPhetres',
        'Ramarui', 'Sohl', 'Emesiochl', 'Yamaguchi'
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->option('count');
        $outputPath = $this->option('output') ?: storage_path('csv-templates/test-data-3000.csv');

        $this->info("Generating {$count} test book entries...");

        // Ensure directory exists
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Open file for writing
        $handle = fopen($outputPath, 'w');

        // Write BOM for Excel compatibility
        fputs($handle, "\xEF\xBB\xBF");

        // Write headers (readable)
        $headers = $this->getHeaders();
        fputcsv($handle, $headers);

        // Write database mapping row
        $mappingRow = $this->getMappingRow();
        fputcsv($handle, $mappingRow);

        // Generate test data
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        for ($i = 1; $i <= $count; $i++) {
            $row = $this->generateBookRow($i);
            fputcsv($handle, $row);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        fclose($handle);

        $fileSize = filesize($outputPath);
        $fileSizeMB = round($fileSize / 1024 / 1024, 2);

        $this->info("✓ Test data generated successfully!");
        $this->info("  File: {$outputPath}");
        $this->info("  Records: {$count}");
        $this->info("  Size: {$fileSizeMB} MB");

        return self::SUCCESS;
    }

    /**
     * Get CSV headers
     */
    protected function getHeaders(): array
    {
        return [
            'ID', 'PALM code', 'Title', 'Sub-title', 'Translated-title',
            'Physical_type', 'Collection', 'Publisher', 'Publisher_program', 'Year',
            'Pages', 'Description', 'TOC', 'Notes_issue', 'Notes_content', 'Contact',
            'Access_level', 'VLA_standard', 'VLA_benchmark',
            'Primary_language', 'Primary_language_ISO', 'Secondary_language',
            'Secondary_language_ISO', 'Additional_languages',
            'Author 1', 'Author 2', 'Author 3', 'Illustrator 1', 'Illustrator 2',
            'Illustrator 3', 'Illustrator 4', 'Illustrator 5', 'Editor',
            'Other_creator', 'Other_creator_role',
            'Purpose', 'Genre', 'Sub-genre', 'Type', 'Themes', 'Learner_level',
            'Geographic_location_island', 'Geographic_location_state',
            'Keywords', 'PDF_filename', 'PDF_filename_alt', 'Digital_source',
            'Digital_source_alt', 'Thumbnail_filename', 'Thumbnail_filename_alt',
            'Audio_files', 'Video_URLs',
            'UH_reference', 'UH_call_number', 'UH_notes',
            'COM_reference', 'COM_call_number', 'COM_notes',
            'Same_content_different_version', 'Omnibus', 'Supporting_book',
            'Different_language_same_content',
            'Is_featured', 'Is_active', 'Sort_order'
        ];
    }

    /**
     * Get database mapping row
     */
    protected function getMappingRow(): array
    {
        return [
            'books.internal_id', 'books.palm_code', 'books.title', 'books.subtitle',
            'books.translated_title', 'books.physical_type', 'collections.name',
            'publishers.name', 'publishers.program_name', 'books.publication_year',
            'books.pages', 'books.description', 'books.toc', 'books.notes_issue',
            'books.notes_content', 'books.contact', 'books.access_level',
            'books.vla_standard', 'books.vla_benchmark',
            'languages.name', 'languages.code', 'languages.name', 'languages.code',
            'languages.name', 'creators.name', 'creators.name', 'creators.name',
            'creators.name', 'creators.name', 'creators.name', 'creators.name',
            'creators.name', 'creators.name', 'creators.name', 'creators.role',
            'classification_values.value', 'classification_values.value',
            'classification_values.value', 'classification_values.value',
            'classification_values.value', 'classification_values.value',
            'geographic_locations.name', 'geographic_locations.name',
            'book_keywords.keyword', 'book_files.filename', 'book_files.filename',
            'book_files.digital_source', 'book_files.digital_source',
            'book_files.filename', 'book_files.filename', 'book_files.filename',
            'book_files.file_path', 'library_references.reference_number',
            'library_references.call_number', 'library_references.notes',
            'library_references.reference_number', 'library_references.call_number',
            'library_references.notes', 'book_relationships.related_book_id',
            'book_relationships.related_book_id', 'book_relationships.related_book_id',
            'book_relationships.related_book_id', 'books.is_featured', 'books.is_active',
            'books.sort_order'
        ];
    }

    /**
     * Generate a single book row
     */
    protected function generateBookRow(int $index): array
    {
        // Generate varied data
        $year = rand(1990, 2024);
        $hasSubtitle = rand(0, 100) < 30; // 30% have subtitles
        $hasTranslation = rand(0, 100) < 20; // 20% have translations
        $hasTOC = rand(0, 100) < 40; // 40% have table of contents
        $hasIssueNotes = rand(0, 100) < 10; // 10% have issue notes
        $multipleAuthors = rand(0, 100) < 40; // 40% have multiple authors
        $hasIllustrators = rand(0, 100) < 50; // 50% have illustrators
        $hasEditor = rand(0, 100) < 20; // 20% have editors

        // Generate title
        $titlePrefix = $this->titles[array_rand($this->titles)];
        $subject = $this->subjects[array_rand($this->subjects)];
        $location = $this->locations[array_rand($this->locations)];
        $title = "{$titlePrefix} {$subject}";

        // Add location sometimes
        if (rand(0, 100) < 60) {
            $title .= " in {$location}";
        }

        // Add special characters sometimes for testing
        if ($index % 100 === 0) {
            $title .= " (Volume " . ($index / 100) . ")";
        }
        if ($index % 50 === 0) {
            $title = "\"" . $title . "\""; // Test quoted titles
        }

        // Generate description (vary length)
        $descLength = rand(1, 4);
        $description = $this->generateDescription($subject, $location, $descLength);

        // Very long description for some entries
        if ($index % 200 === 0) {
            $description = str_repeat($description . " ", 5);
        }

        // Primary language
        $primaryLang = $this->languages[array_rand($this->languages)];
        $primaryISO = $this->getLanguageISO($primaryLang);

        // Secondary language (40% of books)
        $secondaryLang = '';
        $secondaryISO = '';
        if (rand(0, 100) < 40) {
            $availableLangs = array_diff($this->languages, [$primaryLang]);
            $secondaryLang = $availableLangs[array_rand($availableLangs)];
            $secondaryISO = $this->getLanguageISO($secondaryLang);
        }

        // Access level distribution
        $accessDist = rand(1, 100);
        if ($accessDist <= 70) {
            $accessLevel = 'Y'; // 70% full access
        } elseif ($accessDist <= 90) {
            $accessLevel = 'L'; // 20% limited
        } else {
            $accessLevel = 'N'; // 10% unavailable
        }

        // Generate authors
        $author1 = $this->generateAuthorName();
        $author2 = $multipleAuthors ? $this->generateAuthorName() : '';
        $author3 = ($multipleAuthors && rand(0, 100) < 20) ? $this->generateAuthorName() : '';

        // Generate illustrators
        $illustrators = [];
        if ($hasIllustrators) {
            $illustratorCount = rand(1, 5);
            for ($j = 0; $j < $illustratorCount; $j++) {
                $illustrators[] = $this->generateAuthorName();
            }
        }
        while (count($illustrators) < 5) {
            $illustrators[] = '';
        }

        // Editor
        $editor = $hasEditor ? $this->generateAuthorName() : '';

        // Other creator
        $otherCreator = '';
        $otherRole = '';
        if (rand(0, 100) < 15) {
            $otherCreator = $this->generateAuthorName();
            $otherRole = ['Translator', 'Compiler', 'Contributor', 'Photographer'][array_rand(['Translator', 'Compiler', 'Contributor', 'Photographer'])];
        }

        // Classifications
        $purposes = $this->getRandomClassifications(['Reading', 'Teaching', 'Reference', 'Assessment'], 1, 2);
        $genres = $this->getRandomClassifications(['Fiction', 'Non-fiction', 'Poetry', 'Drama'], 1, 1);
        $types = $this->getRandomClassifications(['Picture Book', 'Chapter Book', 'Novel', 'Short Stories', 'Poetry Collection'], 1, 1);
        $themes = $this->getRandomClassifications(['Adventure', 'Family', 'Nature', 'Culture', 'History', 'Science'], 0, 3);
        $learnerLevel = $this->getRandomClassifications(['Pre-K', 'K-2', '3-5', '6-8', '9-12', 'Adult'], 1, 1);

        // Keywords
        $keywords = $this->generateKeywords($subject, $location, rand(3, 8));

        // Files
        $pdfFilename = $accessLevel !== 'N' ? "book_{$index}.pdf" : '';
        $thumbnailFilename = "thumb_{$index}.png";

        // Library references (30% of books)
        $uhRef = '';
        $uhCall = '';
        if (rand(0, 100) < 30) {
            $uhRef = 'UH-' . str_pad($index, 6, '0', STR_PAD_LEFT);
            $uhCall = 'PZ7.' . chr(65 + rand(0, 25)) . rand(1000, 9999);
        }

        // Related books (20% have relationships)
        $relatedSameVersion = '';
        $relatedOmnibus = '';
        if ($index > 100 && rand(0, 100) < 20) {
            $relatedSameVersion = 'TEST-' . str_pad(rand(1, $index - 1), 6, '0', STR_PAD_LEFT);
        }

        // Missing optional fields randomly for testing
        $pages = rand(0, 100) < 10 ? '' : rand(8, 300); // 10% missing pages
        $contact = rand(0, 100) < 70 ? '' : 'info@example.com'; // 70% missing contact

        // Some duplicate records for testing (every 500th and 501st are duplicates)
        $internalId = 'TEST-' . str_pad($index, 6, '0', STR_PAD_LEFT);
        if ($index > 1 && $index % 500 === 1) {
            $internalId = 'TEST-' . str_pad($index - 1, 6, '0', STR_PAD_LEFT); // Duplicate ID
        }

        return [
            $internalId, // ID
            'PALM' . $index, // PALM code
            $title, // Title
            $hasSubtitle ? "A Study of {$subject}" : '', // Sub-title
            $hasTranslation ? "{$title} (Translated)" : '', // Translated-title
            ['Book', 'Booklet', 'Poster', 'CD', 'DVD'][array_rand(['Book', 'Booklet', 'Poster', 'CD', 'DVD'])], // Physical_type
            'Collection ' . (($index % 10) + 1), // Collection
            $this->publishers[array_rand($this->publishers)], // Publisher
            '', // Publisher_program
            $year, // Year
            $pages, // Pages
            $description, // Description
            $hasTOC ? "Chapter 1: Introduction|Chapter 2: Main Content|Chapter 3: Conclusion" : '', // TOC
            $hasIssueNotes ? "First edition, printed in {$year}" : '', // Notes_issue
            '', // Notes_content
            $contact, // Contact
            $accessLevel, // Access_level
            '', // VLA_standard
            '', // VLA_benchmark
            $primaryLang, // Primary_language
            $primaryISO, // Primary_language_ISO
            $secondaryLang, // Secondary_language
            $secondaryISO, // Secondary_language_ISO
            '', // Additional_languages
            $author1, // Author 1
            $author2, // Author 2
            $author3, // Author 3
            $illustrators[0], // Illustrator 1
            $illustrators[1], // Illustrator 2
            $illustrators[2], // Illustrator 3
            $illustrators[3], // Illustrator 4
            $illustrators[4], // Illustrator 5
            $editor, // Editor
            $otherCreator, // Other_creator
            $otherRole, // Other_creator_role
            $purposes, // Purpose
            $genres, // Genre
            '', // Sub-genre
            $types, // Type
            $themes, // Themes
            $learnerLevel, // Learner_level
            rand(0, 100) < 40 ? $location : '', // Geographic_location_island
            '', // Geographic_location_state
            $keywords, // Keywords
            $pdfFilename, // PDF_filename
            '', // PDF_filename_alt
            'Digital Archive', // Digital_source
            '', // Digital_source_alt
            $thumbnailFilename, // Thumbnail_filename
            '', // Thumbnail_filename_alt
            '', // Audio_files
            '', // Video_URLs
            $uhRef, // UH_reference
            $uhCall, // UH_call_number
            '', // UH_notes
            '', // COM_reference
            '', // COM_call_number
            '', // COM_notes
            $relatedSameVersion, // Same_content_different_version
            $relatedOmnibus, // Omnibus
            '', // Supporting_book
            '', // Different_language_same_content
            $index % 20 === 0 ? 'true' : 'false', // Is_featured (5% featured)
            'true', // Is_active
            $index, // Sort_order
        ];
    }

    /**
     * Generate author name
     */
    protected function generateAuthorName(): string
    {
        $first = $this->authorFirstNames[array_rand($this->authorFirstNames)];
        $last = $this->authorLastNames[array_rand($this->authorLastNames)];
        return "{$first} {$last}";
    }

    /**
     * Generate description
     */
    protected function generateDescription(string $subject, string $location, int $sentences): string
    {
        $descriptions = [
            "This book explores {$subject} in {$location}.",
            "A comprehensive guide to understanding {$subject} for students and teachers.",
            "Learn about the fascinating world of {$subject} through stories and examples.",
            "This resource provides valuable insights into {$subject} and its importance in island communities.",
            "Discover the rich traditions and knowledge related to {$subject} in the Pacific region.",
            "An educational resource designed to help learners understand {$subject} concepts.",
            "This book combines traditional knowledge with modern perspectives on {$subject}.",
        ];

        $result = [];
        for ($i = 0; $i < $sentences; $i++) {
            $result[] = $descriptions[array_rand($descriptions)];
        }

        return implode(' ', $result);
    }

    /**
     * Get language ISO code
     */
    protected function getLanguageISO(string $language): string
    {
        $isoCodes = [
            'English' => 'en',
            'Chuukese' => 'chk',
            'Pohnpeian' => 'pon',
            'Yapese' => 'yap',
            'Kosraean' => 'kos',
            'Marshallese' => 'mh',
            'Palauan' => 'pau',
            'Chamorro' => 'ch',
            'Carolinian' => 'cal',
            'Kiribati' => 'gil',
        ];

        return $isoCodes[$language] ?? '';
    }

    /**
     * Get random classifications
     */
    protected function getRandomClassifications(array $options, int $min, int $max): string
    {
        $count = rand($min, $max);
        if ($count === 0) {
            return '';
        }

        $selected = array_rand(array_flip($options), $count);
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        return implode('|', $selected);
    }

    /**
     * Generate keywords
     */
    protected function generateKeywords(string $subject, string $location, int $count): string
    {
        $allKeywords = [
            $subject,
            $location,
            'education',
            'learning',
            'Pacific',
            'island',
            'culture',
            'traditional',
            'modern',
            'students',
            'teachers',
            'community',
            'language',
            'reading',
            'literacy',
        ];

        $selected = array_rand(array_flip($allKeywords), min($count, count($allKeywords)));
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        return implode('|', $selected);
    }
}
