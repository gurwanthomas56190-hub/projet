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
    // Le dossier racine autorisé pour l'utilisateur
    $baseFolder = $user->service ?? 'Administration'; 

    // On récupère le chemin. Si l'utilisateur est à la racine de son service, 
    // on ne doit pas rajouter le nom du dossier si on est déjà dedans.
    $requestedPath = $request->query('path', $baseFolder);

    // Nettoyage pour empêcher de remonter plus haut que le dossier racine
    $safeRelativePath = str_replace(['../', '..'], '', $requestedPath);
    
    // Si le chemin est vide ou égal au service, on force la lecture du dossier du service
    if ($safeRelativePath === $baseFolder) {
        $path = $baseFolder;
    } else {
        $path = $safeRelativePath;
    }

    try {
        // On lit le contenu du dossier
        $files = Storage::disk('nas')->files($path);
        $directories = Storage::disk('nas')->directories($path);

        return view('filemanager', [
            'files' => $files,
            'directories' => $directories,
            'currentPath' => $path,
            'baseFolder' => $baseFolder
        ]);
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Erreur : ' . $e->getMessage()]);
    }
}

    public function download($path)
    {
        $path = base64_decode($path);
        return Storage::disk('nas')->download($path);
    }
}