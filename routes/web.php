<?php

use Illuminate\Support\Facades\Route;

// Route de la page d'accueil
Route::get('/', function () {
    return view('welcome');
});

// NOUVELLE ROUTE : La page Annuaire
Route::get('/annuaire', function () {
    return view('annuaire');
});

Route::get('/planning', function () {
    return view('planning');
});

use App\Http\Controllers\LoginController;

// Pages de connexion
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// On protège le planning : seul un utilisateur connecté peut y aller
Route::get('/planning', function () {
    return view('planning');
})->middleware('auth');