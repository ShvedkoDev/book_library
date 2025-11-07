<?php

namespace App\Jobs;

use App\Models\CsvImport;
use App\Services\BookCsvImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ImportBooksFromCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CsvImport $csvImport;
    public string $filePath;
    public array $options;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct(CsvImport $csvImport, string $filePath, array $options = [])
    {
        $this->csvImport = $csvImport;
        $this->filePath = $filePath;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(BookCsvImportService $importService): void
    {
        try {
            Log::info('Starting CSV import job', [
                'import_id' => $this->csvImport->id,
                'file' => $this->filePath,
            ]);

            // Mark as processing
            $this->csvImport->markAsProcessing();

            // Set import service to use this import session
            $importService->setImportSession($this->csvImport);

            // Process the import
            $importService->processCsvFile($this->filePath, $this->options);

            // Mark as completed
            $this->csvImport->markAsCompleted();

            Log::info('CSV import job completed successfully', [
                'import_id' => $this->csvImport->id,
                'processed' => $this->csvImport->processed_rows,
                'successful' => $this->csvImport->successful_rows,
                'failed' => $this->csvImport->failed_rows,
            ]);

        } catch (Exception $e) {
            Log::error('CSV import job failed', [
                'import_id' => $this->csvImport->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->csvImport->markAsFailed($e->getMessage());

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('CSV import job failed permanently', [
            'import_id' => $this->csvImport->id,
            'exception' => $exception->getMessage(),
        ]);

        $this->csvImport->update([
            'status' => 'failed',
            'error_log' => ($this->csvImport->error_log ?? '') . "\n\nJob failed: " . $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }

    /**
     * Get the tags for the job.
     */
    public function tags(): array
    {
        return [
            'csv-import',
            'import:' . $this->csvImport->id,
            'user:' . $this->csvImport->user_id,
        ];
    }
}
