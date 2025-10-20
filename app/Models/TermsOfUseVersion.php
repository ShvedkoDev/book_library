<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsOfUseVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'content',
        'effective_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    public function userAcceptances()
    {
        return $this->hasMany(UserTermsAcceptance::class, 'terms_version_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatest($query)
    {
        return $query->where('is_active', true)
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    public function scopeByVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    // Helper Methods

    public function getAcceptanceCount()
    {
        return $this->userAcceptances()->count();
    }
}
