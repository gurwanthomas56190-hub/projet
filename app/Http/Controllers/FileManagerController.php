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

        $userService = ucfirst(strtolower($user->getService())); 
        $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];

        $baseFolder = in_array($userService, $servicesAutorises) ? $userService : null;

        if ($baseFolder === null) {
            abort(403, "Vous n'avez pas l'autorisation d'accéder au gestionnaire de fichiers.");
        }

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

        $isAtRoot = ($safeRelativePath === $baseFolder);
        $showBackBtn = !$isAtRoot;

        // Lecture du NAS
        $folders = Storage::disk('nas')->directories($storagePath);
        $rawFiles = Storage::disk('nas')->files($storagePath);

        $files = [];
        foreach ($rawFiles as $file) {
            try {
                $size = Storage::disk('nas')->size($file);
                $time = Storage::disk('nas')->lastModified($file);
                
                $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
                $i = 0;
                while ($size >= 1024 && $i < 4) {
                    $size /= 1024;
                    $i++;
                }
                $formattedSize = round($size, 2) . ' ' . $units[$i];
                $formattedDate = date('d/m/Y à H:i', $time);
                
            } catch (\Exception $e) {
                $formattedSize = '--';
                $formattedDate = '--';
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $type = $extension ? strtoupper($extension) : 'Fichier';
            
            // ATTRIBUTION DES ICÔNES ET COULEURS (Bootstrap)
            $icon = 'bi-file-earmark text-secondary'; // Gris par défaut
            if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'svg'])) $icon = 'bi-file-earmark-image text-primary'; // Bleu
            elseif (in_array($extension, ['pdf'])) $icon = 'bi-file-earmark-pdf text-danger'; // Rouge
            elseif (in_array($extension, ['doc', 'docx'])) $icon = 'bi-file-earmark-word text-info'; // Bleu clair
            elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) $icon = 'bi-file-earmark-excel text-success'; // Vert
            elseif (in_array($extension, ['zip', 'rar', '7z'])) $icon = 'bi-file-earmark-zip text-dark'; // Sombre
            elseif (in_array($extension, ['mp4', 'avi', 'mkv'])) $icon = 'bi-file-earmark-play text-warning'; // Jaune
            elseif (in_array($extension, ['mp3', 'wav'])) $icon = 'bi-file-earmark-music text-info'; // Bleu clair

            $files[] = [
                'path' => $file,
                'name' => basename($file),
                'size' => $formattedSize,
                'date' => $formattedDate,
                'type' => $type,
                'icon' => $icon // On envoie la classe Bootstrap complète à la vue
            ];
        }

        return view('filemanager', compact(
            'folders', 'files', 'currentFolder', 'userService', 'safeRelativePath', 'parentPath', 'showBackBtn'
        ));
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