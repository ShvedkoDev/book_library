<?php

namespace App\Filament\Pages;

use App\Models\Book;
use App\Models\BookFile;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class MediaHealthCheck extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.media-health-check';

    protected static ?string $navigationGroup = 'Media';

    protected static ?string $navigationLabel = 'Media Health Check';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Media Health Check';

    // Scan results
    public array $booksWithoutPdf = [];
    public array $booksWithoutCover = [];
    public array $booksWithMissingPdfFiles = [];
    public array $booksWithMissingCoverFiles = [];
    public array $orphanedFiles = [];
    public bool $scanCompleted = false;
    public array $stats = [];

    public function mount(): void
    {
        $this->resetScan();
    }

    protected function resetScan(): void
    {
        $this->booksWithoutPdf = [];
        $this->booksWithoutCover = [];
        $this->booksWithMissingPdfFiles = [];
        $this->booksWithMissingCoverFiles = [];
        $this->orphanedFiles = [];
        $this->scanCompleted = false;
        $this->stats = [
            'total_books' => 0,
            'books_without_pdf' => 0,
            'books_without_cover' => 0,
            'books_with_missing_pdf_files' => 0,
            'books_with_missing_cover_files' => 0,
            'orphaned_files' => 0,
            'total_files_in_storage' => 0,
            'total_book_files_in_db' => 0,
        ];
    }

    public function startScan(): void
    {
        $this->resetScan();

        try {
            // Scan books
            $this->scanBooks();

            // Scan orphaned files
            $this->scanOrphanedFiles();

            $this->scanCompleted = true;

            Notification::make()
                ->success()
                ->title('Scan completed')
                ->body('Media health check completed successfully.')
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Scan failed')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }

    protected function scanBooks(): void
    {
        $books = Book::with(['files'])->get();
        $this->stats['total_books'] = $books->count();

        foreach ($books as $book) {
            // Check for PDF
            $pdfFiles = $book->files->where('file_type', 'pdf');

            if ($pdfFiles->isEmpty()) {
                $this->booksWithoutPdf[] = [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'internal_id' => $book->internal_id,
                    'palm_code' => $book->palm_code,
                ];
                $this->stats['books_without_pdf']++;
            } else {
                // Check if PDF files actually exist in storage
                foreach ($pdfFiles as $pdfFile) {
                    if ($pdfFile->file_path && !Storage::disk('public')->exists($pdfFile->file_path)) {
                        $this->booksWithMissingPdfFiles[] = [
                            'id' => $book->id,
                            'title' => $book->title,
                            'slug' => $book->slug,
                            'file_id' => $pdfFile->id,
                            'file_path' => $pdfFile->file_path,
                            'filename' => $pdfFile->filename,
                        ];
                        $this->stats['books_with_missing_pdf_files']++;
                    }
                }
            }

            // Check for Cover/Thumbnail
            $coverFiles = $book->files->where('file_type', 'thumbnail');

            if ($coverFiles->isEmpty()) {
                $this->booksWithoutCover[] = [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'internal_id' => $book->internal_id,
                    'palm_code' => $book->palm_code,
                ];
                $this->stats['books_without_cover']++;
            } else {
                // Check if cover files actually exist in storage
                foreach ($coverFiles as $coverFile) {
                    if ($coverFile->file_path && !Storage::disk('public')->exists($coverFile->file_path)) {
                        $this->booksWithMissingCoverFiles[] = [
                            'id' => $book->id,
                            'title' => $book->title,
                            'slug' => $book->slug,
                            'file_id' => $coverFile->id,
                            'file_path' => $coverFile->file_path,
                            'filename' => $coverFile->filename,
                        ];
                        $this->stats['books_with_missing_cover_files']++;
                    }
                }
            }
        }
    }

    protected function scanOrphanedFiles(): void
    {
        // Get all files from storage
        $storageFiles = Storage::disk('public')->files('books');
        $this->stats['total_files_in_storage'] = count($storageFiles);

        // Get all files from database
        $dbFiles = BookFile::all()->pluck('file_path')->toArray();
        $this->stats['total_book_files_in_db'] = count($dbFiles);

        // Find files in storage but not in database
        foreach ($storageFiles as $storageFile) {
            // Skip if file is in database
            if (in_array($storageFile, $dbFiles)) {
                continue;
            }

            // Check with case-insensitive comparison
            $found = false;
            foreach ($dbFiles as $dbFile) {
                if (strcasecmp($storageFile, $dbFile) === 0) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $fullPath = Storage::disk('public')->path($storageFile);
                $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
                $fileType = $this->getFileType(pathinfo($storageFile, PATHINFO_EXTENSION));

                $this->orphanedFiles[] = [
                    'path' => $storageFile,
                    'filename' => basename($storageFile),
                    'size' => $this->formatBytes($fileSize),
                    'type' => $fileType,
                    'modified' => file_exists($fullPath) ? date('Y-m-d H:i:s', filemtime($fullPath)) : 'Unknown',
                ];
                $this->stats['orphaned_files']++;
            }
        }
    }

    protected function getFileType(string $extension): string
    {
        return match(strtolower($extension)) {
            'pdf' => 'PDF',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'Image',
            'mp4', 'avi', 'mov', 'wmv' => 'Video',
            'mp3', 'wav', 'ogg' => 'Audio',
            default => 'Other',
        };
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function deleteOrphanedFile(string $path): void
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                // Remove from orphaned files list
                $this->orphanedFiles = array_filter(
                    $this->orphanedFiles,
                    fn($file) => $file['path'] !== $path
                );
                $this->stats['orphaned_files']--;

                Notification::make()
                    ->success()
                    ->title('File deleted')
                    ->body('Orphaned file has been deleted successfully.')
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Delete failed')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function deleteAllOrphanedFiles(): void
    {
        try {
            $deletedCount = 0;

            foreach ($this->orphanedFiles as $file) {
                if (Storage::disk('public')->exists($file['path'])) {
                    Storage::disk('public')->delete($file['path']);
                    $deletedCount++;
                }
            }

            $this->orphanedFiles = [];
            $this->stats['orphaned_files'] = 0;

            Notification::make()
                ->success()
                ->title('Files deleted')
                ->body("Successfully deleted {$deletedCount} orphaned file(s).")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Delete failed')
                ->body('Error: ' . $e->getMessage())
                ->send();
        }
    }

    public function exportResults(): void
    {
        $results = [
            'scan_date' => now()->toDateTimeString(),
            'statistics' => $this->stats,
            'books_without_pdf' => $this->booksWithoutPdf,
            'books_without_cover' => $this->booksWithoutCover,
            'books_with_missing_pdf_files' => $this->booksWithMissingPdfFiles,
            'books_with_missing_cover_files' => $this->booksWithMissingCoverFiles,
            'orphaned_files' => $this->orphanedFiles,
        ];

        $filename = 'media-health-check-' . now()->format('Y-m-d-His') . '.json';
        $path = storage_path('app/public/' . $filename);

        file_put_contents($path, json_encode($results, JSON_PRETTY_PRINT));

        Notification::make()
            ->success()
            ->title('Export complete')
            ->body("Results exported to {$filename}")
            ->send();
    }
}
