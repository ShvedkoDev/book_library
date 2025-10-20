<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassificationValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'classification_type_id',
        'value',
        'parent_id',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'classification_type_id' => 'integer',
            'parent_id' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function classificationType()
    {
        return $this->belongsTo(ClassificationType::class);
    }

    public function parent()
    {
        return $this->belongsTo(ClassificationValue::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ClassificationValue::class, 'parent_id');
    }

    public function bookClassifications()
    {
        return $this->hasMany(BookClassification::class);
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_classifications', 'classification_value_id', 'book_id')
            ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeOfType($query, $typeId)
    {
        return $query->where('classification_type_id', $typeId);
    }

    public function scopeByTypeSlug($query, $slug)
    {
        return $query->whereHas('classificationType', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }
}
