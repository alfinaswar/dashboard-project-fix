<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aplikasi ini hanya menyediakan terjemahan Indonesia (folder lang/id),
        // jadi locale dipaksa "id" walau APP_LOCALE di .env masih bawaan "en".
        app()->setLocale('id');
    }
}
