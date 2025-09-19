<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * CMS User Role Model
 *
 * Manages the relationship between users and their assigned roles
 */
class CmsUserRole extends Model
{
    use HasFactory;

    protected $table = 'cms_user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'assigned_at',
        'assigned_by',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user for this role assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role for this assignment
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(CmsRole::class, 'role_id');
    }

    /**
     * Get the user who assigned this role
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for active role assignments
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for expired role assignments
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if role assignment is active
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if role assignment is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Activate role assignment
     */
    public function activate(): self
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate role assignment
     */
    public function deactivate(): self
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    /**
     * Extend role assignment expiration
     */
    public function extend(\Carbon\Carbon $newExpiration): self
    {
        $this->update(['expires_at' => $newExpiration]);
        return $this;
    }
}