@extends('layouts.app')

@section('title', 'Gestionnaire de fichiers')

@section('content')
{{-- Importation des icônes pour le style moderne --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container full-width"> 
    <main>
        {{-- Centrage du bloc identique à vos autres pages --}}
        <div class="card mx-auto" style="max-width: 1100px;">
            
            {{-- En-tête avec bouton retour --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">📁 Serveur de fichiers : {{ $userService }}</h2>
                
                @if($showBackBtn)
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" class="btn" style="text-decoration: none; background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px 20px; border-radius: 50px; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-arrow-left-circle-fill text-primary"></i> Retour
                    </a>
                @endif
            </div>

            <p style="color: #666; margin-bottom: 20px;">📂 Emplacement : <strong>{{ $currentFolder }}</strong></p>

            {{-- ZONE D'ACTION : Upload et Nouveau Dossier --}}
            <div style="display: flex; gap: 20px; background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #eee; flex-wrap: wrap;">
                
                {{-- Formulaire d'envoi de fichier --}}
                <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 300px; margin: 0;">
                    @csrf
                    <input type="hidden" name="path" value="{{ $safeRelativePath }}">
                    <label style="font-weight: bold; font-size: 0.9rem;">Ajouter un fichier:</label>
                    <input type="file" name="file" required style="font-size: 0.8rem;">
                    <button type="submit" class="logout-button" style="background-color: #198754; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">📤 Envoyer</button>
                </form>

                <div style="border-left: 1px solid #ddd; margin: 0 10px;"></div>

                {{-- Formulaire de création de dossier --}}
                <form action="{{ route('files.mkdir') }}" method="POST" style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 300px; margin: 0;">
                    @csrf
                    <input type="hidden" name="path" value="{{ $safeRelativePath }}">
                    <label style="font-weight: bold; font-size: 0.9rem;">Nouveau dossier:</label>
                    <input type="text" name="folder_name" placeholder="Nom du dossier" required style="padding: 6px; border-radius: 4px; border: 1px solid #ccc; flex: 1;">
                    <button type="submit" style="background-color: #0d6efd; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">➕ Créer</button>
                </form>
            </div>

            {{-- Notifications --}}
            @if(session('success')) <div style="color: #0f5132; background-color: #d1e7dd; padding: 12px; border-radius: 5px; margin-bottom: 15px;">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div style="color: #842029; background-color: #f8d7da; padding: 12px; border-radius: 5px; margin-bottom: 15px;">❌ {{ session('error') }}</div> @endif

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Taille</th>
                        <th>Modifié le</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. DOSSIERS : Toute la ligne est cliquable --}}
                    @foreach($folders as $folder)
                        @php
                            $newPath = $safeRelativePath . '/' . $folder['name'];
                        @endphp
                        <tr onclick="window.location='{{ route('files.index', ['path' => $newPath]) }}'" 
                            style="cursor: pointer;" 
                            onmouseover="this.style.backgroundColor='#f1f1f1'" 
                            onmouseout="this.style.backgroundColor=''">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    {{-- DOSSIER EN JAUNE --}}
                                    <i class="bi bi-folder-fill" style="color: #ffc107; font-size: 1.4rem;"></i>
                                    <strong>{{ $folder['name'] }}</strong>
                                </div>
                            </td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    @endforeach

                    {{-- 2. FICHIERS : Colorés par type --}}
                    @foreach($files as $file)
                        @php
                            $color = "#6c757d"; // Gris par défaut
                            $icon = "bi-file-earmark-fill";

                            if(in_array($file['ext'], ['jpg', 'jpeg', 'png', 'gif', 'svg'])) { $color = "#0dcaf0"; $icon = "bi-file-earmark-image-fill"; }
                            elseif($file['ext'] == 'pdf') { $color = "#dc3545"; $icon = "bi-file-earmark-pdf-fill"; }
                            elseif(in_array($file['ext'], ['xls', 'xlsx', 'csv'])) { $color = "#198754"; $icon = "bi-file-earmark-excel-fill"; }
                            elseif(in_array($file['ext'], ['doc', 'docx'])) { $color = "#0d6efd"; $icon = "bi-file-earmark-word-fill"; }
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
                                <div style="display: flex; justify-content: center; gap: 20px; align-items: center;">
                                    <a href="{{ route('files.download', ['path' => $file['path']]) }}" title="Télécharger" style="text-decoration: none; font-size: 1.2rem;">⬇️</a>
                                    
                                    <form action="{{ route('files.destroy') }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce fichier ?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="path" value="{{ $file['path'] }}">
                                        <button type="submit" title="Supprimer" style="background: none; border: none; padding: 0; cursor: pointer; font-size: 1.2rem;">❌</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if(empty($folders) && empty($files))
                        <tr><td colspan="4" style="text-align: center; padding: 40px; color: #999;">Ce dossier est vide.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection