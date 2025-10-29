<?php

namespace App\Filament\Resources\BookViewResource\Pages;

use App\Filament\Resources\BookViewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookView extends EditRecord
{
    protected static string $resource = BookViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
