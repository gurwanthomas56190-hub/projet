<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // <-- IMPORTANT : Assurez-vous d'importer votre modèle User local

class LoginController extends Controller
{
    // Affiche la page de connexion ou connecte automatiquement (SSO)
    public function showLoginForm() {
        dd($_SERVER);        
        // 1. TENTATIVE D'AUTHENTIFICATION AUTOMATIQUE (SSO Windows)
        // Récupération de l'utilisateur fourni par le serveur web (Apache/IIS)
        $remoteUser = $_SERVER['REMOTE_USER'] ?? $_SERVER['AUTH_USER'] ?? null;

        if (!Auth::check() && $remoteUser) {
            
            // L'identifiant est souvent au format "DOMAINE\utilisateur"
            // On le nettoie pour ne garder que le "samaccountname"
            $parts = explode('\\', $remoteUser);
            $samaccountname = end($parts);

            // On cherche l'utilisateur dans la base de données locale
            $user = User::where('samaccountname', $samaccountname)->first();

            if ($user) {
                // Si l'utilisateur est trouvé, on le connecte automatiquement
                Auth::login($user);
                
                // Redirection vers la page d'accueil ou la page initialement demandée
                return redirect()->intended('/');
            }
        }

        // 2. FALLBACK : Si la connexion automatique échoue ou ne trouve rien, 
        // on affiche simplement le formulaire de connexion manuel comme avant.
        return view('login'); //
    }

    // Gère la tentative de connexion (Reste INCHANGÉ)
    public function login(Request $request) {
        $credentials = $request->validate([
            'samaccountname' => ['required'], // L'identifiant Windows
            'password' => ['required'], //
        ]);

        if (Auth::attempt($credentials)) { //
            $request->session()->regenerate(); //
            return redirect()->intended('/'); //
        }

        return back()->withErrors([
            'samaccountname' => 'Identifiant ou mot de passe incorrect.', //
        ]);
    }

    // Déconnexion (Reste INCHANGÉ)
    public function logout(Request $request) {
        Auth::logout(); //
        $request->session()->invalidate(); //
        $request->session()->regenerateToken(); //
        return redirect('/'); //
    }
}