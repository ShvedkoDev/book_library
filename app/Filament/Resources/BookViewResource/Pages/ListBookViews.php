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
            Actions\CreateAction::make(),
        ];
    }
}
