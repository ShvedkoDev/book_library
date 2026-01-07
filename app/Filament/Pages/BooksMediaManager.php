<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\BookFile;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BooksMediaManager extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.books-media-manager';

    protected static ?string $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Books (pdfs)';

    protected static ?int $navigationSort = 1;

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
                Section::make('File Statistics')
                    ->description('Overview of files in the books directory')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('file_stats')
                            ->label('')
                            ->content(function () {
                                $stats = $this->getFileTypeBreakdown();
                                $html = '<div class="grid grid-cols-2 md:grid-cols-4 gap-4">';

                                foreach ($stats as $type => $count) {
                                    $icon = match($type) {
                                        'pdf' => '📄',
                                        'image' => '🖼️',
                                        'video' => '🎬',
                                        'audio' => '🎵',
                                        default => '📁',
                                    };
                                    $color = match($type) {
                                        'pdf' => 'text-red-600',
                                        'image' => 'text-green-600',
                                        'video' => 'text-blue-600',
                                        'audio' => 'text-yellow-600',
                                        default => 'text-gray-600',
                                    };
                                    $html .= "<div class='p-3 bg-gray-50 dark:bg-gray-800 rounded-lg'>";
                                    $html .= "<div class='text-2xl'>{$icon}</div>";
                                    $html .= "<div class='font-bold text-xl {$color}'>{$count}</div>";
                                    $html .= "<div class='text-sm text-gray-600 dark:text-gray-400'>" . strtoupper($type) . "</div>";
                                    $html .= "</div>";
                                }

                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(false),
                Section::make('Upload Books')
                    ->description('Upload PDF files for your book library')
                    ->schema([
                        FileUpload::make('books')
                            ->label('PDF files')
                            ->disk('public')
                            ->directory('books')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(512000) // 500MB
                            ->preserveFilenames()
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->helperText('Upload PDF files (max 50MB each). Drag and drop multiple files supported.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->defaultSort('modified', 'desc')
            ->striped()
            ->deferLoading()
            ->headerActions([
                Action::make('export')
                    ->label('Export Files')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        return $this->exportFiles();
                    }),
            ])
            ->columns([
                TextColumn::make('filename')
                    ->label('File name')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->filename)
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
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => Storage::disk('public')->url($record->path))
                    ->openUrlInNewTab(),
                Action::make('books')
                    ->label('Show books')
                    ->icon('heroicon-m-book-open')
                    ->modalHeading('Books using this PDF')
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
                    ->modalHeading('Delete file')
                    ->modalDescription(function ($record) {
                        if ($record->books_count > 0) {
                            return "⚠️ WARNING: This file is currently used by {$record->books_count} book(s).\n\nDeleting this file will break the book references and the PDF will no longer be accessible from those books.\n\nAre you absolutely sure you want to continue?";
                        }
                        return 'Are you sure you want to delete this file? This action cannot be undone.';
                    })
                    ->requiresConfirmation()
                    ->color(fn ($record) => $record->books_count > 0 ? 'danger' : 'warning')
                    ->before(function ($record, DeleteAction $action) {
                        // Check if file is in use
                        if ($record->books_count > 0) {
                            // Show additional warning notification
                            Notification::make()
                                ->warning()
                                ->title('File in use!')
                                ->body("This file is currently used by {$record->books_count} book(s). Please review the books using this file before deleting.")
                                ->persistent()
                                ->send();
                        }
                    })
                    ->action(function ($record) {
                        $booksCount = $record->books_count;

                        if (Storage::disk('public')->exists($record->path)) {
                            Storage::disk('public')->delete($record->path);

                            // Clear cached files
                            $this->cachedFiles = null;

                            if ($booksCount > 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('File deleted')
                                    ->body("The file '{$record->filename}' has been deleted. Note: {$booksCount} book(s) were using this file and now have broken references.")
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
            ->emptyStateHeading('No PDF files found')
            ->emptyStateDescription('Upload PDF files using the form above')
            ->emptyStateIcon('heroicon-o-document-text');
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

    protected function getAllFileRecords(): \Illuminate\Support\Collection
    {
        if ($this->cachedFiles === null) {
            $this->cachedFiles = collect(Storage::disk('public')->files('books'))
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
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'powerpoint',
            'txt', 'rtf' => 'text',
            'zip', 'rar', '7z', 'tar', 'gz' => 'archive',
            default => 'other',
        };
    }

    protected function getBooksUsingFileCount(string $path): int
    {
        $filename = basename($path);
        return BookFile::where('file_path', 'like', "%{$filename}%")
            ->where('file_type', 'pdf')
            ->distinct('book_id')
            ->count('book_id');
    }

    protected function getBooksUsingFile(string $path): \Illuminate\Support\Collection
    {
        $filename = basename($path);
        $bookFiles = BookFile::where('file_path', 'like', "%{$filename}%")
            ->where('file_type', 'pdf')
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

    protected function getFileTypeBreakdown(): array
    {
        $files = Storage::disk('public')->files('books');
        $breakdown = [
            'pdf' => 0,
            'image' => 0,
            'video' => 0,
            'audio' => 0,
            'other' => 0,
        ];

        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $type = $this->getFileType($extension);
            $breakdown[$type] = ($breakdown[$type] ?? 0) + 1;
        }

        // Remove types with 0 count
        return array_filter($breakdown, fn($count) => $count > 0);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (!empty($data['books'])) {
            Notification::make()
                ->success()
                ->title('Files uploaded')
                ->body(count($data['books']) . ' file(s) uploaded successfully.')
                ->send();

            // Clear cache to refresh stats and table
            $this->cachedFiles = null;

            // Reset form
            $this->form->fill();

            // Refresh page
            $this->dispatch('$refresh');
        }
    }

    /**
     * Override to prevent Filament from querying the database for file records.
     * Since FileRecord instances are in-memory only, we need to find them from our cached collection.
     */
    public function getTableRecord($key): ?FileRecord
    {
        $records = $this->getAllFileRecords();

        foreach ($records as $record) {
            if ($record->getKey() === $key) {
                return $record;
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

        // Get all file records from cache
        $items = $this->getAllFileRecords();

        // Apply search filter if present
        $searchQuery = trim($this->tableSearch ?? '');
        \Log::info('Search Query:', ['query' => $searchQuery, 'tableSearch' => $this->tableSearch]);

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

    /**
     * Export all file records to CSV
     */
    public function exportFiles(): StreamedResponse
    {
        $timestamp = now()->format('Y-m-d_His');
        $filename = "books-media-export_{$timestamp}.csv";

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Filename',
                'Type',
                'Size (Bytes)',
                'Size (Human)',
                'Last Modified',
                'Books Count',
                'File Path',
            ]);

            // Get all file records
            $files = $this->getAllFileRecords();

            // Write each file record to CSV
            foreach ($files as $file) {
                fputcsv($handle, [
                    $file->filename,
                    strtoupper($file->type),
                    $file->size,
                    $this->formatBytes($file->size),
                    date('Y-m-d H:i:s', $file->modified),
                    $file->books_count,
                    $file->path,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

}
