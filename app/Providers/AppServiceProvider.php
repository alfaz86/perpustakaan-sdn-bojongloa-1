<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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
        FilamentAsset::register([
            Js::make('custom-script', resource_path('js/custom.js')),
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_END,
            fn(): string => Blade::render('<livewire:action-shortcuts />'),
        );

        FilamentIcon::register([
            'panels::topbar.open-database-notifications-button' => view('filament.icons.database-notification'),
        ]);
    }
}
