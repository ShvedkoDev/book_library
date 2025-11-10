# Settings System Documentation
*Complete implementation guide - 2025-11-10*

## Overview
The Micronesian Teachers Digital Library includes a flexible key-value settings system for managing site-wide configuration without code changes.

---

## ✅ System Components

### 1. Database Table
**Table**: `settings`

**Structure**:
- `id` - Primary key
- `key` - Unique setting identifier (indexed)
- `value` - Setting value (text, supports all types)
- `type` - Data type: string, text, boolean, integer, json
- `group` - Organization: general, library, email, system
- `description` - Human-readable explanation
- `timestamps` - created_at, updated_at

**Indexes**:
- `key` - Fast lookups
- `group` - Group filtering

---

### 2. Setting Model
**Location**: `app/Models/Setting.php`

**Key Features**:
- ✅ **Caching**: 1-hour cache for performance
- ✅ **Helper Methods**: get(), set(), getGroup()
- ✅ **Cache Invalidation**: Automatic on updates

**Usage Examples**:

```php
// Get a setting
$siteName = Setting::get('site_name', 'Default Library Name');

// Set a setting
Setting::set('library_email', 'newlibrary@example.com');

// Get all settings in a group
$librarySettings = Setting::getGroup('library');
```

---

### 3. Admin Resource
**Location**: `app/Filament/Resources/SettingResource.php`

**Admin Panel Access**: `/admin` → System → Settings

**Features**:
- ✅ Create, Read, Update, Delete settings
- ✅ Group-based organization
- ✅ Type validation (string, text, boolean, integer, json)
- ✅ Search and filter by group
- ✅ Copyable keys and values
- ✅ Last updated timestamps

**Form Fields**:
1. **Key** - Unique identifier (required, unique)
2. **Group** - Dropdown: general, library, email, system (required)
3. **Type** - Dropdown: string, text, boolean, integer, json (required)
4. **Value** - Text area for setting value
5. **Description** - Explanation of what the setting controls

**Table Display**:
- Key (searchable, sortable, copyable)
- Value (searchable, copyable, truncated to 50 chars)
- Group (badge with color coding)
- Type (badge)
- Last Updated (datetime with "X ago" format)

---

## 📋 Current Default Settings

### 1. Library Email
- **Key**: `library_email`
- **Value**: `library@example.com`
- **Type**: string
- **Group**: library
- **Purpose**: Email address for library access requests
- **Usage**: Access request notifications

### 2. Site Name
- **Key**: `site_name`
- **Value**: `Micronesian Teachers Digital Library`
- **Type**: string
- **Group**: general
- **Purpose**: Display name for the library site
- **Usage**: Page titles, headers, emails

---

## 🎯 Recommended Settings to Add

### General Settings
```
site_name - Already exists ✅
site_description - Brief description of library purpose
site_url - Base URL of the site
contact_email - General contact email
maintenance_mode - Enable/disable site maintenance
timezone - Default timezone (Pacific/Chuuk, Pacific/Pohnpei, etc.)
```

### Library Settings
```
library_email - Already exists ✅
items_per_page - Number of books to show per page (default: 10)
featured_books_count - Number of featured books to show
popular_books_threshold - Minimum views to be "popular"
new_books_days - Days to mark book as "new" (default: 30)
access_request_auto_approve - Auto-approve access requests (boolean)
```

### Email Settings
```
from_email - Default "from" address for emails
from_name - Default "from" name for emails
smtp_host - SMTP server host
smtp_port - SMTP port (587, 465, etc.)
smtp_encryption - TLS or SSL
admin_notification_email - Email for admin notifications
```

### System Settings
```
analytics_enabled - Enable/disable analytics tracking
cache_duration - Default cache duration in minutes
max_upload_size - Maximum file upload size in MB
allowed_file_types - JSON array of allowed file extensions
session_timeout - Session timeout in minutes
password_min_length - Minimum password length (default: 8)
require_email_verification - Require email verification for new users
```

### UI/Display Settings
```
books_grid_columns - Grid layout columns (default: 3)
show_author_bios - Show author biographies on book pages
show_download_count - Display download counts publicly
show_ratings - Display ratings to public users
show_reviews - Display reviews to public users
default_sort_order - Default book sorting (title, date, popularity)
```

### Access Control Settings
```
allow_guest_browsing - Allow unauthenticated browsing (default: true)
allow_guest_downloads - Allow downloads without login (default: false)
require_approval_for_access - Access requests require admin approval (default: true)
download_limit_per_day - Max downloads per user per day (0 = unlimited)
```

---

## 🔧 How to Add New Settings

### Method 1: Via Admin Panel (Recommended)
1. Login to admin panel: `/admin`
2. Navigate to: **System → Settings**
3. Click **"Create Setting"**
4. Fill in form:
   - Key: `items_per_page` (no spaces, use underscores)
   - Group: `library`
   - Type: `integer`
   - Value: `10`
   - Description: `Number of books to display per page in library view`
5. Save

### Method 2: Via Database Seeder
Create a new seeder:

```bash
docker-compose exec app php artisan make:seeder AdditionalSettingsSeeder
```

**File**: `database/seeders/AdditionalSettingsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class AdditionalSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            [
                'key' => 'site_description',
                'value' => 'A digital library providing educational resources for Micronesian teachers and students',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Brief description of the library for meta tags and about pages',
            ],
            [
                'key' => 'timezone',
                'value' => 'Pacific/Chuuk',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application',
            ],

            // Library
            [
                'key' => 'items_per_page',
                'value' => '10',
                'type' => 'integer',
                'group' => 'library',
                'description' => 'Number of books to display per page',
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
                'description' => 'Days to mark a book as "new"',
            ],

            // System
            [
                'key' => 'analytics_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable or disable analytics tracking',
            ],
            [
                'key' => 'cache_duration',
                'value' => '60',
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Default cache duration in minutes',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
```

Run the seeder:
```bash
docker-compose exec app php artisan db:seed --class=AdditionalSettingsSeeder
```

### Method 3: Programmatically
```php
use App\Models\Setting;

Setting::set('items_per_page', 10);
```

---

## 💻 Using Settings in Code

### In Controllers
```php
use App\Models\Setting;

class BookController extends Controller
{
    public function index()
    {
        $perPage = Setting::get('items_per_page', 10);
        $books = Book::paginate($perPage);

        return view('books.index', compact('books'));
    }
}
```

### In Views (Blade)
```php
use App\Models\Setting;

$siteName = Setting::get('site_name', 'Library');
```

```html
<title>{{ Setting::get('site_name', 'Library') }} - Books</title>

@if(Setting::get('show_ratings', 'true') === 'true')
    <div class="ratings">
        <!-- Rating display -->
    </div>
@endif
```

### In Configuration Files
```php
// config/app.php
'name' => env('APP_NAME', Setting::get('site_name', 'Library')),
```

### Group-based Retrieval
```php
// Get all library settings at once
$librarySettings = Setting::getGroup('library');

$itemsPerPage = $librarySettings['items_per_page'] ?? 10;
$featuredCount = $librarySettings['featured_books_count'] ?? 6;
```

---

## 🎨 Color Coding in Admin Panel

**Group Badge Colors**:
- 🟢 **General** - Gray
- 🟢 **Library** - Success (Green)
- 🔵 **Email** - Info (Blue)
- 🟡 **System** - Warning (Orange)

---

## 🔒 Security Considerations

### Sensitive Settings
For sensitive values (API keys, passwords), consider:

1. **Use .env variables** instead of database settings
2. **Encrypt values** if storing in database:
   ```php
   use Illuminate\Support\Facades\Crypt;

   Setting::set('api_key', Crypt::encryptString($apiKey));
   $apiKey = Crypt::decryptString(Setting::get('api_key'));
   ```

3. **Restrict access** to Settings resource:
   ```php
   // In SettingResource.php
   public static function canViewAny(): bool
   {
       return auth()->user()->role === 'admin';
   }
   ```

### Best Practices
- ✅ Use descriptive keys (e.g., `library_email` not `email1`)
- ✅ Always provide default values: `Setting::get('key', 'default')`
- ✅ Document each setting's purpose in the description field
- ✅ Use appropriate types (integer for numbers, boolean for flags)
- ✅ Group related settings together
- ❌ Don't store complex logic in settings
- ❌ Don't store frequently-changing data (use cache or database)
- ❌ Don't store file paths (use config files)

---

## 📊 Performance Notes

### Caching
- Settings are cached for **1 hour** by default
- Cache key format: `setting.{key}`
- Cache automatically cleared on update

### Optimization Tips
1. **Batch retrieval**: Use `getGroup()` instead of multiple `get()` calls
2. **Cache warming**: Pre-load frequently used settings
3. **Avoid in loops**: Retrieve settings once, store in variable

```php
// ❌ Bad - Multiple database/cache hits
@foreach($books as $book)
    @if(Setting::get('show_ratings') === 'true')
        ...
    @endif
@endforeach

// ✅ Good - Single retrieval
@php
    $showRatings = Setting::get('show_ratings', 'true') === 'true';
@endphp
@foreach($books as $book)
    @if($showRatings)
        ...
    @endif
@endforeach
```

---

## 🔄 Cache Management

### Clear Setting Cache
```php
use Illuminate\Support\Facades\Cache;

// Clear specific setting
Cache::forget('setting.site_name');

// Clear all settings (prefix-based)
Cache::flush(); // Clears all cache

// Or use Laravel's cache clear command
php artisan cache:clear
```

### Manual Cache Refresh
```php
// Force refresh from database
Cache::forget('setting.site_name');
$value = Setting::get('site_name');
```

---

## 🎯 Common Use Cases

### 1. Maintenance Mode
```php
// Set maintenance mode
Setting::set('maintenance_mode', 'true');

// Check in middleware
if (Setting::get('maintenance_mode', 'false') === 'true') {
    abort(503, 'Site is under maintenance');
}
```

### 2. Pagination
```php
$perPage = Setting::get('items_per_page', 10);
$books = Book::paginate($perPage);
```

### 3. Feature Toggles
```php
if (Setting::get('analytics_enabled', 'true') === 'true') {
    // Track analytics
    AnalyticsService::track($event);
}
```

### 4. Email Configuration
```php
$settings = Setting::getGroup('email');

config([
    'mail.from.address' => $settings['from_email'] ?? 'noreply@library.com',
    'mail.from.name' => $settings['from_name'] ?? 'Library',
]);
```

---

## 📝 Future Enhancements

### Potential Improvements
- [ ] Settings validation rules
- [ ] Settings export/import (JSON)
- [ ] Settings versioning/history
- [ ] Settings permissions (role-based access)
- [ ] Settings groups with tabs in UI
- [ ] Settings preview before save
- [ ] Settings search across all fields
- [ ] Settings backup/restore
- [ ] Environment-specific settings (dev, staging, prod)

---

## ✅ Summary

**Current Status**: ✅ **FULLY IMPLEMENTED**

The Settings system is production-ready with:
- ✅ Database table with indexes
- ✅ Eloquent model with caching
- ✅ Full CRUD admin interface
- ✅ 2 default settings seeded
- ✅ Group organization (4 groups)
- ✅ Type system (5 types)
- ✅ Helper methods for easy access
- ✅ Cache invalidation on updates

**Access**: `/admin` → System → Settings

**Next Steps**:
1. Add recommended settings via admin panel or seeder
2. Integrate settings into application views and logic
3. Document settings for end users/admins
4. Consider encrypting sensitive settings

---

*For questions or issues, refer to `app/Models/Setting.php` or `app/Filament/Resources/SettingResource.php`*
