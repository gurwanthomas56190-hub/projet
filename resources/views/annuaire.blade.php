@extends('layouts.app')

@section('title', 'Annuaire')

@section('content')
<div class="container full-width"> 
    <main>
        
        <div class="card">
            <h2>📞 Annuaire des collaborateurs</h2>
            <p>Retrouvez facilement les coordonnées de vos collègues (données synchronisées avec l'Active Directory).</p>
            
            {{-- Messages de succès --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Messages d'erreur --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <span class="alert-icon">❌</span>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Bouton Ajouter, visible uniquement par les admins --}}
            @can('gerer-annuaire')
                <div style="margin-bottom: 15px;">
                    <a href="{{ route('annuaire.create') }}" class="btn btn-primary">
                        <span>＋</span> Ajouter un employé
                    </a>
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
                        
                        {{-- En-tête des actions pour les admins --}}
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

                            {{-- Boutons de modification et suppression pour les admins --}}
                            @can('gerer-annuaire')
                                <td class="actions-cell">
                                    <a href="{{ route('annuaire.edit', $user->getFirstAttribute('samaccountname')) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        ✏️ Modifier
                                    </a>
                                    
                                    <form action="{{ route('annuaire.destroy', $user->getFirstAttribute('samaccountname')) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer {{ $user->getFirstAttribute('cn') }} de l\'Active Directory ?\n\nCette action est irréversible !')">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            @endcan                      
                        </tr>
                        
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center;">Aucun utilisateur trouvé dans l'Active Directory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection