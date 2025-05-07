@extends('layouts.app')

@section('content')
<style>
    /* Style personnalisé pour les checkboxes */
    .custom-checkbox {
        width: 20px;
        height: 20px;
        margin: 0;
        position: relative;
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: 2px solid #ddd;
        border-radius: 4px;
        background-color: white;
        transition: all 0.3s ease;
    }

    .custom-checkbox:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .custom-checkbox:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 14px;
    }

    .custom-checkbox-warning {
        width: 20px;
        height: 20px;
        margin: 0;
        position: relative;
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: 2px solid #ddd;
        border-radius: 4px;
        background-color: white;
        transition: all 0.3s ease;
    }

    .custom-checkbox-warning:checked {
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .custom-checkbox-warning:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: black;
        font-size: 14px;
    }

    /* Style pour le conteneur de checkbox */
    .checkbox-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    /* Animation pour la sélection */
    tr:has(.custom-checkbox:checked) {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    tr:has(.custom-checkbox-warning:checked) {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
</style>

<div class="content-wrapper">
    <div class="row">
        <!-- Produits Expirés -->
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card border border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Produits Expirés à Déstocker</h4>
                        @if(count($produitsExpires) > 0)
                            <button type="button" class="btn btn-danger" onclick="selectAllExpired()">
                                <i class="fas fa-check-square"></i> Sélectionner tous les produits expirés
                            </button>
                        @endif
                    </div>

                    <form action="{{ route('destockage.destocker') }}" method="POST" id="formDestockage">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 80px;">Sélectionner</th>
                                        <th>Nom du Produit</th>
                                        <th>Numéro de Lot</th>
                                        <th>Date d'Expiration</th>
                                        <th>Quantité</th>
                                        <th>Perte Estimée</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($produitsExpires as $produit)
                                        <tr>
                                            <td>
                                                <div class="checkbox-container">
                                                    <input type="checkbox" name="lots[]" value="{{ $produit['id'] }}|{{ $produit['lot_id'] }}" class="custom-checkbox expired-checkbox">
                                                </div>
                                            </td>
                                            <td>{{ $produit['nom'] }}</td>
                                            <td>{{ $produit['lot_numero'] }}</td>
                                            <td>{{ $produit['date_expiration_affichage'] }}</td>
                                            <td>{{ $produit['quantite_disponible'] }}</td>
                                            <td>{{ number_format($produit['quantite_disponible'] * $produit['prix_achat'], 2) }} F</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Aucun produit expiré à déstocker</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Produits à Expirer -->
                        <div class="mt-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title">Produits Proches de l'Expiration (3 mois)</h4>
                                @if(count($produitsAExpirer) > 0)
                                    <button type="button" class="btn btn-warning" onclick="selectAllExpiring()">
                                        <i class="fas fa-check-square"></i> Sélectionner tous les produits proches d'expiration
                                    </button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 80px;">Sélectionner</th>
                                            <th>Nom du Produit</th>
                                            <th>Numéro de Lot</th>
                                            <th>Date d'Expiration</th>
                                            <th>Quantité</th>
                                            <th>Perte Estimée</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($produitsAExpirer as $produit)
                                            <tr>
                                                <td>
                                                    <div class="checkbox-container">
                                                        <input type="checkbox" name="lots[]" value="{{ $produit['id'] }}|{{ $produit['lot_id'] }}" class="custom-checkbox-warning expiring-checkbox">
                                                    </div>
                                                </td>
                                                <td>{{ $produit['nom'] }}</td>
                                                <td>{{ $produit['lot_numero'] }}</td>
                                                <td>{{ $produit['date_expiration_affichage'] }}</td>
                                                <td>{{ $produit['quantite_disponible'] }}</td>
                                                <td>{{ number_format($produit['quantite_disponible'] * $produit['prix_achat'], 2) }} F</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Aucun produit proche de l'expiration</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if(count($produitsExpires) > 0 || count($produitsAExpirer) > 0)
                            <div class="mt-4">
                                <div class="form-group">
                                    <label for="raison">Raison du déstockage</label>
                                    <select name="raison" id="raison" class="form-control" required>
                                        <option value="expiration">Expiration</option>
                                        <option value="deterioration">Détérioration</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="commentaire">Commentaire (optionnel)</label>
                                    <textarea name="commentaire" id="commentaire" class="form-control" rows="3"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary" id="btnDestocker">
                                    <i class="fas fa-trash-alt"></i> Procéder au Déstockage
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectAllExpired() {
        document.querySelectorAll('.expired-checkbox').forEach(checkbox => {
            checkbox.checked = true;
            updateRowHighlight(checkbox);
        });
    }

    function selectAllExpiring() {
        document.querySelectorAll('.expiring-checkbox').forEach(checkbox => {
            checkbox.checked = true;
            updateRowHighlight(checkbox);
        });
    }

    // Remplacer le setTimeout par SweetAlert2 pour les messages de succès/erreur
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Succès!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Erreur!',
            text: "{{ session('error') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    // Ajouter la confirmation avec SweetAlert2 pour le déstockage
    document.getElementById('formDestockage').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Vérifier si au moins une case est cochée
        const checkedBoxes = document.querySelectorAll('input[name="lots[]"]:checked');
        if (checkedBoxes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention!',
                text: 'Veuillez sélectionner au moins un produit à déstocker.',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Confirmation de déstockage',
            text: 'Êtes-vous sûr de vouloir déstocker ces produits ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, déstocker',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Ajouter des écouteurs d'événements pour les checkboxes
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.custom-checkbox, .custom-checkbox-warning').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateRowHighlight(this);
            });
        });
    });

    function updateRowHighlight(checkbox) {
        const row = checkbox.closest('tr');
        if (checkbox.checked) {
            row.style.transition = 'background-color 0.3s ease';
        }
    }
</script>
@endpush

@endsection 