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
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BooksMediaManager extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.books-media-manager';

    protected static ?string $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Books (PDFs)';

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
                Section::make('Upload Books')
                    ->description('Upload PDF files for your book library')
                    ->schema([
                        FileUpload::make('books')
                            ->label('PDF Files')
                            ->disk('public')
                            ->directory('books')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200) // 50MB
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
            ->paginated(false) // Disable pagination since we're loading all files
            ->columns([
                TextColumn::make('filename')
                    ->label('File Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->path)
                    ->copyable()
                    ->copyMessage('Path copied!')
                    ->icon('heroicon-m-document'),
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
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => Storage::disk('public')->url($record->path))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->label('Rename')
                    ->icon('heroicon-m-pencil')
                    ->form([
                        TextInput::make('filename')
                            ->label('File Name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Enter the new filename (with extension)')
                    ])
                    ->fillForm(fn ($record) => [
                        'filename' => $record->filename,
                    ])
                    ->action(function ($record, array $data) {
                        $oldPath = $record->path;
                        $directory = dirname($oldPath);
                        $newPath = $directory . '/' . $data['filename'];

                        if (Storage::disk('public')->exists($oldPath)) {
                            if (Storage::disk('public')->exists($newPath)) {
                                Notification::make()
                                    ->danger()
                                    ->title('File already exists')
                                    ->body("A file named '{$data['filename']}' already exists.")
                                    ->send();
                                return;
                            }

                            Storage::disk('public')->move($oldPath, $newPath);

                            // Clear cached files
                            $this->cachedFiles = null;

                            Notification::make()
                                ->success()
                                ->title('File renamed')
                                ->body("The file has been renamed to '{$data['filename']}'.")
                                ->send();
                        }
                    }),
                Action::make('books')
                    ->label('Show Books')
                    ->icon('heroicon-m-book-open')
                    ->modalHeading('Books Using This PDF')
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
                    ->modalHeading('Delete PDF File')
                    ->modalDescription(fn ($record) => $record->books_count > 0
                        ? "Warning: This file is used by {$record->books_count} book(s). Deleting it will break those references."
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

            // Convert to Eloquent Collection
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

    public function save(): void
    {
        $data = $this->form->getState();

        if (!empty($data['books'])) {
            Notification::make()
                ->success()
                ->title('Files uploaded')
                ->body(count($data['books']) . ' file(s) uploaded successfully.')
                ->send();

            // Reset form
            $this->form->fill();

            // Refresh table
            $this->dispatch('$refresh');
        }
    }

    /**
     * Override to prevent Filament from querying the database for file records.
     * Since FileRecord instances are in-memory only, we need to find them from our cached collection.
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
