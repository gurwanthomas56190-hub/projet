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
        Gate::define('gerer-annuaire', function ($user) {
            $username = is_array($user->samaccountname) ? $user->samaccountname[0] : $user->samaccountname;

            // 1. On donne tous les droits d'office au compte "Administrateur"
            if (strtolower($username) === 'administrateur') {
                return true;
            }

            // 2. Pour les autres, on vérifie le groupe AD
            $ldapUser = \App\Ldap\User::where('samaccountname', $username)->first();
            
            // N'oublie pas d'adapter ce chemin (DN) à ton vrai Active Directory Windows Server !
            return $ldapUser && $ldapUser->groups()->exists('cn=Intranet_Admins,ou=Groupes,dc=silvadec,dc=local');
        });
    }
}