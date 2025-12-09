<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\BooksStatsWidget;
use App\Filament\Widgets\RatingAnalyticsWidget;
use App\Filament\Widgets\UserActivityWidget;
use App\Filament\Widgets\DownloadsChartWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\PopularBooksWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandLogo(fn () => view('filament.admin.brand-logo'))
            ->brandName('FSM National Vernacular Language Arts (VLA) Curriculum')
            ->colors([
                'primary' => Color::hex('#009877'),    // COE green
                'secondary' => Color::hex('#005a70'),  // COE blue
                'danger' => Color::Red,
                'gray' => Color::Gray,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'info' => Color::Blue,
            ])
            ->font('"proxima-nova", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(true)  // Enable dark mode toggle
            ->login()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->navigationGroups([
                'Library',
                'Analytics',
                'CSV Import/Export',
                'Media',
                'CMS',
                'System',
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Analytics Widgets
                BooksStatsWidget::class,
                RatingAnalyticsWidget::class,
                UserActivityWidget::class,
                DownloadsChartWidget::class,
                RecentActivityWidget::class,
                PopularBooksWidget::class,

                // Default Widgets
                // Widgets\AccountWidget::class,  // Disabled - requires authenticated user
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
