@extends('layouts.app')

@section('title', 'Annuaire')

@section('content')
<div class="container full-width"> 
    <main>
        <div class="card">
            <h2>📞 Annuaire des collaborateurs</h2>
            <p>Retrouvez facilement les coordonnées de vos collègues (données synchronisées avec l'Active Directory).</p>
            
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Téléphone</th>
                        <th>Service</th> 
                        <th>Site</th>
                        <th>Email</th>
                        <th>adresse</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td><strong>{{ $user->getFirstAttribute('cn') ?? 'Nom inconnu' }}</strong></td>
                            
                            
                            <td>{{ $user->getFirstAttribute('telephonenumber') ?? '-' }}</td>
                            <td>{{ $user->getSite() }}</td>
                            <td>{{ $user->getService() }}</td>
                            <td>
                                <a href="mailto:{{ $user->getFirstAttribute('mail') }}">
                                    {{ $user->getFirstAttribute('mail') }}
                                </a>   
                            </td>
                            <td>{{ $user->getFirstAttribute('streetaddress') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center;">Aucun utilisateur trouvé dans l'Active Directory.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection