<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL; // <-- N'oubliez pas cet import !

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forcer Laravel à utiliser HTTPS derrière le proxy Apache
        URL::forceScheme('https');

        // ... Votre code existant pour le Gate 'gerer-annuaire' reste ici ...
        Gate::define('gerer-annuaire', function ($user) {
            // ...
        });
    }
}