@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Ajouter un nouvel employé à l'AD</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('annuaire.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email professionnel</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('annuaire.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">Créer le compte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection