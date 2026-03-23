<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Activation du SSO pour les routes Web
        $middleware->web(append: [
            WindowsAuthenticate::class,
        ]);

        // IMPORTANT : Autorise Laravel à lire les headers envoyés par ton Nginx (Docker)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();