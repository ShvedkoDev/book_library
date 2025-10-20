<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeographicLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_type',
        'name',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relationships

    public function parent()
    {
        return $this->belongsTo(GeographicLocation::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(GeographicLocation::class, 'parent_id');
    }

    public function bookLocations()
    {
        return $this->hasMany(BookLocation::class, 'location_id');
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'book_locations', 'location_id', 'book_id')
            ->withTimestamps();
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStates($query)
    {
        return $query->where('location_type', 'state');
    }

    public function scopeIslands($query)
    {
        return $query->where('location_type', 'island');
    }

    public function scopeRegions($query)
    {
        return $query->where('location_type', 'region');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeOfParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }
}
