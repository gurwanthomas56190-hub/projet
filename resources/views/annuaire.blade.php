@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Annuaire du personnel Silvadec</h1>

    {{-- Bouton d'ajout visible uniquement par les admins --}}
    @can('gerer-annuaire')
        <a href="{{ route('annuaire.create') }}" class="btn btn-primary mb-3">Ajouter un employé</a>
    @endcan

    <table class="table">
        <thead>
            <tr>
                <th>Nom Complet</th>
                <th>Email</th>
                <th>Téléphone</th>
                @can('gerer-annuaire')
                    <th>Actions</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @foreach($employes as $employe)
                <tr>
                    {{-- LdapRecord retourne souvent les attributs sous forme de tableaux --}}
                    <td>{{ $employe->getFirstAttribute('cn') }}</td>
                    <td>{{ $employe->getFirstAttribute('mail') }}</td>
                    <td>{{ $employe->getFirstAttribute('telephonenumber') }}</td>
                    
                    {{-- Actions d'édition/suppression visibles uniquement par les admins --}}
                    @can('gerer-annuaire')
                        <td>
                            <a href="{{ route('annuaire.edit', $employe->getFirstAttribute('samaccountname')) }}" class="btn btn-sm btn-warning">Modifier</a>
                            
                            <form action="{{ route('annuaire.destroy', $employe->getFirstAttribute('samaccountname')) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection