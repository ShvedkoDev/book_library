<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share common settings with all views
        View::composer('*', function ($view) {
            $view->with('siteName', Setting::get('site_name', 'Micronesian Teachers Digital Library'));
            $view->with('siteDescription', Setting::get('site_description', 'A digital library providing educational resources for Micronesian teachers and students'));
            $view->with('contactEmail', Setting::get('contact_email', 'contact@library.com'));
            $view->with('libraryEmail', Setting::get('library_email', 'library@library.com'));
        });
    }
}
