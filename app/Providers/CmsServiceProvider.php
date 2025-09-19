<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use App\Models\Page;
use App\Models\CmsCategory;
use App\Models\ContentBlock;
use App\Models\CmsSetting;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

/**
 * CMS Service Provider
 *
 * Handles registration and booting of CMS services, routes, permissions,
 * media collections, and view composers.
 */
class CmsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge CMS configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/cms.php',
            'cms'
        );

        // Register CMS services
        $this->registerCmsServices();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load CMS routes (disabled until controllers are created)
        // $this->loadCmsRoutes();

        // Register media collections
        $this->registerMediaCollections();

        // Set up CMS permissions and gates
        $this->setupPermissionsAndGates();

        // Register view composers
        $this->registerViewComposers();

        // Publish configuration and assets
        $this->publishAssets();

        // Set up model observers
        $this->setupModelObservers();

        // Extend existing functionality
        $this->extendExistingFunctionality();
    }

    /**
     * Register CMS services and bindings.
     *
     * @return void
     */
    protected function registerCmsServices(): void
    {
        // Register CMS cache service (placeholder implementation)
        $this->app->singleton('cms.cache', function ($app) {
            // Return a simple cache wrapper for now
            return new class($app['cache.store'], config('cms.cache')) {
                protected $cache;
                protected $config;

                public function __construct($cache, $config)
                {
                    $this->cache = $cache;
                    $this->config = $config;
                }

                public function remember($key, $ttl, $callback)
                {
                    if (!$this->config['enabled']) {
                        return $callback();
                    }
                    return $this->cache->remember($key, $ttl, $callback);
                }

                public function forget($key)
                {
                    return $this->cache->forget($key);
                }

                public function flush()
                {
                    return $this->cache->flush();
                }
            };
        });

        // Register CMS SEO service (placeholder implementation)
        $this->app->singleton('cms.seo', function ($app) {
            return new class(config('cms.seo')) {
                protected $config;

                public function __construct($config)
                {
                    $this->config = $config;
                }

                public function generateMetaTags($data)
                {
                    return [];
                }

                public function generateStructuredData($data)
                {
                    return [];
                }
            };
        });

        // Register CMS media service (placeholder implementation)
        $this->app->singleton('cms.media', function ($app) {
            return new class(config('cms.media')) {
                protected $config;

                public function __construct($config)
                {
                    $this->config = $config;
                }

                public function processUpload($file)
                {
                    return $file;
                }
            };
        });

        // Register content block renderer (placeholder implementation)
        $this->app->singleton('cms.blocks', function ($app) {
            return new class(config('cms.blocks')) {
                protected $config;

                public function __construct($config)
                {
                    $this->config = $config;
                }

                public function render($block)
                {
                    if (is_object($block) && method_exists($block, 'render')) {
                        return $block->render();
                    }
                    return '';
                }
            };
        });

        // Register CMS navigation service (placeholder implementation)
        $this->app->singleton('cms.navigation', function ($app) {
            return new class {
                public function getMainNavigation()
                {
                    return collect();
                }

                public function getBreadcrumb($page)
                {
                    return collect();
                }
            };
        });
    }

    /**
     * Load CMS routes.
     *
     * @return void
     */
    protected function loadCmsRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        // Load frontend routes if file exists
        $frontendRoutesPath = __DIR__ . '/../../routes/cms.php';
        if (file_exists($frontendRoutesPath)) {
            Route::middleware(config('cms.routing.middleware', ['web']))
                ->prefix(config('cms.routing.prefix', 'cms'))
                ->group(function () use ($frontendRoutesPath) {
                    $this->loadRoutesFrom($frontendRoutesPath);
                });
        }

        // Load admin routes if file exists (if not using Filament routing)
        $adminRoutesPath = __DIR__ . '/../../routes/cms-admin.php';
        if (file_exists($adminRoutesPath)) {
            Route::middleware(['web', 'auth'])
                ->prefix(config('cms.routing.admin_prefix', 'admin/cms'))
                ->group(function () use ($adminRoutesPath) {
                    $this->loadRoutesFrom($adminRoutesPath);
                });
        }
    }

    /**
     * Register media collections for CMS models.
     *
     * @return void
     */
    protected function registerMediaCollections(): void
    {
        // This method will be called when media collections are registered
        // The actual collection definitions are in the model's registerMediaCollections method

        // Register media conversions that will be applied globally
        $this->registerGlobalMediaConversions();
    }

    /**
     * Register global media conversions.
     *
     * @return void
     */
    protected function registerGlobalMediaConversions(): void
    {
        // Register conversions that should be available for all media
        $conversions = config('cms.media.conversions', []);

        foreach ($conversions as $name => $config) {
            // These will be registered in the Page model's registerMediaConversions method
            // This is just to ensure the configuration is available
        }
    }

    /**
     * Set up CMS permissions and gates.
     *
     * @return void
     */
    protected function setupPermissionsAndGates(): void
    {
        // Define CMS permissions
        $permissions = [
            'cms.view' => 'View CMS',
            'cms.pages.view' => 'View Pages',
            'cms.pages.create' => 'Create Pages',
            'cms.pages.edit' => 'Edit Pages',
            'cms.pages.delete' => 'Delete Pages',
            'cms.pages.publish' => 'Publish Pages',
            'cms.categories.view' => 'View Categories',
            'cms.categories.create' => 'Create Categories',
            'cms.categories.edit' => 'Edit Categories',
            'cms.categories.delete' => 'Delete Categories',
            'cms.media.view' => 'View Media',
            'cms.media.upload' => 'Upload Media',
            'cms.media.edit' => 'Edit Media',
            'cms.media.delete' => 'Delete Media',
            'cms.settings.view' => 'View Settings',
            'cms.settings.edit' => 'Edit Settings',
        ];

        // Register gates
        foreach ($permissions as $permission => $description) {
            Gate::define($permission, function (User $user) use ($permission) {
                // Check if user has the specific permission
                if (method_exists($user, 'hasPermissionTo')) {
                    return $user->hasPermissionTo($permission);
                }

                // Fallback: check if user is admin (assuming you have an is_admin field)
                if (isset($user->is_admin)) {
                    return $user->is_admin;
                }

                // Default: check if user has a role called 'admin'
                if (method_exists($user, 'hasRole')) {
                    return $user->hasRole('admin') || $user->hasRole('super-admin');
                }

                // Final fallback: check email (for development)
                return in_array($user->email, config('cms.admin_emails', []));
            });
        }

        // Register model-specific gates
        $this->registerModelGates();
    }

    /**
     * Register model-specific gates.
     *
     * @return void
     */
    protected function registerModelGates(): void
    {
        // Page gates
        Gate::define('view-page', function (User $user, Page $page) {
            if ($page->canBeViewed()) {
                return true;
            }
            return Gate::allows('cms.pages.view');
        });

        Gate::define('edit-page', function (User $user, Page $page) {
            // Check if user can edit any page
            if (Gate::allows('cms.pages.edit')) {
                return true;
            }
            // Check if user can edit their own pages
            return $page->created_by === $user->id && Gate::allows('cms.pages.edit.own');
        });

        Gate::define('delete-page', function (User $user, Page $page) {
            // Check if user can delete any page
            if (Gate::allows('cms.pages.delete')) {
                return true;
            }
            // Check if user can delete their own pages
            return $page->created_by === $user->id && Gate::allows('cms.pages.delete.own');
        });

        Gate::define('publish-page', function (User $user, Page $page) {
            return Gate::allows('cms.pages.publish');
        });

        // Category gates
        Gate::define('edit-category', function (User $user, CmsCategory $category) {
            return Gate::allows('cms.categories.edit');
        });

        Gate::define('delete-category', function (User $user, CmsCategory $category) {
            // Don't allow deletion if category has pages or children
            if ($category->pages()->count() > 0 || $category->children()->count() > 0) {
                return false;
            }
            return Gate::allows('cms.categories.delete');
        });
    }

    /**
     * Register view composers for CMS navigation and data.
     *
     * @return void
     */
    protected function registerViewComposers(): void
    {
        // Register CMS navigation composer
        View::composer(['cms.*', 'layouts.cms'], function ($view) {
            if (!$view->offsetExists('cmsNavigation')) {
                $navigation = $this->app->make('cms.navigation');
                $view->with('cmsNavigation', $navigation->getMainNavigation());
            }
        });

        // Register CMS settings composer
        View::composer(['cms.*', 'layouts.cms'], function ($view) {
            if (!$view->offsetExists('cmsSettings')) {
                $settings = CmsSetting::getGroup('general');
                $view->with('cmsSettings', $settings);
            }
        });

        // Register SEO data composer
        View::composer(['cms.pages.*', 'cms.categories.*'], function ($view) {
            if (!$view->offsetExists('seoData')) {
                $seo = $this->app->make('cms.seo');
                $seoData = $seo->generateMetaTags($view->getData());
                $view->with('seoData', $seoData);
            }
        });

        // Register categories composer for navigation
        View::composer(['cms.*'], function ($view) {
            if (!$view->offsetExists('cmsCategories')) {
                $categories = CmsCategory::active()
                    ->roots()
                    ->with('children')
                    ->orderBy('sort_order')
                    ->get();
                $view->with('cmsCategories', $categories);
            }
        });
    }

    /**
     * Publish configuration files and assets.
     *
     * @return void
     */
    protected function publishAssets(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration
            $this->publishes([
                __DIR__ . '/../../config/cms.php' => config_path('cms.php'),
            ], 'cms-config');

            // Publish views
            $this->publishes([
                __DIR__ . '/../../resources/views/cms' => resource_path('views/cms'),
            ], 'cms-views');

            // Publish assets
            $this->publishes([
                __DIR__ . '/../../public/cms' => public_path('cms'),
            ], 'cms-assets');

            // Publish migrations (if needed)
            $this->publishes([
                __DIR__ . '/../../database/migrations/cms' => database_path('migrations'),
            ], 'cms-migrations');
        }
    }

    /**
     * Set up model observers.
     *
     * @return void
     */
    protected function setupModelObservers(): void
    {
        // Register observers only if observer classes exist
        if (class_exists(\App\Observers\PageObserver::class)) {
            Page::observe(new \App\Observers\PageObserver());
        }

        if (class_exists(\App\Observers\CmsCategoryObserver::class)) {
            CmsCategory::observe(new \App\Observers\CmsCategoryObserver());
        }

        if (class_exists(\App\Observers\ContentBlockObserver::class)) {
            ContentBlock::observe(new \App\Observers\ContentBlockObserver());
        }

        if (class_exists(\App\Observers\CmsSettingObserver::class)) {
            CmsSetting::observe(new \App\Observers\CmsSettingObserver());
        }
    }

    /**
     * Extend existing functionality.
     *
     * @return void
     */
    protected function extendExistingFunctionality(): void
    {
        // Add CMS middleware to HTTP kernel if needed
        $this->registerCmsMiddleware();

        // Extend existing commands if needed
        $this->extendArtisanCommands();

        // Add custom validation rules
        $this->registerCustomValidationRules();

        // Extend blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register CMS middleware.
     *
     * @return void
     */
    protected function registerCmsMiddleware(): void
    {
        $router = $this->app['router'];

        // Register route middleware only if classes exist
        if (class_exists(\App\Http\Middleware\CmsAuthMiddleware::class)) {
            $router->aliasMiddleware('cms.auth', \App\Http\Middleware\CmsAuthMiddleware::class);
        }

        if (class_exists(\App\Http\Middleware\CmsPermissionMiddleware::class)) {
            $router->aliasMiddleware('cms.permission', \App\Http\Middleware\CmsPermissionMiddleware::class);
        }

        if (class_exists(\App\Http\Middleware\CmsCacheMiddleware::class)) {
            $router->aliasMiddleware('cms.cache', \App\Http\Middleware\CmsCacheMiddleware::class);
        }
    }

    /**
     * Extend Artisan commands.
     *
     * @return void
     */
    protected function extendArtisanCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $commands = [];

            // Register commands only if they exist
            if (class_exists(\App\Console\Commands\CmsInstallCommand::class)) {
                $commands[] = \App\Console\Commands\CmsInstallCommand::class;
            }

            if (class_exists(\App\Console\Commands\CmsClearCacheCommand::class)) {
                $commands[] = \App\Console\Commands\CmsClearCacheCommand::class;
            }

            if (class_exists(\App\Console\Commands\CmsGenerateSitemapCommand::class)) {
                $commands[] = \App\Console\Commands\CmsGenerateSitemapCommand::class;
            }

            if (class_exists(\App\Console\Commands\CmsOptimizeImagesCommand::class)) {
                $commands[] = \App\Console\Commands\CmsOptimizeImagesCommand::class;
            }

            if (!empty($commands)) {
                $this->commands($commands);
            }
        }
    }

    /**
     * Register custom validation rules.
     *
     * @return void
     */
    protected function registerCustomValidationRules(): void
    {
        // Add custom validation rule for unique slug within page type
        \Illuminate\Support\Facades\Validator::extend('unique_page_slug', function ($attribute, $value, $parameters, $validator) {
            $pageId = $parameters[0] ?? null;
            $query = Page::where('slug', $value);

            if ($pageId) {
                $query->where('id', '!=', $pageId);
            }

            return !$query->exists();
        });

        // Add custom validation rule for valid content block type
        \Illuminate\Support\Facades\Validator::extend('valid_block_type', function ($attribute, $value, $parameters, $validator) {
            return array_key_exists($value, config('cms.blocks', []));
        });
    }

    /**
     * Register custom Blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives(): void
    {
        // @cms_setting('key', 'default')
        \Illuminate\Support\Facades\Blade::directive('cms_setting', function ($expression) {
            return "<?php echo e(\\App\\Models\\CmsSetting::get($expression)); ?>";
        });

        // @cms_cache('key', content)
        \Illuminate\Support\Facades\Blade::directive('cms_cache', function ($expression) {
            return "<?php if(config('cms.cache.enabled')): ?><?php \$cacheKey = $expression; ?><?php echo cache()->remember(\$cacheKey, config('cms.cache.ttl'), function() { ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcms_cache', function () {
            return "<?php }); ?><?php endif; ?>";
        });

        // @can_cms('permission')
        \Illuminate\Support\Facades\Blade::directive('can_cms', function ($expression) {
            return "<?php if(\\Illuminate\\Support\\Facades\\Gate::allows($expression)): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcan_cms', function () {
            return "<?php endif; ?>";
        });

        // @cms_block($block)
        \Illuminate\Support\Facades\Blade::directive('cms_block', function ($expression) {
            return "<?php echo app('cms.blocks')->render($expression); ?>";
        });
    }

    /**
     * Check if Spatie Permission package is installed.
     *
     * @return bool
     */
    protected function hasSpatiePermission(): bool
    {
        return class_exists(\Spatie\Permission\Models\Permission::class);
    }

    /**
     * Create default CMS roles and permissions if Spatie Permission is available.
     *
     * @return void
     */
    protected function createDefaultRolesAndPermissions(): void
    {
        if (!$this->hasSpatiePermission() || !Schema::hasTable('permissions')) {
            return;
        }

        $permissions = [
            'cms.view',
            'cms.pages.view',
            'cms.pages.create',
            'cms.pages.edit',
            'cms.pages.edit.own',
            'cms.pages.delete',
            'cms.pages.delete.own',
            'cms.pages.publish',
            'cms.categories.view',
            'cms.categories.create',
            'cms.categories.edit',
            'cms.categories.delete',
            'cms.media.view',
            'cms.media.upload',
            'cms.media.edit',
            'cms.media.delete',
            'cms.settings.view',
            'cms.settings.edit',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $editorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cms-editor']);
        $authorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cms-author']);
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'cms-admin']);

        // Assign permissions to roles
        $authorRole->syncPermissions([
            'cms.view',
            'cms.pages.view',
            'cms.pages.create',
            'cms.pages.edit.own',
            'cms.pages.delete.own',
            'cms.categories.view',
            'cms.media.view',
            'cms.media.upload',
        ]);

        $editorRole->syncPermissions([
            'cms.view',
            'cms.pages.view',
            'cms.pages.create',
            'cms.pages.edit',
            'cms.pages.delete',
            'cms.pages.publish',
            'cms.categories.view',
            'cms.categories.create',
            'cms.categories.edit',
            'cms.categories.delete',
            'cms.media.view',
            'cms.media.upload',
            'cms.media.edit',
            'cms.media.delete',
        ]);

        $adminRole->syncPermissions($permissions);
    }
}