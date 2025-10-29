<?php

namespace App\Filament\Resources\FilterAnalyticResource\Pages;

use App\Filament\Resources\FilterAnalyticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilterAnalytics extends ListRecords
{
    protected static string $resource = FilterAnalyticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
