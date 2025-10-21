<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'allow_multiple',
        'use_for_filtering',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allow_multiple' => 'boolean',
            'use_for_filtering' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function classificationValues()
    {
        return $this->hasMany(ClassificationValue::class);
    }

    public function activeValues()
    {
        return $this->hasMany(ClassificationValue::class)->where('is_active', true);
    }

    public function books()
    {
        return $this->hasManyThrough(
            Book::class,
            ClassificationValue::class,
            'classification_type_id', // Foreign key on classification_values table
            'id', // Foreign key on books table (we'll join via book_classifications)
            'id', // Local key on classification_types table
            'id' // Local key on classification_values table
        )->join('book_classifications', 'book_classifications.classification_value_id', '=', 'classification_values.id')
         ->where('book_classifications.book_id', '=', \DB::raw('books.id'));
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForFiltering($query)
    {
        return $query->where('use_for_filtering', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }
}
