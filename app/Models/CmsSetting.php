<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Class CmsSetting
 *
 * @property int $id
 * @property string $key
 * @property array|null $value
 * @property string|null $group
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CmsSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cms_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Cache for settings to avoid repeated database queries.
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * Setting group constants
     */
    const GROUP_GENERAL = 'general';
    const GROUP_SEO = 'seo';
    const GROUP_SOCIAL = 'social';
    const GROUP_ANALYTICS = 'analytics';
    const GROUP_MEDIA = 'media';
    const GROUP_CONTENT = 'content';
    const GROUP_SECURITY = 'security';
    const GROUP_EMAIL = 'email';
    const GROUP_CACHE = 'cache';
    const GROUP_API = 'api';

    /**
     * Available setting groups
     *
     * @return array<string, string>
     */
    public static function getGroups(): array
    {
        return [
            self::GROUP_GENERAL => 'General',
            self::GROUP_SEO => 'SEO',
            self::GROUP_SOCIAL => 'Social Media',
            self::GROUP_ANALYTICS => 'Analytics',
            self::GROUP_MEDIA => 'Media',
            self::GROUP_CONTENT => 'Content',
            self::GROUP_SECURITY => 'Security',
            self::GROUP_EMAIL => 'Email',
            self::GROUP_CACHE => 'Cache',
            self::GROUP_API => 'API',
        ];
    }

    /**
     * Scope a query to filter settings by group.
     *
     * @param Builder $query
     * @param string $group
     * @return Builder
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope a query to only include active settings.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Check cache first
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        $setting = static::active()->where('key', $key)->first();

        if (!$setting) {
            static::$cache[$key] = $default;
            return $default;
        }

        $value = $setting->value;

        // If value is an array with a single value, return that value
        if (is_array($value) && count($value) === 1 && isset($value[0])) {
            $value = $value[0];
        }

        static::$cache[$key] = $value;
        return $value;
    }

    /**
     * Set a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @return CmsSetting
     */
    public static function set(string $key, $value, ?string $group = null): CmsSetting
    {
        // Clear cache for this key
        unset(static::$cache[$key]);

        // Ensure value is properly formatted for JSON storage
        if (!is_array($value)) {
            $value = [$value];
        }

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group ?? self::GROUP_GENERAL,
                'is_active' => true,
            ]
        );

        return $setting;
    }

    /**
     * Get all settings for a specific group.
     *
     * @param string $group
     * @return Collection
     */
    public static function getGroup(string $group): Collection
    {
        $settings = static::active()->byGroup($group)->get();

        return $settings->pluck('value', 'key')->map(function ($value) {
            // If value is an array with a single value, return that value
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                return $value[0];
            }
            return $value;
        });
    }

    /**
     * Set multiple settings for a group.
     *
     * @param string $group
     * @param array $settings
     * @return void
     */
    public static function setGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value, $group);
        }
    }

    /**
     * Delete a setting by key.
     *
     * @param string $key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        unset(static::$cache[$key]);
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Get all settings as a flat array.
     *
     * @return array
     */
    public static function all(): array
    {
        $settings = static::active()->get();

        $result = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            // If value is an array with a single value, return that value
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $value = $value[0];
            }
            $result[$setting->key] = $value;
        }

        return $result;
    }

    /**
     * Get all settings grouped by their group.
     *
     * @return array
     */
    public static function allGrouped(): array
    {
        $settings = static::active()->get();

        $result = [];
        foreach ($settings as $setting) {
            $group = $setting->group ?? self::GROUP_GENERAL;
            if (!isset($result[$group])) {
                $result[$group] = [];
            }

            $value = $setting->value;
            // If value is an array with a single value, return that value
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $value = $value[0];
            }
            $result[$group][$setting->key] = $value;
        }

        return $result;
    }

    /**
     * Clear the settings cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        static::$cache = [];
    }

    /**
     * Refresh the settings cache.
     *
     * @return void
     */
    public static function refreshCache(): void
    {
        static::clearCache();
        static::all(); // This will populate the cache
    }

    /**
     * Get default CMS settings.
     *
     * @return array
     */
    public static function getDefaults(): array
    {
        return [
            // General settings
            'site_name' => 'CMS Site',
            'site_description' => 'A powerful content management system',
            'site_logo' => null,
            'site_favicon' => null,
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',

            // SEO settings
            'seo_title_template' => '{{ title }} | {{ site_name }}',
            'seo_description_length' => 160,
            'seo_keywords_max' => 10,
            'seo_robots' => 'index,follow',

            // Social settings
            'social_facebook' => null,
            'social_twitter' => null,
            'social_instagram' => null,
            'social_linkedin' => null,

            // Content settings
            'content_excerpt_length' => 160,
            'content_per_page' => 10,
            'content_rich_editor' => 'tinymce',
            'content_allow_comments' => true,

            // Media settings
            'media_max_file_size' => 10, // MB
            'media_allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'],
            'media_image_quality' => 85,
            'media_generate_webp' => true,

            // Cache settings
            'cache_enabled' => true,
            'cache_ttl' => 3600, // seconds
            'cache_pages' => true,
            'cache_categories' => true,

            // Security settings
            'security_login_attempts' => 5,
            'security_lockout_duration' => 900, // seconds
            'security_force_https' => false,
            'security_csrf_protection' => true,
        ];
    }

    /**
     * Seed default settings.
     *
     * @return void
     */
    public static function seedDefaults(): void
    {
        $defaults = static::getDefaults();
        $groups = [
            'site_' => self::GROUP_GENERAL,
            'seo_' => self::GROUP_SEO,
            'social_' => self::GROUP_SOCIAL,
            'content_' => self::GROUP_CONTENT,
            'media_' => self::GROUP_MEDIA,
            'cache_' => self::GROUP_CACHE,
            'security_' => self::GROUP_SECURITY,
        ];

        foreach ($defaults as $key => $value) {
            if (!static::has($key)) {
                $group = self::GROUP_GENERAL;
                foreach ($groups as $prefix => $groupName) {
                    if (str_starts_with($key, $prefix)) {
                        $group = $groupName;
                        break;
                    }
                }
                static::set($key, $value, $group);
            }
        }
    }

    /**
     * Export settings as JSON.
     *
     * @param string|null $group
     * @return string
     */
    public static function export(?string $group = null): string
    {
        if ($group) {
            $settings = static::getGroup($group);
        } else {
            $settings = static::all();
        }

        return json_encode($settings, JSON_PRETTY_PRINT);
    }

    /**
     * Import settings from JSON.
     *
     * @param string $json
     * @param string|null $group
     * @return bool
     */
    public static function import(string $json, ?string $group = null): bool
    {
        try {
            $settings = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            foreach ($settings as $key => $value) {
                static::set($key, $value, $group);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the value attribute in a user-friendly format.
     *
     * @return mixed
     */
    public function getFormattedValueAttribute()
    {
        $value = $this->value;

        if (is_array($value) && count($value) === 1 && isset($value[0])) {
            return $value[0];
        }

        return $value;
    }

    /**
     * Get the setting type based on the value.
     *
     * @return string
     */
    public function getTypeAttribute(): string
    {
        $value = $this->formatted_value;

        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_numeric($value)) {
            return is_int($value) ? 'integer' : 'float';
        }

        if (is_array($value)) {
            return 'array';
        }

        return 'string';
    }
}