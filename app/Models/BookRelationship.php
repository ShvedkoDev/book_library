<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'related_book_id',
        'relationship_type',
        'relationship_code',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'related_book_id' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function relatedBook()
    {
        return $this->belongsTo(Book::class, 'related_book_id');
    }

    // Scopes

    public function scopeOfType($query, $type)
    {
        return $query->where('relationship_type', $type);
    }

    public function scopeSameVersion($query)
    {
        return $query->where('relationship_type', 'same_version');
    }

    public function scopeSameLanguage($query)
    {
        return $query->where('relationship_type', 'same_language');
    }

    public function scopeSupporting($query)
    {
        return $query->where('relationship_type', 'supporting');
    }

    public function scopeOtherLanguage($query)
    {
        return $query->where('relationship_type', 'other_language');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('relationship_code', $code);
    }
}
