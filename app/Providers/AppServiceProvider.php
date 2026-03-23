// app/Providers/AppServiceProvider.php

use LdapRecord\Laravel\Middleware\WindowsAuthenticate;

public function boot(): void
{
    // On dit au middleware de regarder notre header personnalisé
    WindowsAuthenticate::serverKey('HTTP_X_REMOTE_USER');
}