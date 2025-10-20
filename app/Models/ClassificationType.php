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
