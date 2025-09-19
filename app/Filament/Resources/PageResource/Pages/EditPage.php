<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Preview Page')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => $this->record->getUrl())
                ->openUrlInNewTab()
                ->visible(fn () => $this->record && ($this->record->status === 'published' || $this->record->status === 'draft')),

            Actions\DeleteAction::make(),
        ];
    }
}
