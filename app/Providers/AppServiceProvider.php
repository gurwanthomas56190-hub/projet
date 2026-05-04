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
            // On récupère l'utilisateur LDAP correspondant à l'utilisateur connecté
            $ldapUser = LdapUser::where('samaccountname', $user->samaccountname)->first();
            
            // Remplace le DN par celui de ton groupe Admin dans ton arborescence AD Windows Server
            return $ldapUser && $ldapUser->groups()->exists('cn=Intranet_Admins,ou=Groupes,dc=silvadec,dc=local');
        });
    }
}