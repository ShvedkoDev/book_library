<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CmsRole;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Super Admin',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_cms_user' => true,
            'is_active' => true
        ]);

        $superAdminRole = CmsRole::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $user->assignCmsRole($superAdminRole);
            echo "Super admin user created with email: admin@example.com and password: password\n";
        } else {
            echo "Super admin role not found\n";
        }
    }
}