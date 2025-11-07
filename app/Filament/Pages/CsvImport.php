<?php

namespace App\Filament\Pages;

use App\Services\BookCsvImportService;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Storage;

class CsvImport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.pages.csv-import';

    protected static ?string $navigationGroup = 'CSV Import/Export';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'CSV Import';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload CSV File')
                    ->description('Select a CSV file to import books. The file must match the required format.')
                    ->schema([
                        Forms\Components\FileUpload::make('csv_file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv'])
                            ->maxSize(51200) // 50MB
                            ->disk('local')
                            ->directory('csv-imports')
                            ->visibility('private')
                            ->required()
                            ->helperText('Maximum file size: 50MB. Accepted formats: .csv, .txt'),
                    ]),

                Forms\Components\Section::make('Import Settings')
                    ->description('Configure how the import should be processed')
                    ->schema([
                        Forms\Components\Radio::make('mode')
                            ->label('Import Mode')
                            ->options([
                                'upsert' => 'Upsert (Create new or update existing)',
                                'create_only' => 'Create Only (Skip existing books)',
                                'update_only' => 'Update Only (Skip new books)',
                                'create_duplicates' => 'Create Duplicates (Allow duplicate books)',
                            ])
                            ->descriptions([
                                'upsert' => 'Recommended for most imports. Creates new books and updates existing ones.',
                                'create_only' => 'Only creates new books. Existing books are skipped.',
                                'update_only' => 'Only updates existing books. New books are skipped.',
                                'create_duplicates' => 'Creates all books as new, even if they already exist.',
                            ])
                            ->default('upsert')
                            ->required()
                            ->inline(false),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Checkbox::make('create_missing_relations')
                                    ->label('Create Missing Relations')
                                    ->helperText('Auto-create collections, publishers, and creators if they don\'t exist')
                                    ->default(true),

                                Forms\Components\Checkbox::make('skip_invalid_rows')
                                    ->label('Skip Invalid Rows')
                                    ->helperText('Continue import even if some rows have errors')
                                    ->default(true),
                            ]),
                    ]),

                Forms\Components\Section::make('Template & Documentation')
                    ->description('Download template or view documentation')
                    ->schema([
                        Forms\Components\Placeholder::make('template_info')
                            ->label('')
                            ->content(function () {
                                $templatePath = storage_path('csv-templates/book-import-template.csv');
                                $examplePath = storage_path('csv-templates/book-import-example.csv');

                                $links = [];

                                if (file_exists($templatePath)) {
                                    $links[] = '<a href="'.route('csv.download-template', ['type' => 'blank']).'" class="text-primary-600 hover:underline">Download Blank Template</a>';
                                }

                                if (file_exists($examplePath)) {
                                    $links[] = '<a href="'.route('csv.download-template', ['type' => 'example']).'" class="text-primary-600 hover:underline">Download Example Template</a>';
                                }

                                $links[] = '<a href="/docs/CSV_FIELD_MAPPING.md" target="_blank" class="text-primary-600 hover:underline">View Field Documentation</a>';

                                return new \Illuminate\Support\HtmlString(implode(' | ', $links));
                            }),
                    ])->collapsed(),
            ])
            ->statePath('data');
    }

    public function validateCsv(): void
    {
        $data = $this->form->getState();

        if (empty($data['csv_file'])) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please select a CSV file to import.')
                ->danger()
                ->send();
            return;
        }

        try {
            $importService = app(BookCsvImportService::class);

            // Handle array or string from file upload
            $csvFile = is_array($data['csv_file']) ? $data['csv_file'][0] : $data['csv_file'];
            $filePath = Storage::disk('local')->path($csvFile);

            $validation = $importService->validateCsv($filePath);

            if (!empty($validation['errors'])) {
                Notification::make()
                    ->title('Validation Failed')
                    ->body('The CSV file has '.count($validation['errors']).' validation errors. Please fix them and try again.')
                    ->danger()
                    ->duration(10000)
                    ->send();
                return;
            }

            if (!empty($validation['warnings'])) {
                Notification::make()
                    ->title('Validation Warnings')
                    ->body('The CSV file has '.count($validation['warnings']).' warnings. You can proceed with the import.')
                    ->warning()
                    ->duration(8000)
                    ->send();
            }

            Notification::make()
                ->title('Validation Passed')
                ->body('The CSV file is valid and ready to import.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Validation Error')
                ->body('Failed to validate CSV file: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function import(): void
    {
        $data = $this->form->getState();

        if (empty($data['csv_file'])) {
            Notification::make()
                ->title('Import Error')
                ->body('Please select a CSV file to import.')
                ->danger()
                ->send();
            return;
        }

        try {
            $importService = app(BookCsvImportService::class);

            // Handle array or string from file upload
            $csvFile = is_array($data['csv_file']) ? $data['csv_file'][0] : $data['csv_file'];
            $filePath = Storage::disk('local')->path($csvFile);

            $options = [
                'mode' => $data['mode'] ?? 'upsert',
                'create_missing_relations' => $data['create_missing_relations'] ?? false,
                'skip_invalid_rows' => $data['skip_invalid_rows'] ?? false,
                'original_filename' => basename($csvFile),
            ];

            // Perform import
            $result = $importService->importCsv($filePath, $options, auth()->id());

            // Show success notification
            Notification::make()
                ->title('Import Completed')
                ->body("Imported {$result->successful_rows} books successfully. {$result->failed_rows} failed.")
                ->success()
                ->duration(10000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Details')
                        ->url(route('filament.admin.resources.csv-imports.view', ['record' => $result->id]))
                        ->button(),
                ])
                ->send();

            // Reset form
            $this->form->fill();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('Failed to import CSV file: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('validateCsv')
                ->label('Validate Only')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->action('validateCsv'),

            Forms\Components\Actions\Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('import')
                ->requiresConfirmation()
                ->modalHeading('Confirm Import')
                ->modalDescription('Are you sure you want to import this CSV file? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, Import'),
        ];
    }
}
