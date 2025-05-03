@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Liste des Pharmaciens</h3>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createPharmacienModal">
                Ajouter un Pharmacien
            </button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Identifiant</th>
                        <th>Téléphone</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pharmaciens as $pharmacien)
                        <tr>
                            <td>{{ $pharmacien['nom'] }}</td>
                            <td>{{ $pharmacien['prenom'] }}</td>
                            <td>{{ $pharmacien['identifiant'] }}</td>
                            <td>{{ $pharmacien['telephone'] }}</td>
                            <td>{{ $pharmacien['role'] }}</td>
                            <td>
                               <!--  <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editPharmacienModal{{ $pharmacien['id'] }}">
                                    Éditer
                                </button> -->
                                <form action="{{ route('pharmaciens.destroy', $pharmacien['id']) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce pharmacien ?')">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <!-- Modal pour l'édition du pharmacien -->
                        <div class="modal fade" id="editPharmacienModal{{ $pharmacien['id'] }}" tabindex="-1" role="dialog" aria-labelledby="editPharmacienModalLabel{{ $pharmacien['id'] }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editPharmacienModalLabel{{ $pharmacien['id'] }}">Éditer Pharmacien</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('pharmaciens.update', $pharmacien['id']) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group">
                                                <label for="nom">Nom</label>
                                                <input type="text" class="form-control" id="nom" name="nom" value="{{ $pharmacien['nom'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="prenom">Prénom</label>
                                                <input type="text" class="form-control" id="prenom" name="prenom" value="{{ $pharmacien['prenom'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="identifiant">Identifiant</label>
                                                <input type="text" class="form-control" id="identifiant" name="identifiant" value="{{ $pharmacien['identifiant'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="telephone">Téléphone</label>
                                                <input type="text" class="form-control" id="telephone" name="telephone" value="{{ $pharmacien['telephone'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="role">Rôle</label>
                                                <div>
                                                    <label>
                                                        <input type="radio" name="role" value="superviseur" {{ $pharmacien['role'] == 'superviseur' ? 'checked' : '' }} required>
                                                        Superviseur
                                                    </label>
                                                </div>
                                                <div>
                                                    <label>
                                                        <input type="radio" name="role" value="caissier" {{ $pharmacien['role'] == 'caissier' ? 'checked' : '' }} required>
                                                        Caissier
                                                    </label>
                                                </div>
                                            </div>

                                           <!--  <div class="form-group">
                                                <label for="mot_de_passe">Mot de passe</label>
                                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                                            </div> -->
                                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucun pharmacien trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour la création d'un pharmacien -->
<div class="modal fade" id="createPharmacienModal" tabindex="-1" role="dialog" aria-labelledby="createPharmacienModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPharmacienModalLabel">Ajouter un Pharmacien</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('pharmaciens.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="identifiant">Identifiant</label>
                        <input type="text" class="form-control" id="identifiant" name="identifiant" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone" required>
                    </div>
                    <div class="form-group">
    <label for="role">Rôle</label>
    <div>
        <label>
            <input type="radio" name="role" value="superviseur" {{ $pharmacien['role'] == 'superviseur' ? 'checked' : '' }} required>
            Superviseur
        </label>
    </div>
    <div>
        <label>
            <input type="radio" name="role" value="caissier" {{ $pharmacien['role'] == 'caissier' ? 'checked' : '' }} required>
            Caissier
        </label>
    </div>
</div>

                    <div class="form-group">
                        <label for="mot_de_passe">Mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
