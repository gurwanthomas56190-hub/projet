@extends('layouts.app')

@section('title', 'Annuaire')

@section('content')
<div class="container full-width"> 
    <main>
        <div class="card">
            <h2>📞 Annuaire des collaborateurs</h2>
            <p>Retrouvez facilement les coordonnées de vos collègues.</p>
            
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nom & Prénom</th>
                        <th>Service</th>
                        <th>Poste interne</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Alice Dupont</strong></td>
                        <td>Ressources Humaines</td>
                        <td>205</td>
                        <td>alice.d@silvadec.com</td>
                    </tr>
                    <tr>
                        <td><strong>Bob Martin</strong></td>
                        <td>Support Informatique</td>
                        <td>404</td>
                        <td>bob.m@silvadec.com</td>
                    </tr>
                    <tr>
                        <td><strong>Charlie Leblanc</strong></td>
                        <td>Comptabilité</td>
                        <td>301</td>
                        <td>charlie.l@silvadec.com</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection