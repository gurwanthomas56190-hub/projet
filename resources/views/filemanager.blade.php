@extends('layouts.app')

@section('title', 'Gestionnaire de fichiers')

@section('content')
{{-- Icônes Bootstrap pour les couleurs --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container full-width"> 
    <main>
        <div class="card">
            {{-- En-tête avec bouton retour propre --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>📁 Serveur de fichiers : {{ $userService }}</h2>
                
                @if($showBackBtn)
                    <a href="{{ route('files.index', ['path' => $parentPath]) }}" class="btn" style="text-decoration: none; background-color: #f8f9fa; border: 1px solid #ddd; padding: 10px 20px; border-radius: 50px; color: #333; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-arrow-left-circle-fill text-primary"></i> Retour
                    </a>
                @endif
            </div>

            {{-- Zone d'envoi --}}
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
                    @csrf
                    <input type="hidden" name="path" value="{{ $safeRelativePath ?: $userService }}">
                    <input type="file" name="file" required>
                    <button type="submit" class="logout-button" style="background-color: #28a745; border: none; padding: 8px 15px;">📤 Envoyer</button>
                </form>
            </div>

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
                    {{-- 1. DOSSIERS (Ligne entière cliquable et icône JAUNE) --}}
                    @foreach($folders as $folder)
                        @php
                            $newPath = $safeRelativePath ? $safeRelativePath . '/' . basename($folder) : basename($folder);
                        @endphp
                        <tr class="position-relative" style="cursor: pointer;">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="bi bi-folder-fill" style="color: #ffc107; font-size: 1.4rem;"></i>
                                    {{-- Le lien s'étend sur toute la ligne 'tr' grâce à stretched-link --}}
                                    <a href="{{ route('files.index', ['path' => $newPath]) }}" class="stretched-link" style="text-decoration: none; color: #333; font-weight: bold;">
                                        {{ basename($folder) }}
                                    </a>
                                </div>
                            </td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    @endforeach

                    {{-- 2. FICHIERS (Couleurs par type) --}}
                    @foreach($files as $file)
                        @php
                            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $color = "#6c757d"; 
                            $icon = "bi-file-earmark-fill";

                            if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $color = "#0dcaf0"; $icon = "bi-file-earmark-image-fill"; }
                            elseif($ext == 'pdf') { $color = "#dc3545"; $icon = "bi-file-earmark-pdf-fill"; }
                            elseif(in_array($ext, ['xls', 'xlsx', 'csv'])) { $color = "#198754"; $icon = "bi-file-earmark-excel-fill"; }
                            elseif(in_array($ext, ['doc', 'docx'])) { $color = "#0d6efd"; $icon = "bi-file-earmark-word-fill"; }
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
                                {{-- On garde les boutons d'actions au premier plan --}}
                                <div style="display: flex; justify-content: center; gap: 15px; position: relative; z-index: 5;">
                                    <a href="{{ route('files.download', ['path' => $file['path']]) }}" title="Télécharger" style="text-decoration: none; font-size: 1.2rem;">⬇️</a>
                                    
                                    <form action="{{ route('files.destroy') }}" method="POST" onsubmit="return confirm('Supprimer ce fichier ?');" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="path" value="{{ $file['path'] }}">
                                        <button type="submit" style="background:none; border:none; cursor:pointer; font-size: 1.2rem;">❌</button>
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