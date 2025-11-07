<?php

namespace App\Console\Commands;

use App\Services\BookCsvImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportBooksFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:import-csv
                            {file : Path to CSV file}
                            {--mode=upsert : Import mode (create_only, update_only, upsert, create_duplicates)}
                            {--validate-only : Only validate, do not import}
                            {--create-missing : Create missing relations (collections, publishers, creators)}
                            {--skip-invalid : Skip invalid rows instead of failing}
                            {--user-id=1 : User ID for import session}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import books from CSV file';

    /**
     * Execute the console command.
     */
    public function handle(BookCsvImportService $importService): int
    {
        $filePath = $this->argument('file');

        // Validate file exists
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Processing CSV file: {$filePath}");
        $this->newLine();

        // Validate CSV
        $this->info('Validating CSV structure...');
        $validation = $importService->validateCsv($filePath);

        if (!empty($validation['errors'])) {
            $this->error('Validation failed with ' . count($validation['errors']) . ' errors:');
            $this->newLine();

            foreach ($validation['errors'] as $error) {
                $this->error('  • ' . $error);
            }

            return self::FAILURE;
        }

        if (!empty($validation['warnings'])) {
            $this->warn('Validation completed with ' . count($validation['warnings']) . ' warnings:');
            $this->newLine();

            foreach ($validation['warnings'] as $warning) {
                $this->warn('  • ' . $warning);
            }

            $this->newLine();
        }

        $this->info('✓ Validation passed');
        $this->newLine();

        // If validate-only, stop here
        if ($this->option('validate-only')) {
            $this->info('Validation-only mode. No data was imported.');
            return self::SUCCESS;
        }

        // Confirm before importing
        if (!$this->confirm('Do you want to proceed with the import?', true)) {
            $this->info('Import cancelled.');
            return self::SUCCESS;
        }

        // Prepare import options
        $options = [
            'mode' => $this->option('mode'),
            'create_missing_relations' => $this->option('create-missing'),
            'skip_invalid_rows' => $this->option('skip-invalid'),
            'original_filename' => basename($filePath),
        ];

        $userId = $this->option('user-id');

        // Start import
        $this->info('Starting import...');
        $this->newLine();

        $startTime = microtime(true);

        try {
            $result = $importService->importCsv($filePath, $options, $userId);

            $duration = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('Import completed in ' . $duration . ' seconds');
            $this->newLine();

            // Display results
            $this->displayResults($result);

            if ($result->status === 'completed') {
                return self::SUCCESS;
            } else {
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            Log::error('CSV Import Command Error', [
                'file' => $filePath,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Display import results
     */
    protected function displayResults($result): void
    {
        $headers = ['Metric', 'Count'];
        $rows = [
            ['Total Rows', $result->total_rows],
            ['Processed', $result->processed_rows],
            ['Created', $result->created_count],
            ['Updated', $result->updated_count],
            ['Failed', $result->failed_rows],
            ['Skipped', $result->skipped_rows],
            ['Success Rate', $result->getSuccessRate() . '%'],
        ];

        $this->table($headers, $rows);

        if ($result->failed_rows > 0) {
            $this->newLine();
            $this->warn('Some rows failed to import. Check the import log for details.');

            // Display first few errors
            $errors = json_decode($result->error_log ?? '[]', true);
            if (!empty($errors) && is_array($errors)) {
                $this->newLine();
                $this->warn('First few errors:');
                foreach (array_slice($errors, 0, 5) as $error) {
                    $row = $error['row'] ?? 'Unknown';
                    $message = $error['message'] ?? 'Unknown error';
                    $this->warn("  Row {$row}: {$message}");
                }

                if (count($errors) > 5) {
                    $remaining = count($errors) - 5;
                    $this->warn("  ... and {$remaining} more errors");
                }
            }
        }
    }
}
