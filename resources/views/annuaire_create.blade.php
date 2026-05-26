@extends('layouts.app')

@section('title', 'Ajouter un employé')

@section('content')
<div class="container full-width">
    <main>
        <div class="card">
            <h2>➕ Ajouter un collaborateur à l'Active Directory</h2>
            <p>Veuillez remplir les informations du nouvel employé.</p>

            <form action="{{ route('annuaire.store') }}" method="POST" class="styled-form">
                @csrf
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="prenom"><strong>Prénom *</strong></label><br>
                    <input type="text" id="prenom" name="prenom" required class="form-control" style="width: 100%; padding: 8px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nom"><strong>Nom *</strong></label><br>
                    <input type="text" id="nom" name="nom" required class="form-control" style="width: 100%; padding: 8px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email"><strong>Email</strong></label><br>
                    <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 8px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="telephone"><strong>Téléphone</strong></label><br>
                    <input type="text" id="telephone" name="telephone" class="form-control" style="width: 100%; padding: 8px;">
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">✔️ Créer le compte AD</button>
                    <a href="{{ route('annuaire.index') }}" class="btn btn-danger" style="margin-left: 10px;">❌ Annuler</a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection