<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Ldap\User as LdapUser;
use App\Http\Controllers\FileManagerController;

// Route de la page d'accueil
Route::get('/', function () {
    return view('welcome');
})->middleware('auth');

// NOUVELLE ROUTE : La page Annuaire
Route::get('/annuaire', function () {
    // 1. On récupère les utilisateurs avec get()
    // 2. Ensuite on trie la collection avec sortBy()
    
    $users = LdapUser::whereHas('mail')->get()->sortBy(function($user) {
        // On trie alphabétiquement en utilisant le texte du Common Name (cn)
        return $user->getFirstAttribute('cn'); 
    });

    return view('annuaire', [
        'users' => $users
    ]);
})->middleware('auth');


Route::get('/planning', function () {
    return view('planning');
});

Route::get('/support_informatique', function () {
    // Affiche les données du premier utilisateur avec un email directement sur la page web
    dd( App\Ldap\User::whereHas('mail')->first()->getAttributes() ); 
});

Route::get('/support_informatique', function () {
    // Affiche les données du premier utilisateur avec un email directement sur la page web
    dd( App\Ldap\User::whereHas('mail')->first()->getAttributes() ); 
})->middleware('auth');

// Pages de connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// On protège le planning : seul un utilisateur connecté peut y aller
Route::get('/planning', function () {
    return view('planning');
})->middleware('auth');

Route::get('/fichiers', [FileManagerController::class, 'index'])->name('files.index')->middleware('auth');
Route::get('/fichiers/download', [FileManagerController::class, 'download'])->name('files.download')->middleware('auth');