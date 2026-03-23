<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

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
        // On indique le nom du header configuré dans Nginx
        // Note : PHP transforme "X-Remote-User" en "HTTP_X_REMOTE_USER"
        WindowsAuthenticate::serverKey('HTTP_X_REMOTE_USER');

        // Nettoyage du login : Si Nginx envoie "user@SILVADEC.LOCAL", 
        // on ne garde que "user" pour la recherche dans l'AD.
        WindowsAuthenticate::retrieveUserWith(function ($user) {
            return explode('@', $user)[0];
        });
    }
}