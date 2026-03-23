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
        
        // TRICHE DE DÉVELOPPEMENT (Sans base de données)
        //if (app()->environment('local')) {
            
            // On cherche directement votre compte Administrateur dans l'Active Directory
            // Remplacez 'Administrateur' par votre identifiant si besoin (ex: 'g.thomas')
            //$user = \App\Ldap\User::where('samaccountname', 'Administrateur')->first();
            
            //if ($user) {
                // On connecte directement l'utilisateur LDAP
                //\Illuminate\Support\Facades\Auth::login($user);
                //return redirect()->intended('/');
            //}
        //}

        // Si on n'est pas en local ou que la triche échoue, on affiche le formulaire normal
        //return view('login');
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