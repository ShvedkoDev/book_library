<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * CMS Role Model
 *
 * Manages user roles and their associated permissions in the CMS
 */
class CmsRole extends Model
{
    use HasFactory;

    protected $table = 'cms_roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_system_role',
        'level',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system_role' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    // Role level constants
    const LEVEL_SUPER_ADMIN = 100;
    const LEVEL_EDITOR = 80;
    const LEVEL_REVIEWER = 60;
    const LEVEL_AUTHOR = 40;
    const LEVEL_CONTRIBUTOR = 20;
    const LEVEL_VIEWER = 10;

    // System role names
    const SUPER_ADMIN = 'super_admin';
    const EDITOR = 'editor';
    const AUTHOR = 'author';
    const CONTRIBUTOR = 'contributor';
    const REVIEWER = 'reviewer';
    const VIEWER = 'viewer';

    /**
     * Get users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cms_user_roles', 'role_id', 'user_id')
            ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(CmsPermission::class, 'cms_role_permissions', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    /**
     * Get user role assignments
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(CmsUserRole::class, 'role_id');
    }

    /**
     * Scope for active roles
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for system roles
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system_role', true);
    }

    /**
     * Scope by minimum level
     */
    public function scopeMinLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(string $permission): bool
    {
        // Check direct permissions array
        if (is_array($this->permissions) && in_array($permission, $this->permissions)) {
            return true;
        }

        // Check through permission relationships
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if role has all given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Give permission to role
     */
    public function givePermission(string|CmsPermission $permission): self
    {
        if (is_string($permission)) {
            $permission = CmsPermission::where('name', $permission)->firstOrFail();
        }

        if (!$this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
        }

        return $this;
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(string|CmsPermission $permission): self
    {
        if (is_string($permission)) {
            $permission = CmsPermission::where('name', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission->id);

        return $this;
    }

    /**
     * Sync permissions for role
     */
    public function syncPermissions(array $permissions): self
    {
        $permissionIds = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $permissionModel = CmsPermission::where('name', $permission)->first();
                if ($permissionModel) {
                    $permissionIds[] = $permissionModel->id;
                }
            } elseif ($permission instanceof CmsPermission) {
                $permissionIds[] = $permission->id;
            } elseif (is_numeric($permission)) {
                $permissionIds[] = $permission;
            }
        }

        $this->permissions()->sync($permissionIds);

        return $this;
    }

    /**
     * Get all permission names for this role
     */
    public function getPermissionNames(): array
    {
        return $this->permissions()->pluck('name')->toArray();
    }

    /**
     * Check if role is higher level than another role
     */
    public function isHigherThan(CmsRole $role): bool
    {
        return $this->level > $role->level;
    }

    /**
     * Check if role is system role
     */
    public function isSystemRole(): bool
    {
        return $this->is_system_role;
    }

    /**
     * Get default roles configuration
     */
    public static function getDefaultRoles(): array
    {
        return [
            self::SUPER_ADMIN => [
                'display_name' => 'Super Administrator',
                'description' => 'Full system access including settings and user management',
                'level' => self::LEVEL_SUPER_ADMIN,
                'is_system_role' => true,
                'permissions' => ['*'], // All permissions
            ],
            self::EDITOR => [
                'display_name' => 'Editor',
                'description' => 'Can create, edit, delete all content and manage categories',
                'level' => self::LEVEL_EDITOR,
                'is_system_role' => true,
                'permissions' => [
                    'cms.pages.view', 'cms.pages.create', 'cms.pages.edit.any', 'cms.pages.delete.any',
                    'cms.pages.publish', 'cms.categories.manage', 'cms.media.manage',
                    'cms.workflow.review', 'cms.workflow.approve'
                ],
            ],
            self::REVIEWER => [
                'display_name' => 'Reviewer',
                'description' => 'Can review and approve/reject submitted content',
                'level' => self::LEVEL_REVIEWER,
                'is_system_role' => true,
                'permissions' => [
                    'cms.pages.view', 'cms.workflow.review', 'cms.workflow.approve',
                    'cms.workflow.reject', 'cms.pages.edit.own'
                ],
            ],
            self::AUTHOR => [
                'display_name' => 'Author',
                'description' => 'Can create and edit own content, submit for review',
                'level' => self::LEVEL_AUTHOR,
                'is_system_role' => true,
                'permissions' => [
                    'cms.pages.view', 'cms.pages.create', 'cms.pages.edit.own',
                    'cms.pages.delete.own', 'cms.workflow.submit', 'cms.media.upload'
                ],
            ],
            self::CONTRIBUTOR => [
                'display_name' => 'Contributor',
                'description' => 'Can create content and submit for review, no delete permissions',
                'level' => self::LEVEL_CONTRIBUTOR,
                'is_system_role' => true,
                'permissions' => [
                    'cms.pages.view', 'cms.pages.create', 'cms.pages.edit.own',
                    'cms.workflow.submit', 'cms.media.upload'
                ],
            ],
            self::VIEWER => [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to admin panel',
                'level' => self::LEVEL_VIEWER,
                'is_system_role' => true,
                'permissions' => [
                    'cms.pages.view', 'cms.categories.view', 'cms.media.view'
                ],
            ],
        ];
    }

    /**
     * Create default system roles
     */
    public static function createDefaultRoles(): void
    {
        $defaultRoles = self::getDefaultRoles();

        foreach ($defaultRoles as $name => $config) {
            self::updateOrCreate(
                ['name' => $name],
                array_merge($config, ['name' => $name])
            );
        }
    }

    /**
     * Get role statistics
     */
    public function getStats(): array
    {
        return [
            'total_users' => $this->users()->count(),
            'active_users' => $this->users()->wherePivot('is_active', true)->count(),
            'permissions_count' => $this->permissions()->count(),
            'created_at' => $this->created_at,
        ];
    }
}