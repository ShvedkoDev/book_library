<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CmsRole;
use App\Models\CmsPermission;
use App\Models\User;

class CmsRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Create default permissions first
        $permissions = CmsPermission::getDefaultPermissions();

        foreach ($permissions as $permissionName => $permissionConfig) {
            CmsPermission::firstOrCreate(['name' => $permissionName], [
                'display_name' => $permissionConfig['display_name'],
                'description' => $permissionConfig['description'],
                'group' => $permissionConfig['group'],
                'is_system_permission' => true
            ]);
        }

        // Create default roles
        $roles = CmsRole::getDefaultRoles();

        foreach ($roles as $roleName => $roleData) {
            $role = CmsRole::firstOrCreate(['name' => $roleName], [
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description'],
                'level' => $roleData['level'],
                'is_system_role' => true
            ]);

            // Assign permissions to the role
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = CmsPermission::where('name', $permissionName)->first();
                if ($permission) {
                    $role->givePermission($permission);
                }
            }
        }

        // Create a default admin user if one doesn't exist
        $adminUser = User::where('email', 'admin@booklib.local')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@booklib.local',
                'password' => bcrypt('admin123'),
                'email_verified_at' => now(),
                'role' => 'admin'
            ]);

            // Assign Super Admin role to the admin user
            $superAdminRole = CmsRole::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                $adminUser->assignCmsRole($superAdminRole);
            }

            $this->command->info('Created admin user: admin@booklib.local (password: admin123)');
        }

        $this->command->info('CMS roles and permissions seeded successfully!');
    }
}