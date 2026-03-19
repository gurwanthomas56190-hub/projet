<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Ldap\User as LdapUser;     // <-- Pour parler à l'Active Directory
use App\Models\User as LocalUser;  // <-- Pour connecter l'utilisateur dans Laravel

class LoginController extends Controller
{
    // Affiche la page de connexion OU connecte automatiquement via l'AD
    public function showLoginForm() {
    
    // TRICHE DE DÉVELOPPEMENT : Faux SSO invisible
    // Si l'application est en mode "local" (voir fichier .env)
    if (app()->environment('local')) {
        
        // On récupère votre compte dans la base locale (remplacez 'g.thomas' par votre vrai identifiant)
        $user = \App\Models\User::where('samaccountname', 'g.thomas')->first();
        
        if ($user) {
            \Illuminate\Support\Facades\Auth::login($user);
            return redirect()->intended('/');
        }
    }

    // Le vrai code pour la production (quand ce ne sera plus en local)
    // On affiche la page normale
    return view('login');
    }


    // Gère la tentative de connexion manuelle (reste inchangé)
    public function login(Request $request) {
        $credentials = $request->validate([
            'samaccountname' => ['required'], // L'identifiant Windows
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'samaccountname' => 'Identifiant ou mot de passe incorrect.',
        ]);
    }

    // Déconnexion (reste inchangé)
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}