<?php

namespace App\Console\Commands;

use App\Services\BookCsvImportService;
use Illuminate\Console\Command;

class ProcessBookRelationships extends Command
{
    protected $signature = 'books:process-relationships';
    protected $description = 'Process pending book relationships (match books with same relationship codes)';

    public function handle(BookCsvImportService $importService): int
    {
        $this->info('Processing book relationships...');
        
        $importService->processBookRelationships();
        
        $this->info('Book relationships processed successfully!');
        
        return 0;
    }
}
