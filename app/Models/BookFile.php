<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'file_type',
        'file_path',
        'filename',
        'file_size',
        'mime_type',
        'is_primary',
        'digital_source',
        'external_url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'file_size' => 'integer',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeAlternative($query)
    {
        return $query->where('is_primary', false);
    }

    public function scopePdfs($query)
    {
        return $query->where('file_type', 'pdf');
    }

    public function scopeThumbnails($query)
    {
        return $query->where('file_type', 'thumbnail');
    }

    public function scopeAudio($query)
    {
        return $query->where('file_type', 'audio');
    }

    public function scopeVideo($query)
    {
        return $query->where('file_type', 'video');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper Methods

    public function getFullPath()
    {
        return storage_path('app/' . $this->file_path);
    }

    public function getPublicUrl()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFileSizeFormatted()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
