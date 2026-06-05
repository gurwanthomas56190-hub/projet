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

        // 1. Définition du service et du chemin
        $userService = ucfirst(strtolower(trim($user->service ?? 'Administration')));
        $path = $request->query('path', $userService);
        
        // Sécurité de base
        $path = str_replace('..', '', $path); 
        if (!str_starts_with($path, $userService)) $path = $userService;

        // 2. Formatage des dossiers
        $folders = collect(Storage::disk('nas')->directories($path))->map(function($f) {
            return ['name' => basename($f), 'path' => $f];
        });

        // 3. Formatage des fichiers (taille calculée simplement en Ko)
        $files = collect(Storage::disk('nas')->files($path))->map(function($f) {
            $time = Storage::disk('nas')->lastModified($f);
            return [
                'name' => basename($f),
                'path' => $f,
                'size' => round(Storage::disk('nas')->size($f) / 1024, 2) . ' Ko',
                'date' => date('d/m/Y H:i', $time),
                'ext' => strtolower(pathinfo($f, PATHINFO_EXTENSION))
            ];
        });

        // 4. Gestion du bouton "Retour"
        $parentPath = dirname($path);
        if (!str_starts_with($parentPath, $userService)) $parentPath = $userService;

        return view('filemanager', [
            'folders' => $folders,
            'files' => $files,
            'currentFolder' => $path,
            'userService' => $userService,
            'safeRelativePath' => $path,
            'parentPath' => $parentPath,
            'showBackBtn' => ($path !== $userService)
        ]);
    }

    public function makeDirectory(Request $request)
    {
        $path = $request->input('path') . '/' . $request->input('folder_name');
        Storage::disk('nas')->makeDirectory($path);
        return back()->with('success', 'Dossier créé.');
    }

    public function download(Request $request)
    {
        return Storage::disk('nas')->download($request->query('path'));
    }

    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Storage::disk('nas')->putFileAs($request->input('path'), $file, $file->getClientOriginalName());
        }
        return back()->with('success', 'Fichier ajouté.');
    }

    public function destroy(Request $request)
    {
        $path = $request->input('path');
        // Si c'est un dossier, on supprime le dossier, sinon on supprime le fichier
        if (Storage::disk('nas')->directoryExists($path)) {
            Storage::disk('nas')->deleteDirectory($path);
        } else {
            Storage::disk('nas')->delete($path);
        }
        return back()->with('success', 'Élément supprimé.');
    }
}