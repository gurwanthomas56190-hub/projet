<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Affiche la page de connexion
    public function showLoginForm() {
        return view('login');
    }

    // Gère la tentative de connexion
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

    // Déconnexion
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}