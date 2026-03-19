<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Ldap\User as LdapUser;     // <-- Pour parler à l'Active Directory
use App\Models\User as LocalUser;  // <-- Pour connecter l'utilisateur dans Laravel

class LoginController extends Controller
{
    // Affiche la page de connexion OU connecte automatiquement via l'AD
    public function showLoginForm(Request $request) {
        
        // 1. On récupère le paramètre ?guid=... dans l'URL
        if ($request->has('guid')) {
            $guid = $request->query('guid');

            try {
                // 2. On interroge DIRECTEMENT l'Active Directory
                // LdapRecord va chercher si ce "objectguid" existe dans votre AD
                $ldapUser = LdapUser::findByGuid($guid);

                // 3. Si l'utilisateur est trouvé dans l'AD
                if ($ldapUser) {
                    
                    // On récupère son identifiant Windows (ex: "g.thomas") depuis l'AD
                    $samaccountname = $ldapUser->getFirstAttribute('samaccountname');

                    // 4. On cherche le compte associé dans la base de données Laravel
                    // (Car Auth::login() a besoin d'un utilisateur local pour la session web)
                    $localUser = LocalUser::where('samaccountname', $samaccountname)->first();

                    if ($localUser) {
                        // 5. Tout est bon ! On le connecte de force, sans mot de passe
                        Auth::login($localUser);
                        
                        $request->session()->regenerate();
                        return redirect()->intended('/');
                    } else {
                        // S'il est dans l'AD mais qu'il ne s'est jamais connecté à Laravel avant
                        return view('login')->withErrors([
                            'samaccountname' => "Compte AD trouvé ($samaccountname), mais introuvable dans la base locale. Connectez-vous avec votre mot de passe une première fois."
                        ]);
                    }
                } else {
                    // S'il n'existe pas ou plus dans l'AD
                    return view('login')->withErrors([
                        'samaccountname' => "Le compte associé à ce GUID n'existe pas dans l'Active Directory."
                    ]);
                }
            } catch (\Exception $e) {
                // Si l'AD est injoignable ou si le format du GUID est mauvais
                return view('login')->withErrors([
                    'samaccountname' => "Erreur de communication avec l'AD ou GUID mal formaté."
                ]);
            }
        }

        // 6. Si pas de GUID dans l'URL, on affiche simplement le formulaire
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