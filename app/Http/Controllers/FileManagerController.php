<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        // Récupération de l'utilisateur authentifié
        $user = Auth::user();

        // Définition du dossier de base selon le service de l'utilisateur
        // On suppose que l'objet user a une propriété 'service'
        $baseFolder = $user->service ?? 'Administration'; 

        // Récupération du chemin demandé via l'URL, sinon racine du service
        $requestedPath = $request->query('path', $baseFolder);

        // Sécurisation basique pour éviter de sortir du dossier racine
        // Note : Dans un environnement réel, validez strictement que $requestedPath commence par $baseFolder
        $safeRelativePath = str_replace(['../', '..'], '', $requestedPath);

        try {
            // Récupération des fichiers et dossiers
            // On utilise le disque 'nas' défini dans config/filesystems.php
            $files = Storage::disk('nas')->files($safeRelativePath);
            $directories = Storage::disk('nas')->directories($safeRelativePath);

            return view('filemanager', [
                'files' => $files,
                'directories' => $directories,
                'currentPath' => $safeRelativePath,
                'baseFolder' => $baseFolder
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Impossible d\'accéder au dossier : ' . $e->getMessage()]);
        }
    }

    public function download($path)
    {
        $path = base64_decode($path);
        return Storage::disk('nas')->download($path);
    }
}