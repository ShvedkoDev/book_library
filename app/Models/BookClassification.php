<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'classification_value_id',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'classification_value_id' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function classificationValue()
    {
        return $this->belongsTo(ClassificationValue::class);
    }

    // Scopes

    public function scopeOfType($query, $typeSlug)
    {
        return $query->whereHas('classificationValue.classificationType', function ($q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }
}
