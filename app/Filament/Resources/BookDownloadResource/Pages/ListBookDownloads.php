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
            Actions\CreateAction::make(),
        ];
    }
}
