<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // On définit la clé de serveur pour lire ce que Nginx envoie
        WindowsAuthenticate::serverKey('HTTP_X_REMOTE_USER');
    }
}