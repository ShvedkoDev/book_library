<?php

namespace App\Providers;

use App\Http\ViewComposers\NavigationComposer;
use App\Models\Page;
use App\Observers\PageObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Page observer for automatic section syncing
        Page::observe(PageObserver::class);

        // Register view composer for navigation menu
        View::composer('layouts.library', NavigationComposer::class);
    }
}
