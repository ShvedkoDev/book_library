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

    protected static ?string $navigationLabel = 'Bulk media upload';

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
                            ->label('Clear selected files')
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
                            ->label('PDF files')
                            ->disk('public')
                            ->directory('books')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(512000) // 500MB
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->helperText("📄 Drag and drop PDF files. Max {$this->maxFilesPerBatch} files per batch. Max 500MB per file.")
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
                            ->label('Clear selected files')
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
                            ->label('Thumbnail files')
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
            ->query(FileRecord::query())
            ->modifyQueryUsing(fn ($query) => $query) // No-op, we handle data in getTableRecords
            ->heading('Uploaded Files')
            ->description('Manage all uploaded PDF and thumbnail files')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->striped()
            ->columns([
                TextColumn::make('filename')
                    ->label('File name')
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
                    ->label('Last modified')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('books_count')
                    ->label('Used by')
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
                    ->label('Show books')
                    ->icon('heroicon-m-book-open')
                    ->modalHeading('Books using this file')
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
                    ->url(fn ($record) => route('admin.media.download', ['file' => base64_encode($record->path)]))
                    ->openUrlInNewTab(false),
                DeleteAction::make()
                    ->label('Delete')
                    ->modalHeading('Delete file')
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
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\BulkAction::make('delete')
                        ->label('Delete selected')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected files')
                        ->modalDescription('Are you sure you want to delete the selected files? This action cannot be undone.')
                        ->modalSubmitActionLabel('Delete')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (array $records) {
                            $deletedCount = 0;
                            $filesInUse = [];

                            foreach ($records as $record) {
                                if ($record->books_count > 0) {
                                    $filesInUse[] = $record->filename;
                                }

                                if (Storage::disk('public')->exists($record->path)) {
                                    Storage::disk('public')->delete($record->path);
                                    $deletedCount++;
                                }
                            }

                            $this->cachedFiles = null;

                            if (!empty($filesInUse)) {
                                Notification::make()
                                    ->warning()
                                    ->title('Files deleted with warnings')
                                    ->body("Deleted {$deletedCount} file(s). " . count($filesInUse) . " file(s) were in use by books: " . implode(', ', $filesInUse))
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->success()
                                    ->title('Files deleted')
                                    ->body("Successfully deleted {$deletedCount} file(s).")
                                    ->send();
                            }
                        }),

                    \Filament\Tables\Actions\BulkAction::make('deleteAll')
                        ->label('Delete all')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete all files')
                        ->modalDescription('⚠️ WARNING: This will delete ALL files in the books directory. This action cannot be undone!')
                        ->modalSubmitActionLabel('Yes, delete all')
                        ->deselectRecordsAfterCompletion()
                        ->action(function () {
                            $files = Storage::disk('public')->files('books');
                            $deletedCount = 0;
                            $filesInUseCount = 0;

                            foreach ($files as $file) {
                                $booksCount = $this->getBooksUsingFileCount($file);
                                if ($booksCount > 0) {
                                    $filesInUseCount++;
                                }

                                Storage::disk('public')->delete($file);
                                $deletedCount++;
                            }

                            $this->cachedFiles = null;

                            if ($filesInUseCount > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('All files deleted')
                                    ->body("Deleted {$deletedCount} file(s). {$filesInUseCount} file(s) were in use by books.")
                                    ->persistent()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->success()
                                    ->title('All files deleted')
                                    ->body("Successfully deleted {$deletedCount} file(s).")
                                    ->send();
                            }
                        }),

                    \Filament\Tables\Actions\BulkAction::make('downloadSelected')
                        ->label('Download as zip')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Download selected files')
                        ->modalDescription('This will create a ZIP archive of the selected files and download it.')
                        ->modalSubmitActionLabel('Download zip')
                        ->action(function (array $records) {
                            $zip = new \ZipArchive();
                            $zipFileName = 'books_export_' . date('Y-m-d_His') . '.zip';
                            $zipPath = storage_path('app/public/' . $zipFileName);

                            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                                foreach ($records as $record) {
                                    $filePath = Storage::disk('public')->path($record->path);
                                    if (file_exists($filePath)) {
                                        $zip->addFile($filePath, $record->filename);
                                    }
                                }
                                $zip->close();

                                Notification::make()
                                    ->success()
                                    ->title('ZIP created')
                                    ->body('Your ZIP file has been created. Download will start shortly.')
                                    ->send();

                                return response()->download($zipPath)->deleteFileAfterSend(true);
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Error creating ZIP')
                                    ->body('Failed to create ZIP archive.')
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('No files found')
            ->emptyStateDescription('Upload PDF and thumbnail files using the forms above')
            ->emptyStateIcon('heroicon-o-document-text');
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

        // Refresh table
        $this->dispatch('$refresh');
    }

    /**
     * Override to get a specific file record by its MD5 hash key.
     */
    public function getTableRecord($key): ?FileRecord
    {
        $files = Storage::disk('public')->files('books');

        foreach ($files as $file) {
            if (md5($file) === $key) {
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
            }
        }

        return null;
    }

    /**
     * Override to provide custom pagination for filesystem-based records.
     */
    protected function paginateTableQuery(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Contracts\Pagination\Paginator
    {
        $perPage = $this->getTableRecordsPerPage();
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');

        // Get all file records
        $items = collect(Storage::disk('public')->files('books'))
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

        // Apply search filter if present
        $searchQuery = trim($this->tableSearch ?? '');

        if (!empty($searchQuery)) {
            $searchLower = strtolower($searchQuery);
            $items = $items->filter(function ($record) use ($searchLower) {
                // Search in filename
                if (str_contains(strtolower($record->filename), $searchLower)) {
                    return true;
                }
                // Search in file type
                if (str_contains(strtolower($record->type), $searchLower)) {
                    return true;
                }
                // Search in file size (human-readable format)
                if (str_contains(strtolower($this->formatBytes($record->size)), $searchLower)) {
                    return true;
                }
                return false;
            })->values();
        }

        $totalCount = $items->count();
        $paginatedItems = $items->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $totalCount,
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}
