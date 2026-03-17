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

    // 1. On récupère le service et on force la première lettre en Majuscule (ex: informatique -> Informatique)
    $userService = ucfirst(strtolower($user->getService())); 
    
    $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];

    // 2. On détermine le dossier autorisé
    // Si l'utilisateur est dans la liste, son dossier est son service. Sinon, il n'a accès à RIEN (ou un dossier public).
    $baseFolder = in_array($userService, $servicesAutorises) ? $userService : null;

    // SÉCURITÉ : Si l'utilisateur n'appartient à aucun service autorisé, on lui refuse l'accès
    if ($baseFolder === null) {
        abort(403, "Vous n'avez pas l'autorisation d'accéder au gestionnaire de fichiers.");
    }

    // 3. On récupère le chemin demandé
    $requestedPath = $request->query('path', $baseFolder);

    // 4. SÉCURITÉ : Nettoyage contre les piratages de type "../"
    $safeRelativePath = str_replace('..', '', (string)$requestedPath);

    // 5. LE VERROU : Si le chemin demandé ne commence pas par le dossier du service, on le ramène à son dossier
    if (!str_starts_with($safeRelativePath, $baseFolder)) {
        $safeRelativePath = $baseFolder;
    }

    $storagePath = $safeRelativePath; 
    $currentFolder = $safeRelativePath; 

    // Calcul du parent pour le bouton retour
    $parentPath = dirname($safeRelativePath);
    // On empêche de remonter plus haut que le dossier du service
    if (!str_starts_with($parentPath, $baseFolder)) {
        $parentPath = $baseFolder;
    }

    // Gestion de l'affichage du bouton "Retour"
    $isAtRoot = ($safeRelativePath === $baseFolder);
    $showBackBtn = !$isAtRoot;

    // 6. Lecture du NAS
    $folders = Storage::disk('nas')->directories($storagePath);
    $files = Storage::disk('nas')->files($storagePath);

    return view('filemanager', compact(
        'folders', 
        'files', 
        'currentFolder', 
        'userService', 
        'safeRelativePath', 
        'parentPath',
        'showBackBtn'
        ));
    }
    public function download(Request $request)
    {
        $path = $request->query('path'); 
        
        // On vérifie si le fichier existe sur le disque NAS
        if (!$path || !Storage::disk('nas')->exists($path)) {
            abort(404, "Fichier introuvable.");
        }

        return Storage::disk('nas')->download($path);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        
        // Le dossier dans lequel l'utilisateur essaie d'envoyer le fichier
        $path = $request->input('path'); 

        // SÉCURITÉ GPO : On s'assure qu'il ne peut uploader que dans son propre service
        if (!str_starts_with($path, $userService)) {
            return back()->with('error', 'Vous n\'avez pas le droit de déposer des fichiers dans ce dossier.');
        }

        // On vérifie qu'un fichier a bien été envoyé (et on limite la taille si besoin, ex: 10Mo = 10240)
        $request->validate([
            'file' => 'required|file|max:10240', 
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // On récupère le vrai nom du fichier
            $name = $file->getClientOriginalName();
            
            // On le sauvegarde sur le NAS dans le dossier actuel
            Storage::disk('nas')->putFileAs($path, $file, $name);
            
            return back()->with('success', 'Le fichier a été envoyé avec succès !');
        }

        return back()->with('error', 'Erreur lors de l\'envoi du fichier.');
    }
}