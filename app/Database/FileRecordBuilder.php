<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Models\FileRecord;
use App\Models\BookFile;

class FileRecordBuilder extends Builder
{
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

    protected function getFileRecords()
    {
        return collect(Storage::disk('public')->files('books'))
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

    public function get($columns = ['*'])
    {
        return $this->getFileRecords();
    }

    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $items = $this->getFileRecords();
        $paginatedItems = $items->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    public function count()
    {
        return $this->getFileRecords()->count();
    }
}
