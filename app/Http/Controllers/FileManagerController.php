<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user(); 

        if (!$user) {
            return redirect('/login');
        }

        $userService = $user->getService(); 
        $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];

        // On détermine le dossier de base de l'utilisateur
        $baseFolder = in_array($userService, $servicesAutorises) ? $userService : '';

        // On récupère le chemin demandé dans l'URL
        $requestedPath = $request->query('path', $baseFolder);

        // SÉCURITÉ : On empêche de remonter dans les dossiers interdits avec "../"
        $safeRelativePath = str_replace('..', '', (string)$requestedPath);

        // Si l'utilisateur n'est pas admin (il a un baseFolder), on le force à rester dans son dossier
        if ($baseFolder !== '' && !str_starts_with($safeRelativePath, $baseFolder)) {
            $safeRelativePath = $baseFolder;
        }

        // --- LE CORRECTIF EST ICI ---
        // $storagePath = Le vrai chemin informatique (vide = la racine du serveur)
        $storagePath = $safeRelativePath ?: ''; 
        
        // $currentFolder = Le joli nom pour l'affichage sur la page web
        $currentFolder = $safeRelativePath ?: 'Racine'; 

        // Calcul du chemin du dossier parent (pour le bouton Retour)
        $parentPath = dirname($safeRelativePath);
        if ($parentPath === '.' || $parentPath === '\\' || $parentPath === '/') {
            $parentPath = '';
        }

        // On cache le bouton "Retour" si on est déjà à la racine du service
        if ($safeRelativePath === $baseFolder || $safeRelativePath === '') {
            $safeRelativePath = null;
        }

        // --- DEUXIÈME CORRECTIF ---
        // On utilise $storagePath au lieu de $currentFolder pour interroger le disque !
        $folders = Storage::disk('nas')->directories($storagePath);
        $files = Storage::disk('nas')->files($storagePath);

        // On envoie tout à la vue
        return view('filemanager', compact(
            'folders', 
            'files', 
            'currentFolder', 
            'userService', 
            'safeRelativePath', 
            'parentPath'
        ));
    }

    public function download(Request $request)
    {
        $path = $request->query('path'); 
        
        if (!$path || !Storage::disk('nas')->exists($path)) {
            abort(404, "Fichier introuvable.");
        }

        return Storage::disk('nas')->download($path);
    }
}