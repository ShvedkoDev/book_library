# Settings Parameters Implementation Status

This document tracks all available settings and their integration status in the application.

**Legend:**
- ✅ **Integrated** - Setting is actively used in the application
- ⚠️ **Not Integrated** - Setting exists in database but not yet used in code
- 🔄 **Partial** - Setting is partially integrated

---

## General Settings (Site Configuration)

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `site_name` | string | ✅ Integrated | Name of the website displayed in header, title tags, and throughout the site | - Page titles<br>- Meta tags<br>- Headers<br>- Footers<br>- All layouts |
| `site_description` | text | ✅ Integrated | Brief description of the library for meta tags and about pages | - Meta descriptions<br>- Footer tagline<br>- OG tags |
| `site_logo` | string | ⚠️ Not Integrated | Path to the site logo image | *Need to integrate in header* |
| `site_favicon` | string | ⚠️ Not Integrated | Path to the site favicon | *Need to add to layout head* |
| `contact_email` | string | ✅ Integrated | General contact email address displayed on contact pages | - Shared with all views via SettingsServiceProvider |
| `contact_phone` | string | ⚠️ Not Integrated | Contact phone number | *Available but not displayed* |
| `contact_address` | text | ⚠️ Not Integrated | Physical address of the organization | *Available but not displayed* |
| `timezone` | string | ⚠️ Not Integrated | Default timezone for the application (Pacific/Chuuk, Pacific/Pohnpei, Pacific/Majuro, etc.) | *Need to set in config/app.php* |
| `language` | string | ⚠️ Not Integrated | Default language for the website interface | *Need to set in config/app.php* |
| `social_facebook` | string | ⚠️ Not Integrated | Facebook page URL | *Need to add to footer* |
| `social_twitter` | string | ⚠️ Not Integrated | Twitter/X profile URL | *Need to add to footer* |

---

## Library Settings

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `items_per_page` | integer | ⚠️ Not Integrated | Number of books to display per page in library view | *Need to integrate in LibraryController* |
| `featured_books_count` | integer | ⚠️ Not Integrated | Number of featured books to show on homepage | *Need to integrate in homepage logic* |
| `new_books_days` | integer | ⚠️ Not Integrated | Number of days to mark a book as "new" | *Need to integrate in Book model* |
| `popular_books_threshold` | integer | ⚠️ Not Integrated | Minimum number of views for a book to be considered "popular" | *Need to integrate in Book queries* |
| `access_request_auto_approve` | boolean | ⚠️ Not Integrated | Automatically approve access requests without admin review | *Need to integrate in AccessRequestController* |
| `allow_guest_browsing` | boolean | ⚠️ Not Integrated | Allow unauthenticated users to browse the library | *Need to add middleware check* |
| `allow_guest_downloads` | boolean | ⚠️ Not Integrated | Allow downloads without user login | *Need to integrate in LibraryController* |
| `show_download_count` | boolean | ⚠️ Not Integrated | Display download counts publicly on book pages | *Need to integrate in book detail view* |
| `default_sort_order` | string | ⚠️ Not Integrated | Default book sorting order (title, publication_year, popularity, created_at) | *Need to integrate in LibraryController* |
| `related_books_count` | integer | ⚠️ Not Integrated | Number of related books to show on book detail pages | *Need to integrate in book detail view* |
| `enable_advanced_search` | boolean | ⚠️ Not Integrated | Enable advanced search with multiple filters | *Need to integrate in search UI* |

---

## Feature Toggles

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `enable_ratings` | boolean | ⚠️ Not Integrated | Enable book rating functionality | *Need to hide/show ratings UI* |
| `enable_reviews` | boolean | ⚠️ Not Integrated | Enable book review functionality | *Need to hide/show reviews UI* |
| `enable_bookmarks` | boolean | ⚠️ Not Integrated | Enable bookmark/favorites functionality | *Need to hide/show bookmark buttons* |
| `enable_notes` | boolean | ⚠️ Not Integrated | Enable user notes on books | *Need to hide/show notes UI* |
| `enable_sharing` | boolean | ⚠️ Not Integrated | Enable social sharing of books | *Need to hide/show share buttons* |
| `enable_pdf_viewer` | boolean | ⚠️ Not Integrated | Enable in-browser PDF viewer (vs download only) | *Need to integrate in view PDF logic* |
| `enable_user_registration` | boolean | ⚠️ Not Integrated | Allow new users to register accounts | *Need to hide/show registration link* |
| `enable_access_requests` | boolean | ⚠️ Not Integrated | Allow users to request access to restricted books | *Need to hide/show access request button* |
| `require_login_to_view` | boolean | ⚠️ Not Integrated | Require users to login to view book details | *Need to add middleware/check* |
| `require_login_to_download` | boolean | ⚠️ Not Integrated | Require users to login to download books | *Currently hardcoded as true* |

---

## Analytics Settings

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `enable_analytics` | boolean | ⚠️ Not Integrated | Enable internal analytics tracking (views, downloads, searches) | *Analytics currently always enabled* |
| `google_analytics_id` | string | ⚠️ Not Integrated | Google Analytics Measurement ID (e.g., G-XXXXXXXXXX) | *Need to add GA script to layout* |
| `google_tag_manager_id` | string | ⚠️ Not Integrated | Google Tag Manager ID (e.g., GTM-XXXXXXX) | *Need to add GTM script to layout* |
| `track_anonymous_users` | boolean | ⚠️ Not Integrated | Track actions of non-logged-in users | *Currently always tracking* |
| `analytics_retention_days` | integer | ⚠️ Not Integrated | Number of days to retain analytics data (0 = forever) | *Need to create cleanup job* |

---

## Email Settings

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `from_email` | string | ⚠️ Not Integrated | Default "from" email address for system emails | *Need to set in config/mail.php* |
| `from_name` | string | ⚠️ Not Integrated | Default "from" name for system emails | *Need to set in config/mail.php* |
| `admin_notification_email` | string | ⚠️ Not Integrated | Email address for admin notifications (access requests, new reviews, etc.) | *Need to use in notification logic* |
| `library_email` | string | ✅ Integrated | General library contact email | - Footer contact section<br>- Shared with all views |
| `enable_email_notifications` | boolean | ⚠️ Not Integrated | Enable email notifications for users and admins | *Need to check before sending emails* |
| `notify_on_new_review` | boolean | ⚠️ Not Integrated | Send email to admins when new review is posted | *Need to create notification* |
| `notify_on_access_request` | boolean | ⚠️ Not Integrated | Send email to admins when access is requested | *Need to create notification* |
| `notify_users_on_approval` | boolean | ⚠️ Not Integrated | Send email to users when access request is approved | *Need to create notification* |

---

## Maintenance Mode Settings

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `maintenance_mode` | boolean | ✅ Integrated | Enable or disable site maintenance mode | - CheckMaintenanceMode middleware<br>- Blocks all non-admin access |
| `maintenance_message` | text | ✅ Integrated | Message to display when site is in maintenance mode | - 503 error page |
| `maintenance_allow_ips` | json | ✅ Integrated | JSON array of IP addresses allowed to access site during maintenance | - CheckMaintenanceMode middleware<br>- IP whitelist check |
| `maintenance_retry_after` | integer | ✅ Integrated | Retry-After header value in seconds for maintenance mode | - HTTP Retry-After header |

---

## System Settings

| Setting Key | Type | Status | Description | Where Used |
|-------------|------|--------|-------------|------------|
| `cache_duration` | integer | ⚠️ Not Integrated | Default cache duration in minutes | *Currently hardcoded to 60 in Setting model* |
| `session_timeout` | integer | ⚠️ Not Integrated | Session timeout in minutes | *Need to set in config/session.php* |
| `password_min_length` | integer | ⚠️ Not Integrated | Minimum password length for user accounts | *Need to integrate in validation rules* |
| `max_upload_size` | integer | ⚠️ Not Integrated | Maximum file upload size in megabytes (for admin uploads) | *Need to integrate in upload validation* |
| `allowed_file_types` | json | ⚠️ Not Integrated | JSON array of allowed file extensions for uploads | *Need to integrate in upload validation* |
| `items_per_admin_page` | integer | ⚠️ Not Integrated | Number of items to display per page in admin tables | *Need to integrate in Filament resources* |
| `enable_debug_mode` | boolean | ⚠️ Not Integrated | Enable debug mode for troubleshooting (WARNING: shows sensitive data) | *Need to set in config/app.php* |
| `backup_retention_days` | integer | ⚠️ Not Integrated | Number of days to keep database backups | *Need backup system first* |

---

## Implementation Summary

**Total Settings:** 57

**Status Breakdown:**
- ✅ **Integrated:** 8 settings (14%)
- ⚠️ **Not Integrated:** 49 settings (86%)

**Fully Integrated Settings:**
1. site_name
2. site_description
3. contact_email
4. library_email
5. maintenance_mode
6. maintenance_message
7. maintenance_allow_ips
8. maintenance_retry_after

---

## Priority Integration Recommendations

### High Priority (Should integrate next):
1. **items_per_page** - Essential for pagination control
2. **enable_ratings** / **enable_reviews** / **enable_bookmarks** - Feature toggles for user functionality
3. **google_analytics_id** - Easy to integrate, adds valuable tracking
4. **site_logo** / **site_favicon** - Complete branding
5. **default_sort_order** - Library UX improvement
6. **require_login_to_download** - Currently hardcoded

### Medium Priority:
1. **allow_guest_browsing** / **allow_guest_downloads** - Access control
2. **email notification settings** - Complete notification system
3. **social media links** - Footer enhancement
4. **timezone** / **language** - Localization
5. **password_min_length** - Security

### Low Priority:
1. **backup_retention_days** - Need backup system first
2. **items_per_admin_page** - Nice to have
3. **analytics_retention_days** - Need cleanup job
4. **debug mode toggle** - Usually managed via .env

---

## How Settings Work

### Backend:
- Settings stored in `settings` table
- `Setting::get('key', 'default')` retrieves value with automatic type casting
- Values cached for 1 hour for performance
- SettingsServiceProvider shares common settings with all views

### Admin Panel:
- Manage at `/admin/settings`
- Proper UI: toggles for boolean, numeric inputs for integer, etc.
- Grouped by category for easy navigation
- Color-coded badges

### Frontend:
- Common settings available as variables: `$siteName`, `$siteDescription`, `$contactEmail`, `$libraryEmail`
- Use `Setting::get('key')` in controllers/middleware for other settings

---

## Next Steps

1. Choose which settings to integrate based on priority
2. Update controllers/views to use settings instead of hardcoded values
3. Test each integration thoroughly
4. Update this document as settings are integrated
5. Add migration scripts if default values change

---

*Last Updated: 2025-11-13*
*Maintained by: Development Team*
