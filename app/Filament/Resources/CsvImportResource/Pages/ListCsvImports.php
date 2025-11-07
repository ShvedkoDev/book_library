<?php

namespace App\Filament\Resources\CsvImportResource\Pages;

use App\Filament\Resources\CsvImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCsvImports extends ListRecords
{
    protected static string $resource = CsvImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('filament.admin.pages.csv-import')),

            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->url(route('filament.admin.pages.csv-export')),
        ];
    }
}
