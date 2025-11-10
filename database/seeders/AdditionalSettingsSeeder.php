<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalSettingsSeeder extends Seeder
{
    /**
     * Seed additional recommended settings for the library system.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_description',
                'value' => 'A digital library providing educational resources for Micronesian teachers and students',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Brief description of the library for meta tags and about pages',
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@library.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'General contact email address',
            ],
            [
                'key' => 'timezone',
                'value' => 'Pacific/Chuuk',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application (Pacific/Chuuk, Pacific/Pohnpei, Pacific/Majuro, etc.)',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'Enable or disable site maintenance mode',
            ],

            // Library Settings
            [
                'key' => 'items_per_page',
                'value' => '10',
                'type' => 'integer',
                'group' => 'library',
                'description' => 'Number of books to display per page in library view',
            ],
            [
                'key' => 'featured_books_count',
                'value' => '6',
                'type' => 'integer',
                'group' => 'library',
                'description' => 'Number of featured books to show on homepage',
            ],
            [
                'key' => 'new_books_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'library',
                'description' => 'Number of days to mark a book as "new"',
            ],
            [
                'key' => 'popular_books_threshold',
                'value' => '50',
                'type' => 'integer',
                'group' => 'library',
                'description' => 'Minimum number of views for a book to be considered "popular"',
            ],
            [
                'key' => 'access_request_auto_approve',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Automatically approve access requests without admin review',
            ],
            [
                'key' => 'allow_guest_browsing',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Allow unauthenticated users to browse the library',
            ],
            [
                'key' => 'allow_guest_downloads',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Allow downloads without user login',
            ],
            [
                'key' => 'show_download_count',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Display download counts publicly on book pages',
            ],
            [
                'key' => 'show_ratings',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Display ratings to public users',
            ],
            [
                'key' => 'show_reviews',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Display reviews to public users',
            ],
            [
                'key' => 'default_sort_order',
                'value' => 'title',
                'type' => 'string',
                'group' => 'library',
                'description' => 'Default book sorting order (title, publication_year, popularity, created_at)',
            ],

            // Email Settings
            [
                'key' => 'from_email',
                'value' => 'noreply@library.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default "from" email address for system emails',
            ],
            [
                'key' => 'from_name',
                'value' => 'Micronesian Teachers Digital Library',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default "from" name for system emails',
            ],
            [
                'key' => 'admin_notification_email',
                'value' => 'admin@library.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Email address for admin notifications (access requests, new reviews, etc.)',
            ],

            // System Settings
            [
                'key' => 'analytics_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable or disable analytics tracking (views, downloads, searches)',
            ],
            [
                'key' => 'cache_duration',
                'value' => '60',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Default cache duration in minutes',
            ],
            [
                'key' => 'session_timeout',
                'value' => '120',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Session timeout in minutes',
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Minimum password length for user accounts',
            ],
            [
                'key' => 'require_email_verification',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Require email verification for new user accounts',
            ],
            [
                'key' => 'max_upload_size',
                'value' => '50',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Maximum file upload size in megabytes',
            ],
            [
                'key' => 'allowed_file_types',
                'value' => '["pdf","epub","doc","docx"]',
                'type' => 'json',
                'group' => 'system',
                'description' => 'JSON array of allowed file extensions for uploads',
            ],
        ];

        foreach ($settings as $setting) {
            // Use updateOrCreate to avoid duplicates if seeder runs multiple times
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Added ' . count($settings) . ' settings to the database.');
        $this->command->info('Access them at: /admin → System → Settings');
    }
}
