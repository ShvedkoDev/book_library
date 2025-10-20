<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Models\BookKeyword;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    protected function afterCreate(): void
    {
        // Handle keyword_list field - sync keywords to book_keywords table
        if ($this->data['keyword_list'] ?? null) {
            $keywords = $this->data['keyword_list'];

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
