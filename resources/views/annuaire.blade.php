@extends('layouts.app')

@section('content')
<div class="container">
    <h2>📞 Annuaire des collaborateurs</h2>
    <p>Retrouvez facilement les coordonnées de vos collègues (données synchronisées avec l'Active Directory).</p>

    {{-- Affichage des messages de succès ou d'erreur --}}
    @if(session('success'))
        <div class="alert alert-success" style="color: green; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Vérification si l'utilisateur connecté est Administrateur --}}
    @php
        $isAdmin = Auth::check() && strtolower(Auth::user()->getFirstAttribute('samaccountname')) === 'administrateur';
    @endphp

    {{-- BOUTON AJOUTER : Visible UNIQUEMENT par l'administrateur --}}
    @if($isAdmin)
        <div style="margin-bottom: 20px;">
            <a href="{{ route('annuaire.create') }}" class="btn btn-primary" style="background-color: #0056b3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                ➕ Ajouter un collaborateur
            </a>
        </div>
    @endif

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>Nom & Prénom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Service</th>
                <th>Site</th>
                
                {{-- COLONNE ACTIONS : Visible UNIQUEMENT par l'administrateur --}}
                @if($isAdmin)
                    <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                @php
                    // Extraction du Service et du Site à partir du DN (Distinguished Name) de l'Active Directory
                    // Le chemin ressemble à : CN=TEST TEST,OU=Administration,OU=Allemagne,OU=Silvadec,DC=silvadec,DC=local
                    $dn = $user->getDn();
                    $parts = explode(',', $dn);
                    $service = isset($parts[1]) ? str_replace('OU=', '', $parts[1]) : '-';
                    $site = isset($parts[2]) ? str_replace('OU=', '', $parts[2]) : '-';
                @endphp
                <tr>
                    <td>{{ $user->getFirstAttribute('cn') }}</td>
                    <td>{{ $user->getFirstAttribute('telephonenumber') }}</td>
                    <td>
                        @if($user->getFirstAttribute('mail'))
                            <a href="mailto:{{ $user->getFirstAttribute('mail') }}">{{ $user->getFirstAttribute('mail') }}</a>
                        @endif
                    </td>
                    <td>{{ $user->getFirstAttribute('streetaddress') }}</td>
                    <td>{{ $service }}</td>
                    <td>{{ $site }}</td>
                    
                    {{-- BOUTONS MODIFIER ET SUPPRIMER : Visibles UNIQUEMENT par l'administrateur --}}
                    @if($isAdmin)
                        <td>
                            <a href="{{ route('annuaire.edit', $user->getFirstAttribute('samaccountname')) }}" 
                               style="background-color: #ffc107; color: black; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 0.9em;">
                               ✏️ Modifier
                            </a>
                            
                            <form action="{{ route('annuaire.destroy', $user->getFirstAttribute('samaccountname')) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer {{ $user->getFirstAttribute('cn') }} de l\'Active Directory ? Cette action est irréversible.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 6px 10px; cursor: pointer; border-radius: 3px; font-size: 0.9em; margin-left: 5px;">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection