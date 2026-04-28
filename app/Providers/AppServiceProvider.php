<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;

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
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            Vite::useHotFile(storage_path('framework/vite.hot.disabled'));
        }

        // Usa sempre il template Tailwind per la paginazione (niente Bootstrap)
        Paginator::useBootstrapFive();
        // Oppure Tailwind: Paginator::useTailwind();
    }
}
