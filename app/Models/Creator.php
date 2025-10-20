<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'biography',
        'birth_year',
        'death_year',
        'nationality',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'birth_year' => 'integer',
            'death_year' => 'integer',
        ];
    }

    // Relationships

    public function bookCreators()
    {
        return $this->hasMany(BookCreator::class);
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_creators')
            ->withPivot('creator_type', 'role_description', 'sort_order')
            ->withTimestamps();
    }

    public function authoredBooks()
    {
        return $this->belongsToMany(Book::class, 'book_creators')
            ->wherePivot('creator_type', 'author')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function illustratedBooks()
    {
        return $this->belongsToMany(Book::class, 'book_creators')
            ->wherePivot('creator_type', 'illustrator')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    // Scopes

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('biography', 'like', "%{$term}%");
    }

    public function scopeByNationality($query, $nationality)
    {
        return $query->where('nationality', $nationality);
    }

    // Helper Methods

    public function getFullTextAttribute()
    {
        return $this->name . ' ' . ($this->biography ?? '');
    }
}
