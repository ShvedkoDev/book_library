<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'library_code',
        'library_name',
        'reference_number',
        'call_number',
        'catalog_link',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Scopes

    public function scopeByLibraryCode($query, $code)
    {
        return $query->where('library_code', $code);
    }

    public function scopeUH($query)
    {
        return $query->where('library_code', 'UH');
    }

    public function scopeCOM($query)
    {
        return $query->where('library_code', 'COM');
    }

    // Helper Methods

    public function hasPhysicalCopy()
    {
        return !empty($this->reference_number) || !empty($this->call_number);
    }
}
