<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for handling book duplication operations
 *
 * This service manages the complex process of duplicating books with all their
 * relationships while ensuring data integrity and preventing common errors.
 */
class BookDuplicationService
{
    /**
     * Duplicate a book with all specified relationships and classifications
     *
     * @param Book $sourceBook The book to duplicate
     * @param array $options Duplication options
     * @return Book The newly created duplicate book
     * @throws Exception If duplication fails
     */
    public function duplicate(Book $sourceBook, array $options = []): Book
    {
        // Set default options
        $options = array_merge($this->getDefaultOptions(), $options);

        try {
            return DB::transaction(function () use ($sourceBook, $options) {
                // Step 1: Create the duplicate book record
                $duplicateBook = $this->createDuplicateRecord($sourceBook, $options);

                // Step 2: Copy relationships based on options
                if ($options['copy_creators']) {
                    $this->copyCreators($sourceBook, $duplicateBook);
                }

                if ($options['copy_languages']) {
                    $this->copyLanguages($sourceBook, $duplicateBook);
                }

                if ($options['copy_classifications']) {
                    $this->copyClassifications($sourceBook, $duplicateBook);
                }

                if ($options['copy_geographic_locations']) {
                    $this->copyGeographicLocations($sourceBook, $duplicateBook);
                }

                if ($options['copy_keywords']) {
                    $this->copyKeywords($sourceBook, $duplicateBook);
                }

                if ($options['copy_library_references']) {
                    $this->copyLibraryReferences($sourceBook, $duplicateBook);
                }

                if ($options['copy_files']) {
                    $this->copyFiles($sourceBook, $duplicateBook, $options);
                }

                // Step 3: Log the duplication for audit trail
                $this->logDuplication($sourceBook, $duplicateBook);

                return $duplicateBook->fresh(); // Reload with all relationships
            });
        } catch (Exception $e) {
            Log::error('Book duplication failed', [
                'source_book_id' => $sourceBook->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new Exception("Failed to duplicate book: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get default duplication options
     *
     * @return array Default options
     */
    protected function getDefaultOptions(): array
    {
        return [
            // Relationships to copy
            'copy_creators' => true,           // Authors, illustrators, editors
            'copy_languages' => true,          // Languages
            'copy_classifications' => true,    // Purpose, genre, type, learner level, etc.
            'copy_geographic_locations' => true, // Islands, states
            'copy_keywords' => true,           // Keywords
            'copy_library_references' => false, // Library catalog references (usually unique)
            'copy_files' => false,             // PDF, thumbnails (NOT recommended)

            // Field handling
            'clear_title' => true,             // Clear title (must be unique)
            'clear_identifiers' => true,       // Clear internal_id, palm_code
            'clear_statistics' => true,        // Reset view_count, download_count
            'append_copy_suffix' => true,      // Add " (Copy)" to title if not cleared

            // Content handling
            'copy_description' => true,        // Copy description (should be reviewed)
            'copy_toc' => true,               // Copy table of contents
            'copy_notes' => true,             // Copy notes fields
            'mark_for_review' => true,        // Add note about needing review
        ];
    }

    /**
     * Create the duplicate book record with appropriate fields cleared
     *
     * @param Book $sourceBook
     * @param array $options
     * @return Book
     */
    protected function createDuplicateRecord(Book $sourceBook, array $options): Book
    {
        // Get all attributes from source book
        $attributes = $sourceBook->getAttributes();

        // Remove primary key and timestamps (will be auto-generated)
        unset($attributes['id']);
        unset($attributes['created_at']);
        unset($attributes['updated_at']);

        // Clear unique identifiers
        if ($options['clear_identifiers']) {
            $attributes['internal_id'] = null;
            $attributes['palm_code'] = null;
        }

        // Clear or modify title
        if ($options['clear_title']) {
            $attributes['title'] = null;
            $attributes['slug'] = null; // Will be auto-generated from title
        } elseif ($options['append_copy_suffix']) {
            $attributes['title'] = $attributes['title'] . ' (Copy)';
            $attributes['slug'] = null; // Will be regenerated
        }

        // Clear statistics
        if ($options['clear_statistics']) {
            $attributes['view_count'] = 0;
            $attributes['download_count'] = 0;
        }

        // Clear publication year (should be set manually for duplicates)
        // Keep it for now but mark that it should be reviewed
        // We'll add a note in the notes_issue field

        // Add duplication tracking
        $attributes['duplicated_from_book_id'] = $sourceBook->id;
        $attributes['duplicated_at'] = now();

        // Add review note if enabled
        if ($options['mark_for_review'] && $options['copy_notes']) {
            $reviewNote = "\n\n[DUPLICATED from \"{$sourceBook->title}\" on " . now()->format('Y-m-d') . " - Please review all fields]";
            $attributes['notes_issue'] = ($attributes['notes_issue'] ?? '') . $reviewNote;
        }

        // Create the new book
        $duplicateBook = new Book();
        $duplicateBook->fill($attributes);
        $duplicateBook->save();

        return $duplicateBook;
    }

    /**
     * Copy creator relationships (authors, illustrators, editors)
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function copyCreators(Book $sourceBook, Book $duplicateBook): void
    {
        $creators = $sourceBook->bookCreators()->get();

        foreach ($creators as $bookCreator) {
            $duplicateBook->bookCreators()->create([
                'creator_id' => $bookCreator->creator_id,
                'creator_type' => $bookCreator->creator_type,
                'role_description' => $bookCreator->role_description,
                'sort_order' => $bookCreator->sort_order,
            ]);
        }
    }

    /**
     * Copy language relationships
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function copyLanguages(Book $sourceBook, Book $duplicateBook): void
    {
        $languages = $sourceBook->bookLanguages()->get();

        foreach ($languages as $bookLanguage) {
            $duplicateBook->bookLanguages()->create([
                'language_id' => $bookLanguage->language_id,
                'is_primary' => $bookLanguage->is_primary,
            ]);
        }
    }

    /**
     * Copy classification relationships (purpose, genre, type, learner level, etc.)
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function copyClassifications(Book $sourceBook, Book $duplicateBook): void
    {
        $classifications = $sourceBook->bookClassifications()->get();

        foreach ($classifications as $classification) {
            $duplicateBook->bookClassifications()->create([
                'classification_value_id' => $classification->classification_value_id,
            ]);
        }
    }

    /**
     * Copy geographic location relationships
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function copyGeographicLocations(Book $sourceBook, Book $duplicateBook): void
    {
        $locations = $sourceBook->bookLocations()->get();

        foreach ($locations as $location) {
            $duplicateBook->bookLocations()->create([
                'location_id' => $location->location_id,
            ]);
        }
    }

    /**
     * Copy keywords
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function copyKeywords(Book $sourceBook, Book $duplicateBook): void
    {
        $keywords = $sourceBook->keywords()->get();

        foreach ($keywords as $keyword) {
            $duplicateBook->keywords()->create([
                'keyword' => $keyword->keyword,
            ]);
        }
    }

    /**
     * Copy library references
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function copyLibraryReferences(Book $sourceBook, Book $duplicateBook): void
    {
        $references = $sourceBook->libraryReferences()->get();

        foreach ($references as $reference) {
            $duplicateBook->libraryReferences()->create([
                'library_code' => $reference->library_code,
                'library_name' => $reference->library_name,
                'reference_number' => $reference->reference_number,
                'call_number' => $reference->call_number,
                'catalog_link' => $reference->catalog_link,
                'notes' => $reference->notes,
            ]);
        }
    }

    /**
     * Copy file relationships (PDF, thumbnails, etc.)
     *
     * WARNING: This does NOT copy the actual files, only the database records.
     * This can lead to multiple books pointing to the same file, which may not be desired.
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @param array $options
     * @return void
     */
    protected function copyFiles(Book $sourceBook, Book $duplicateBook, array $options): void
    {
        // Only copy file records if explicitly requested
        // This is NOT recommended as it creates shared file references

        if (!$options['copy_files']) {
            return;
        }

        Log::warning('Copying file references for book duplication', [
            'source_book_id' => $sourceBook->id,
            'duplicate_book_id' => $duplicateBook->id,
            'warning' => 'Multiple books will share the same file paths'
        ]);

        $files = $sourceBook->files()->get();

        foreach ($files as $file) {
            $duplicateBook->files()->create([
                'file_type' => $file->file_type,
                'file_path' => $file->file_path,
                'external_url' => $file->external_url,
                'digital_source' => $file->digital_source,
                'filename' => $file->filename,
                'is_primary' => $file->is_primary,
                'is_active' => $file->is_active,
            ]);
        }
    }

    /**
     * Log duplication for audit trail
     *
     * @param Book $sourceBook
     * @param Book $duplicateBook
     * @return void
     */
    protected function logDuplication(Book $sourceBook, Book $duplicateBook): void
    {
        Log::info('Book duplicated successfully', [
            'source_book_id' => $sourceBook->id,
            'source_book_title' => $sourceBook->title,
            'duplicate_book_id' => $duplicateBook->id,
            'duplicate_book_title' => $duplicateBook->title,
            'duplicated_at' => $duplicateBook->duplicated_at,
            'user_id' => auth()->id() ?? null,
        ]);
    }

    /**
     * Get all books that were duplicated from a specific source book
     *
     * @param Book $sourceBook
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDuplicates(Book $sourceBook)
    {
        return Book::where('duplicated_from_book_id', $sourceBook->id)
            ->orderBy('duplicated_at', 'desc')
            ->get();
    }

    /**
     * Get the original source book (traverses duplication chain)
     *
     * @param Book $book
     * @return Book|null The original source book, or null if this is not a duplicate
     */
    public function getOriginalSource(Book $book): ?Book
    {
        if (!$book->duplicated_from_book_id) {
            return null; // This is not a duplicate
        }

        // Traverse the chain to find the original
        $current = $book;
        $visited = [$current->id]; // Prevent infinite loops

        while ($current->duplicated_from_book_id) {
            if (in_array($current->duplicated_from_book_id, $visited)) {
                // Circular reference detected
                Log::error('Circular duplication reference detected', [
                    'book_id' => $book->id,
                    'chain' => $visited
                ]);
                break;
            }

            $source = Book::find($current->duplicated_from_book_id);

            if (!$source) {
                // Source book was deleted
                break;
            }

            $visited[] = $source->id;
            $current = $source;
        }

        return $current->id !== $book->id ? $current : null;
    }

    /**
     * Get duplication statistics for a book
     *
     * @param Book $book
     * @return array Statistics about duplication
     */
    public function getDuplicationStats(Book $book): array
    {
        return [
            'is_duplicate' => !is_null($book->duplicated_from_book_id),
            'duplicated_from' => $book->duplicated_from_book_id
                ? Book::find($book->duplicated_from_book_id)?->title
                : null,
            'duplicated_at' => $book->duplicated_at,
            'times_duplicated' => $this->getDuplicates($book)->count(),
            'duplicates' => $this->getDuplicates($book)->pluck('title', 'id')->toArray(),
            'original_source' => $this->getOriginalSource($book)?->title,
        ];
    }

    /**
     * Bulk duplicate multiple books
     *
     * @param array $bookIds Array of book IDs to duplicate
     * @param array $options Duplication options
     * @return array Array of ['success' => [...], 'failed' => [...]]
     */
    public function bulkDuplicate(array $bookIds, array $options = []): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($bookIds as $bookId) {
            try {
                $sourceBook = Book::findOrFail($bookId);
                $duplicate = $this->duplicate($sourceBook, $options);

                $results['success'][] = [
                    'source_id' => $sourceBook->id,
                    'source_title' => $sourceBook->title,
                    'duplicate_id' => $duplicate->id,
                    'duplicate_title' => $duplicate->title,
                ];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'book_id' => $bookId,
                    'error' => $e->getMessage(),
                ];

                Log::error('Bulk duplication failed for book', [
                    'book_id' => $bookId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Validate that a book can be safely duplicated
     *
     * @param Book $book
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateForDuplication(Book $book): array
    {
        $errors = [];

        // Check if book has required relationships
        if ($book->languages()->count() === 0) {
            $errors[] = 'Book must have at least one language';
        }

        // Check if book has minimum required fields
        if (empty($book->title)) {
            $errors[] = 'Book must have a title';
        }

        // Warn if book is already a duplicate
        if ($book->duplicated_from_book_id) {
            $errors[] = 'Warning: This book is already a duplicate. Consider duplicating from the original instead.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
