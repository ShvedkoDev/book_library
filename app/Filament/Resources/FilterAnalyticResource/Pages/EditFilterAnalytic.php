<?php

namespace App\Filament\Resources\FilterAnalyticResource\Pages;

use App\Filament\Resources\FilterAnalyticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilterAnalytic extends EditRecord
{
    protected static string $resource = FilterAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
