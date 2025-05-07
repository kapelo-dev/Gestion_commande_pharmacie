@extends('layouts.app')

@section('content')
<!-- Ajout des CDN nécessaires -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Liste des Pharmaciens</h3>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createPharmacienModal">
                <i class="fas fa-plus"></i> Ajouter un Pharmacien
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
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editPharmacienModal{{ $pharmacien['id'] }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $pharmacien['id'] }}')" title="Supprimer">
                                        <i class="fas fa-trash"></i> 
                                    </button>
                                </div>
                                <form id="delete-form-{{ $pharmacien['id'] }}" action="{{ route('pharmaciens.destroy', $pharmacien['id']) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>

                        <!-- Modal pour l'édition du pharmacien -->
                        <div class="modal fade" id="editPharmacienModal{{ $pharmacien['id'] }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Éditer Pharmacien</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="{{ route('pharmaciens.update', $pharmacien['id']) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="nom">Nom</label>
                                                        <input type="text" class="form-control form-control-sm" id="nom" name="nom" value="{{ $pharmacien['nom'] }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="prenom">Prénom</label>
                                                        <input type="text" class="form-control form-control-sm" id="prenom" name="prenom" value="{{ $pharmacien['prenom'] }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="identifiant">Identifiant</label>
                                                        <input type="text" class="form-control form-control-sm" id="identifiant" name="identifiant" value="{{ $pharmacien['identifiant'] }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="telephone">Téléphone</label>
                                                        <input type="text" class="form-control form-control-sm" id="telephone" name="telephone" value="{{ $pharmacien['telephone'] }}" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="d-block mb-2">Rôle</label>
                                                <div class="radio-container">
                                                    <div class="custom-radio">
                                                        <input type="radio" id="role_superviseur" name="role" value="superviseur" {{ $pharmacien['role'] == 'superviseur' ? 'checked' : '' }} required>
                                                        <label for="role_superviseur">Superviseur</label>
                                                    </div>
                                                    <div class="custom-radio">
                                                        <input type="radio" id="role_caissier" name="role" value="caissier" {{ $pharmacien['role'] == 'caissier' ? 'checked' : '' }} required>
                                                        <label for="role_caissier">Caissier</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer px-0 pb-0">
                                                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Aucun pharmacien trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour l'ajout d'un pharmacien -->
    <div class="modal fade" id="createPharmacienModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Pharmacien</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('pharmaciens.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nom">Nom</label>
                                    <input type="text" class="form-control form-control-sm" id="nom" name="nom" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prenom">Prénom</label>
                                    <input type="text" class="form-control form-control-sm" id="prenom" name="prenom" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="identifiant">Identifiant</label>
                                    <input type="text" class="form-control form-control-sm" id="identifiant" name="identifiant" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telephone">Téléphone</label>
                                    <input type="text" class="form-control form-control-sm" id="telephone" name="telephone" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="d-block mb-2">Rôle</label>
                            <div class="radio-container">
                                <div class="custom-radio">
                                    <input type="radio" id="new_role_superviseur" name="role" value="superviseur" required>
                                    <label for="new_role_superviseur">Superviseur</label>
                                </div>
                                <div class="custom-radio">
                                    <input type="radio" id="new_role_caissier" name="role" value="caissier" required>
                                    <label for="new_role_caissier">Caissier</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="mot_de_passe">Mot de passe</label>
                            <input type="password" class="form-control form-control-sm" id="mot_de_passe" name="mot_de_passe" required>
                        </div>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(pharmacienId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form-' + pharmacienId);
            form.submit();
        }
    });
}

// Animation des messages de succès
const successMessage = document.querySelector('.alert-success');
if (successMessage) {
    setTimeout(() => {
        successMessage.style.transition = 'opacity 0.5s ease';
        successMessage.style.opacity = '0';
        setTimeout(() => {
            successMessage.remove();
        }, 500);
    }, 3000);
}
</script>
@endpush

@endsection
