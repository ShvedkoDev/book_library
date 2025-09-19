<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CmsRole;
use App\Models\CmsPermission;

class CmsRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions first
        $this->createPermissions();

        // Create roles and assign permissions
        $this->createRoles();
    }

    private function createPermissions(): void
    {
        $permissions = [
            // System permissions
            ['name' => 'cms.system.access', 'display_name' => 'Access CMS', 'description' => 'Basic access to CMS interface', 'group' => 'system'],
            ['name' => 'cms.system.settings', 'display_name' => 'Manage System Settings', 'description' => 'Configure CMS settings', 'group' => 'system'],

            // Page permissions
            ['name' => 'cms.pages.view', 'display_name' => 'View Pages', 'description' => 'View page list and details', 'group' => 'pages'],
            ['name' => 'cms.pages.create', 'display_name' => 'Create Pages', 'description' => 'Create new pages', 'group' => 'pages'],
            ['name' => 'cms.pages.edit.own', 'display_name' => 'Edit Own Pages', 'description' => 'Edit pages created by user', 'group' => 'pages'],
            ['name' => 'cms.pages.edit.any', 'display_name' => 'Edit Any Pages', 'description' => 'Edit all pages', 'group' => 'pages'],
            ['name' => 'cms.pages.delete.own', 'display_name' => 'Delete Own Pages', 'description' => 'Delete pages created by user', 'group' => 'pages'],
            ['name' => 'cms.pages.delete.any', 'display_name' => 'Delete Any Pages', 'description' => 'Delete all pages', 'group' => 'pages'],
            ['name' => 'cms.pages.publish', 'display_name' => 'Publish Pages', 'description' => 'Publish and unpublish pages', 'group' => 'pages'],

            // Category permissions
            ['name' => 'cms.categories.view', 'display_name' => 'View Categories', 'description' => 'View category list and details', 'group' => 'categories'],
            ['name' => 'cms.categories.create', 'display_name' => 'Create Categories', 'description' => 'Create new categories', 'group' => 'categories'],
            ['name' => 'cms.categories.edit', 'display_name' => 'Edit Categories', 'description' => 'Edit categories', 'group' => 'categories'],
            ['name' => 'cms.categories.delete', 'display_name' => 'Delete Categories', 'description' => 'Delete categories', 'group' => 'categories'],

            // Media permissions
            ['name' => 'cms.media.view', 'display_name' => 'View Media', 'description' => 'View media library', 'group' => 'media'],
            ['name' => 'cms.media.upload', 'display_name' => 'Upload Media', 'description' => 'Upload files to media library', 'group' => 'media'],
            ['name' => 'cms.media.edit', 'display_name' => 'Edit Media', 'description' => 'Edit media files and metadata', 'group' => 'media'],
            ['name' => 'cms.media.delete', 'display_name' => 'Delete Media', 'description' => 'Delete media files', 'group' => 'media'],

            // Workflow permissions
            ['name' => 'cms.workflow.submit', 'display_name' => 'Submit Content', 'description' => 'Submit content for review', 'group' => 'workflow'],
            ['name' => 'cms.workflow.review', 'display_name' => 'Review Content', 'description' => 'Review submitted content', 'group' => 'workflow'],
            ['name' => 'cms.workflow.approve', 'display_name' => 'Approve Content', 'description' => 'Approve content for publishing', 'group' => 'workflow'],
            ['name' => 'cms.workflow.manage', 'display_name' => 'Manage Workflows', 'description' => 'Manage workflow settings', 'group' => 'workflow'],

            // User permissions
            ['name' => 'cms.users.view', 'display_name' => 'View Users', 'description' => 'View user list and profiles', 'group' => 'users'],
            ['name' => 'cms.users.edit', 'display_name' => 'Edit Users', 'description' => 'Edit user profiles', 'group' => 'users'],
            ['name' => 'cms.users.roles', 'display_name' => 'Manage User Roles', 'description' => 'Assign roles to users', 'group' => 'users'],

            // Role permissions
            ['name' => 'cms.roles.view', 'display_name' => 'View Roles', 'description' => 'View roles and permissions', 'group' => 'roles'],
            ['name' => 'cms.roles.create', 'display_name' => 'Create Roles', 'description' => 'Create new roles', 'group' => 'roles'],
            ['name' => 'cms.roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Edit roles and permissions', 'group' => 'roles'],
            ['name' => 'cms.roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Delete custom roles', 'group' => 'roles'],
        ];

        foreach ($permissions as $permission) {
            CmsPermission::firstOrCreate(
                ['name' => $permission['name']],
                $permission + ['is_system_permission' => true]
            );
        }
    }

    private function createRoles(): void
    {
        // Super Admin Role
        $superAdmin = CmsRole::firstOrCreate([
            'name' => 'super-admin',
        ], [
            'display_name' => 'Super Administrator',
            'description' => 'Full system access with all permissions',
            'level' => 100,
            'is_system_role' => true,
            'is_active' => true,
        ]);

        // Assign all permissions to super admin
        $superAdmin->permissions()->sync(CmsPermission::all()->pluck('id'));

        // Editor Role
        $editor = CmsRole::firstOrCreate([
            'name' => 'editor',
        ], [
            'display_name' => 'Editor',
            'description' => 'Can manage content, categories, and approve content',
            'level' => 80,
            'is_system_role' => true,
            'is_active' => true,
        ]);

        $editorPermissions = CmsPermission::whereIn('name', [
            'cms.system.access',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit.any', 'cms.pages.delete.any', 'cms.pages.publish',
            'cms.categories.view', 'cms.categories.create', 'cms.categories.edit', 'cms.categories.delete',
            'cms.media.view', 'cms.media.upload', 'cms.media.edit', 'cms.media.delete',
            'cms.workflow.submit', 'cms.workflow.review', 'cms.workflow.approve',
            'cms.users.view',
        ])->pluck('id');
        $editor->permissions()->sync($editorPermissions);

        // Author Role
        $author = CmsRole::firstOrCreate([
            'name' => 'author',
        ], [
            'display_name' => 'Author',
            'description' => 'Can create and edit own content',
            'level' => 40,
            'is_system_role' => true,
            'is_active' => true,
        ]);

        $authorPermissions = CmsPermission::whereIn('name', [
            'cms.system.access',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit.own', 'cms.pages.delete.own',
            'cms.categories.view',
            'cms.media.view', 'cms.media.upload', 'cms.media.edit',
            'cms.workflow.submit',
        ])->pluck('id');
        $author->permissions()->sync($authorPermissions);

        // Contributor Role
        $contributor = CmsRole::firstOrCreate([
            'name' => 'contributor',
        ], [
            'display_name' => 'Contributor',
            'description' => 'Can create content but needs approval to publish',
            'level' => 20,
            'is_system_role' => true,
            'is_active' => true,
        ]);

        $contributorPermissions = CmsPermission::whereIn('name', [
            'cms.system.access',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit.own',
            'cms.categories.view',
            'cms.media.view', 'cms.media.upload',
            'cms.workflow.submit',
        ])->pluck('id');
        $contributor->permissions()->sync($contributorPermissions);

        // Reviewer Role
        $reviewer = CmsRole::firstOrCreate([
            'name' => 'reviewer',
        ], [
            'display_name' => 'Reviewer',
            'description' => 'Can review and approve content submitted by others',
            'level' => 60,
            'is_system_role' => true,
            'is_active' => true,
        ]);

        $reviewerPermissions = CmsPermission::whereIn('name', [
            'cms.system.access',
            'cms.pages.view', 'cms.pages.edit.any',
            'cms.categories.view',
            'cms.media.view',
            'cms.workflow.review', 'cms.workflow.approve',
        ])->pluck('id');
        $reviewer->permissions()->sync($reviewerPermissions);

        // Viewer Role
        $viewer = CmsRole::firstOrCreate([
            'name' => 'viewer',
        ], [
            'display_name' => 'Viewer',
            'description' => 'Read-only access to CMS content',
            'level' => 1,
            'is_system_role' => true,
            'is_active' => true,
        ]);

        $viewerPermissions = CmsPermission::whereIn('name', [
            'cms.system.access',
            'cms.pages.view',
            'cms.categories.view',
            'cms.media.view',
        ])->pluck('id');
        $viewer->permissions()->sync($viewerPermissions);
    }
}