<?php

namespace App\Filament\Resources\BookReviewResource\Pages;

use App\Filament\Resources\BookReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookReview extends EditRecord
{
    protected static string $resource = BookReviewResource::class;

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
                ->label('Save and Continue Editing')
                ->action(function () {
                    $this->save(shouldRedirect: false);
                })
                ->keyBindings(['mod+s'])
                ->icon('heroicon-o-check')
                ->color('success'),

            // Save and Go to List
            Actions\Action::make('saveAndGoToList')
                ->label('Save and Go to List')
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
