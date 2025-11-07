<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Filament\Resources\BookResource;
use App\Models\BookKeyword;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class EditBook extends EditRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('duplicate')
                ->label('Duplicate This Book')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Duplicate This Book')
                ->modalDescription(fn () => "Create a copy of \"{$this->record->title}\" with all relationships and classifications.")
                ->modalSubmitActionLabel('Duplicate')
                ->action(function () {
                    try {
                        // Validate before duplication
                        $validation = $this->record->canBeDuplicated();

                        if (!$validation['valid']) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Duplicate Book')
                                ->body(implode("\n", $validation['errors']))
                                ->persistent()
                                ->send();
                            return;
                        }

                        // Perform duplication
                        $duplicate = $this->record->duplicate([
                            'clear_title' => false,
                            'append_copy_suffix' => true, // Add " (Copy)" to title
                        ]);

                        // Success notification with redirect option
                        Notification::make()
                            ->success()
                            ->title('Book Duplicated Successfully!')
                            ->body("Created duplicate of \"{$this->record->title}\". Redirecting to edit the new book...")
                            ->persistent()
                            ->send();

                        // Redirect to edit the duplicate
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $duplicate->id]));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Duplication Failed')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                    }
                })
                ->successNotification(null), // Custom notification

            Actions\DeleteAction::make(),
        ];
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validation Error')
            ->body('Please check the form for errors. Required fields are missing or invalid.')
            ->persistent()
            ->send();

        parent::onValidationError($exception);
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
