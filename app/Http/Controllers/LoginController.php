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
    // Si le middleware SSO a déjà connecté l'utilisateur, on le redirige à l'accueil
    if (Auth::check()) {
        return redirect()->intended('/');
    }
    
    // Sinon, on affiche le formulaire normal en solution de secours
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