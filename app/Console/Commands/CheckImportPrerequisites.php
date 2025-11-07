<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Publisher;
use App\Models\Language;
use App\Models\ClassificationType;
use App\Models\ClassificationValue;
use App\Models\GeographicLocation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckImportPrerequisites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:check-prerequisites
                            {--detailed : Show detailed lists of existing data}
                            {--csv-file= : CSV file to check for specific requirements}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all prerequisites are met for bulk book import';

    protected array $issues = [];
    protected array $warnings = [];
    protected array $passed = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('===========================================');
        $this->info('CSV IMPORT PREREQUISITES CHECKER');
        $this->info('===========================================');
        $this->newLine();

        // Run all checks
        $this->checkDatabasePrerequisites();
        $this->checkStorageDirectories();
        $this->checkConfiguration();
        $this->checkQueueWorker();

        if ($this->option('csv-file')) {
            $this->checkCsvFile($this->option('csv-file'));
        }

        // Display results
        $this->newLine();
        $this->displayResults();

        return empty($this->issues) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Check database prerequisites
     */
    protected function checkDatabasePrerequisites(): void
    {
        $this->line('<fg=cyan>Checking Database Prerequisites...</>');
        $this->newLine();

        // Collections
        $collectionsCount = Collection::count();
        if ($collectionsCount > 0) {
            $this->passed[] = "Collections: {$collectionsCount} available";
            if ($this->option('detailed')) {
                $collections = Collection::pluck('name')->take(10);
                $this->line('  → ' . $collections->implode(', '));
                if ($collectionsCount > 10) {
                    $this->line('  → ... and ' . ($collectionsCount - 10) . ' more');
                }
            }
        } else {
            $this->warnings[] = 'No collections found. Enable create_missing_relations or add collections first.';
        }

        // Publishers
        $publishersCount = Publisher::count();
        if ($publishersCount > 0) {
            $this->passed[] = "Publishers: {$publishersCount} available";
            if ($this->option('detailed')) {
                $publishers = Publisher::pluck('name')->take(10);
                $this->line('  → ' . $publishers->implode(', '));
                if ($publishersCount > 10) {
                    $this->line('  → ... and ' . ($publishersCount - 10) . ' more');
                }
            }
        } else {
            $this->warnings[] = 'No publishers found. Enable create_missing_relations or add publishers first.';
        }

        // Languages
        $languagesCount = Language::count();
        if ($languagesCount > 0) {
            $this->passed[] = "Languages: {$languagesCount} available";
            if ($this->option('detailed')) {
                $languages = Language::pluck('name')->take(10);
                $this->line('  → ' . $languages->implode(', '));
                if ($languagesCount > 10) {
                    $this->line('  → ... and ' . ($languagesCount - 10) . ' more');
                }
            }
        } else {
            $this->issues[] = 'No languages found. You must create languages before importing.';
        }

        // Classification Types
        $classificationTypesCount = ClassificationType::count();
        if ($classificationTypesCount >= 6) {
            $this->passed[] = "Classification Types: {$classificationTypesCount} available";
            if ($this->option('detailed')) {
                $types = ClassificationType::pluck('name');
                $this->line('  → ' . $types->implode(', '));
            }
        } else {
            $this->issues[] = "Only {$classificationTypesCount} classification types found. Expected at least 6 (Purpose, Genre, Sub-genre, Type, Themes-Uses, Learner-Level).";
        }

        // Classification Values
        $classificationValuesCount = ClassificationValue::count();
        if ($classificationValuesCount > 0) {
            $this->passed[] = "Classification Values: {$classificationValuesCount} available";
            if ($this->option('detailed')) {
                $valuesByType = ClassificationValue::with('type')
                    ->get()
                    ->groupBy('type.name');
                foreach ($valuesByType as $typeName => $values) {
                    $this->line("  → {$typeName}: " . $values->count() . ' values');
                }
            }
        } else {
            $this->warnings[] = 'No classification values found. Books may not be properly categorized.';
        }

        // Geographic Locations
        $locationsCount = GeographicLocation::count();
        if ($locationsCount > 0) {
            $this->passed[] = "Geographic Locations: {$locationsCount} available";
            if ($this->option('detailed')) {
                $locations = GeographicLocation::pluck('name')->take(10);
                $this->line('  → ' . $locations->implode(', '));
                if ($locationsCount > 10) {
                    $this->line('  → ... and ' . ($locationsCount - 10) . ' more');
                }
            }
        } else {
            $this->warnings[] = 'No geographic locations found. Location data will not be associated.';
        }

        $this->newLine();
    }

    /**
     * Check storage directories
     */
    protected function checkStorageDirectories(): void
    {
        $this->line('<fg=cyan>Checking Storage Directories...</>');
        $this->newLine();

        $directories = [
            'csv-imports' => storage_path('csv-imports'),
            'csv-exports' => storage_path('csv-exports'),
            'csv-templates' => storage_path('csv-templates'),
            'logs/csv-imports' => storage_path('logs/csv-imports'),
            'books/pdfs' => storage_path('app/public/books/pdfs'),
            'books/thumbnails' => storage_path('app/public/books/thumbnails'),
            'books/audio' => storage_path('app/public/books/audio'),
            'books/video' => storage_path('app/public/books/video'),
        ];

        foreach ($directories as $name => $path) {
            if (is_dir($path)) {
                $writable = is_writable($path);
                if ($writable) {
                    $this->passed[] = "Directory '{$name}' exists and is writable";
                } else {
                    $this->issues[] = "Directory '{$name}' exists but is not writable: {$path}";
                }
            } else {
                $this->warnings[] = "Directory '{$name}' does not exist: {$path}";
                $this->line("  → Run: mkdir -p {$path}");
            }
        }

        $this->newLine();
    }

    /**
     * Check configuration
     */
    protected function checkConfiguration(): void
    {
        $this->line('<fg=cyan>Checking Configuration...</>');
        $this->newLine();

        // Check if config file exists
        if (config('csv-import')) {
            $this->passed[] = 'CSV import configuration loaded';

            // Check critical config values
            $chunkSize = config('csv-import.batch_size', 100);
            $maxFileSize = config('csv-import.max_file_size', 52428800);
            $timeout = config('csv-import.timeout', 600);

            $this->line("  → Batch size: {$chunkSize} rows");
            $this->line("  → Max file size: " . number_format($maxFileSize / 1048576, 2) . " MB");
            $this->line("  → Timeout: {$timeout} seconds");
        } else {
            $this->issues[] = 'CSV import configuration not found. Publish config/csv-import.php';
        }

        // Check upload limits
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $this->line("  → PHP upload_max_filesize: {$uploadMaxFilesize}");
        $this->line("  → PHP post_max_size: {$postMaxSize}");

        $this->newLine();
    }

    /**
     * Check queue worker status
     */
    protected function checkQueueWorker(): void
    {
        $this->line('<fg=cyan>Checking Queue Worker...</>');
        $this->newLine();

        // Check if queue connection is configured
        $queueDriver = config('queue.default');
        $this->line("  → Queue driver: {$queueDriver}");

        if ($queueDriver === 'sync') {
            $this->warnings[] = "Queue driver is 'sync'. Large imports will run synchronously. Consider using 'database' or 'redis' for background processing.";
        } else {
            $this->passed[] = "Queue driver '{$queueDriver}' configured for background processing";
        }

        $this->newLine();
    }

    /**
     * Check specific CSV file
     */
    protected function checkCsvFile(string $filePath): void
    {
        $this->line('<fg=cyan>Checking CSV File...</>');
        $this->newLine();

        if (!file_exists($filePath)) {
            $this->issues[] = "CSV file not found: {$filePath}";
            return;
        }

        $this->passed[] = "CSV file exists: {$filePath}";

        // Check file size
        $fileSize = filesize($filePath);
        $this->line('  → File size: ' . number_format($fileSize / 1048576, 2) . ' MB');

        // Check if readable
        if (!is_readable($filePath)) {
            $this->issues[] = 'CSV file is not readable';
            return;
        }

        // Check encoding
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        if (substr($firstLine, 0, 3) === "\xEF\xBB\xBF") {
            $this->passed[] = 'CSV has UTF-8 BOM (Excel compatible)';
        } else {
            $this->line('  → CSV does not have BOM (may have encoding issues in Excel)');
        }

        // Count rows
        $handle = fopen($filePath, 'r');
        $rowCount = 0;
        while (fgets($handle) !== false) {
            $rowCount++;
        }
        fclose($handle);

        $dataRows = max(0, $rowCount - 2); // Subtract header rows
        $this->line("  → Total rows: {$rowCount}");
        $this->line("  → Data rows: {$dataRows}");

        if ($dataRows < 1) {
            $this->warnings[] = 'CSV file has no data rows';
        }

        $this->newLine();
    }

    /**
     * Display final results
     */
    protected function displayResults(): void
    {
        $this->info('===========================================');
        $this->info('RESULTS SUMMARY');
        $this->info('===========================================');
        $this->newLine();

        // Passed checks
        if (!empty($this->passed)) {
            $this->line('<fg=green>✓ PASSED (' . count($this->passed) . '):</>');
            foreach ($this->passed as $pass) {
                $this->line("  <fg=green>✓</> {$pass}");
            }
            $this->newLine();
        }

        // Warnings
        if (!empty($this->warnings)) {
            $this->line('<fg=yellow>⚠ WARNINGS (' . count($this->warnings) . '):</>');
            foreach ($this->warnings as $warning) {
                $this->line("  <fg=yellow>⚠</> {$warning}");
            }
            $this->newLine();
        }

        // Issues
        if (!empty($this->issues)) {
            $this->line('<fg=red>✗ ISSUES (' . count($this->issues) . '):</>');
            foreach ($this->issues as $issue) {
                $this->line("  <fg=red>✗</> {$issue}");
            }
            $this->newLine();
        }

        // Final verdict
        if (empty($this->issues)) {
            if (empty($this->warnings)) {
                $this->info('✓ All prerequisites are met! Ready for bulk import.');
            } else {
                $this->warn('⚠ Prerequisites mostly met, but there are warnings to review.');
            }
        } else {
            $this->error('✗ Some prerequisites are missing. Please fix the issues above before importing.');
        }

        $this->newLine();

        // Next steps
        if (empty($this->issues) && empty($this->warnings)) {
            $this->line('<fg=cyan>Next Steps:</>');
            $this->line('1. Prepare your CSV file with book data');
            $this->line('2. Upload PDF files and thumbnails');
            $this->line('3. Run validation: php artisan books:import-csv <file> --validate-only');
            $this->line('4. Review validation report');
            $this->line('5. Run import: php artisan books:import-csv <file> --mode=upsert --create-missing');
        }
    }
}
