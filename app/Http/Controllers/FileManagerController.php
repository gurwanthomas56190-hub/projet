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

        // 1. Détermination du service et du dossier de base
        $userService = ucfirst(strtolower($user->getService())); 
        $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];
        $baseFolder = in_array($userService, $servicesAutorises) ? $userService : null;

        if ($baseFolder === null) {
            abort(403, "Vous n'avez pas l'autorisation d'accéder au gestionnaire de fichiers.");
        }

        // 2. Sécurisation du chemin
        $requestedPath = $request->query('path', $baseFolder);
        $safeRelativePath = str_replace('..', '', (string)$requestedPath);

        if (!str_starts_with($safeRelativePath, $baseFolder)) {
            $safeRelativePath = $baseFolder;
        }

        $storagePath = $safeRelativePath; 
        $currentFolder = $safeRelativePath; 
        $parentPath = dirname($safeRelativePath);
        
        if (!str_starts_with($parentPath, $baseFolder)) {
            $parentPath = $baseFolder;
        }

        $showBackBtn = ($safeRelativePath !== $baseFolder);

        // 3. Récupération et TRI des DOSSIERS (plus récents en premier)
        $rawFolders = Storage::disk('nas')->directories($storagePath);
        $folders = [];
        foreach ($rawFolders as $f) {
            $folders[] = [
                'name' => basename($f),
                'path' => $f,
                'timestamp' => Storage::disk('nas')->lastModified($f)
            ];
        }
        usort($folders, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // 4. Récupération et TRI des FICHIERS (plus récents en premier)
        $rawFiles = Storage::disk('nas')->files($storagePath);
        $files = [];
        foreach ($rawFiles as $f) {
            $time = Storage::disk('nas')->lastModified($f);
            $size = Storage::disk('nas')->size($f);
            
            // Formatage de la taille
            $units = ['o', 'Ko', 'Mo', 'Go'];
            $i = 0;
            $bytes = $size;
            while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }

            $files[] = [
                'name' => basename($f),
                'path' => $f,
                'size' => round($bytes, 2) . ' ' . $units[$i],
                'date' => date('d/m/Y H:i', $time),
                'timestamp' => $time,
                'ext' => strtolower(pathinfo($f, PATHINFO_EXTENSION))
            ];
        }
        usort($files, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return view('filemanager', compact(
            'folders', 'files', 'currentFolder', 'userService', 'safeRelativePath', 'parentPath', 'showBackBtn'
        ));
    }

    // NOUVELLE MÉTHODE : Créer un dossier
    public function makeDirectory(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        $path = $request->input('path');
        $folderName = $request->input('folder_name');

        if (!str_starts_with($path, $userService)) {
            return back()->with('error', 'Action non autorisée dans ce répertoire.');
        }

        $request->validate(['folder_name' => 'required|string|max:255']);
        $fullPath = $path . '/' . $folderName;

        if (Storage::disk('nas')->exists($fullPath)) {
            return back()->with('error', 'Ce dossier existe déjà.');
        }

        Storage::disk('nas')->makeDirectory($fullPath);
        return back()->with('success', 'Dossier créé avec succès.');
    }

    public function download(Request $request)
    {
        $path = $request->query('path'); 
        if (!$path || !Storage::disk('nas')->exists($path)) { abort(404, "Fichier introuvable."); }
        return Storage::disk('nas')->download($path);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        $path = $request->input('path'); 

        if (!str_starts_with($path, $userService)) {
            return back()->with('error', 'Action non autorisée.');
        }

        $request->validate(['file' => 'required|file|max:10240']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Storage::disk('nas')->putFileAs($path, $file, $file->getClientOriginalName());
            return back()->with('success', 'Fichier ajouté avec succès.');
        }
        return back()->with('error', 'Erreur lors de l\'envoi.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        $path = $request->input('path');
        $safePath = str_replace('..', '', (string)$path);

        if (!str_starts_with($safePath, $userService) || !Storage::disk('nas')->exists($safePath)) {
            return back()->with('error', "Impossible de supprimer ce fichier.");
        }

        Storage::disk('nas')->delete($safePath);
        return back()->with('success', "Le fichier a été supprimé.");
    }
}