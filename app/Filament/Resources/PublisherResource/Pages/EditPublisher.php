<?php

namespace App\Filament\Resources\PublisherResource\Pages;

use App\Filament\Resources\PublisherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPublisher extends EditRecord
{
    protected static string $resource = PublisherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            // Save and Continue Editing
            Actions\Action::make('save')
                ->label('Save and continue editing')
                ->action(function () {
                    $this->save(shouldRedirect: false);
                })
                ->keyBindings(['mod+s'])
                ->icon('heroicon-o-check')
                ->color('success'),

            // Save and Go to List
            Actions\Action::make('saveAndGoToList')
                ->label('Save and go to list')
                ->action(function () {
                    $this->save(shouldRedirect: false);
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->icon('heroicon-o-arrow-left')
                ->color('primary'),

            // Cancel
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}
