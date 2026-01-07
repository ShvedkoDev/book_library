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
                            ->label('CSV file')
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
                            ->label('Import mode')
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
                                    ->label('Create missing relations')
                                    ->helperText('Auto-create collections, publishers, and creators if they don\'t exist')
                                    ->default(true),

                                Forms\Components\Checkbox::make('skip_invalid_rows')
                                    ->label('Skip invalid rows')
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
                ->title('Validation error')
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
                // Format errors for display
                $errorCount = count($validation['errors']);
                $errorList = collect($validation['errors'])
                    ->take(10) // Show first 10 errors
                    ->map(fn($error) => "• {$error}")
                    ->join("\n");

                $moreErrors = $errorCount > 10 ? "\n\n...and " . ($errorCount - 10) . " more errors" : "";

                Notification::make()
                    ->title('Validation Failed')
                    ->body("Found {$errorCount} validation error(s):\n\n{$errorList}{$moreErrors}")
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }

            if (!empty($validation['warnings'])) {
                // Format warnings for display
                $warningCount = count($validation['warnings']);
                $warningList = collect($validation['warnings'])
                    ->take(5) // Show first 5 warnings
                    ->map(fn($warning) => "• {$warning}")
                    ->join("\n");

                $moreWarnings = $warningCount > 5 ? "\n\n...and " . ($warningCount - 5) . " more warnings" : "";

                Notification::make()
                    ->title('Validation Warnings')
                    ->body("Found {$warningCount} warning(s):\n\n{$warningList}{$moreWarnings}\n\nYou can proceed with the import.")
                    ->warning()
                    ->persistent()
                    ->send();
            }

            Notification::make()
                ->title('Validation Passed')
                ->body('The CSV file is valid and ready to import.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Validation error')
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
                'create_missing_relations' => $data['create_missing_relations'] ?? true,
                'skip_invalid_rows' => $data['skip_invalid_rows'] ?? true,
                'original_filename' => basename($csvFile),
            ];

            // Perform import
            $result = $importService->importCsv($filePath, $options, auth()->id());

            // Show appropriate notification based on results
            if ($result->failed_rows > 0) {
                // Parse error log to show specific errors
                $errors = [];
                if (!empty($result->error_log)) {
                    $errorLines = is_array($result->error_log)
                        ? $result->error_log
                        : json_decode($result->error_log, true) ?? [];

                    $errors = collect($errorLines)
                        ->take(10) // Show first 10 errors
                        ->map(fn($error) => "• {$error}")
                        ->toArray();
                }

                $errorList = !empty($errors) ? "\n\n" . implode("\n", $errors) : "";
                $moreErrors = count($errors) < $result->failed_rows ? "\n\n...and " . ($result->failed_rows - count($errors)) . " more errors. View details for complete list." : "";

                Notification::make()
                    ->title('Import Completed with Errors')
                    ->body("Successfully imported {$result->successful_rows} books. {$result->failed_rows} failed.{$errorList}{$moreErrors}")
                    ->warning()
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('View full details')
                            ->url(route('filament.admin.resources.csv-imports.view', ['record' => $result->id]))
                            ->button(),
                    ])
                    ->send();
            } else {
                // Success - show notification and open modal
                Notification::make()
                    ->title('Import Completed Successfully')
                    ->body("Imported {$result->successful_rows} books successfully. Click 'Process Relationships' to link related books and generate translation relationships.")
                    ->success()
                    ->duration(15000)
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('View details')
                            ->url(route('filament.admin.resources.csv-imports.view', ['record' => $result->id]))
                            ->button(),
                    ])
                    ->send();

                // Auto-open the relationships modal using Livewire dispatch
                $this->dispatch('open-modal', id: 'process-relationships-modal');
            }

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
                ->label('Validate only')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->action('validateCsv'),

            Forms\Components\Actions\Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('import')
                ->requiresConfirmation()
                ->modalHeading('Confirm import')
                ->modalDescription('Are you sure you want to import this CSV file? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, import'),
        ];
    }

    public function processRelationshipsModal(): void
    {
        $this->dispatch('open-modal', id: 'process-relationships-modal');
    }

    public function processRelationships(): void
    {
        try {
            Notification::make()
                ->title('Processing Relationships')
                ->body('Book relationships and translations are being processed. This may take a few minutes...')
                ->info()
                ->send();

            $importService = app(BookCsvImportService::class);
            $importService->processBookRelationships();

            Notification::make()
                ->title('Relationships Processed')
                ->body('All book relationships and translation links have been processed successfully!')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Processing Failed')
                ->body('Failed to process relationships: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
