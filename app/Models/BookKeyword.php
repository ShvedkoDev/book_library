<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'keyword',
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

    public function scopeSearch($query, $term)
    {
        return $query->where('keyword', 'like', "%{$term}%");
    }
}
