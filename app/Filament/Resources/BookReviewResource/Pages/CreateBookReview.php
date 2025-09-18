<?php

namespace App\Filament\Resources\BookReviewResource\Pages;

use App\Filament\Resources\BookReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookReview extends CreateRecord
{
    protected static string $resource = BookReviewResource::class;
}
