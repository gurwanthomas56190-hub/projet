<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FileManagerController extends Controller
{
    public function index()
    {
        $user = Auth::user(); 

        if (!$user) {
            return redirect('/login');
        }

        // On récupère le nom du service grâce à ta super fonction !
        $userService = $user->getService(); // ex: "Informatique", "Administration"...

        $folders = [];
        $files = [];
        $currentFolder = 'Racine';

        // Si l'utilisateur est dans l'un de ces services, on lui montre son dossier
        $servicesAutorises = ['Administration', 'Informatique', 'Marketing', 'Production'];

        if (in_array($userService, $servicesAutorises)) {
            // On liste uniquement le contenu du dossier de SON service
            $currentFolder = $userService;
            $folders = Storage::disk('nas')->directories($userService);
            $files = Storage::disk('nas')->files($userService);
        } else {
            // Si le service n'est pas reconnu (ou Admin global), on peut soit bloquer, soit tout afficher.
            // Ici, on affiche la racine complète par défaut
            $folders = Storage::disk('nas')->directories();
            $files = Storage::disk('nas')->files();
        }

        return view('filemanager', compact('folders', 'files', 'currentFolder', 'userService'));
    }

    public function download(Request $request)
    {
        $path = $request->query('path'); // Le chemin complet du fichier
        
        // Petite sécurité basique
        if (!$path || !Storage::disk('nas')->exists($path)) {
            abort(404, "Fichier introuvable.");
        }

        return Storage::disk('nas')->download($path);
    }
}