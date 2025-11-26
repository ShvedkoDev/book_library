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

    /**
     * Relationship type constants
     */
    public const TYPE_SAME_VERSION = 'same_version';
    public const TYPE_SAME_LANGUAGE = 'same_language';
    public const TYPE_SUPPORTING = 'supporting';
    public const TYPE_OTHER_LANGUAGE = 'other_language';
    public const TYPE_TRANSLATED = 'translated';  // NEW
    public const TYPE_CUSTOM = 'custom';

    /**
     * Get all available relationship types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_SAME_VERSION => 'Same Version/Edition',
            self::TYPE_SAME_LANGUAGE => 'Omnibus/Collection',
            self::TYPE_SUPPORTING => 'Supporting Materials',
            self::TYPE_OTHER_LANGUAGE => 'Other Language',
            self::TYPE_TRANSLATED => 'Translated',  // NEW
            self::TYPE_CUSTOM => 'Custom',
        ];
    }

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

    public function scopeTranslated($query)
    {
        return $query->where('relationship_type', 'translated');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('relationship_code', $code);
    }
}
