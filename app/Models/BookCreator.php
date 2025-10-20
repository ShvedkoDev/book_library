<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCreator extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'creator_id',
        'creator_type',
        'role_description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'creator_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    // Scopes

    public function scopeOfType($query, $type)
    {
        return $query->where('creator_type', $type);
    }

    public function scopeAuthors($query)
    {
        return $query->where('creator_type', 'author');
    }

    public function scopeIllustrators($query)
    {
        return $query->where('creator_type', 'illustrator');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
