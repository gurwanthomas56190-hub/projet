<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\FileManagerController;
use App\Ldap\User as LdapUser;
use App\Http\Controllers\AnnuaireController;

// ==========================================
// ROUTES LIBRES (Non protégées)
// ==========================================

// --- Authentification Manuelle ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- La Porte VIP Kerberos (Doit être libre d'accès !) ---
Route::get('/sso-login', function (\Illuminate\Http\Request $request) {
    $remoteUser = $request->header('X-Remote-User');
    if ($remoteUser) {
        $username = explode('@', $remoteUser)[0];
        $user = LdapUser::where('samaccountname', $username)->first();
        if ($user) {
            Auth::login($user);
            return redirect('/');
        }
    }
    return redirect('/login')->withErrors(['sso' => 'Échec de la connexion réseau. Avez-vous un ticket Kerberos ?']);
})->name('sso.login');


// ==========================================
// ROUTES PROTÉGÉES (Réservées aux connectés)
// ==========================================
Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/annuaire', function () {
        $users = LdapUser::whereHas('mail')->get()->sortBy(function($user) {
            return $user->getFirstAttribute('cn'); 
        });
        return view('annuaire', ['users' => $users]);
    })->name('annuaire');

    Route::get('/planning', function () {
        return view('planning');
    })->name('planning');

    Route::get('/support_informatique', function () {
        if (Auth::user()->getFirstAttribute('samaccountname') !== 'Administrateur') {
            abort(403, 'Accès non autorisé.');
        }
        return view('support_informatique');
    })->name('support');

    // Gestionnaire de fichiers
    Route::prefix('fichiers')->name('files.')->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::get('/download', [FileManagerController::class, 'download'])->name('download');
        Route::post('/upload', [FileManagerController::class, 'store'])->name('store');
        Route::delete('/delete', [FileManagerController::class, 'destroy'])->name('destroy');
        Route::post('/mkdir', [FileManagerController::class, 'makeDirectory'])->name('mkdir');
    });
});

Route::resource('annuaire', AnnuaireController::class)->parameters([
    'annuaire' => 'samaccountname' // On utilise le samaccountname au lieu de l'ID classique pour chercher dans l'AD
]);