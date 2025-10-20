<?php

namespace App\Filament\Resources\GeographicLocationResource\Pages;

use App\Filament\Resources\GeographicLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeographicLocations extends ListRecords
{
    protected static string $resource = GeographicLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
