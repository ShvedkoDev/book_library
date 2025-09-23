<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'bio',
        'avatar',
        'phone',
        'department',
        'is_cms_user',
        'cms_preferences',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_cms_user' => 'boolean',
            'is_active' => 'boolean',
            'last_cms_access' => 'datetime',
            'cms_preferences' => 'array',
        ];
    }

    /**
     * Get CMS roles for this user
     */
    public function cmsRoles(): BelongsToMany
    {
        return $this->belongsToMany(CmsRole::class, 'cms_user_roles', 'user_id', 'role_id')
            ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get active CMS roles for this user
     */
    public function activeCmsRoles(): BelongsToMany
    {
        return $this->cmsRoles()
            ->wherePivot('is_active', true)
            ->where(function ($query) {
                $query->whereNull('cms_user_roles.expires_at')
                      ->orWhere('cms_user_roles.expires_at', '>', now());
            });
    }

    /**
     * Get user role assignments
     */
    public function cmsUserRoles(): HasMany
    {
        return $this->hasMany(CmsUserRole::class);
    }

    /**
     * Get pages created by this user
     */
    public function createdPages(): HasMany
    {
        return $this->hasMany(Page::class, 'created_by');
    }

    /**
     * Get pages updated by this user
     */
    public function updatedPages(): HasMany
    {
        return $this->hasMany(Page::class, 'updated_by');
    }

    /**
     * Get workflow items where user is author
     */
    public function authoredWorkflows(): HasMany
    {
        return $this->hasMany(CmsContentWorkflow::class, 'author_id');
    }

    /**
     * Get workflow items assigned to user for review
     */
    public function reviewWorkflows(): HasMany
    {
        return $this->hasMany(CmsContentWorkflow::class, 'reviewer_id');
    }

    /**
     * Get workflow items approved by user
     */
    public function approvedWorkflows(): HasMany
    {
        return $this->hasMany(CmsContentWorkflow::class, 'approver_id');
    }

    /**
     * Get audit logs for this user
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(CmsAuditLog::class);
    }

    /**
     * Scope for CMS users
     */
    public function scopeCmsUsers(Builder $query): Builder
    {
        return $query->where('is_cms_user', true);
    }

    /**
     * Scope for active users
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if user has CMS role
     */
    public function hasCmsRole(string|CmsRole $role): bool
    {
        if (is_string($role)) {
            return $this->activeCmsRoles()->where('name', $role)->exists();
        }

        return $this->activeCmsRoles()->where('cms_roles.id', $role->id)->exists();
    }

    /**
     * Check if user has any of the given CMS roles
     */
    public function hasAnyCmsRole(array $roles): bool
    {
        $roleNames = [];
        foreach ($roles as $role) {
            $roleNames[] = is_string($role) ? $role : $role->name;
        }

        return $this->activeCmsRoles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has CMS permission
     */
    public function hasCmsPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->hasCmsRole(CmsRole::SUPER_ADMIN)) {
            return true;
        }

        $roles = $this->activeCmsRoles()->get();

        foreach ($roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given CMS permissions
     */
    public function hasAnyCmsPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasCmsPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all given CMS permissions
     */
    public function hasAllCmsPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasCmsPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Assign CMS role to user
     */
    public function assignCmsRole(string|CmsRole $role, ?User $assignedBy = null, ?\Carbon\Carbon $expiresAt = null): self
    {
        if (is_string($role)) {
            $role = CmsRole::where('name', $role)->firstOrFail();
        }

        // Check if user already has this role
        $existingRole = $this->cmsUserRoles()
            ->where('role_id', $role->id)
            ->where('is_active', true)
            ->first();

        if ($existingRole) {
            return $this; // Already has role
        }

        $this->cmsUserRoles()->create([
            'role_id' => $role->id,
            'assigned_at' => now(),
            'assigned_by' => $assignedBy?->id,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        // Mark as CMS user if not already
        if (!$this->is_cms_user) {
            $this->update(['is_cms_user' => true]);
        }

        return $this;
    }

    /**
     * Remove CMS role from user
     */
    public function removeCmsRole(string|CmsRole $role): self
    {
        if (is_string($role)) {
            $role = CmsRole::where('name', $role)->firstOrFail();
        }

        $this->cmsUserRoles()
            ->where('role_id', $role->id)
            ->update(['is_active' => false]);

        return $this;
    }

    /**
     * Sync CMS roles for user
     */
    public function syncCmsRoles(array $roles, ?User $assignedBy = null): self
    {
        // Deactivate all current roles
        $this->cmsUserRoles()->update(['is_active' => false]);

        // Assign new roles
        foreach ($roles as $role) {
            $this->assignCmsRole($role, $assignedBy);
        }

        return $this;
    }

    /**
     * Get all CMS permission names for user
     */
    public function getCmsPermissionNames(): array
    {
        $permissions = [];

        $roles = $this->activeCmsRoles()->get();

        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $role->getPermissionNames());
        }

        return array_unique($permissions);
    }

    /**
     * Get user's highest CMS role level
     */
    public function getHighestCmsRoleLevel(): int
    {
        return $this->activeCmsRoles()->max('level') ?: 0;
    }

    /**
     * Check if user can access CMS
     */
    public function canAccessCms(): bool
    {
        return $this->is_cms_user
            && $this->is_active
            && $this->hasCmsPermission('cms.system.access');
    }

    /**
     * Update last CMS access time
     */
    public function updateCmsAccess(): self
    {
        $this->update(['last_cms_access' => now()]);
        return $this;
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }

        return $this->name;
    }

    /**
     * Get user's initials
     */
    public function getInitialsAttribute(): string
    {
        $name = $this->full_name;
        $nameParts = explode(' ', $name);

        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    /**
     * Get user's avatar URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        // Generate avatar using initials
        return "https://ui-avatars.com/api/?name={$this->initials}&size=100&background=0D47A1&color=fff";
    }

    /**
     * Get user statistics
     */
    public function getCmsStats(): array
    {
        return [
            'pages_created' => $this->createdPages()->count(),
            'pages_updated' => $this->updatedPages()->count(),
            'workflows_authored' => $this->authoredWorkflows()->count(),
            'workflows_reviewed' => $this->reviewWorkflows()->count(),
            'workflows_approved' => $this->approvedWorkflows()->count(),
            'last_cms_access' => $this->last_cms_access,
            'roles_count' => $this->activeCmsRoles()->count(),
        ];
    }
}