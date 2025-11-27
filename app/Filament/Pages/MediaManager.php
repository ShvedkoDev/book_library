<?php

namespace App\Filament\Pages;

use App\Models\BookFile;
use App\Models\FileRecord;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class MediaManager extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static string $view = 'filament.pages.media-manager';

    protected static ?string $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Bulk Media Upload';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Bulk Media Upload Manager';

    public ?array $data = [];

    protected $cachedFiles = null;

    // Properties for batch processing
    public int $maxFilesPerBatch = 18; // Leave 2 files buffer from PHP's limit of 20
    public int $pdfFileCount = 0;
    public int $thumbnailFileCount = 0;
    public int $totalFileCount = 0;
    public int $batchesNeeded = 0;
    public int $currentBatch = 0;
    public int $totalUploaded = 0;

    public function mount(): void
    {
        $this->form->fill();
        $this->loadPhpLimit();
    }

    protected function loadPhpLimit(): void
    {
        $phpMaxFiles = (int) ini_get('max_file_uploads');
        if ($phpMaxFiles > 0) {
            // Leave 2 files buffer to be safe
            $this->maxFilesPerBatch = max(1, $phpMaxFiles - 2);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload PDF Files')
                    ->description('Upload PDF book files. Files will be saved with their original filenames.')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->headerActions([
                        \Filament\Forms\Components\Actions\Action::make('clearPdfs')
                            ->label('Clear Selected Files')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->visible(fn () => $this->pdfFileCount > 0)
                            ->action(function () {
                                $this->data['pdfs'] = [];
                                $this->pdfFileCount = 0;
                                $this->updateTotalCounts();
                            }),
                    ])
                    ->schema([
                        Placeholder::make('pdf_info')
                            ->label('')
                            ->content(function () {
                                if ($this->pdfFileCount > 0) {
                                    $batches = (int) ceil($this->pdfFileCount / $this->maxFilesPerBatch);
                                    $batchInfo = $batches > 1 ? " (will be split into {$batches} batches)" : "";
                                    return "📊 **{$this->pdfFileCount} PDF file(s) selected**{$batchInfo}";
                                }
                                return '📄 No PDF files selected yet';
                            })
                            ->visible(fn () => $this->pdfFileCount > 0),

                        FileUpload::make('pdfs')
                            ->label('PDF Files')
                            ->disk('public')
                            ->directory('books')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(102400) // 100MB
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->helperText("📄 Drag and drop PDF files. Max {$this->maxFilesPerBatch} files per batch. Max 100MB per file.")
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->pdfFileCount = is_array($state) ? count($state) : 0;
                                $this->updateTotalCounts();
                            }),
                    ]),

                Section::make('Upload Thumbnail Images')
                    ->description('Upload book cover thumbnails. Files will be saved with their original filenames.')
                    ->icon('heroicon-o-photo')
                    ->collapsible()
                    ->headerActions([
                        \Filament\Forms\Components\Actions\Action::make('clearThumbnails')
                            ->label('Clear Selected Files')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->visible(fn () => $this->thumbnailFileCount > 0)
                            ->action(function () {
                                $this->data['thumbnails'] = [];
                                $this->thumbnailFileCount = 0;
                                $this->updateTotalCounts();
                            }),
                    ])
                    ->schema([
                        Placeholder::make('thumbnail_info')
                            ->label('')
                            ->content(function () {
                                if ($this->thumbnailFileCount > 0) {
                                    $batches = (int) ceil($this->thumbnailFileCount / $this->maxFilesPerBatch);
                                    $batchInfo = $batches > 1 ? " (will be split into {$batches} batches)" : "";
                                    return "📊 **{$this->thumbnailFileCount} thumbnail(s) selected**{$batchInfo}";
                                }
                                return '🖼️ No thumbnails selected yet';
                            })
                            ->visible(fn () => $this->thumbnailFileCount > 0),

                        FileUpload::make('thumbnails')
                            ->label('Thumbnail Files')
                            ->disk('public')
                            ->directory('books')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                            ->maxSize(10240) // 10MB
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->imagePreviewHeight('0') // Disable preview
                            ->panelLayout('list') // Use list layout instead of grid
                            ->helperText("🖼️ Drag and drop thumbnails (PNG, JPG). Max {$this->maxFilesPerBatch} files per batch. Max 10MB per file.")
                            ->columnSpanFull()
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->thumbnailFileCount = is_array($state) ? count($state) : 0;
                                $this->updateTotalCounts();
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function updateTotalCounts(): void
    {
        $this->totalFileCount = $this->pdfFileCount + $this->thumbnailFileCount;
        $this->batchesNeeded = $this->totalFileCount > 0
            ? (int) ceil($this->totalFileCount / $this->maxFilesPerBatch)
            : 0;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->heading('Uploaded Files')
            ->description('Manage all uploaded PDF and thumbnail files')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->paginatedWhileReordering()
            ->striped()
            ->columns([
                TextColumn::make('filename')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->path)
                    ->copyable()
                    ->copyMessage('Filename copied!')
                    ->icon('heroicon-m-document')
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pdf' => 'danger',
                        'image' => 'success',
                        'video' => 'info',
                        'audio' => 'warning',
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
                TextColumn::make('books_count')
                    ->label('Used By')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state . ' book(s)'),
            ])
            ->filters([
                // You can add filters here if needed
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => Storage::disk('public')->url($record->path))
                    ->openUrlInNewTab(),
                Action::make('books')
                    ->label('Show Books')
                    ->icon('heroicon-m-book-open')
                    ->modalHeading('Books Using This File')
                    ->modalDescription(fn ($record) => $record->filename)
                    ->modalContent(fn ($record) => view('filament.pages.media-usage', [
                        'items' => $this->getBooksUsingFile($record->path),
                        'type' => 'books',
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn ($record) => $record->books_count > 0),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn ($record) => Storage::disk('public')->download($record->path, $record->filename)),
                DeleteAction::make()
                    ->label('Delete')
                    ->modalHeading('Delete File')
                    ->modalDescription(function ($record) {
                        if ($record->books_count > 0) {
                            return "⚠️ WARNING: This file is currently used by {$record->books_count} book(s).\n\nDeleting this file will break the book references.\n\nAre you absolutely sure you want to continue?";
                        }
                        return 'Are you sure you want to delete this file? This action cannot be undone.';
                    })
                    ->requiresConfirmation()
                    ->color(fn ($record) => $record->books_count > 0 ? 'danger' : 'warning')
                    ->before(function ($record, DeleteAction $action) {
                        if ($record->books_count > 0) {
                            Notification::make()
                                ->warning()
                                ->title('File in use!')
                                ->body("This file is currently used by {$record->books_count} book(s).")
                                ->persistent()
                                ->send();
                        }
                    })
                    ->action(function ($record) {
                        $booksCount = $record->books_count;

                        if (Storage::disk('public')->exists($record->path)) {
                            Storage::disk('public')->delete($record->path);
                            $this->cachedFiles = null;

                            if ($booksCount > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('File deleted')
                                    ->body("The file '{$record->filename}' has been deleted. {$booksCount} book(s) had references to this file.")
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->success()
                                    ->title('File deleted')
                                    ->body("The file '{$record->filename}' has been deleted successfully.")
                                    ->send();
                            }
                        }
                    }),
            ])
            ->selectCurrentPageOnly()
            ->bulkActions([
                // Note: Bulk actions work with FileRecord virtual models by using selectCurrentPageOnly()
                // This prevents Filament from querying the non-existent database table
                \Filament\Tables\Actions\BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Selected Files')
                    ->modalDescription('Are you sure you want to delete the selected files? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete them')
                    ->action(function ($records) {
                        $count = 0;
                        $inUseCount = 0;

                        foreach ($records as $record) {
                            if (Storage::disk('public')->exists($record->path)) {
                                if ($record->books_count > 0) {
                                    $inUseCount++;
                                }
                                Storage::disk('public')->delete($record->path);
                                $count++;
                            }
                        }

                        $this->cachedFiles = null;

                        if ($inUseCount > 0) {
                            Notification::make()
                                ->warning()
                                ->title('Files deleted with warnings')
                                ->body("Deleted {$count} file(s). Warning: {$inUseCount} file(s) were in use by books.")
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('Files deleted')
                                ->body("Successfully deleted {$count} file(s).")
                                ->send();
                        }
                    }),
                \Filament\Tables\Actions\BulkAction::make('download')
                    ->label('Download Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($records) {
                        if ($records->count() === 1) {
                            $record = $records->first();
                            return Storage::disk('public')->download($record->path, $record->filename);
                        }

                        // For multiple files, create a zip
                        $zip = new \ZipArchive();
                        $zipFileName = 'files_' . now()->format('Y-m-d_His') . '.zip';
                        $zipPath = storage_path('app/temp/' . $zipFileName);

                        // Create temp directory if it doesn't exist
                        if (!file_exists(storage_path('app/temp'))) {
                            mkdir(storage_path('app/temp'), 0755, true);
                        }

                        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                            foreach ($records as $record) {
                                $filePath = Storage::disk('public')->path($record->path);
                                if (file_exists($filePath)) {
                                    $zip->addFile($filePath, $record->filename);
                                }
                            }
                            $zip->close();

                            Notification::make()
                                ->success()
                                ->title('Archive created')
                                ->body("Created archive with {$records->count()} file(s).")
                                ->send();

                            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
                        }

                        Notification::make()
                            ->danger()
                            ->title('Error')
                            ->body('Failed to create archive.')
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No files found')
            ->emptyStateDescription('Upload PDF and thumbnail files using the forms above')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    protected function getTableQuery()
    {
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
            $files = collect(Storage::disk('public')->files('books'))
                ->map(function ($file) {
                    $fullPath = Storage::disk('public')->path($file);
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    return FileRecord::make([
                        'path' => $file,
                        'filename' => basename($file),
                        'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                        'modified' => file_exists($fullPath) ? filemtime($fullPath) : time(),
                        'type' => $this->getFileType($extension),
                        'books_count' => $this->getBooksUsingFileCount($file),
                    ]);
                })
                ->sortByDesc('modified')
                ->values();

            $this->cachedFiles = new \Illuminate\Database\Eloquent\Collection($files->all());
        }

        return $this->cachedFiles;
    }

    protected function getFileType(string $extension): string
    {
        return match(strtolower($extension)) {
            'pdf' => 'pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp' => 'image',
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm' => 'video',
            'mp3', 'wav', 'ogg', 'm4a', 'flac' => 'audio',
            default => 'other',
        };
    }

    protected function getBooksUsingFileCount(string $path): int
    {
        $filename = basename($path);
        return BookFile::where('file_path', 'like', "%{$filename}%")
            ->distinct('book_id')
            ->count('book_id');
    }

    protected function getBooksUsingFile(string $path): \Illuminate\Support\Collection
    {
        $filename = basename($path);
        $bookFiles = BookFile::where('file_path', 'like', "%{$filename}%")
            ->with('book:id,title')
            ->get();

        return $bookFiles->map(function ($bookFile) {
            return (object) [
                'id' => $bookFile->book->id ?? null,
                'title' => $bookFile->book->title ?? 'Unknown',
                'file_path' => $bookFile->file_path,
            ];
        })->filter(fn ($item) => $item->id !== null);
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

        $pdfs = $data['pdfs'] ?? [];
        $thumbnails = $data['thumbnails'] ?? [];

        $totalFiles = array_merge($pdfs, $thumbnails);
        $totalCount = count($totalFiles);

        if ($totalCount === 0) {
            Notification::make()
                ->warning()
                ->title('No files selected')
                ->body('Please select files to upload.')
                ->send();
            return;
        }

        // Check if batching is needed
        if ($totalCount <= $this->maxFilesPerBatch) {
            // Single batch upload
            $this->processSingleBatch($pdfs, $thumbnails);
        } else {
            // Multiple batch upload
            $this->processMultipleBatches($pdfs, $thumbnails);
        }
    }

    protected function processSingleBatch(array $pdfs, array $thumbnails): void
    {
        $pdfCount = count($pdfs);
        $thumbnailCount = count($thumbnails);
        $totalCount = $pdfCount + $thumbnailCount;

        $message = "Successfully uploaded {$totalCount} file(s)";

        if ($pdfCount > 0 && $thumbnailCount > 0) {
            $message .= " ({$pdfCount} PDF(s) and {$thumbnailCount} thumbnail(s))";
        } elseif ($pdfCount > 0) {
            $message .= " ({$pdfCount} PDF(s))";
        } elseif ($thumbnailCount > 0) {
            $message .= " ({$thumbnailCount} thumbnail(s))";
        }

        Notification::make()
            ->success()
            ->title('Upload Complete')
            ->body($message)
            ->send();

        $this->resetAfterUpload();
    }

    protected function processMultipleBatches(array $pdfs, array $thumbnails): void
    {
        $allFiles = [
            ['type' => 'pdf', 'files' => $pdfs],
            ['type' => 'thumbnail', 'files' => $thumbnails],
        ];

        $batches = $this->createBatches($allFiles);
        $totalBatches = count($batches);
        $totalUploaded = 0;

        foreach ($batches as $index => $batch) {
            $batchNum = $index + 1;
            $batchSize = count($batch);
            $totalUploaded += $batchSize;

            Notification::make()
                ->success()
                ->title("Batch {$batchNum}/{$totalBatches} uploaded")
                ->body("Uploaded {$batchSize} files. Total: {$totalUploaded}/{$this->totalFileCount}")
                ->send();
        }

        Notification::make()
            ->success()
            ->title('All batches uploaded!')
            ->body("Successfully uploaded all {$totalUploaded} files in {$totalBatches} batches.")
            ->persistent()
            ->send();

        $this->resetAfterUpload();
    }

    protected function createBatches(array $fileGroups): array
    {
        $allFiles = [];

        foreach ($fileGroups as $group) {
            foreach ($group['files'] as $file) {
                $allFiles[] = $file;
            }
        }

        return array_chunk($allFiles, $this->maxFilesPerBatch);
    }

    protected function resetAfterUpload(): void
    {
        // Reset form
        $this->form->fill();

        // Reset counters
        $this->pdfFileCount = 0;
        $this->thumbnailFileCount = 0;
        $this->totalFileCount = 0;
        $this->batchesNeeded = 0;
        $this->currentBatch = 0;
        $this->totalUploaded = 0;

        // Clear cache and refresh table
        $this->cachedFiles = null;
        $this->dispatch('$refresh');
    }

    /**
     * Override to prevent Filament from querying the database for file records.
     */
    public function getTableRecord($key): ?FileRecord
    {
        $records = $this->getTableRecords();

        foreach ($records as $record) {
            if ($record->getKey() === $key) {
                return $record;
            }
        }

        return null;
    }
}
