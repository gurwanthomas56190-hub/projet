<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Options d'authentification par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option définit le "garde" (guard) d'authentification et le 
    | "courtier" (broker) de réinitialisation de mot de passe par défaut pour
    | votre application. Vous pouvez modifier ces valeurs selon vos besoins.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gardes d'authentification
    |--------------------------------------------------------------------------
    |
    | Ici, vous pouvez définir chaque garde d'authentification pour votre 
    | application. Une configuration par défaut utilisant le stockage de 
    | session et le fournisseur d'utilisateurs Eloquent a déjà été définie.
    |
    | Tous les gardes ont un fournisseur d'utilisateurs, qui définit comment
    | les utilisateurs sont réellement récupérés de votre base de données ou
    | de tout autre système de stockage.
    |
    | Supporté : "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fournisseurs d'utilisateurs
    |--------------------------------------------------------------------------
    |
    | Tous les gardes d'authentification ont un fournisseur d'utilisateurs. 
    | Typiquement, c'est Eloquent qui est utilisé.
    |
    | Si vous avez plusieurs tables ou modèles d'utilisateurs, vous pouvez 
    | configurer plusieurs fournisseurs pour représenter chaque modèle/table.
    |
    | Supporté : "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'ldap',
            // Utilise directement le modèle LDAP d'Active Directory
            'model' => App\Ldap\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Réinitialisation des mots de passe
    |--------------------------------------------------------------------------
    |
    | Ces options spécifient le comportement de la fonctionnalité de 
    | réinitialisation de mot de passe de Laravel, incluant la table utilisée
    | pour le stockage des jetons et le fournisseur utilisé pour récupérer
    | les utilisateurs.
    |
    | La durée d'expiration est le nombre de minutes pendant lesquelles chaque
    | jeton est considéré comme valide.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Délai de confirmation du mot de passe
    |--------------------------------------------------------------------------
    |
    | Ici, vous définissez le nombre de secondes avant qu'une fenêtre de 
    | confirmation de mot de passe n'expire et que l'utilisateur doive 
    | saisir à nouveau son mot de passe.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];