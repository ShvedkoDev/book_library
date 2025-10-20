<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'language_id',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'language_id' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    // Scopes

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }
}
