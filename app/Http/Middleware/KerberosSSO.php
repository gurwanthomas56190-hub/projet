<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Ldap\User as LdapUser;

class KerberosSSO
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur n'est pas déjà connecté dans Laravel
        if (!Auth::check()) {
            
            // On récupère l'identifiant transmis par le Reverse Proxy Nginx
            $remoteUser = $request->header('X-Remote-User');

            if ($remoteUser) {
                // Le nom est souvent sous la forme "identifiant@DOMAINE.LOCAL"
                // On extrait juste l'identifiant "samaccountname"
                $username = explode('@', $remoteUser)[0];

                // On cherche l'utilisateur correspondant dans l'Active Directory via le modèle LDAP
                $user = LdapUser::where('samaccountname', $username)->first();

                if ($user) {
                    // On force la connexion de l'utilisateur dans Laravel
                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }
}