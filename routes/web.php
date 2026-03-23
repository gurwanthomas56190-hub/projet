<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FileManagerController;
use App\Ldap\User as LdapUser;

/*
|--------------------------------------------------------------------------
| Web Routes - Intranet Silvadec
|--------------------------------------------------------------------------
*/

// --- AUTHENTIFICATION MANUELLE (Fallback / Hors-Domaine) ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- ROUTES PROTÉGÉES (SSO Kerberos & Auth LDAP) ---
// Toutes les routes à l'intérieur de ce groupe nécessitent d'être connecté.
// Le SSO configuré dans bootstrap/app.php connectera l'utilisateur automatiquement ici.
Route::middleware('auth')->group(function () {

    // Page d'accueil de l'intranet
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    // Annuaire LDAP : Récupère les utilisateurs ayant un mail et trie par nom (CN)
    Route::get('/annuaire', function () {
        $users = LdapUser::whereHas('mail')->get()->sortBy(function($user) {
            return $user->getFirstAttribute('cn'); 
        });

        return view('annuaire', [
            'users' => $users
        ]);
    })->name('annuaire');

    // Page Planning
    Route::get('/planning', function () {
        return view('planning');
    })->name('planning');

    // Support Informatique : Restriction stricte à l'utilisateur "Administrateur"
    Route::get('/support_informatique', function () {
        $user = Auth::user();
        
        // Vérification du sAMAccountName de l'utilisateur AD connecté
        if ($user->getFirstAttribute('samaccountname') !== 'Administrateur') {
            abort(403, 'Accès non autorisé : Réservé à l\'administrateur du domaine.');
        }
        
        return view('support_informatique');
    })->name('support');

    // --- GESTIONNAIRE DE FICHIERS ---
    // Utilisation d'un préfixe pour regrouper les routes de fichiers
    Route::prefix('fichiers')->name('files.')->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::get('/download', [FileManagerController::class, 'download'])->name('download');
        Route::post('/upload', [FileManagerController::class, 'store'])->name('store');
        Route::delete('/delete', [FileManagerController::class, 'destroy'])->name('destroy');
        Route::post('/mkdir', [FileManagerController::class, 'makeDirectory'])->name('mkdir');
    });

});