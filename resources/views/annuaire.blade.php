@extends('layouts.app')

@section('title', 'Annuaire')

@section('content')
<div class="container full-width"> 
    <main>
        
        <div class="card">
            <h2>📞 Annuaire des collaborateurs</h2>
            <p>Retrouvez facilement les coordonnées de vos collègues (données synchronisées avec l'Active Directory).</p>
            
            {{-- AJOUT : Message de succès après une action --}}
            @if(session('success'))
                <div style="color: green; font-weight: bold; margin-bottom: 15px;">
                    {{ session('success') }}
                </div>
            @endif

            {{-- AJOUT : Bouton Ajouter, visible uniquement par les admins --}}
            @can('gerer-annuaire')
                <div style="margin-bottom: 15px;">
                    <a href="{{ route('annuaire.create') }}" class="btn btn-primary">+ Ajouter un employé</a>
                </div>
            @endcan

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Service</th> 
                        <th>Site</th>
                        
                        {{-- AJOUT : En-tête des actions pour les admins --}}
                        @can('gerer-annuaire')
                            <th>Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td><strong>{{ $user->getFirstAttribute('cn') ?? 'Nom inconnu' }}</strong></td>
                            
                            <td>{{ $user->getFirstAttribute('telephonenumber') ?? '-' }}</td>
                            
                            <td>
                                <a href="mailto:{{ $user->getFirstAttribute('mail') }}">
                                    {{ $user->getFirstAttribute('mail') }}
                                </a>   
                            </td>

                            <td>{{ $user->getService() }}</td>
                            <td>{{ $user->getSite() }}</td>     

                            {{-- AJOUT : Boutons de modification et suppression pour les admins --}}
                            @can('gerer-annuaire')
                                <td>
                                    <a href="{{ route('annuaire.edit', $user->getFirstAttribute('samaccountname')) }}" style="margin-right: 10px;">✏️ Modifier</a>
                                    
                                    <form action="{{ route('annuaire.destroy', $user->getFirstAttribute('samaccountname')) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: red; cursor: pointer; text-decoration: underline;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet employé de l\'Active Directory ?')">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            @endcan                      
                        </tr>
                        
                    @empty
                        <tr>
                            {{-- Changement du colspan à 6 pour couvrir la colonne Action si elle est là --}}
                            <td colspan="6" style="text-align: center;">Aucun utilisateur trouvé dans l'Active Directory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection