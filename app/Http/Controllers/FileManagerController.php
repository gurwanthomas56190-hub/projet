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
        if (!$user) return redirect('/login');

        $userService = ucfirst(strtolower($user->getService())); 
        $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];
        $baseFolder = in_array($userService, $servicesAutorises) ? $userService : null;

        if ($baseFolder === null) abort(403, "Accès refusé.");

        $requestedPath = $request->query('path', $baseFolder);
        $safeRelativePath = str_replace('..', '', (string)$requestedPath);
        if (!str_starts_with($safeRelativePath, $baseFolder)) $safeRelativePath = $baseFolder;

        $storagePath = $safeRelativePath; 
        $currentFolder = $safeRelativePath; 
        $parentPath = dirname($safeRelativePath);
        if (!str_starts_with($parentPath, $baseFolder)) $parentPath = $baseFolder;

        $showBackBtn = ($safeRelativePath !== $baseFolder);

        // Récupération et Tri des DOSSIERS par date
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

        // Récupération et Tri des FICHIERS par date
        $rawFiles = Storage::disk('nas')->files($storagePath);
        $files = [];
        foreach ($rawFiles as $f) {
            $time = Storage::disk('nas')->lastModified($f);
            $size = Storage::disk('nas')->size($f);
            $units = ['o', 'Ko', 'Mo', 'Go'];
            $i = 0; $bytes = $size;
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

        return view('filemanager', compact('folders', 'files', 'currentFolder', 'userService', 'safeRelativePath', 'parentPath', 'showBackBtn'));
    }

    // Création de dossier
    public function makeDirectory(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        $path = $request->input('path');
        $folderName = $request->input('folder_name');

        if (!str_starts_with($path, $userService)) return back()->with('error', 'Action non autorisée.');
        
        $request->validate(['folder_name' => 'required|string|max:255']);
        $fullPath = $path . '/' . $folderName;

        if (Storage::disk('nas')->exists($fullPath)) return back()->with('error', 'Ce dossier existe déjà.');

        Storage::disk('nas')->makeDirectory($fullPath);
        return back()->with('success', 'Dossier créé avec succès.');
    }

    public function download(Request $request)
    {
        $path = $request->query('path'); 
        if (!$path || !Storage::disk('nas')->exists($path)) abort(404);
        return Storage::disk('nas')->download($path);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        $path = $request->input('path'); 
        if (!str_starts_with($path, $userService)) return back()->with('error', 'Action non autorisée.');

        $request->validate(['file' => 'required|file|max:10240']);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Storage::disk('nas')->putFileAs($path, $file, $file->getClientOriginalName());
            return back()->with('success', 'Fichier ajouté avec succès.');
        }
        return back()->with('error', 'Erreur lors de l\'envoi.');
    }

    // Suppression universelle (Fichier ou Dossier)
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $userService = ucfirst(strtolower($user->getService()));
        $path = $request->input('path');

        if (!str_starts_with($path, $userService) || !Storage::disk('nas')->exists($path)) {
            return back()->with('error', "Action impossible ou élément introuvable.");
        }

        // Détection : est-ce un dossier ?
        $parent = dirname($path);
        $directories = Storage::disk('nas')->directories($parent);
        
        if (in_array($path, $directories)) {
            Storage::disk('nas')->deleteDirectory($path);
            $msg = "Le dossier a été supprimé.";
        } else {
            Storage::disk('nas')->delete($path);
            $msg = "Le fichier a été supprimé.";
        }

        return back()->with('success', $msg);
    }
}