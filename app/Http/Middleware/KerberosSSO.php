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
        // 1. On ignore complètement les requêtes parasites du navigateur (icônes, etc.)
        if ($request->is('favicon.ico') || $request->is('build/*') || $request->is('*.css')) {
            return $next($request);
        }

        // On ne fait ça que si l'utilisateur n'est pas déjà connecté
        if (!Auth::check()) {
            
            $remoteUser = $request->header('X-Remote-User');
            
            // Si l'en-tête est vide, on affiche la vraie URL qui bloque !
            if (!$remoteUser) {
                dd(
                    "ÉTAPE 1 (ÉCHEC) : Nginx n'a pas envoyé l'identifiant.",
                    "URL demandée : " . $request->url(),
                    "Avez-vous bien utilisé le nom de domaine (http://intranet.silvadec.local) et non l'adresse IP ?",
                    "Voici ce que Laravel a reçu :",
                    $request->headers->all()
                );
            }

            $username = explode('@', $remoteUser)[0];
            $user = LdapUser::where('samaccountname', $username)->first();
            
            if (!$user) {
                dd("ÉTAPE 2 (ÉCHEC) : L'identifiant '$username' est introuvable dans l'AD.");
            }

            Auth::login($user);
            
            if (!Auth::check()) {
                dd("ÉTAPE 3 (ÉCHEC) : Session refusée par Laravel.");
            }

            dd("ÉTAPE 4 (SUCCÈS) : Vous êtes connecté en tant que '$username'.");
        }

        return $next($request);
    }
}