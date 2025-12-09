<?php

namespace App\Jobs;

use App\Models\CsvImport;
use App\Services\BookCsvImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessCsvImport implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600;
    public $tries = 1;

    public function __construct(
        public string $filePath,
        public array $options,
        public int $userId
    ) {
    }

    public function handle(BookCsvImportService $importService): void
    {
        try {
            Log::info('Processing CSV import job', [
                'file' => $this->filePath,
                'user_id' => $this->userId,
            ]);

            $importService->importCsv($this->filePath, $this->options, $this->userId);

            Log::info('CSV import job completed successfully');
        } catch (\Exception $e) {
            Log::error('CSV import job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
