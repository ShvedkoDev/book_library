<?php

namespace App\Filament\Resources\BookRatingResource\Pages;

use App\Filament\Resources\BookRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookRating extends CreateRecord
{
    protected static string $resource = BookRatingResource::class;
}
