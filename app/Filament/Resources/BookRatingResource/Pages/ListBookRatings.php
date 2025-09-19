<?php

namespace App\Filament\Resources\BookRatingResource\Pages;

use App\Filament\Resources\BookRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookRatings extends ListRecords
{
    protected static string $resource = BookRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
