<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    protected $fillable = [
        'name',
        'biography',
        'birth_year',
        'death_year',
        'nationality',
        'website'
    ];

    protected $casts = [
        'birth_year' => 'integer',
        'death_year' => 'integer'
    ];

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_authors');
    }
}
