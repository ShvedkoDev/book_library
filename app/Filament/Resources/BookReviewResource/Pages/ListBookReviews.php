<?php

namespace App\Filament\Resources\BookReviewResource\Pages;

use App\Filament\Resources\BookReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookReviews extends ListRecords
{
    protected static string $resource = BookReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
