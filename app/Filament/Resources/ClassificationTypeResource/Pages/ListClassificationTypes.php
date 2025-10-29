<?php

namespace App\Filament\Resources\ClassificationTypeResource\Pages;

use App\Filament\Resources\ClassificationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClassificationTypes extends ListRecords
{
    protected static string $resource = ClassificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
