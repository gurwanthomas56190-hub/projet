<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Ldap\User as LdapUser;

// Route de la page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// NOUVELLE ROUTE : La page Annuaire
Route::get('/annuaire', function () {
    // On récupère les utilisateurs de l'AD.
    // Le filtre "whereHas('mail')" est une astuce très utile : 
    // ça évite d'afficher les comptes systèmes de Windows (Administrateur, Invité, etc.)
    $users = LdapUser::whereHas('mail')->sortBy('cn')->get();

    return view('annuaire', [
        'users' => $users
    ]);
})->middleware('auth');
Route::get('/planning', function () {
    return view('planning');
});



// Pages de connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// On protège le planning : seul un utilisateur connecté peut y aller
Route::get('/planning', function () {
    return view('planning');
})->middleware('auth');