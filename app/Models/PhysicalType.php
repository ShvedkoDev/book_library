<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PhysicalType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($physicalType) {
            if (empty($physicalType->slug)) {
                $physicalType->slug = Str::slug($physicalType->name);
            }
        });
    }

    /**
     * Get all books with this physical type
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'physical_type_id');
    }

    /**
     * Scope to only get active physical types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get or create a physical type by name
     *
     * @param string $name
     * @return PhysicalType
     */
    public static function getOrCreate(string $name): PhysicalType
    {
        // Trim and normalize the name
        $name = trim($name);

        // Try to find existing physical type (case-insensitive)
        $physicalType = static::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if ($physicalType) {
            return $physicalType;
        }

        // Create new physical type
        return static::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
            'sort_order' => static::max('sort_order') + 1,
        ]);
    }
}
