@extends('layouts.app')

@section('title', 'Ajouter un employé')

@section('content')
<div class="container full-width">
    <main>
        <div class="card">
            <h2>➕ Ajouter un collaborateur (Standard Silvadec)</h2>
            <p>Création d'un compte avec classement automatique dans la bonne OU.</p>

            @if($errors->any())
                <div class="alert alert-danger">
                    <span class="alert-icon">❌</span>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('annuaire.store') }}" method="POST" class="styled-form">
                @csrf
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nom"><strong>Nom *</strong></label><br>
                    <input type="text" id="nom" name="nom" required class="form-control" style="width: 100%; padding: 8px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="prenom"><strong>Prénom *</strong></label><br>
                    <input type="text" id="prenom" name="prenom" required class="form-control" style="width: 100%; padding: 8px;">
                </div>

                {{-- NOUVEAU : Choix de la Ville et du Service --}}
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="ville"><strong>Ville (Site) *</strong></label><br>
                    <select id="ville" name="ville" required class="form-control" style="width: 100%; padding: 8px;">
                        <option value="Allemagne">Allemagne</option>
                        <option value="Arzal">Arzal</option>
                        <option value="Eleven">Eleven</option>
                        <option value="Roc-Saint-Andre">Roc-Saint-André</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="service"><strong>Service *</strong></label><br>
                    <select id="service" name="service" required class="form-control" style="width: 100%; padding: 8px;">
                        <option value="Administration">Administration</option>
                        <option value="Informatique">Informatique</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Production">Production</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="adresse"><strong>Adresse postale</strong></label><br>
                    <input type="text" id="adresse" name="adresse" placeholder="123 Rue Exemple, Ville, CP" class="form-control" style="width: 100%; padding: 8px;">
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