<?php

namespace App\Providers;

use App\Services\SettingsService;
use App\Services\ThemeManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(ThemeManager::class);
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        Route::resourceVerbs(['create' => 'crear', 'edit' => 'editar']);
    }
}
