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
        // On ne fait ça que si l'utilisateur n'est pas déjà connecté
        if (!Auth::check()) {
            
            // On lit l'en-tête envoyé par Nginx
            $remoteUser = $request->header('X-Remote-User');
            
            if ($remoteUser) {
                // On nettoie le nom (ex: gurwan@SILVADEC.LOCAL -> gurwan)
                $username = explode('@', $remoteUser)[0];

                // On cherche l'utilisateur dans l'Active Directory
                $user = LdapUser::where('samaccountname', $username)->first();
                
                // Si on le trouve, on le connecte silencieusement !
                if ($user) {
                    Auth::login($user);
                }
            }
        }

        // On laisse la requête continuer vers la vraie page web
        return $next($request);
    }
}