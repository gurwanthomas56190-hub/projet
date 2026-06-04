@extends('layouts.app')

@section('title', 'Gestionnaire de fichiers')

@section('content')
{{-- Importation des icônes --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container full-width"> 
    <main>
        <div class="card mx-auto" style="max-width: 1100px;">
            
            {{-- En-tête --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">📁 Serveur de fichiers : {{ $userService }}</h2>
                @if($showBackBtn)
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" class="btn" style="text-decoration: none; background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px 18px; border-radius: 50px; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-arrow-left-circle-fill text-primary" style="font-size: 1.2rem;"></i> Retour
                    </a>
                @endif
            </div>

            <p style="color: #666; margin-bottom: 20px;">Dossier actuel : <strong>{{ $currentFolder }}</strong></p>

            {{-- ZONE D'ACTIONS : Upload et Nouveau Dossier (CORRIGÉE POUR LE DÉPASSEMENT) --}}
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #eee;">
                <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
                    
                    {{-- Créer un Dossier --}}
                    <form action="{{ route('files.mkdir') }}" method="POST" style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 250px; margin: 0;">
                        @csrf
                        <input type="hidden" name="path" value="{{ $safeRelativePath }}">
                        <label style="font-weight: bold; font-size: 0.9rem; white-space: nowrap;">📁 Dossier:</label>
                        <input type="text" name="folder_name" placeholder="Nom" required style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; flex-grow: 1; min-width: 0;">
                        <button type="submit" style="background-color: #0d6efd; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; white-space: nowrap;">➕ Créer</button>
                    </form>

                    <div style="border-left: 1px solid #ddd; height: 30px;" class="d-none d-md-block"></div>

                    {{-- Envoyer un Fichier --}}
                    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 250px; margin: 0;">
                        @csrf
                        <input type="hidden" name="path" value="{{ $safeRelativePath }}">
                        <label style="font-weight: bold; font-size: 0.9rem; white-space: nowrap;">📄 Fichier:</label>
                        <input type="file" name="file" required style="font-size: 0.8rem; flex-grow: 1; min-width: 0;">
                        <button type="submit" class="logout-button" style="background-color: #198754; border: none; padding: 8px 15px; color: white; border-radius: 4px; cursor: pointer; white-space: nowrap;">📤 Envoyer</button>
                    </form>
                </div>
            </div>

            @if(session('success')) <div style="color: #0f5132; background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px;">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div style="color: #842029; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;">❌ {{ session('error') }}</div> @endif

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Taille / Type</th>
                        <th>Modifié le</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- DOSSIERS --}}
                    @foreach($folders as $folder)
                        <tr onmouseover="this.style.backgroundColor='#f1f1f1'" onmouseout="this.style.backgroundColor=''">
                            <td onclick="window.location='{{ route('files.index', ['path' => $folder['path']]) }}'" style="cursor: pointer;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-folder-fill" style="color: #ffc107; font-size: 1.4rem;"></i>
                                    <strong>{{ $folder['name'] }}</strong>
                                </div>
                            </td>
                            <td class="text-muted">Dossier</td>
                            <td class="text-muted">-</td>
                            <td style="text-align: center;">
                                <form action="{{ route('files.destroy') }}" method="POST" onsubmit="return confirm('Supprimer ce dossier et son contenu ?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="path" value="{{ $folder['path'] }}">
                                    <button type="submit" title="Supprimer le dossier" style="background: none; border: none; padding: 0; cursor: pointer; color: #dc3545; font-size: 1.2rem;">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    {{-- FICHIERS --}}
                    @foreach($files as $file)
                        @php
                            $color = "#6c757d"; $icon = "bi-file-earmark-fill";
                            if(in_array($file['ext'], ['jpg','png','jpeg'])) { $color = "#0dcaf0"; $icon = "bi-file-earmark-image-fill"; }
                            elseif($file['ext'] == 'pdf') { $color = "#dc3545"; $icon = "bi-file-earmark-pdf-fill"; }
                            elseif(in_array($file['ext'], ['xls','xlsx'])) { $color = "#198754"; $icon = "bi-file-earmark-excel-fill"; }
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi {{ $icon }}" style="color: {{ $color }}; font-size: 1.4rem;"></i>
                                    {{ $file['name'] }}
                                </div>
                            </td>
                            <td>{{ $file['size'] }}</td>
                            <td>{{ $file['date'] }}</td>
                            <td style="text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 15px; align-items: center;">
                                    <a href="{{ route('files.download', ['path' => $file['path']]) }}" title="Télécharger" style="text-decoration: none; font-size: 1.2rem;">
                                        <i class="bi bi-download text-success"></i>
                                    </a>
                                    <form action="{{ route('files.destroy') }}" method="POST" onsubmit="return confirm('Supprimer ce fichier ?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="path" value="{{ $file['path'] }}">
                                        <button type="submit" title="Supprimer" style="background: none; border: none; padding: 0; cursor: pointer; color: #dc3545; font-size: 1.2rem;">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection