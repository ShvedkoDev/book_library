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
                'key' => 'site_name',
                'value' => 'FSM National Vernacular Language Arts (VLA) Curriculum',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Name of the website displayed in header, title tags, and throughout the site',
            ],
            [
                'key' => 'site_description',
                'value' => 'A digital library providing educational resources for Micronesian teachers and students',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Brief description of the library for meta tags and about pages',
            ],
            [
                'key' => 'site_logo',
                'value' => '/images/logo.png',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Path to the site logo image',
            ],
            [
                'key' => 'site_favicon',
                'value' => '/favicon.ico',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Path to the site favicon',
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@library.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'General contact email address displayed on contact pages',
            ],
            [
                'key' => 'contact_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Contact phone number',
            ],
            [
                'key' => 'contact_address',
                'value' => '',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Physical address of the organization',
            ],
            [
                'key' => 'timezone',
                'value' => 'Pacific/Chuuk',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application (Pacific/Chuuk, Pacific/Pohnpei, Pacific/Majuro, etc.)',
            ],
            [
                'key' => 'language',
                'value' => 'en',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default language for the website interface',
            ],
            [
                'key' => 'social_facebook',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Facebook page URL',
            ],
            [
                'key' => 'social_twitter',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Twitter/X profile URL',
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
                'key' => 'default_sort_order',
                'value' => 'title',
                'type' => 'string',
                'group' => 'library',
                'description' => 'Default book sorting order (title, publication_year, popularity, created_at)',
            ],
            [
                'key' => 'related_books_count',
                'value' => '5',
                'type' => 'integer',
                'group' => 'library',
                'description' => 'Number of related books to show on book detail pages',
            ],
            [
                'key' => 'enable_advanced_search',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'library',
                'description' => 'Enable advanced search with multiple filters',
            ],

            // Feature Toggles
            [
                'key' => 'enable_ratings',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable book rating functionality',
            ],
            [
                'key' => 'enable_reviews',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable book review functionality',
            ],
            [
                'key' => 'enable_bookmarks',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable bookmark/favorites functionality',
            ],
            [
                'key' => 'enable_notes',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable user notes on books',
            ],
            [
                'key' => 'enable_sharing',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable social sharing of books',
            ],
            [
                'key' => 'enable_pdf_viewer',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Enable in-browser PDF viewer (vs download only)',
            ],
            [
                'key' => 'enable_user_registration',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Allow new users to register accounts',
            ],
            [
                'key' => 'enable_access_requests',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Allow users to request access to restricted books',
            ],
            [
                'key' => 'require_login_to_view',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Require users to login to view book details',
            ],
            [
                'key' => 'require_login_to_download',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'description' => 'Require users to login to download books',
            ],

            // Analytics Settings
            [
                'key' => 'enable_analytics',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'analytics',
                'description' => 'Enable internal analytics tracking (views, downloads, searches)',
            ],
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Analytics Measurement ID (e.g., G-XXXXXXXXXX)',
            ],
            [
                'key' => 'google_tag_manager_id',
                'value' => '',
                'type' => 'string',
                'group' => 'analytics',
                'description' => 'Google Tag Manager ID (e.g., GTM-XXXXXXX)',
            ],
            [
                'key' => 'track_anonymous_users',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'analytics',
                'description' => 'Track actions of non-logged-in users',
            ],
            [
                'key' => 'analytics_retention_days',
                'value' => '365',
                'type' => 'integer',
                'group' => 'analytics',
                'description' => 'Number of days to retain analytics data (0 = forever)',
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
                'value' => 'FSM National Vernacular Language Arts (VLA) Curriculum',
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
            [
                'key' => 'library_email',
                'value' => 'library@library.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'General library contact email',
            ],
            [
                'key' => 'enable_email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Enable email notifications for users and admins',
            ],
            [
                'key' => 'notify_on_new_review',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send email to admins when new review is posted',
            ],
            [
                'key' => 'notify_on_access_request',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send email to admins when access is requested',
            ],
            [
                'key' => 'notify_users_on_approval',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Send email to users when access request is approved',
            ],

            // Maintenance Mode
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'maintenance',
                'description' => 'Enable or disable site maintenance mode',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'We are currently performing scheduled maintenance. Please check back soon.',
                'type' => 'text',
                'group' => 'maintenance',
                'description' => 'Message to display when site is in maintenance mode',
            ],
            [
                'key' => 'maintenance_allow_ips',
                'value' => '[]',
                'type' => 'json',
                'group' => 'maintenance',
                'description' => 'JSON array of IP addresses allowed to access site during maintenance',
            ],
            [
                'key' => 'maintenance_retry_after',
                'value' => '3600',
                'type' => 'integer',
                'group' => 'maintenance',
                'description' => 'Retry-After header value in seconds for maintenance mode',
            ],

            // System Settings
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
                'key' => 'max_upload_size',
                'value' => '50',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Maximum file upload size in megabytes (for admin uploads)',
            ],
            [
                'key' => 'allowed_file_types',
                'value' => '["pdf","epub","doc","docx"]',
                'type' => 'json',
                'group' => 'system',
                'description' => 'JSON array of allowed file extensions for uploads',
            ],
            [
                'key' => 'items_per_admin_page',
                'value' => '25',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Number of items to display per page in admin tables',
            ],
            [
                'key' => 'enable_debug_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable debug mode for troubleshooting (WARNING: shows sensitive data)',
            ],
            [
                'key' => 'backup_retention_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Number of days to keep database backups',
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
