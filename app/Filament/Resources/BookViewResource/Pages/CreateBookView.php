<?php

namespace App\Filament\Resources\BookViewResource\Pages;

use App\Filament\Resources\BookViewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBookView extends CreateRecord
{
    protected static string $resource = BookViewResource::class;
}
