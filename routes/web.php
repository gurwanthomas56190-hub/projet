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