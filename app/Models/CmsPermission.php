<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * CMS Permission Model
 *
 * Manages granular permissions for the CMS system
 */
class CmsPermission extends Model
{
    use HasFactory;

    protected $table = 'cms_permissions';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group',
        'is_system_permission',
    ];

    protected $casts = [
        'is_system_permission' => 'boolean',
    ];

    // Permission groups
    const GROUP_PAGES = 'pages';
    const GROUP_CATEGORIES = 'categories';
    const GROUP_MEDIA = 'media';
    const GROUP_WORKFLOW = 'workflow';
    const GROUP_SETTINGS = 'settings';
    const GROUP_USERS = 'users';
    const GROUP_SYSTEM = 'system';

    /**
     * Get roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(CmsRole::class, 'cms_role_permissions', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Scope by permission group
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope for system permissions
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system_permission', true);
    }

    /**
     * Get all permissions grouped by category
     */
    public static function getGroupedPermissions(): array
    {
        return self::all()->groupBy('group')->map(function ($permissions) {
            return $permissions->mapWithKeys(function ($permission) {
                return [$permission->name => $permission->display_name];
            });
        })->toArray();
    }

    /**
     * Get default permissions configuration
     */
    public static function getDefaultPermissions(): array
    {
        return [
            // Pages permissions
            'cms.pages.view' => [
                'display_name' => 'View Pages',
                'description' => 'Can view pages in admin panel',
                'group' => self::GROUP_PAGES,
            ],
            'cms.pages.create' => [
                'display_name' => 'Create Pages',
                'description' => 'Can create new pages',
                'group' => self::GROUP_PAGES,
            ],
            'cms.pages.edit.own' => [
                'display_name' => 'Edit Own Pages',
                'description' => 'Can edit pages they created',
                'group' => self::GROUP_PAGES,
            ],
            'cms.pages.edit.any' => [
                'display_name' => 'Edit Any Pages',
                'description' => 'Can edit any pages in the system',
                'group' => self::GROUP_PAGES,
            ],
            'cms.pages.delete.own' => [
                'display_name' => 'Delete Own Pages',
                'description' => 'Can delete pages they created',
                'group' => self::GROUP_PAGES,
            ],
            'cms.pages.delete.any' => [
                'display_name' => 'Delete Any Pages',
                'description' => 'Can delete any pages in the system',
                'group' => self::GROUP_PAGES,
            ],
            'cms.pages.publish' => [
                'display_name' => 'Publish Pages',
                'description' => 'Can publish and unpublish pages',
                'group' => self::GROUP_PAGES,
            ],

            // Categories permissions
            'cms.categories.view' => [
                'display_name' => 'View Categories',
                'description' => 'Can view categories in admin panel',
                'group' => self::GROUP_CATEGORIES,
            ],
            'cms.categories.manage' => [
                'display_name' => 'Manage Categories',
                'description' => 'Can create, edit, and delete categories',
                'group' => self::GROUP_CATEGORIES,
            ],
            'cms.categories.assign' => [
                'display_name' => 'Assign Categories',
                'description' => 'Can assign categories to content',
                'group' => self::GROUP_CATEGORIES,
            ],

            // Media permissions
            'cms.media.view' => [
                'display_name' => 'View Media',
                'description' => 'Can view media library',
                'group' => self::GROUP_MEDIA,
            ],
            'cms.media.upload' => [
                'display_name' => 'Upload Media',
                'description' => 'Can upload new media files',
                'group' => self::GROUP_MEDIA,
            ],
            'cms.media.manage' => [
                'display_name' => 'Manage Media',
                'description' => 'Can edit, organize, and delete media',
                'group' => self::GROUP_MEDIA,
            ],
            'cms.media.usage' => [
                'display_name' => 'View Media Usage',
                'description' => 'Can view where media files are used',
                'group' => self::GROUP_MEDIA,
            ],

            // Workflow permissions
            'cms.workflow.submit' => [
                'display_name' => 'Submit for Review',
                'description' => 'Can submit content for review',
                'group' => self::GROUP_WORKFLOW,
            ],
            'cms.workflow.review' => [
                'display_name' => 'Review Content',
                'description' => 'Can review submitted content',
                'group' => self::GROUP_WORKFLOW,
            ],
            'cms.workflow.approve' => [
                'display_name' => 'Approve Content',
                'description' => 'Can approve content for publication',
                'group' => self::GROUP_WORKFLOW,
            ],
            'cms.workflow.reject' => [
                'display_name' => 'Reject Content',
                'description' => 'Can reject submitted content',
                'group' => self::GROUP_WORKFLOW,
            ],
            'cms.workflow.manage' => [
                'display_name' => 'Manage Workflow',
                'description' => 'Can manage workflow settings and assignments',
                'group' => self::GROUP_WORKFLOW,
            ],

            // Settings permissions
            'cms.settings.view' => [
                'display_name' => 'View Settings',
                'description' => 'Can view CMS settings',
                'group' => self::GROUP_SETTINGS,
            ],
            'cms.settings.manage' => [
                'display_name' => 'Manage Settings',
                'description' => 'Can modify CMS settings',
                'group' => self::GROUP_SETTINGS,
            ],

            // Users and roles permissions
            'cms.users.view' => [
                'display_name' => 'View Users',
                'description' => 'Can view user list and profiles',
                'group' => self::GROUP_USERS,
            ],
            'cms.users.manage' => [
                'display_name' => 'Manage Users',
                'description' => 'Can create, edit, and delete users',
                'group' => self::GROUP_USERS,
            ],
            'cms.roles.view' => [
                'display_name' => 'View Roles',
                'description' => 'Can view roles and permissions',
                'group' => self::GROUP_USERS,
            ],
            'cms.roles.manage' => [
                'display_name' => 'Manage Roles',
                'description' => 'Can create, edit, and assign roles',
                'group' => self::GROUP_USERS,
            ],

            // System permissions
            'cms.system.access' => [
                'display_name' => 'Access CMS',
                'description' => 'Basic permission to access CMS admin panel',
                'group' => self::GROUP_SYSTEM,
            ],
            'cms.system.audit' => [
                'display_name' => 'View Audit Logs',
                'description' => 'Can view system audit logs',
                'group' => self::GROUP_SYSTEM,
            ],
            'cms.system.maintenance' => [
                'display_name' => 'System Maintenance',
                'description' => 'Can perform system maintenance tasks',
                'group' => self::GROUP_SYSTEM,
            ],
        ];
    }

    /**
     * Create default system permissions
     */
    public static function createDefaultPermissions(): void
    {
        $defaultPermissions = self::getDefaultPermissions();

        foreach ($defaultPermissions as $name => $config) {
            self::updateOrCreate(
                ['name' => $name],
                array_merge($config, [
                    'name' => $name,
                    'is_system_permission' => true
                ])
            );
        }
    }

    /**
     * Check if permission exists
     */
    public static function exists(string $permission): bool
    {
        return self::where('name', $permission)->exists();
    }

    /**
     * Get permission by name
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }

    /**
     * Get permissions for a specific group
     */
    public static function getByGroup(string $group): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('group', $group)->orderBy('display_name')->get();
    }

    /**
     * Get all permission groups
     */
    public static function getGroups(): array
    {
        return [
            self::GROUP_PAGES => 'Pages',
            self::GROUP_CATEGORIES => 'Categories',
            self::GROUP_MEDIA => 'Media',
            self::GROUP_WORKFLOW => 'Workflow',
            self::GROUP_SETTINGS => 'Settings',
            self::GROUP_USERS => 'Users & Roles',
            self::GROUP_SYSTEM => 'System',
        ];
    }
}