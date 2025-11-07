<?php

namespace App\Filament\Resources\DataQualityIssueResource\Pages;

use App\Filament\Resources\DataQualityIssueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewDataQualityIssue extends ViewRecord
{
    protected static string $resource = DataQualityIssueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_book')
                ->label('View Book')
                ->icon('heroicon-o-book-open')
                ->color('gray')
                ->url(fn () => $this->record->book
                    ? route('filament.admin.resources.books.edit', ['record' => $this->record->book_id])
                    : null)
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->book_id),

            Actions\Action::make('resolve')
                ->label('Mark as Resolved')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('resolution_notes')
                        ->label('Resolution Notes')
                        ->rows(3)
                        ->placeholder('Optionally add notes about how this issue was resolved...'),
                ])
                ->action(function (array $data): void {
                    $this->record->markAsResolved($data['resolution_notes'] ?? null);

                    Notification::make()
                        ->title('Issue Resolved')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => !$this->record->is_resolved),

            Actions\Action::make('unresolve')
                ->label('Mark as Unresolved')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'is_resolved' => false,
                        'resolved_at' => null,
                        'resolved_by' => null,
                    ]);

                    Notification::make()
                        ->title('Issue Marked as Unresolved')
                        ->warning()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->is_resolved),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->is_resolved),
        ];
    }
}
