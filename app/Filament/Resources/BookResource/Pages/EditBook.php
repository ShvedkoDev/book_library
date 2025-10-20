<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Models\BookKeyword;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Handle keyword_list field - sync keywords to book_keywords table
        if (array_key_exists('keyword_list', $this->data)) {
            $keywords = $this->data['keyword_list'] ?? [];

            // Delete existing keywords
            $this->record->keywords()->delete();

            // Create new keywords
            foreach ($keywords as $keyword) {
                if (!empty(trim($keyword))) {
                    BookKeyword::create([
                        'book_id' => $this->record->id,
                        'keyword' => trim($keyword),
                    ]);
                }
            }
        }
    }
}
