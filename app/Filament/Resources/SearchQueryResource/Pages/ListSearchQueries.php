<?php

namespace App\Filament\Resources\SearchQueryResource\Pages;

use App\Filament\Resources\SearchQueryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSearchQueries extends ListRecords
{
    protected static string $resource = SearchQueryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
