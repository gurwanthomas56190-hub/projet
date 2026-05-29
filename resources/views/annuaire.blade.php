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

            {{-- Vérification si l'utilisateur connecté est Administrateur --}}
            @php
                $isAdmin = Auth::check() && strtolower(Auth::user()->getFirstAttribute('samaccountname')) === 'administrateur';
            @endphp

            {{-- Bouton Ajouter, visible uniquement par les admins --}}
            @if($isAdmin)
                <div style="margin-bottom: 15px;">
                    <a href="{{ route('annuaire.create') }}" class="btn btn-primary">
                        <span>＋</span> Ajouter un employé
                    </a>
                </div>
            @endif

            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Service</th> 
                        <th>Site</th>
                        
                        {{-- En-tête des actions pour les admins --}}
                        @if($isAdmin)
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            // Extraction du Service et du Site à partir du DN de l'Active Directory
                            $dn = $user->getDn();
                            $parts = explode(',', $dn);
                            $service = isset($parts[1]) ? str_replace('OU=', '', $parts[1]) : '-';
                            $site = isset($parts[2]) ? str_replace('OU=', '', $parts[2]) : '-';
                        @endphp
                        <tr>
                            <td><strong>{{ $user->getFirstAttribute('cn') ?? 'Nom inconnu' }}</strong></td>
                            
                            <td>{{ $user->getFirstAttribute('telephonenumber') ?? '-' }}</td>
                            
                            <td>
                                @if($user->getFirstAttribute('mail'))
                                    <a href="mailto:{{ $user->getFirstAttribute('mail') }}">
                                        {{ $user->getFirstAttribute('mail') }}
                                    </a> 
                                @else
                                    -
                                @endif  
                            </td>

                            <td>{{ $user->getFirstAttribute('streetaddress') ?? '-' }}</td>
                            <td>{{ $service }}</td>
                            <td>{{ $site }}</td>     

                            {{-- Boutons de modification et suppression pour les admins --}}
                            @if($isAdmin)
                                <td class="actions-cell">
                                    <a href="{{ route('annuaire.edit', $user->getFirstAttribute('samaccountname')) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        ✏️ Modifier
                                    </a>
                                    
                                    <form action="{{ route('annuaire.destroy', $user->getFirstAttribute('samaccountname')) }}" method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer {{ $user->getFirstAttribute('cn') }} de l\'Active Directory ?\n\nCette action est irréversible !')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </td>
                            @endif                      
                        </tr>
                        
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" style="text-align: center;">Aucun utilisateur trouvé dans l'Active Directory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection