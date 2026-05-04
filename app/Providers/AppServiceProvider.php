<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Ldap\User as LdapUser;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // On vérifie si l'utilisateur connecté fait partie du groupe des admins de l'intranet dans l'AD
        Gate::define('gerer-annuaire', function ($user) {
            
            // Sécurité : On s'assure d'extraire la chaîne de caractères (string) si l'attribut est un tableau
            $username = is_array($user->samaccountname) ? $user->samaccountname[0] : $user->samaccountname;

            // On récupère l'utilisateur LDAP correspondant à l'utilisateur connecté avec la vraie chaîne de texte
            $ldapUser = LdapUser::where('samaccountname', $username)->first();
            
            // Remplace le DN par celui de ton groupe Admin dans ton arborescence AD Windows Server
            return $ldapUser && $ldapUser->groups()->exists('cn=Intranet_Admins,ou=Groupes,dc=silvadec,dc=local');
        });
    }
}