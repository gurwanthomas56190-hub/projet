@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Annuaire du personnel Silvadec</h1>
        
        @can('gerer-annuaire')
            <a href="{{ route('annuaire.create') }}" class="btn btn-primary">
                + Ajouter un employé
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        @can('gerer-annuaire')
                            <th class="text-center">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($employes as $employe)
                        <tr>
                            <td class="align-middle">{{ $employe->getFirstAttribute('cn') }}</td>
                            <td class="align-middle">{{ $employe->getFirstAttribute('mail') }}</td>
                            <td class="align-middle">{{ $employe->getFirstAttribute('telephonenumber') }}</td>
                            
                            @can('gerer-annuaire')
                                <td class="text-center align-middle">
                                    <a href="{{ route('annuaire.edit', $employe->getFirstAttribute('samaccountname')) }}" class="btn btn-sm btn-warning">
                                        Modifier
                                    </a>
                                    
                                    <form action="{{ route('annuaire.destroy', $employe->getFirstAttribute('samaccountname')) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer {{ $employe->getFirstAttribute('cn') }} de l\'Active Directory ?')">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection