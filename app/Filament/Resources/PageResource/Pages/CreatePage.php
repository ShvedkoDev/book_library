<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
            Actions\Action::make('preview_draft')
                ->label('Preview Draft')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->action(function () {
                    // Save as draft first, then redirect to preview
                    $this->data['status'] = 'draft';
                    $record = $this->handleRecordCreation($this->data);
                    $this->redirect($record->getUrl(), navigate: false);
                })
                ->requiresConfirmation()
                ->modalHeading('Preview Draft')
                ->modalDescription('This will save the page as a draft and open it for preview. Continue?'),
            $this->getCancelFormAction(),
        ];
    }
}
