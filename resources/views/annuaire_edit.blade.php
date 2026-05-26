@extends('layouts.app')

@section('title', 'Modifier un employé')

@section('content')
<div class="container full-width">
    <main>
        <div class="card">
            <h2>✏️ Modifier le collaborateur : {{ $employe->getFirstAttribute('cn') }}</h2>
            <p>Mettez à jour les informations du compte dans l'Active Directory.</p>

            {{-- Attention, on envoie le formulaire sur la route 'update' avec la méthode PUT --}}
            <form action="{{ route('annuaire.update', $employe->getFirstAttribute('samaccountname')) }}" method="POST" class="styled-form">
                @csrf
                @method('PUT')
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="prenom"><strong>Prénom *</strong></label><br>
                    <input type="text" id="prenom" name="prenom" value="{{ $employe->getFirstAttribute('givenname') }}" required class="form-control" style="width: 100%; padding: 8px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nom"><strong>Nom *</strong></label><br>
                    <input type="text" id="nom" name="nom" value="{{ $employe->getFirstAttribute('sn') }}" required class="form-control" style="width: 100%; padding: 8px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email"><strong>Email</strong></label><br>
                    <input type="email" id="email" name="email" value="{{ $employe->getFirstAttribute('mail') }}" class="form-control" style="width: 100%; padding: 8px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="telephone"><strong>Téléphone</strong></label><br>
                    <input type="text" id="telephone" name="telephone" value="{{ $employe->getFirstAttribute('telephonenumber') }}" class="form-control" style="width: 100%; padding: 8px;">
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-warning">💾 Enregistrer les modifications</button>
                    <a href="{{ route('annuaire.index') }}" class="btn btn-danger" style="margin-left: 10px;">❌ Annuler</a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection