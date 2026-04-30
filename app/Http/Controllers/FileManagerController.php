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
        if (!$user) return redirect('/login');[cite: 3]

        // CORRECTION : On récupère le service exact (ex: "Administration")
        // Nous n'utilisons plus strtolower() pour ne pas casser le lien avec Windows
        $userService = $user->service; 
        
        $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];[cite: 3]
        $baseFolder = in_array($userService, $servicesAutorises) ? $userService : null;[cite: 3]

        if ($baseFolder === null) abort(403, "Accès refusé.");[cite: 3]

        $requestedPath = $request->query('path', $baseFolder);[cite: 3]
        $safeRelativePath = str_replace('..', '', (string)$requestedPath);[cite: 3]
        if (!str_starts_with($safeRelativePath, $baseFolder)) $safeRelativePath = $baseFolder;[cite: 3]

        $storagePath = $safeRelativePath; 
        $currentFolder = $safeRelativePath; 
        $parentPath = dirname($safeRelativePath);[cite: 3]
        if (!str_starts_with($parentPath, $baseFolder)) $parentPath = $baseFolder;[cite: 3]

        $showBackBtn = ($safeRelativePath !== $baseFolder);[cite: 3]

        // Récupération des DOSSIERS
        $rawFolders = Storage::disk('nas')->directories($storagePath);[cite: 3]
        $folders = [];
        foreach ($rawFolders as $f) {
            $folders[] = [
                'name' => basename($f),
                'path' => $f,
                'timestamp' => Storage::disk('nas')->lastModified($f)[cite: 3]
            ];
        }

        // Récupération des FICHIERS avec calcul de taille
        $rawFiles = Storage::disk('nas')->files($storagePath);[cite: 3]
        $files = [];
        foreach ($rawFiles as $f) {
            $time = Storage::disk('nas')->lastModified($f);[cite: 3]
            $size = Storage::disk('nas')->size($f);[cite: 3]
            $units = ['o', 'Ko', 'Mo', 'Go'];
            $i = 0; $bytes = $size;
            while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }[cite: 3]

            $files[] = [
                'name' => basename($f),
                'path' => $f,
                'size' => round($bytes, 2) . ' ' . $units[$i],[cite: 3]
                'date' => date('d/m/Y H:i', $time),[cite: 3]
                'timestamp' => $time,
                'ext' => strtolower(pathinfo($f, PATHINFO_EXTENSION))[cite: 3]
            ];
        }

        return view('filemanager', compact('folders', 'files', 'currentFolder', 'userService', 'safeRelativePath', 'parentPath', 'showBackBtn'));[cite: 3]
    }

    // Création de dossier
    public function makeDirectory(Request $request)
    {
        $user = Auth::user();
        $userService = $user->service;
        $path = $request->input('path');
        $folderName = $request->input('folder_name');[cite: 3]

        if (!str_starts_with($path, $userService)) return back()->with('error', 'Action non autorisée.');[cite: 3]
        
        $request->validate(['folder_name' => 'required|string|max:255']);[cite: 3]
        $fullPath = $path . '/' . $folderName;[cite: 3]

        if (Storage::disk('nas')->exists($fullPath)) return back()->with('error', 'Ce dossier existe déjà.');[cite: 3]

        Storage::disk('nas')->makeDirectory($fullPath);[cite: 3]
        return back()->with('success', 'Dossier créé avec succès.');[cite: 3]
    }

    public function download(Request $request)
    {
        $path = $request->query('path');[cite: 3]
        if (!$path || !Storage::disk('nas')->exists($path)) abort(404);[cite: 3]
        return Storage::disk('nas')->download($path);[cite: 3]
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userService = $user->service;
        $path = $request->input('path');[cite: 3]
        if (!str_starts_with($path, $userService)) return back()->with('error', 'Action non autorisée.');[cite: 3]

        $request->validate(['file' => 'required|file|max:10240']);[cite: 3]
        if ($request->hasFile('file')) {
            $file = $request->file('file');[cite: 3]
            Storage::disk('nas')->putFileAs($path, $file, $file->getClientOriginalName());[cite: 3]
            return back()->with('success', 'Fichier ajouté avec succès.');[cite: 3]
        }
        return back()->with('error', 'Erreur lors de l\'envoi.');[cite: 3]
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $userService = $user->service;
        $path = $request->input('path');[cite: 3]

        if (!str_starts_with($path, $userService) || !Storage::disk('nas')->exists($path)) {[cite: 3]
            return back()->with('error', "Action impossible ou élément introuvable.");[cite: 3]
        }

        $parent = dirname($path);[cite: 3]
        $directories = Storage::disk('nas')->directories($parent);[cite: 3]
        
        if (in_array($path, $directories)) {[cite: 3]
            Storage::disk('nas')->deleteDirectory($path);[cite: 3]
            $msg = "Le dossier a été supprimé.";[cite: 3]
        } else {
            Storage::disk('nas')->delete($path);[cite: 3]
            $msg = "Le fichier a été supprimé.";[cite: 3]
        }

        return back()->with('success', $msg);[cite: 3]
    }
}