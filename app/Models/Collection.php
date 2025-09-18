<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_series'
    ];

    protected $casts = [
        'is_series' => 'boolean'
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
