<?php

namespace App\Filament\Resources\ClassificationValueResource\Pages;

use App\Filament\Resources\ClassificationValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassificationValues extends ListRecords
{
    protected static string $resource = ClassificationValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
