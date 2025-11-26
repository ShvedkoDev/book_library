<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookIdentifier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'identifier_type',
        'identifier_value',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'identifier_type' => 'string',
    ];

    /**
     * Available identifier types
     */
    public const TYPE_OCLC = 'oclc';
    public const TYPE_ISBN = 'isbn';
    public const TYPE_ISBN13 = 'isbn13';
    public const TYPE_ISSN = 'issn';
    public const TYPE_DOI = 'doi';
    public const TYPE_LCCN = 'lccn';
    public const TYPE_OTHER = 'other';

    /**
     * Get all available identifier types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_OCLC => 'OCLC Number',
            self::TYPE_ISBN => 'ISBN',
            self::TYPE_ISBN13 => 'ISBN-13',
            self::TYPE_ISSN => 'ISSN',
            self::TYPE_DOI => 'DOI',
            self::TYPE_LCCN => 'LCCN',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the book that owns the identifier.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get a formatted display name for the identifier type.
     */
    public function getTypeDisplayAttribute(): string
    {
        return self::getTypes()[$this->identifier_type] ?? $this->identifier_type;
    }

    /**
     * Scope a query to only include identifiers of a given type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('identifier_type', $type);
    }
}
