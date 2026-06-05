<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // 1. On accepte les en-têtes (headers) envoyés par Nginx (notre Reverse Proxy)
        $middleware->trustProxies(at: '*');

        // 2. On ajoute notre filtre SSO personnalisé pour lire l'identifiant
        $middleware->web(append: [
            \App\Http\Middleware\KerberosSSO::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();