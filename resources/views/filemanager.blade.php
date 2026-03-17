@extends('layouts.app')

@section('content')
{{-- Importation des icônes professionnelles --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Petits ajustements esthétiques */
    .file-row { transition: background-color 0.2s ease; cursor: default; }
    .file-row:hover { background-color: #f8f9fa; }
    .icon-large { font-size: 1.4rem; }
    .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; padding: 0; border-radius: 50%; transition: 0.2s; }
    .btn-icon:hover { background-color: #e9ecef; }
    .table-custom th { font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; border-bottom: 2px solid #eee; padding-bottom: 12px;}
    .table-custom td { border-bottom: 1px solid #f0f0f0; padding: 12px 8px; vertical-align: middle; }
    .folder-link { text-decoration: none; color: #333; transition: color 0.2s; }
    .folder-link:hover { color: #0d6efd; }
</style>

<div class="container mt-5 mb-5">
    
    {{-- En-tête épuré --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Fichiers</h3>
            <div class="text-muted small">
                <i class="bi bi-hdd-network me-1"></i> Serveur NAS / <span class="fw-medium text-dark">{{ $currentFolder }}</span>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            @if($showBackBtn)
                <a href="{{ route('files.index', ['path' => $parentPath]) }}" class="btn btn-light border shadow-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Retour
                </a>
            @endif
            
            {{-- Bouton d'envoi caché qui déclenche l'explorateur --}}
            <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <input type="hidden" name="path" value="{{ $safeRelativePath ?: $userService }}">
                <input type="file" name="file" id="fileInput" class="d-none" onchange="document.getElementById('uploadForm').submit();">
                <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" onclick="document.getElementById('fileInput').click();">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> Uploader
                </button>
            </form>
        </div>
    </div>

    {{-- Alertes esthétiques --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Conteneur Principal Blanc --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-borderless mb-0 w-100">
                    <thead class="text-muted bg-white">
                        <tr>
                            <th class="ps-4" style="width: 50%;">Nom du fichier</th>
                            <th style="width: 15%;">Taille</th>
                            <th style="width: 15%;">Type</th>
                            <th style="width: 15%;">Modifié le</th>
                            <th class="text-end pe-4" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- DOSSIERS --}}
                        @foreach($folders as $folder)
                            @php
                                $newRelativePath = $safeRelativePath ? $safeRelativePath . '/' . basename($folder) : basename($folder);
                            @endphp
                            <tr class="file-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-folder-fill text-warning icon-large me-3"></i>
                                        <a href="{{ route('files.index', ['path' => $newRelativePath]) }}" class="folder-link fw-medium">
                                            {{ basename($folder) }}
                                        </a>
                                    </div>
                                </td>
                                <td class="text-muted small">--</td>
                                <td><span class="badge bg-light text-secondary border fw-normal">Dossier</span></td>
                                <td class="text-muted small">--</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('files.index', ['path' => $newRelativePath]) }}" class="btn btn-icon text-primary" title="Ouvrir">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        {{-- FICHIERS --}}
                        @foreach($files as $file)
                            <tr class="file-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi {{ $file['icon'] }} icon-large me-3"></i>
                                        <span class="fw-medium text-dark">{{ $file['name'] }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $file['size'] }}</td>
                                <td><span class="badge bg-light text-secondary border fw-normal">{{ $file['type'] }}</span></td>
                                <td class="text-muted small">{{ $file['date'] }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        
                                        {{-- Bouton Télécharger --}}
                                        <a href="{{ route('files.download', ['path' => $file['path']]) }}" class="btn btn-icon text-success" title="Télécharger">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        {{-- Bouton Supprimer --}}
                                        <form action="{{ route('files.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce fichier ?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="path" value="{{ $file['path'] }}">
                                            <button type="submit" class="btn btn-icon text-danger" title="Supprimer">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                        
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- VIDE --}}
                        @if(empty($folders) && empty($files))
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                    <h5 class="mt-3 text-muted fw-light">Ce dossier est vide</h5>
                                    <p class="text-muted small">Cliquez sur "Uploader" pour ajouter des fichiers.</p>
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection