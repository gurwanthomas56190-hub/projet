<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL; // <-- L'import ajouté
use App\Ldap\User as LdapUser;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Forcer Laravel à utiliser HTTPS derrière le proxy Apache
        URL::forceScheme('https');

        Gate::define('gerer-annuaire', function ($user) {
            $username = is_array($user->samaccountname) ? $user->samaccountname[0] : $user->samaccountname;

            // 1. On donne tous les droits d'office au compte "Administrateur"
            if (strtolower($username) === 'administrateur') {
                return true;
            }

            // 2. Pour les autres, on vérifie le groupe AD
            $ldapUser = \App\Ldap\User::where('samaccountname', $username)->first();
            
            return $ldapUser && $ldapUser->groups()->exists('cn=Intranet_Admins,ou=Groupes,dc=silvadec,dc=local');
        });
    }
}