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
                // On vérifie si le navigateur a envoyé un ticket secret
                $ticket = $request->header('X-Auth-Header');
                $messageTicket = $ticket ? "OUI ! Il a envoyé : " . substr($ticket, 0, 20) . "..." : "NON (Vide ! Le navigateur refuse d'envoyer le ticket)";

                dd(
                    "ÉTAPE 1 (ÉCHEC) : Nginx a envoyé une identité vide.",
                    "Le navigateur a-t-il envoyé un ticket Kerberos ? -> " . $messageTicket,
                    "En-têtes reçus :",
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