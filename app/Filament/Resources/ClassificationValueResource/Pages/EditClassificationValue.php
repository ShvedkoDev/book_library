<?php

namespace App\Filament\Resources\ClassificationValueResource\Pages;

use App\Filament\Resources\ClassificationValueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassificationValue extends EditRecord
{
    protected static string $resource = ClassificationValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
