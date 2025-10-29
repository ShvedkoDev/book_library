<?php

namespace App\Filament\Pages;

use App\Models\Page as PageModel;
use App\Models\FileRecord;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageMediaManager extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static string $view = 'filament.pages.page-media-manager';

    protected static ?string $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Page Assets';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    protected $cachedFiles = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload Page Assets')
                    ->description('Upload images and documents for use in pages')
                    ->schema([
                        FileUpload::make('media')
                            ->label('Images & Documents')
                            ->disk('public')
                            ->directory('page-media')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                                'image/svg+xml',
                                'application/pdf',
                            ])
                            ->maxSize(10240) // 10MB
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            ->downloadable()
                            ->openable()
                            ->helperText('Upload images (JPG, PNG, GIF, WebP, SVG) or PDFs (max 10MB each).'),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->paginated(false) // Disable pagination since we're loading all files
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Preview')
                    ->defaultImageUrl(fn ($record) => $this->getFileThumbnail($record))
                    ->size(60)
                    ->checkFileExistence(false),
                TextColumn::make('filename')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->path)
                    ->copyable()
                    ->copyMessage('Path copied!'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'image' => 'success',
                        'pdf' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => $this->formatBytes($state))
                    ->sortable(),
                TextColumn::make('modified')
                    ->label('Last Modified')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('pages_count')
                    ->label('Used By')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state . ' page(s)'),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => Storage::disk('public')->url($record->path))
                    ->openUrlInNewTab(),
                Action::make('copy_url')
                    ->label('Copy URL')
                    ->icon('heroicon-m-link')
                    ->action(function ($record) {
                        $url = Storage::disk('public')->url($record->path);

                        // Copy to clipboard via JavaScript
                        $this->dispatch('copy-to-clipboard', url: $url);

                        Notification::make()
                            ->success()
                            ->title('URL copied')
                            ->body('The file URL has been copied to clipboard.')
                            ->send();
                    }),
                Action::make('pages')
                    ->label('Show Pages')
                    ->icon('heroicon-m-document-text')
                    ->modalHeading('Pages Using This File')
                    ->modalDescription(fn ($record) => $record->filename)
                    ->modalContent(fn ($record) => view('filament.pages.media-usage', [
                        'items' => $this->getPagesUsingFile($record->path),
                        'type' => 'pages',
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn ($record) => $record->pages_count > 0),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn ($record) => Storage::disk('public')->download($record->path, $record->filename)),
                DeleteAction::make()
                    ->label('Delete')
                    ->modalHeading('Delete Media File')
                    ->modalDescription(fn ($record) => $record->pages_count > 0
                        ? "Warning: This file is used by {$record->pages_count} page(s). Deleting it may break images/links on those pages."
                        : 'Are you sure you want to delete this file?')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if (Storage::disk('public')->exists($record->path)) {
                            Storage::disk('public')->delete($record->path);

                            Notification::make()
                                ->success()
                                ->title('File deleted')
                                ->body("The file '{$record->filename}' has been deleted.")
                                ->send();
                        }
                    }),
            ])
            ->emptyStateHeading('No media files found')
            ->emptyStateDescription('Upload images and documents using the form above')
            ->emptyStateIcon('heroicon-o-photo');
    }

    protected function getTableQuery()
    {
        // Return a query builder with FileRecord model set
        $query = new \Illuminate\Database\Query\Builder(
            app('db')->connection(),
            app('db')->getQueryGrammar(),
            app('db')->getPostProcessor()
        );

        $builder = new \Illuminate\Database\Eloquent\Builder($query);
        $builder->setModel(new FileRecord());

        return $builder;
    }

    public function getTableRecords(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->cachedFiles === null) {
            $files = collect(Storage::disk('public')->allFiles('page-media'))
                ->filter(function ($file) {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf']);
                })
                ->map(function ($file) {
                    $fullPath = Storage::disk('public')->path($file);
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    return FileRecord::make([
                        'path' => $file,
                        'filename' => basename($file),
                        'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                        'modified' => file_exists($fullPath) ? filemtime($fullPath) : time(),
                        'type' => $this->getFileType($extension),
                        'pages_count' => $this->getPagesUsingFileCount($file),
                    ]);
                })
                ->sortByDesc('modified')
                ->values();

            // Convert to Eloquent Collection
            $this->cachedFiles = new \Illuminate\Database\Eloquent\Collection($files->all());
        }

        return $this->cachedFiles;
    }

    protected function getFileType(string $extension): string
    {
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']) ? 'image' : 'pdf';
    }

    protected function getFileThumbnail($record): string
    {
        if ($record->type === 'image') {
            return Storage::disk('public')->url($record->path);
        }

        // Return PDF icon for non-images
        return asset('images/pdf-icon.png');
    }

    protected function getPagesUsingFileCount(string $path): int
    {
        $filename = basename($path);
        return PageModel::where('content', 'like', "%{$filename}%")->count();
    }

    protected function getPagesUsingFile(string $path): \Illuminate\Support\Collection
    {
        $filename = basename($path);
        return PageModel::where('content', 'like', "%{$filename}%")
            ->select('id', 'title', 'content')
            ->get();
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (!empty($data['media'])) {
            Notification::make()
                ->success()
                ->title('Files uploaded')
                ->body(count($data['media']) . ' file(s) uploaded successfully.')
                ->send();

            // Reset form
            $this->form->fill();

            // Refresh table
            $this->dispatch('$refresh');
        }
    }

}
