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
            
            // 1. Lecture de l'en-tête
            $remoteUser = $request->header('X-Remote-User');
            
            if (!$remoteUser) {
                dd("ÉTAPE 1 (ÉCHEC) : L'en-tête X-Remote-User a disparu ! Voici ce que Laravel reçoit :", $request->headers->all());
            }

            // 2. Nettoyage du nom
            $username = explode('@', $remoteUser)[0];

            // 3. Recherche dans l'AD
            $user = LdapUser::where('samaccountname', $username)->first();
            
            if (!$user) {
                dd("ÉTAPE 2 (ÉCHEC) : L'identifiant '$username' est introuvable dans l'Active Directory.");
            }

            // 4. Connexion Laravel
            Auth::login($user);
            
            if (!Auth::check()) {
                dd("ÉTAPE 3 (ÉCHEC) : Utilisateur trouvé dans l'AD, mais Laravel refuse d'ouvrir la session.");
            }

            // Si on arrive ici, tout a marché !
            dd("ÉTAPE 4 (SUCCÈS) : Vous êtes bien connecté en tant que '$username'. Le SSO fonctionne !");
        }

        return $next($request);
    }
}