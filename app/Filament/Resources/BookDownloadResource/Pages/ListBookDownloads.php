<?php

namespace App\Filament\Resources\BookDownloadResource\Pages;

use App\Filament\Resources\BookDownloadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookDownloads extends ListRecords
{
    protected static string $resource = BookDownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for analytics data
        ];
    }

    public function getTableRecordKey($record): string
    {
        // Use book_id as the record key since we're grouping by book
        return (string) $record->book_id;
    }
}
