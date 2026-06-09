<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // <-- 1. Ajout indispensable pour vérifier l'admin

class FileManagerController extends Controller
{
    // --- NOUVELLE MÉTHODE ---
    // Sécurité : Vérifie si l'utilisateur a le droit d'agir sur ce chemin
    private function aLeDroit($path)
    {
        if (Gate::allows('gerer-annuaire')) return true; // L'admin a tous les droits
        
        $user = Auth::user();
        $userService = ucfirst(strtolower(trim($user->service ?? 'general')));
        // L'employé ne peut agir que si le chemin commence par le nom de son service
        return str_starts_with(str_replace('..', '', $path), $userService);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect('/login');

        // Vérification du rôle et définition de la racine
        $estAdmin = Gate::allows('gerer-annuaire');
        $userService = ucfirst(strtolower(trim($user->service ?? 'general')));
        
        // 2. L'admin démarre à la racine du NAS (''), les employés dans leur service
        $baseFolder = $estAdmin ? '' : $userService; 
        
        $path = $request->query('path', $baseFolder);
        
        // Sécurité de navigation
        $path = str_replace('..', '', $path); 
        if (!$this->aLeDroit($path)) $path = $baseFolder;

        // Formatage des dossiers
        $folders = collect(Storage::disk('nas')->directories($path))->map(function($f) {
            return ['name' => basename($f), 'path' => $f];
        });

        // Formatage des fichiers
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

        // Gestion du bouton "Retour"
        $parentPath = dirname($path);
        if ($parentPath === '.' || $parentPath === '\\') $parentPath = '';
        
        if (!$this->aLeDroit($parentPath)) $parentPath = $baseFolder;

        return view('filemanager', [
            'folders' => $folders,
            'files' => $files,
            'currentFolder' => $path,
            'userService' => $baseFolder, // Informe la vue du dossier racine de l'utilisateur
            'safeRelativePath' => $path,
            'parentPath' => $parentPath,
            'showBackBtn' => ($path !== $baseFolder)
        ]);
    }

    public function makeDirectory(Request $request)
    {
        $path = $request->input('path') . '/' . $request->input('folder_name');
        if (!$this->aLeDroit($path)) return back()->with('error', 'Accès refusé.'); // Sécurité
        
        Storage::disk('nas')->makeDirectory($path);
        return back()->with('success', 'Dossier créé.');
    }

    public function download(Request $request)
    {
        $path = $request->query('path');
        if (!$this->aLeDroit($path)) return abort(403); // Sécurité
        
        return Storage::disk('nas')->download($path);
    }

    public function store(Request $request)
    {
        $path = $request->input('path');
        if (!$this->aLeDroit($path)) return back()->with('error', 'Accès refusé.'); // Sécurité
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            Storage::disk('nas')->putFileAs($path, $file, $file->getClientOriginalName());
        }
        return back()->with('success', 'Fichier ajouté.');
    }

    public function destroy(Request $request)
    {
        $path = $request->input('path');
        if (!$this->aLeDroit($path)) return back()->with('error', 'Accès refusé.'); // Sécurité
        
        if (Storage::disk('nas')->directoryExists($path)) {
            Storage::disk('nas')->deleteDirectory($path);
        } else {
            Storage::disk('nas')->delete($path);
        }
        return back()->with('success', 'Élément supprimé.');
    }
}