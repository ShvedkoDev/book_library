<?php

namespace App\Console\Commands;

use App\Services\BookCsvExportService;
use Illuminate\Console\Command;
use Exception;

class ExportBooksToCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:export-csv
                            {--output= : Output file path (optional, auto-generated if not specified)}
                            {--format=csv : Export format (csv, tsv)}
                            {--filters= : JSON string of filters to apply}
                            {--access-level= : Filter by access level (full, limited, unavailable)}
                            {--collection= : Filter by collection ID}
                            {--language= : Filter by language ID}
                            {--created-from= : Filter by created date from (Y-m-d)}
                            {--created-to= : Filter by created date to (Y-m-d)}
                            {--year-from= : Filter by publication year from}
                            {--year-to= : Filter by publication year to}
                            {--is-active= : Filter by active status (1 or 0)}
                            {--is-featured= : Filter by featured status (1 or 0)}
                            {--no-bom : Exclude BOM from CSV (for non-Excel compatibility)}
                            {--no-mapping-row : Exclude database mapping row from headers}
                            {--chunk-size=100 : Number of records to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export books to CSV/TSV file with optional filters';

    /**
     * Execute the console command.
     */
    public function handle(BookCsvExportService $exportService): int
    {
        try {
            // Validate format
            $format = strtolower($this->option('format') ?? 'csv');
            if (!in_array($format, ['csv', 'tsv'])) {
                $this->error("Invalid format '{$format}'. Supported formats: csv, tsv");
                return Command::FAILURE;
            }

            $this->info('Starting book export...');
            $this->newLine();

            // Build options array
            $options = $this->buildOptions();

            // Display export configuration
            $this->displayConfiguration($options);

            // Perform export
            $startTime = microtime(true);
            $filePath = $exportService->exportAll($options);
            $duration = round(microtime(true) - $startTime, 2);

            // Success output
            $this->newLine();
            $this->info('✓ Export completed successfully!');
            $this->newLine();

            // Display results
            $this->table(
                ['Metric', 'Value'],
                [
                    ['File Path', $filePath],
                    ['File Size', $this->formatBytes(filesize($filePath))],
                    ['Duration', "{$duration} seconds"],
                    ['Format', strtoupper($options['format'] ?? 'csv')],
                ]
            );

            $this->newLine();
            $this->line("File saved to: <fg=green>{$filePath}</>");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Export failed: ' . $e->getMessage());
            $this->newLine();

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Build options array from command options
     */
    protected function buildOptions(): array
    {
        $options = [
            'format' => $this->option('format') ?? 'csv',
            'include_bom' => !$this->option('no-bom'),
            'include_mapping_row' => !$this->option('no-mapping-row'),
            'chunk_size' => (int) $this->option('chunk-size'),
        ];

        // Custom output path
        if ($this->option('output')) {
            $options['output_path'] = $this->option('output');
        }

        // Build filters array
        $filters = [];

        // Parse JSON filters if provided
        if ($this->option('filters')) {
            $jsonFilters = json_decode($this->option('filters'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $filters = array_merge($filters, $jsonFilters);
            } else {
                $this->warn('Invalid JSON in --filters option, ignoring.');
            }
        }

        // Individual filter options (override JSON filters)
        if ($this->option('access-level')) {
            $filters['access_level'] = $this->option('access-level');
        }

        if ($this->option('collection')) {
            $filters['collection_id'] = (int) $this->option('collection');
        }

        if ($this->option('language')) {
            $filters['language_id'] = (int) $this->option('language');
        }

        if ($this->option('created-from')) {
            $filters['created_from'] = $this->option('created-from');
        }

        if ($this->option('created-to')) {
            $filters['created_to'] = $this->option('created-to');
        }

        if ($this->option('year-from')) {
            $filters['year_from'] = (int) $this->option('year-from');
        }

        if ($this->option('year-to')) {
            $filters['year_to'] = (int) $this->option('year-to');
        }

        if ($this->option('is-active') !== null) {
            $filters['is_active'] = (bool) $this->option('is-active');
        }

        if ($this->option('is-featured') !== null) {
            $filters['is_featured'] = (bool) $this->option('is-featured');
        }

        if (!empty($filters)) {
            $options['filters'] = $filters;
        }

        return $options;
    }

    /**
     * Display export configuration
     */
    protected function displayConfiguration(array $options): void
    {
        $format = strtoupper($options['format'] ?? 'csv');

        $this->line('<fg=cyan>Export Configuration:</>');
        $this->line('-------------------');
        $this->line('Format: ' . $format);

        if ($format === 'CSV') {
            $this->line('Include BOM: ' . ($options['include_bom'] ? 'Yes' : 'No'));
        } else {
            $this->line('Field Separator: Tab (\\t)');
        }

        $this->line('Include Mapping Row: ' . ($options['include_mapping_row'] ? 'Yes' : 'No'));
        $this->line('Chunk Size: ' . $options['chunk_size']);

        if (!empty($options['filters'])) {
            $this->newLine();
            $this->line('<fg=cyan>Active Filters:</>');
            $this->line('---------------');
            foreach ($options['filters'] as $key => $value) {
                $displayValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
                $this->line("  {$key}: {$displayValue}");
            }
        }

        if (!empty($options['output_path'])) {
            $this->newLine();
            $this->line('<fg=cyan>Output Path:</>');
            $this->line($options['output_path']);
        }

        $this->newLine();
    }

    /**
     * Format bytes to human-readable size
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
