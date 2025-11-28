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
            Actions\Action::make('viewLive')
                ->label('View live page')
                ->icon('heroicon-m-eye')
                ->url(fn () => $this->getRecord()->getUrl())
                ->openUrlInNewTab()
                ->color('success')
                ->visible(fn () => $this->getRecord()->isPublished() && $this->getRecord()->getUrl() !== null),
            Actions\Action::make('previewSections')
                ->label('Preview sections')
                ->icon('heroicon-m-list-bullet')
                ->modalHeading('Page sections (h2 anchors)')
                ->modalDescription(fn () => "Sections found in: {$this->getRecord()->title}")
                ->modalContent(function () {
                    $sections = $this->getRecord()->extractSections();

                    if (empty($sections)) {
                        return view('filament.resources.page-resource.no-sections');
                    }

                    return view('filament.resources.page-resource.sections-preview', [
                        'sections' => $sections,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->color('gray'),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Could add last updated widget here
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Page updated successfully';
    }
}
