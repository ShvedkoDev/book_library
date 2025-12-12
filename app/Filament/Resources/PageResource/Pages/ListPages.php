<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use App\Models\ResourceContributor;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Export Pages')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                    return $this->exportPages();
                })
                ->requiresConfirmation()
                ->modalHeading('Export CMS Pages')
                ->modalDescription('Export all CMS pages with their content, relationships, and metadata to a JSON file.')
                ->modalSubmitActionLabel('Export & Download')
                ->successNotificationTitle('Pages exported successfully'),
            Actions\Action::make('import')
                ->label('Import Pages')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->form([
                    Forms\Components\FileUpload::make('import_file')
                        ->label('Import File')
                        ->acceptedFileTypes(['application/json'])
                        ->required()
                        ->helperText('Upload the pages-export.json file exported from another environment')
                        ->disk('local')
                        ->directory('temp')
                        ->storeFileNamesIn('original_filename'),
                    Forms\Components\Toggle::make('fresh_import')
                        ->label('Fresh Import')
                        ->helperText('WARNING: This will DELETE all existing pages before importing!')
                        ->default(false)
                        ->live(),
                    Forms\Components\Placeholder::make('warning')
                        ->label('')
                        ->content('⚠️ Fresh import will permanently delete all existing pages. This action cannot be undone!')
                        ->visible(fn (Forms\Get $get) => $get('fresh_import') === true),
                ])
                ->action(function (array $data): void {
                    // Show additional confirmation for fresh import
                    if ($data['fresh_import'] === true) {
                        // The requiresConfirmation will handle this
                    }
                    $this->importPages($data);
                })
                ->modalHeading('Import CMS Pages')
                ->modalDescription('Upload a JSON file to import or update CMS pages')
                ->modalSubmitActionLabel('Import')
                ->modalWidth('lg')
                ->successNotificationTitle('Pages imported successfully'),
        ];
    }

    protected function exportPages(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Get all pages with their relationships
        $pages = Page::with(['resourceContributors', 'parent', 'children'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $exportData = [
            'export_date' => now()->toDateTimeString(),
            'total_pages' => $pages->count(),
            'pages' => $pages->map(function ($page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content' => $page->content,
                    'custom_html_blocks' => $page->custom_html_blocks,
                    'meta_description' => $page->meta_description,
                    'meta_keywords' => $page->meta_keywords,
                    'is_published' => $page->is_published,
                    'show_in_navigation' => $page->show_in_navigation,
                    'is_homepage' => $page->is_homepage,
                    'published_at' => $page->published_at?->toDateTimeString(),
                    'order' => $page->order,
                    'parent_id' => $page->parent_id,
                    'parent_slug' => $page->parent?->slug,
                    'resource_contributors' => $page->resourceContributors->map(function ($contributor) use ($page) {
                        return [
                            'name' => $contributor->name,
                            'website_url' => $contributor->website_url,
                            'is_active' => $contributor->is_active,
                            'order' => $contributor->pivot->order ?? 0,
                        ];
                    })->toArray(),
                    'created_at' => $page->created_at->toDateTimeString(),
                    'updated_at' => $page->updated_at->toDateTimeString(),
                ];
            })->toArray(),
        ];

        $filename = 'pages-export-' . now()->format('Y-m-d-His') . '.json';
        $content = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        Notification::make()
            ->title('Export Completed')
            ->body("Exported {$pages->count()} pages")
            ->success()
            ->send();

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    protected function importPages(array $data): void
    {
        // Get the uploaded file path from the local disk
        $uploadedFilePath = $data['import_file'];

        if (!Storage::disk('local')->exists($uploadedFilePath)) {
            Notification::make()
                ->title('Import Failed')
                ->body('The uploaded file could not be found.')
                ->danger()
                ->send();
            return;
        }

        $jsonContent = Storage::disk('local')->get($uploadedFilePath);
        $importData = json_decode($jsonContent, true);

        if (!$importData || !isset($importData['pages'])) {
            Notification::make()
                ->title('Import Failed')
                ->body('Invalid import file format. The file must contain a "pages" array.')
                ->danger()
                ->send();

            // Clean up uploaded file
            Storage::disk('local')->delete($uploadedFilePath);
            return;
        }

        try {
            // Fresh import: delete existing pages
            if ($data['fresh_import'] === true) {
                DB::table('page_resource_contributor')->delete();
                Page::query()->forceDelete();
            }

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            // Create slug to ID mapping for parent relationships
            $slugToIdMap = [];

            // First pass: Create/update pages without parent relationships
            foreach ($importData['pages'] as $pageData) {
                try {
                    // Check if page exists by slug
                    $existingPage = Page::withTrashed()->where('slug', $pageData['slug'])->first();

                    if ($existingPage) {
                        // Update existing page
                        $existingPage->fill([
                            'title' => $pageData['title'],
                            'content' => $pageData['content'],
                            'custom_html_blocks' => $pageData['custom_html_blocks'],
                            'meta_description' => $pageData['meta_description'],
                            'meta_keywords' => $pageData['meta_keywords'],
                            'is_published' => $pageData['is_published'],
                            'show_in_navigation' => $pageData['show_in_navigation'],
                            'is_homepage' => $pageData['is_homepage'],
                            'published_at' => $pageData['published_at'],
                            'order' => $pageData['order'],
                        ]);

                        if ($existingPage->trashed()) {
                            $existingPage->restore();
                        }

                        $existingPage->save();
                        $slugToIdMap[$pageData['slug']] = $existingPage->id;
                        $updated++;
                    } else {
                        // Create new page
                        $page = Page::create([
                            'title' => $pageData['title'],
                            'slug' => $pageData['slug'],
                            'content' => $pageData['content'],
                            'custom_html_blocks' => $pageData['custom_html_blocks'],
                            'meta_description' => $pageData['meta_description'],
                            'meta_keywords' => $pageData['meta_keywords'],
                            'is_published' => $pageData['is_published'],
                            'show_in_navigation' => $pageData['show_in_navigation'],
                            'is_homepage' => $pageData['is_homepage'],
                            'published_at' => $pageData['published_at'],
                            'order' => $pageData['order'],
                        ]);

                        $slugToIdMap[$pageData['slug']] = $page->id;
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $skipped++;
                }
            }

            // Second pass: Update parent relationships
            foreach ($importData['pages'] as $pageData) {
                if ($pageData['parent_slug'] && isset($slugToIdMap[$pageData['parent_slug']])) {
                    $page = Page::where('slug', $pageData['slug'])->first();
                    if ($page) {
                        $page->parent_id = $slugToIdMap[$pageData['parent_slug']];
                        $page->save();
                    }
                }
            }

            // Third pass: Attach resource contributors
            foreach ($importData['pages'] as $pageData) {
                if (!empty($pageData['resource_contributors'])) {
                    $page = Page::where('slug', $pageData['slug'])->first();
                    if ($page) {
                        // Detach existing contributors
                        $page->resourceContributors()->detach();

                        // Attach new contributors
                        foreach ($pageData['resource_contributors'] as $contributorData) {
                            $contributor = ResourceContributor::firstOrCreate(
                                ['name' => $contributorData['name']],
                                [
                                    'website_url' => $contributorData['website_url'],
                                    'is_active' => $contributorData['is_active'],
                                ]
                            );

                            $page->resourceContributors()->attach($contributor->id, [
                                'order' => $contributorData['order'],
                            ]);
                        }
                    }
                }
            }

            // Clean up uploaded file
            Storage::disk('local')->delete($uploadedFilePath);

            // Send success notification with details
            $body = "Created: {$imported}, Updated: {$updated}";
            if ($skipped > 0) {
                $body .= ", Skipped: {$skipped}";
            }

            Notification::make()
                ->title('Import Completed')
                ->body($body)
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Clean up uploaded file
            Storage::disk('local')->delete($uploadedFilePath);

            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred during import: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
