<?php

namespace App\Filament\Resources\ClassificationTypeResource\Pages;

use App\Filament\Resources\ClassificationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassificationType extends EditRecord
{
    protected static string $resource = ClassificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
