<?php

namespace App\Filament\Pages;

use App\Services\BookCsvExportService;
use App\Models\Collection;
use App\Models\Language;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Storage;

class CsvExport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static string $view = 'filament.pages.csv-export';

    protected static ?string $navigationGroup = 'CSV Import/Export';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'CSV Export';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Export Settings')
                    ->description('Configure the export format and options')
                    ->schema([
                        Forms\Components\Radio::make('format')
                            ->label('Export Format')
                            ->options([
                                'csv' => 'CSV (Comma-Separated Values)',
                                'tsv' => 'TSV (Tab-Separated Values)',
                            ])
                            ->descriptions([
                                'csv' => 'Standard CSV format with UTF-8 BOM for Excel compatibility',
                                'tsv' => 'Tab-separated format, better for texts containing commas',
                            ])
                            ->default('csv')
                            ->required()
                            ->inline(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Checkbox::make('include_bom')
                                    ->label('Include BOM (CSV only)')
                                    ->helperText('Adds UTF-8 BOM for Excel compatibility')
                                    ->default(true)
                                    ->visible(fn (Forms\Get $get) => $get('format') === 'csv'),

                                Forms\Components\Checkbox::make('include_mapping_row')
                                    ->label('Include Database Mapping Row')
                                    ->helperText('Adds second header row with database field names')
                                    ->default(true),
                            ]),
                    ]),

                Forms\Components\Section::make('Filters')
                    ->description('Filter which books to include in the export')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('collection_id')
                                    ->label('Collection')
                                    ->options(Collection::pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('All collections'),

                                Forms\Components\Select::make('language_id')
                                    ->label('Language')
                                    ->options(Language::pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('All languages'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('access_level')
                                    ->label('Access Level')
                                    ->options([
                                        'full' => 'Full Access',
                                        'limited' => 'Limited Access',
                                        'unavailable' => 'Unavailable',
                                    ])
                                    ->placeholder('All access levels'),

                                Forms\Components\Select::make('is_active')
                                    ->label('Status')
                                    ->options([
                                        '1' => 'Active Only',
                                        '0' => 'Inactive Only',
                                    ])
                                    ->placeholder('All statuses'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('created_from')
                                    ->label('Created From')
                                    ->native(false),

                                Forms\Components\DatePicker::make('created_to')
                                    ->label('Created To')
                                    ->native(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('year_from')
                                    ->label('Publication Year From')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(date('Y') + 5),

                                Forms\Components\TextInput::make('year_to')
                                    ->label('Publication Year To')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(date('Y') + 5),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Checkbox::make('is_featured')
                                    ->label('Featured Only')
                                    ->helperText('Only export featured books'),

                                Forms\Components\TextInput::make('chunk_size')
                                    ->label('Chunk Size')
                                    ->helperText('Number of records to process per batch')
                                    ->numeric()
                                    ->minValue(10)
                                    ->maxValue(1000)
                                    ->default(100),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
    }

    public function export(): void
    {
        $data = $this->form->getState();

        try {
            $exportService = app(BookCsvExportService::class);

            // Build options
            $options = [
                'format' => $data['format'] ?? 'csv',
                'include_bom' => $data['include_bom'] ?? true,
                'include_mapping_row' => $data['include_mapping_row'] ?? true,
                'chunk_size' => $data['chunk_size'] ?? 100,
            ];

            // Build filters
            $filters = [];
            if (!empty($data['collection_id'])) {
                $filters['collection_id'] = $data['collection_id'];
            }
            if (!empty($data['language_id'])) {
                $filters['language_id'] = $data['language_id'];
            }
            if (!empty($data['access_level'])) {
                $filters['access_level'] = $data['access_level'];
            }
            if (!empty($data['created_from'])) {
                $filters['created_from'] = $data['created_from'];
            }
            if (!empty($data['created_to'])) {
                $filters['created_to'] = $data['created_to'];
            }
            if (!empty($data['year_from'])) {
                $filters['year_from'] = $data['year_from'];
            }
            if (!empty($data['year_to'])) {
                $filters['year_to'] = $data['year_to'];
            }
            if (isset($data['is_active']) && $data['is_active'] !== '') {
                $filters['is_active'] = (bool) $data['is_active'];
            }
            if (!empty($data['is_featured'])) {
                $filters['is_featured'] = true;
            }

            if (!empty($filters)) {
                $options['filters'] = $filters;
            }

            // Perform export
            $filePath = $exportService->exportAll($options);

            // Get file size
            $fileSize = filesize($filePath);
            $fileSizeMB = round($fileSize / 1048576, 2);

            // Show success notification with download link
            Notification::make()
                ->title('Export Completed')
                ->body("Exported {$fileSizeMB}MB successfully. Click below to download.")
                ->success()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('download')
                        ->label('Download File')
                        ->url(route('csv.download-export', ['filename' => basename($filePath)]))
                        ->button()
                        ->color('success'),
                ])
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Failed')
                ->body('Failed to export CSV file: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->action('export'),
        ];
    }
}
