@extends('layouts.app')

@section('title', 'Gestionnaire de fichiers')

@section('content')
{{-- On charge les icônes Bootstrap pour les couleurs et les dossiers jaunes --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container full-width"> 
    <main>
        <div class="card">
            {{-- En-tête identique à vos autres pages avec bouton retour ergonomique --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">📁 Serveur de fichiers : {{ $userService }}</h2>
                
                @if($showBackBtn)
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" class="btn" style="text-decoration: none; background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px 20px; border-radius: 50px; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-arrow-left-circle-fill text-primary" style="font-size: 1.2rem;"></i> Retour
                    </a>
                @endif
            </div>

            <p style="color: #666; margin-bottom: 20px;">Emplacement : <strong>{{ $currentFolder }}</strong></p>

            {{-- Zone d'envoi simplifiée (Upload) --}}
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 15px;">
                    @csrf
                    <input type="hidden" name="path" value="{{ $safeRelativePath ?: $userService }}">
                    <input type="file" name="file" required>
                    <button type="submit" class="logout-button" style="background-color: #28a745; border: none; padding: 8px 15px; color: white; border-radius: 4px; cursor: pointer;">📤 Envoyer</button>
                </form>
            </div>

            {{-- Messages de succès ou d'erreur --}}
            @if(session('success')) <div style="color: #0f5132; background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px;">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div style="color: #842029; background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;">❌ {{ session('error') }}</div> @endif

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
                    {{-- 1. DOSSIERS : Toute la ligne est cliquable (même sur les '-') --}}
                    @foreach($folders as $folder)
                        @php
                            $newPath = $safeRelativePath ? $safeRelativePath . '/' . basename($folder) : basename($folder);
                        @endphp
                        <tr onclick="window.location='{{ route('files.index', ['path' => $newPath]) }}'" style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f1f1f1'" onmouseout="this.style.backgroundColor=''">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    {{-- Icône de dossier forcée en JAUNE --}}
                                    <i class="bi bi-folder-fill" style="color: #ffc107; font-size: 1.4rem;"></i>
                                    <strong>{{ basename($folder) }}</strong>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach

                    {{-- 2. FICHIERS : Avec couleurs par type --}}
                    @foreach($files as $file)
                        @php
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            
                            // Logique de couleurs pour les fichiers
                            $color = "#6c757d"; // Gris par défaut
                            $icon = "bi-file-earmark-fill";

                            if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $color = "#0dcaf0"; $icon = "bi-file-earmark-image-fill"; } // Bleu ciel
                            elseif($ext == 'pdf') { $color = "#dc3545"; $icon = "bi-file-earmark-pdf-fill"; } // Rouge
                            elseif(in_array($ext, ['xls', 'xlsx', 'csv'])) { $color = "#198754"; $icon = "bi-file-earmark-excel-fill"; } // Vert
                            elseif(in_array($ext, ['doc', 'docx'])) { $color = "#0d6efd"; $icon = "bi-file-earmark-word-fill"; } // Bleu royal
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
                                    <a href="{{ route('files.download', ['path' => $file['path']]) }}" title="Télécharger" style="text-decoration: none; font-size: 1.2rem;">⬇️</a>
                                    
                                    <form action="{{ route('files.destroy') }}" method="POST" onsubmit="return confirm('Supprimer ce fichier ?');" style="margin: 0;">
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
                        <tr><td colspan="4" style="text-align: center; padding: 30px; color: #666;">Ce dossier est vide.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection