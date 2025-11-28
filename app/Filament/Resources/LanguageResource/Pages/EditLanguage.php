<?php

namespace App\Filament\Resources\LanguageResource\Pages;

use App\Filament\Resources\LanguageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLanguage extends EditRecord
{
    protected static string $resource = LanguageResource::class;

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
