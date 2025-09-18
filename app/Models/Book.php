<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'isbn',
        'isbn13',
        'language_id',
        'publisher_id',
        'collection_id',
        'publication_year',
        'edition',
        'pages',
        'description',
        'cover_image',
        'pdf_file',
        'file_size',
        'access_level',
        'is_featured',
        'view_count',
        'download_count',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'pages' => 'integer',
        'file_size' => 'integer',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean'
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_categories');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(BookRating::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BookReview::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(BookDownload::class);
    }
}
