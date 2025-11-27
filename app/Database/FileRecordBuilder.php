<?php

namespace App\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use App\Models\FileRecord;
use App\Models\BookFile;

class FileRecordBuilder extends Builder
{
    /**
     * Store custom ordering for filesystem records.
     */
    protected array $customOrders = [];

    /**
     * The properties that should be passed through to the query builder.
     * Adding 'orders' allows Filament to access $builder->orders
     */
    protected $propertyPassthru = [
        'from',
        'orders',
    ];

    /**
     * Override to prevent database queries.
     * This builder works with filesystem data, not database tables.
     */
    protected function runSelect()
    {
        return $this->getFileRecords();
    }

    /**
     * Add an "order by" clause to the query.
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->customOrders[] = ['column' => $column, 'direction' => strtolower($direction)];

        // Also update the underlying query builder's orders property
        // so Filament can access it via propertyPassthru
        $this->getQuery()->orders = $this->getQuery()->orders ?? [];
        $this->getQuery()->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction),
        ];

        return $this;
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

    protected function getFileRecords()
    {
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
            });

        // Apply custom ordering if specified
        if (!empty($this->customOrders)) {
            foreach ($this->customOrders as $order) {
                $column = $order['column'];
                $direction = $order['direction'];

                if ($direction === 'desc') {
                    $items = $items->sortByDesc($column);
                } else {
                    $items = $items->sortBy($column);
                }
            }
        } else {
            // Default sort by modified date descending
            $items = $items->sortByDesc('modified');
        }

        return $items->values();
    }

    public function get($columns = ['*'])
    {
        return $this->getFileRecords();
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
    {
        $perPage = $perPage ?: 15;
        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $items = $this->getFileRecords();

        // Use provided total or calculate from items
        $totalCount = $total ?? $items->count();
        $paginatedItems = $items->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $totalCount,
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]
        );
    }

    public function count($columns = '*')
    {
        return $this->getFileRecords()->count();
    }

    /**
     * Get the underlying query builder instance.
     * This is needed for propertyPassthru to work correctly.
     */
    public function toBase()
    {
        // Return the actual query builder so properties like 'orders' can be accessed
        return $this->getQuery();
    }

    /**
     * Override aggregate to prevent database queries.
     */
    public function aggregate($function, $columns = ['*'])
    {
        if ($function === 'count') {
            return $this->count();
        }
        return 0;
    }

    /**
     * Override the newBaseQueryBuilder to prevent database queries.
     * We need to ensure the query builder doesn't try to execute queries.
     */
    protected function newBaseQueryBuilder()
    {
        $query = parent::newBaseQueryBuilder();

        // Initialize orders as empty array to prevent undefined property errors
        $query->orders = [];

        return $query;
    }
}
