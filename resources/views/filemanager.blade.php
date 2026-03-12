@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📁 Gestionnaire de fichiers</h4>
            <span class="badge bg-light text-dark">
                Connecté en tant que : {{ Auth::user()->samaccountname[0] ?? 'Inconnu' }} 
                (Service : {{ $userService }})
            </span>
        </div>
        
        <div class="card-body">
            <h5>Dossier actuel : <strong>{{ $currentFolder }}</strong></h5>
            <hr>

            <ul class="list-group">
                {{-- Affichage des Sous-Dossiers --}}
                @forelse($folders as $folder)
                    <li class="list-group-item list-group-item-warning">
                        🗂️ {{ basename($folder) }}
                    </li>
                @empty
                    <p class="text-muted">Aucun sous-dossier.</p>
                @endforelse
            </ul>

            <ul class="list-group mt-3">
                {{-- Affichage des Fichiers --}}
                @forelse($files as $file)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>📄 {{ basename($file) }}</span>
                        <a href="{{ route('files.download', ['path' => $file]) }}" class="btn btn-sm btn-success">
                            ⬇️ Télécharger
                        </a>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucun fichier dans ce dossier.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection