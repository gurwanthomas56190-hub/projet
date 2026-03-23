// bootstrap/app.php

use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ajoute cette ligne dans le groupe 'web'
        $middleware->web(append: [
            WindowsAuthenticate::class,
        ]);
        
        // Indique à Laravel de faire confiance au proxy (Nginx) pour lire les headers
        $middleware->trustProxies(at: '*'); 
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();