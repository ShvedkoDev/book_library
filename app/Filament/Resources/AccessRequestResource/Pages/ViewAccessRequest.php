<?php

namespace App\Filament\Resources\AccessRequestResource\Pages;

use App\Filament\Resources\AccessRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccessRequest extends ViewRecord
{
    protected static string $resource = AccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
