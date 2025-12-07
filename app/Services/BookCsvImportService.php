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

    // Performance tracking
    protected int $startMemory = 0;
    protected int $peakMemory = 0;
    protected float $startTime = 0;
    protected int $processedCount = 0;

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

            // Remove BOM from first header if present
            $headers = $this->removeBomFromHeaders($headers);

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
        // Initialize performance tracking
        $this->startPerformanceTracking();

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

        // Create backup before import if enabled
        $createBackup = $options['create_backup'] ?? $this->config['backup']['create_before_import'] ?? false;
        if ($createBackup) {
            try {
                $backupService = app(DatabaseBackupService::class);
                $backupResult = $backupService->createBackupBeforeImport($this->importSession->id);

                if ($backupResult['success']) {
                    Log::info('Created backup before import', [
                        'import_id' => $this->importSession->id,
                        'backup_file' => $backupResult['filename'],
                        'backup_size' => $backupResult['size_mb'] . ' MB',
                    ]);
                } else {
                    Log::warning('Failed to create backup before import', [
                        'import_id' => $this->importSession->id,
                        'error' => $backupResult['error'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Backup creation failed', [
                    'import_id' => $this->importSession->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Apply database optimizations if enabled
        $optimizationsEnabled = $options['enable_db_optimizations'] ?? $this->config['performance']['enable_db_optimizations'] ?? true;
        if ($optimizationsEnabled) {
            $this->enableDatabaseOptimizations();
        }

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

            // Mark as completed with performance metrics
            $metrics = $this->getPerformanceMetrics();
            $this->importSession->update([
                'status' => 'completed',
                'completed_at' => now(),
                'duration_seconds' => now()->diffInSeconds($this->importSession->started_at),
                'performance_metrics' => $metrics,
            ]);

            // Disable database optimizations if enabled
            if ($optimizationsEnabled) {
                $this->disableDatabaseOptimizations();
            }

            // Run post-import quality checks if enabled
            if ($options['run_quality_checks'] ?? true) {
                $this->runQualityChecks();
            }

        } catch (Exception $e) {
            // Disable database optimizations on error
            if ($optimizationsEnabled) {
                $this->disableDatabaseOptimizations();
            }
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

        // Remove BOM from first header if present
        $headers = $this->removeBomFromHeaders($headers);

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

            // Extract book title for error reporting
            $bookTitle = $data['title'] ?? 'Unknown';
            $bookId = $data['internal_id'] ?? $data['palm_code'] ?? 'Unknown ID';

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
                    $errorMessage = ($result['error'] ?? 'Unknown error') . " | Book: \"{$bookTitle}\" (ID: {$bookId})";
                    $this->importSession->addError($rowNumber, $result['column'] ?? 'general', $errorMessage);
                }

                // Update performance tracking
                $this->updatePerformanceTracking();

                DB::commit();

            } catch (Exception $e) {
                DB::rollBack();
                $this->importSession->incrementFailed();
                $errorMessage = $e->getMessage() . " | Book: \"{$bookTitle}\" (ID: {$bookId})";
                $this->importSession->addError($rowNumber, 'exception', $errorMessage);
                Log::error('Row Import Error', [
                    'row' => $rowNumber,
                    'book_title' => $bookTitle,
                    'book_id' => $bookId,
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

        // Book Identifiers (NEW: OCLC, ISBN, Other)
        $this->attachBookIdentifiers($book, $data, $isUpdate);

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
            'description',          // NEW: Now separate from abstract
            'abstract',             // NEW: Separate abstract field
            'toc',
            'notes_issue',
            'notes_version',        // NEW: Notes related to version
            'notes_content',
            'contact',
            'vla_standard',
            'vla_benchmark',
        ];

        foreach ($directFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                // Clean and convert character encoding from Windows-1252 to UTF-8
                $value = $this->cleanTextEncoding($data[$field]);

                // Special handling for palm_code: convert "unavailable" to null to avoid duplicates
                if ($field === 'palm_code' && strtolower($value) === 'unavailable') {
                    $bookData[$field] = null;
                } else {
                    $bookData[$field] = $value;
                }
            }
        }

        // Handle access level mapping (Y/N/L → full/unavailable/limited)
        if (isset($data['access_level'])) {
            $accessLevelMapping = $this->config['access_level_mapping'];
            $bookData['access_level'] = $accessLevelMapping[$data['access_level']] ?? 'unavailable';
        }

        // Handle physical type - normalize to allowed values
        if (isset($data['physical_type']) && !empty($data['physical_type'])) {
            $physicalType = strtolower(trim($data['physical_type']));

            // Map common variations to standard values
            $physicalTypeMap = [
                'book' => 'book',
                'books' => 'book',
                'journal' => 'journal',
                'journals' => 'journal',
                'magazine' => 'magazine',
                'magazines' => 'magazine',
                'workbook' => 'workbook',
                'workbooks' => 'workbook',
                'poster' => 'poster',
                'posters' => 'poster',
            ];

            // Use mapped value or 'other' if not recognized
            $bookData['physical_type'] = $physicalTypeMap[$physicalType] ?? 'other';
        }

        // Clean year (remove question marks)
        if (isset($bookData['publication_year'])) {
            $bookData['publication_year'] = (int) str_replace('?', '', $bookData['publication_year']);
        }

        // Clean pages field (handle n/a, unknown, etc.)
        if (isset($bookData['pages'])) {
            $bookData['pages'] = $this->cleanIntegerField($bookData['pages']);
        }

        // Set defaults
        $bookData['is_active'] = true;
        $bookData['is_featured'] = false;

        return $bookData;
    }

    /**
     * Find existing book by internal_id only
     *
     * NOTE: palm_code is NOT used for matching because it represents the base book,
     * not specific editions. Multiple books can share the same palm_code but have
     * different internal_ids (e.g., P011-a and P011-d both have palm_code TAW3).
     * Each internal_id represents a unique edition/version and should be treated
     * as a separate book.
     *
     * @param array $bookData
     * @return Book|null
     */
    protected function findExistingBook(array $bookData): ?Book
    {
        // Match ONLY by internal_id since it's the unique identifier for each edition
        if (!empty($bookData['internal_id'])) {
            $book = Book::where('internal_id', $bookData['internal_id'])->first();
            if ($book) {
                return $book;
            }
        }

        return null;
    }

    /**
     * Preview what changes would be made without actually importing
     *
     * @param string $filePath
     * @param string $mode
     * @return array
     */
    public function previewCsv(string $filePath, string $mode = 'upsert'): array
    {
        $preview = [
            'stats' => [
                'will_create' => 0,
                'will_update' => 0,
                'will_skip' => 0,
                'total' => 0,
            ],
            'creates' => [],
            'updates' => [],
            'skips' => [],
        ];

        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new Exception("Unable to open file: {$filePath}");
            }

            // Read headers
            $headers = fgetcsv($handle);

            // Remove BOM from first header if present
            $headers = $this->removeBomFromHeaders($headers);

            // Check for database mapping row
            $secondRow = fgetcsv($handle);
            $isSecondRowMapping = $this->isHeaderRow($secondRow);

            if (!$isSecondRowMapping) {
                // Reset to read second row as data
                fseek($handle, 0);
                fgetcsv($handle); // Skip header
            }

            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) {
                    continue; // Skip empty rows
                }

                $preview['stats']['total']++;

                // Map row to data
                $data = array_combine($headers, $row);
                $bookData = $this->extractBookData($data);

                // Find existing book
                $existingBook = $this->findExistingBook($bookData);

                // Determine action
                if ($mode === 'create_only' && $existingBook) {
                    $preview['stats']['will_skip']++;
                    $preview['skips'][] = [
                        'title' => $bookData['title'],
                        'id' => $bookData['internal_id'] ?? $bookData['palm_code'],
                        'reason' => 'Already exists',
                    ];
                } elseif ($mode === 'update_only' && !$existingBook) {
                    $preview['stats']['will_skip']++;
                    $preview['skips'][] = [
                        'title' => $bookData['title'],
                        'id' => $bookData['internal_id'] ?? $bookData['palm_code'],
                        'reason' => 'Not found for update',
                    ];
                } elseif ($existingBook && $mode !== 'create_duplicates') {
                    // Will update
                    $preview['stats']['will_update']++;
                    $changes = $this->detectChanges($existingBook, $bookData);
                    $preview['updates'][] = [
                        'id' => $existingBook->internal_id ?? $existingBook->palm_code,
                        'title' => $existingBook->title,
                        'changes' => $changes,
                    ];
                } else {
                    // Will create
                    $preview['stats']['will_create']++;
                    $preview['creates'][] = [
                        'title' => $bookData['title'],
                        'internal_id' => $bookData['internal_id'] ?? null,
                        'palm_code' => $bookData['palm_code'] ?? null,
                    ];
                }
            }

            fclose($handle);

        } catch (Exception $e) {
            Log::error('Preview CSV Error', ['exception' => $e->getMessage()]);
            throw $e;
        }

        return $preview;
    }

    /**
     * Detect changes between existing book and new data
     *
     * @param Book $book
     * @param array $newData
     * @return array
     */
    protected function detectChanges(Book $book, array $newData): array
    {
        $changes = [];

        // Compare each field
        foreach ($newData as $field => $newValue) {
            $oldValue = $book->$field ?? null;

            // Normalize for comparison
            if ($oldValue != $newValue) {
                // Skip if both are empty
                if (empty($oldValue) && empty($newValue)) {
                    continue;
                }

                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
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

    /**
     * Run post-import quality checks
     *
     * @return void
     */
    protected function runQualityChecks(): void
    {
        if (!$this->importSession) {
            return;
        }

        try {
            Log::info('Running post-import quality checks', [
                'import_id' => $this->importSession->id,
            ]);

            // Get books that were created or updated in this import
            // For now, we'll run quality checks on all books since we don't track
            // which books were affected by a specific import
            // This could be optimized by tracking book IDs during import

            $qualityService = app(\App\Services\DataQualityService::class);

            // Run quality checks
            $report = $qualityService->runQualityChecks(
                null, // Check all books (could be optimized to check only affected books)
                $this->importSession->id,
                false // Don't clear existing issues
            );

            Log::info('Post-import quality checks completed', [
                'import_id' => $this->importSession->id,
                'total_issues' => $report['total_issues_found'],
                'critical_issues' => $report['critical_issues'],
                'warnings' => $report['warnings'],
            ]);

        } catch (\Exception $e) {
            Log::error('Post-import quality check failed', [
                'import_id' => $this->importSession->id,
                'exception' => $e->getMessage(),
            ]);
            // Don't fail the import if quality checks fail
        }
    }

    /**
     * Initialize performance tracking
     *
     * @return void
     */
    protected function startPerformanceTracking(): void
    {
        $this->startMemory = memory_get_usage(true);
        $this->startTime = microtime(true);
        $this->processedCount = 0;
        $this->peakMemory = $this->startMemory;
    }

    /**
     * Update performance tracking
     *
     * @return void
     */
    protected function updatePerformanceTracking(): void
    {
        $this->processedCount++;
        $currentMemory = memory_get_usage(true);
        if ($currentMemory > $this->peakMemory) {
            $this->peakMemory = $currentMemory;
        }
    }

    /**
     * Get performance metrics
     *
     * @return array
     */
    protected function getPerformanceMetrics(): array
    {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $duration = $endTime - $this->startTime;

        return [
            'start_memory_mb' => round($this->startMemory / 1024 / 1024, 2),
            'end_memory_mb' => round($endMemory / 1024 / 1024, 2),
            'peak_memory_mb' => round($this->peakMemory / 1024 / 1024, 2),
            'memory_used_mb' => round(($this->peakMemory - $this->startMemory) / 1024 / 1024, 2),
            'duration_seconds' => round($duration, 2),
            'rows_processed' => $this->processedCount,
            'rows_per_second' => $this->processedCount > 0 && $duration > 0
                ? round($this->processedCount / $duration, 2)
                : 0,
        ];
    }

    /**
     * Enable database optimizations for bulk import
     *
     * @return void
     */
    protected function enableDatabaseOptimizations(): void
    {
        try {
            // Disable foreign key checks for faster inserts
            if ($this->config['performance']['disable_foreign_keys'] ?? true) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                Log::info('Foreign key checks disabled for import optimization');
            }

            // Disable query log to save memory
            DB::connection()->disableQueryLog();

            Log::info('Database optimizations enabled for import');
        } catch (Exception $e) {
            Log::warning('Failed to enable database optimizations', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove UTF-8 BOM (Byte Order Mark) from headers if present
     *
     * CSV files saved with UTF-8 BOM (e.g., from Excel) include invisible
     * characters (EF BB BF) at the start of the file, which gets included
     * in the first header. This method strips the BOM to ensure proper
     * header matching.
     *
     * @param array $headers
     * @return array Headers with BOM removed from first element
     */
    protected function removeBomFromHeaders(array $headers): array
    {
        if (empty($headers)) {
            return $headers;
        }

        // UTF-8 BOM is three bytes: EF BB BF (displayed as ﻿)
        $bom = "\xEF\xBB\xBF";

        // Remove BOM from first header if present
        $headers[0] = str_replace($bom, '', $headers[0]);

        return $headers;
    }

    /**
     * Disable database optimizations (restore normal behavior)
     *
     * @return void
     */
    protected function disableDatabaseOptimizations(): void
    {
        try {
            // Re-enable foreign key checks
            if ($this->config['performance']['disable_foreign_keys'] ?? true) {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                Log::info('Foreign key checks re-enabled after import');
            }

            Log::info('Database optimizations disabled after import');
        } catch (Exception $e) {
            Log::warning('Failed to disable database optimizations', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean text encoding - handle both Windows-1252 and UTF-8 CSV files
     *
     * @param string $text
     * @return string
     */
    protected function cleanTextEncoding(string $text): string
    {
        // First, check if the text is already valid UTF-8
        if (mb_check_encoding($text, 'UTF-8')) {
            // Text is already UTF-8, just return it
            return $text;
        }

        // Text is not UTF-8, likely Windows-1252 - convert it
        // Map of Windows-1252 characters to UTF-8 equivalents
        $replacements = [
            "\x96" => "\xE2\x80\x93",  // en-dash
            "\x97" => "\xE2\x80\x94",  // em-dash
            "\x91" => "\xE2\x80\x98",  // left single quote
            "\x92" => "\xE2\x80\x99",  // right single quote
            "\x93" => "\xE2\x80\x9C",  // left double quote
            "\x94" => "\xE2\x80\x9D",  // right double quote
            "\x85" => "\xE2\x80\xA6",  // ellipsis
            "\x80" => "\xE2\x82\xAC",  // euro
            "\x82" => "\xE2\x80\x9A",  // single low-9 quote
            "\x83" => "\xC6\x92",      // f with hook
            "\x84" => "\xE2\x80\x9E",  // double low-9 quote
            "\x86" => "\xE2\x80\xA0",  // dagger
            "\x87" => "\xE2\x80\xA1",  // double dagger
            "\x88" => "\xCB\x86",      // circumflex
            "\x89" => "\xE2\x80\xB0",  // per mille
            "\x8A" => "\xC5\xA0",      // S with caron
            "\x8B" => "\xE2\x80\xB9",  // left single angle quote
            "\x8C" => "\xC5\x92",      // OE ligature
            "\x8E" => "\xC5\xBD",      // Z with caron
            "\x95" => "\xE2\x80\xA2",  // bullet
            "\x98" => "\xCB\x9C",      // small tilde
            "\x99" => "\xE2\x84\xA2",  // trademark
            "\x9A" => "\xC5\xA1",      // s with caron
            "\x9B" => "\xE2\x80\xBA",  // right single angle quote
            "\x9C" => "\xC5\x93",      // oe ligature
            "\x9E" => "\xC5\xBE",      // z with caron
            "\x9F" => "\xC5\xB8",      // Y with diaeresis
        ];

        // Replace Windows-1252 characters
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // Try to convert from Windows-1252 to UTF-8
        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $text);

        return $converted !== false ? $converted : $text;
    }

    /**
     * Clean integer field, converting non-numeric values to null
     *
     * @param mixed $value
     * @return int|null
     */
    protected function cleanIntegerField($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert to string for checking
        $value = trim((string) $value);

        // Check for common non-numeric values
        $nonNumericValues = ['n/a', 'na', 'unknown', 'unavailable', '/', '-', 'none'];
        if (in_array(strtolower($value), $nonNumericValues)) {
            return null;
        }

        // Try to extract numbers from the string
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        // If all else fails, return null
        return null;
    }
}
