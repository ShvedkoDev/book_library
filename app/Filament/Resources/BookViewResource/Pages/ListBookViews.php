<?php

namespace App\Filament\Resources\BookViewResource\Pages;

use App\Filament\Resources\BookViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookViews extends ListRecords
{
    protected static string $resource = BookViewResource::class;

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
