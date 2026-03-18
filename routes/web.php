<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <-- AJOUT IMPORTANT ICI
use App\Http\Controllers\LoginController;
use App\Ldap\User as LdapUser;
use App\Http\Controllers\FileManagerController;

// Pages de connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Route de la page d'accueil
Route::get('/', function () {
    return view('welcome');
})->middleware('auth');

// La page Annuaire
Route::get('/annuaire', function () {
    $users = LdapUser::whereHas('mail')->get()->sortBy(function($user) {
        return $user->getFirstAttribute('cn'); 
    });

    return view('annuaire', [
        'users' => $users
    ]);
})->middleware('auth');

// La page Planning (On garde uniquement la version protégée par 'auth')
Route::get('/planning', function () {
    return view('planning');
})->middleware('auth');

// La page Support Informatique (Réservée à l'administrateur)
Route::get('/support_informatique', function () {
    // On vérifie si l'utilisateur est connecté et s'il est admin
    if (!Auth::check() || Auth::user()->getFirstAttribute('samaccountname') !== 'Administrateur') {
        abort(403, 'Accès non autorisé.'); // Renvoie une erreur 403 (Accès refusé)
    }
    
    // On commente le dd() qui servait aux tests, et on retourne la vue
    // dd( App\Ldap\User::whereHas('mail')->first()->getAttributes() ); 
    
    return view('support_informatique'); // ou l'appel à votre contrôleur
})->middleware('auth');

// Serveur de fichiers
Route::get('/fichiers', [FileManagerController::class, 'index'])->name('files.index')->middleware('auth');
Route::get('/fichiers/download', [FileManagerController::class, 'download'])->name('files.download')->middleware('auth');
Route::post('/fichiers/upload', [FileManagerController::class, 'store'])->name('files.store'); // Correction: j'ai enlevé App\Http\Controllers\ car il est déjà importé en haut
Route::delete('/fichiers/delete', [FileManagerController::class, 'destroy'])->name('files.destroy')->middleware('auth');
Route::post('/fichiers/mkdir', [FileManagerController::class, 'makeDirectory'])->name('files.mkdir')->middleware('auth');