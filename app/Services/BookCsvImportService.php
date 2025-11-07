<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Collection;
use App\Models\Publisher;
use App\Models\Language;
use App\Models\Creator;
use App\Models\ClassificationValue;
use App\Models\ClassificationType;
use App\Models\GeographicLocation;
use App\Models\BookKeyword;
use App\Models\BookFile;
use App\Models\LibraryReference;
use App\Models\CsvImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class BookCsvImportService
{
    use BookCsvImportValidation;
    use BookCsvImportRelationships;

    protected array $config;
    protected array $fieldMapping;
    protected string $separator;
    protected array $errors = [];
    protected array $warnings = [];
    protected int $currentRow = 0;
    protected ?CsvImport $importSession = null;

    public function __construct()
    {
        $this->config = config('csv-import');
        $this->fieldMapping = $this->config['field_mapping'];
        $this->separator = $this->config['separator'];
    }

    /**
     * Validate CSV file structure and content
     *
     * @param string $filePath
     * @return array Validation result with errors and warnings
     */
    public function validateCsv(string $filePath): array
    {
        $this->errors = [];
        $this->warnings = [];

        try {
            // Check file exists
            if (!file_exists($filePath)) {
                $this->errors[] = "File not found: {$filePath}";
                return $this->getValidationResult();
            }

            // Check file size
            $fileSize = filesize($filePath);
            if ($fileSize > $this->config['max_file_size']) {
                $maxSizeMB = $this->config['max_file_size'] / 1048576;
                $this->errors[] = "File size ({$fileSize} bytes) exceeds maximum allowed size ({$maxSizeMB}MB)";
                return $this->getValidationResult();
            }

            // Open and parse CSV
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                $this->errors[] = "Unable to open file for reading";
                return $this->getValidationResult();
            }

            // Read and validate headers
            $headers = fgetcsv($handle);
            if (!$headers) {
                $this->errors[] = "CSV file appears to be empty or improperly formatted";
                fclose($handle);
                return $this->getValidationResult();
            }

            // Validate required columns exist
            $this->validateHeaders($headers);

            // Read second row (database mapping row or first data row)
            $secondRow = fgetcsv($handle);
            $isSecondRowMapping = $this->isHeaderRow($secondRow);

            // If second row is database mapping, skip it
            $dataStartRow = $isSecondRowMapping ? 3 : 2;

            // Validate data rows
            rewind($handle);
            fgetcsv($handle); // Skip header
            if ($isSecondRowMapping) {
                fgetcsv($handle); // Skip mapping row
            }

            $rowCount = 0;
            $this->currentRow = $dataStartRow;

            while (($row = fgetcsv($handle)) !== false) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $rowCount++;
                $this->currentRow++;

                // Validate row structure
                if (count($row) !== count($headers)) {
                    $this->warnings[] = "Row {$this->currentRow}: Column count mismatch (expected " . count($headers) . ", got " . count($row) . ")";
                }

                // Validate data types and required fields
                $this->validateRow($row, $headers);

                // Limit validation to first 100 rows for performance
                if ($rowCount >= 100) {
                    $this->warnings[] = "Validation limited to first 100 rows for performance. Full validation will occur during import.";
                    break;
                }
            }

            fclose($handle);

            if ($rowCount === 0) {
                $this->errors[] = "No data rows found in CSV file";
            }

        } catch (Exception $e) {
            $this->errors[] = "Validation error: " . $e->getMessage();
            Log::error('CSV Validation Error', ['exception' => $e]);
        }

        return $this->getValidationResult();
    }

    /**
     * Import CSV file into database
     *
     * @param string $filePath
     * @param array $options
     * @param int|null $userId
     * @return CsvImport
     */
    public function importCsv(string $filePath, array $options = [], ?int $userId = null): CsvImport
    {
        // Create import session
        $this->importSession = CsvImport::create([
            'user_id' => $userId ?? auth()->id() ?? 1,
            'filename' => basename($filePath),
            'original_filename' => $options['original_filename'] ?? basename($filePath),
            'file_path' => $filePath,
            'file_size' => filesize($filePath),
            'mode' => $options['mode'] ?? $this->config['default_mode'],
            'options' => array_merge($this->config['options'], $options),
            'status' => 'processing',
            'started_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            // Validate first
            $validation = $this->validateCsv($filePath);
            if (!empty($validation['errors'])) {
                $this->importSession->update([
                    'status' => 'failed',
                    'validation_errors' => json_encode($validation['errors']),
                    'completed_at' => now(),
                ]);
                return $this->importSession;
            }

            // Mark as processing
            $this->importSession->markAsProcessing();

            // Process the CSV file
            $this->processCsvFile($filePath, $options);

            // Mark as completed
            $this->importSession->markAsCompleted();

        } catch (Exception $e) {
            Log::error('CSV Import Error', [
                'import_id' => $this->importSession->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->importSession->markAsFailed($e->getMessage());
        }

        return $this->importSession;
    }

    /**
     * Process CSV file in batches
     *
     * @param string $filePath
     * @param array $options
     * @return void
     */
    protected function processCsvFile(string $filePath, array $options): void
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);

        // Check if second row is database mapping
        $secondRow = fgetcsv($handle);
        if (!$this->isHeaderRow($secondRow)) {
            rewind($handle);
            fgetcsv($handle); // Skip header row
        }

        $batch = [];
        $batchSize = $this->config['batch_size'];
        $totalRows = 0;
        $this->currentRow = $this->isHeaderRow($secondRow) ? 3 : 2;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                $this->currentRow++;
                continue;
            }

            $batch[] = [
                'row_number' => $this->currentRow,
                'data' => $this->mapRowToArray($row, $headers),
            ];

            $totalRows++;
            $this->currentRow++;

            // Process batch when it reaches batch size
            if (count($batch) >= $batchSize) {
                $this->processBatch($batch, $options);
                $batch = [];
            }
        }

        // Process remaining rows
        if (!empty($batch)) {
            $this->processBatch($batch, $options);
        }

        fclose($handle);

        // Update total rows
        $this->importSession->update(['total_rows' => $totalRows]);
    }

    /**
     * Process a batch of rows
     *
     * @param array $batch
     * @param array $options
     * @return void
     */
    protected function processBatch(array $batch, array $options): void
    {
        $mode = $options['mode'] ?? $this->config['default_mode'];

        foreach ($batch as $item) {
            $rowNumber = $item['row_number'];
            $data = $item['data'];

            try {
                DB::beginTransaction();

                // Process single row
                $result = $this->processRow($data, $mode, $options);

                if ($result['success']) {
                    if ($result['action'] === 'created') {
                        $this->importSession->incrementCreated();
                    } elseif ($result['action'] === 'updated') {
                        $this->importSession->incrementUpdated();
                    } elseif ($result['action'] === 'skipped') {
                        $this->importSession->incrementSkipped();
                    }
                } else {
                    $this->importSession->incrementFailed();
                    $this->importSession->addError($rowNumber, 'general', $result['error'] ?? 'Unknown error');
                }

                DB::commit();

            } catch (Exception $e) {
                DB::rollBack();
                $this->importSession->incrementFailed();
                $this->importSession->addError($rowNumber, 'exception', $e->getMessage());
                Log::error('Row Import Error', [
                    'row' => $rowNumber,
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Process a single row of data
     *
     * @param array $data
     * @param string $mode
     * @param array $options
     * @return array
     */
    protected function processRow(array $data, string $mode, array $options): array
    {
        // Extract book core fields
        $bookData = $this->extractBookData($data);

        // Find existing book if updating
        $existingBook = $this->findExistingBook($bookData);

        // Determine action based on mode
        if ($mode === 'create_only' && $existingBook) {
            return ['success' => true, 'action' => 'skipped', 'message' => 'Book already exists'];
        }

        if ($mode === 'update_only' && !$existingBook) {
            return ['success' => true, 'action' => 'skipped', 'message' => 'Book not found for update'];
        }

        // Create or update book
        if ($existingBook && $mode !== 'create_duplicates') {
            $book = $this->updateBook($existingBook, $bookData, $data, $options);
            $action = 'updated';
        } else {
            $book = $this->createBook($bookData, $data, $options);
            $action = 'created';
        }

        return ['success' => true, 'action' => $action, 'book_id' => $book->id];
    }

    /**
     * Create a new book
     *
     * @param array $bookData
     * @param array $fullData
     * @param array $options
     * @return Book
     */
    protected function createBook(array $bookData, array $fullData, array $options): Book
    {
        // Create book
        $book = Book::create($bookData);

        // Attach relationships
        $this->attachRelationships($book, $fullData, $options);

        return $book;
    }

    /**
     * Update existing book
     *
     * @param Book $book
     * @param array $bookData
     * @param array $fullData
     * @param array $options
     * @return Book
     */
    protected function updateBook(Book $book, array $bookData, array $fullData, array $options): Book
    {
        // Update book data
        $book->update($bookData);

        // Update relationships (detach and reattach)
        $this->attachRelationships($book, $fullData, $options, true);

        return $book;
    }

    /**
     * Attach relationships to book
     *
     * @param Book $book
     * @param array $data
     * @param array $options
     * @param bool $isUpdate
     * @return void
     */
    protected function attachRelationships(Book $book, array $data, array $options, bool $isUpdate = false): void
    {
        // Collection
        if (!empty($data['collection'])) {
            $collection = $this->resolveCollection($data['collection'], $options);
            if ($collection) {
                $book->collection_id = $collection->id;
                $book->save();
            }
        }

        // Publisher
        if (!empty($data['publisher'])) {
            $publisher = $this->resolvePublisher($data['publisher'], $data['publisher_program'] ?? null, $options);
            if ($publisher) {
                $book->publisher_id = $publisher->id;
                $book->save();
            }
        }

        // Languages
        $this->attachLanguages($book, $data, $options, $isUpdate);

        // Creators (Authors, Illustrators, etc.)
        $this->attachCreators($book, $data, $options, $isUpdate);

        // Classifications
        $this->attachClassifications($book, $data, $options, $isUpdate);

        // Geographic Locations
        $this->attachGeographicLocations($book, $data, $options, $isUpdate);

        // Keywords
        $this->attachKeywords($book, $data, $isUpdate);

        // Files
        $this->attachFiles($book, $data, $options);

        // Library References
        $this->attachLibraryReferences($book, $data, $options, $isUpdate);

        // Book Relationships
        $this->attachBookRelationships($book, $data, $isUpdate);
    }

    /**
     * Extract core book data from row
     *
     * @param array $data
     * @return array
     */
    protected function extractBookData(array $data): array
    {
        $bookData = [];

        // Direct field mappings
        $directFields = [
            'internal_id',
            'palm_code',
            'title',
            'subtitle',
            'translated_title',
            'physical_type',
            'publication_year',
            'pages',
            'description',
            'toc',
            'notes_issue',
            'notes_content',
            'contact',
            'vla_standard',
            'vla_benchmark',
        ];

        foreach ($directFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $bookData[$field] = $data[$field];
            }
        }

        // Handle access level mapping (Y/N/L → full/unavailable/limited)
        if (isset($data['access_level'])) {
            $accessLevelMapping = $this->config['access_level_mapping'];
            $bookData['access_level'] = $accessLevelMapping[$data['access_level']] ?? 'unavailable';
        }

        // Handle physical type mapping (normalize to lowercase)
        if (isset($data['physical_type'])) {
            $physicalTypeMapping = $this->config['physical_type_mapping'];
            $bookData['physical_type'] = $physicalTypeMapping[$data['physical_type']] ?? 'book';
        }

        // Clean year (remove question marks)
        if (isset($bookData['publication_year'])) {
            $bookData['publication_year'] = (int) str_replace('?', '', $bookData['publication_year']);
        }

        // Set defaults
        $bookData['is_active'] = true;
        $bookData['is_featured'] = false;

        return $bookData;
    }

    /**
     * Find existing book by internal_id or palm_code
     *
     * @param array $bookData
     * @return Book|null
     */
    protected function findExistingBook(array $bookData): ?Book
    {
        // Try by internal_id first
        if (!empty($bookData['internal_id'])) {
            $book = Book::where('internal_id', $bookData['internal_id'])->first();
            if ($book) {
                return $book;
            }
        }

        // Try by palm_code
        if (!empty($bookData['palm_code']) && $bookData['palm_code'] !== 'unavailable') {
            $book = Book::where('palm_code', $bookData['palm_code'])->first();
            if ($book) {
                return $book;
            }
        }

        return null;
    }

    /**
     * Set import session (for queue jobs)
     *
     * @param CsvImport $importSession
     * @return void
     */
    public function setImportSession(CsvImport $importSession): void
    {
        $this->importSession = $importSession;
    }

    /**
     * Dispatch import to queue
     *
     * @param string $filePath
     * @param array $options
     * @param int|null $userId
     * @return CsvImport
     */
    public function importCsvAsync(string $filePath, array $options = [], ?int $userId = null): CsvImport
    {
        // Create import session
        $importSession = CsvImport::create([
            'user_id' => $userId ?? auth()->id() ?? 1,
            'filename' => basename($filePath),
            'original_filename' => $options['original_filename'] ?? basename($filePath),
            'file_path' => $filePath,
            'file_size' => filesize($filePath),
            'mode' => $options['mode'] ?? $this->config['default_mode'],
            'options' => array_merge($this->config['options'], $options),
            'status' => 'pending',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Dispatch to queue
        \App\Jobs\ImportBooksFromCsvJob::dispatch($importSession, $filePath, $options);

        return $importSession;
    }

    /**
     * Get import session progress
     *
     * @param int $importId
     * @return array
     */
    public function getImportProgress(int $importId): array
    {
        $import = CsvImport::find($importId);

        if (!$import) {
            return [
                'found' => false,
                'message' => 'Import session not found',
            ];
        }

        return [
            'found' => true,
            'import_id' => $import->id,
            'status' => $import->status,
            'progress' => [
                'total' => $import->total_rows,
                'processed' => $import->processed_rows,
                'successful' => $import->successful_rows,
                'failed' => $import->failed_rows,
                'skipped' => $import->skipped_rows,
                'created' => $import->created_count,
                'updated' => $import->updated_count,
            ],
            'percentage' => $import->total_rows > 0
                ? round(($import->processed_rows / $import->total_rows) * 100, 2)
                : 0,
            'success_rate' => $import->getSuccessRate(),
            'timing' => [
                'started_at' => $import->started_at?->toISOString(),
                'completed_at' => $import->completed_at?->toISOString(),
                'duration_seconds' => $import->duration_seconds,
            ],
            'is_complete' => $import->isComplete(),
            'is_failed' => $import->isFailed(),
            'is_processing' => $import->isProcessing(),
        ];
    }

    /**
     * Cancel an in-progress import
     *
     * @param int $importId
     * @return bool
     */
    public function cancelImport(int $importId): bool
    {
        $import = CsvImport::find($importId);

        if (!$import || !$import->isProcessing()) {
            return false;
        }

        $import->update([
            'status' => 'cancelled',
            'completed_at' => now(),
            'duration_seconds' => $import->started_at ? now()->diffInSeconds($import->started_at) : null,
        ]);

        return true;
    }
}
