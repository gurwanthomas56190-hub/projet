@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        
        {{-- 1. L'EN-TÊTE (HEADER) --}}
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📁 Gestionnaire de fichiers</h4>
            <span class="badge bg-light text-dark">
                Connecté en tant que : {{ Auth::user()->samaccountname[0] ?? 'Inconnu' }} 
                (Service : {{ $userService }})
            </span>
        </div>
        
        <div class="card-body">
            
            {{-- 2. NAVIGATION ET CHEMIN ACTUEL --}}
            <h5 class="mb-3">Dossier actuel : <strong>{{ $currentFolder }}</strong></h5>

            {{-- Bouton pour remonter au dossier parent (s'affiche uniquement si on n'est pas à la racine) --}}
            @if($showBackBtn)
            <a href="{{ route('files.index', ['path' => $parentPath]) }}" class="btn btn-secondary mb-3">
            ⬆️ Dossier parent
            </a>
            @endif

            <hr>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- FORMULAIRE D'ENVOI DE FICHIER --}}
            <div class="card card-body bg-light mb-4">
                <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center">
                    @csrf
                    <input type="hidden" name="path" value="{{ $safeRelativePath ?: $userService }}">
                    
                    <input type="file" name="file" class="form-control me-3" required>
                    <button type="submit" class="btn btn-primary text-nowrap">📤 Envoyer le fichier</button>
                </form>
            </div>

            <div class="list-group">
                
                {{-- 3. AFFICHAGE DES SOUS-DOSSIERS --}}
                @foreach($folders as $folder)
                    @php
                        // On construit le lien correct pour entrer dans le dossier
                        $newRelativePath = $safeRelativePath ? $safeRelativePath . '/' . basename($folder) : basename($folder);
                    @endphp
                    
                    <a href="{{ route('files.index', ['path' => $newRelativePath]) }}" class="list-group-item list-group-item-action list-group-item-warning font-weight-bold">
                        🗂️ {{ basename($folder) }}
                    </a>
                @endforeach

                {{-- 4. AFFICHAGE DES FICHIERS --}}
                @foreach($files as $file)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>📄 {{ basename($file) }}</span>
                        
                        {{-- Bouton de téléchargement --}}
                        <a href="{{ route('files.download', ['path' => $file]) }}" class="btn btn-sm btn-success">
                            ⬇️ Télécharger
                        </a>
                    </div>
                @endforeach

                {{-- 5. CAS OÙ LE DOSSIER EST VIDE --}}
                @if(empty($folders) && empty($files))
                    <div class="list-group-item text-muted text-center py-4">
                        Ce dossier est vide.
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection