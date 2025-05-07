@extends('layouts.app')

@section('content')
<!-- Ajout des CDN nécessaires -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .produit-autocomplete-container {
        position: relative;
    }

    .autocomplete-list {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        border: 1px solid #ddd;
        background-color: #fff;
        z-index: 10;
        max-height: 200px;
        overflow-y: auto;
    }

    .autocomplete-item {
        padding: 10px;
        cursor: pointer;
    }

    .autocomplete-item:hover {
        background-color: #f0f0f0;
    }

    .autocomplete-item.active {
        background-color: #e0e0e0;
    }

    .preview-table {
        margin-top: 1rem;
        max-height: 300px;
        overflow-y: auto;
    }

    .table {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
    }

    .table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .btn-group .btn {
        padding: 0.375rem 0.75rem;
        margin: 0 2px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        border: none;
    }

    .btn-group .btn i {
        font-size: 1rem;
        line-height: 1;
        margin: 0;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white !important;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white !important;
    }

    .btn-info:hover {
        background-color: #138496;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .table td {
        vertical-align: middle;
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des Ravitaillements</h2>
        <div>
            <button type="button" class="btn btn-success me-2" onclick="openImportModal()">
                <i class="fas fa-file-excel"></i> Importer Excel
            </button>
            <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRavitaillementModal">
                <i class="fas fa-plus"></i> Nouveau Ravitaillement
            </button> -->
        </div>
    </div>

    @if(session('success'))
        <div id="successMessage" class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div id="errorMessage" class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <h1>Liste des Ravitaillements</h1>

    <!-- Liste des ravitaillements -->
    <div class="table-responsive mb-5">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%">#</th>
                    <th style="width: 20%">Date</th>
                    <th style="width: 20%">Fichier</th>
                    <th class="text-center" style="width: 15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ravitaillements as $index => $ravitaillement)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $ravitaillement['date'] }}</td>
                        <td>{{ $ravitaillement['fichier'] }}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button type="button" 
                                    class="btn btn-info btn-sm"
                                    onclick="previewRavitaillement('{{ $ravitaillement['id'] }}')"
                                    title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="confirmDelete('{{ $ravitaillement['id'] }}')"
                                    title="Supprimer">
                                    <i class="fas fa-trash"></i>
                            </button>
                            </div>
                            <form id="delete-form-{{ $ravitaillement['id'] }}"
                                action="{{ route('ravitaillements.destroy', $ravitaillement['id']) }}"
                                method="POST"
                                style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal pour l'importation Excel -->
    <div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Importer un fichier Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm" action="{{ route('ravitaillements.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="fichier_excel" class="form-label">Fichier Excel</label>
                            <input type="file" class="form-control" id="fichier_excel" name="fichier_excel" accept=".xlsx,.xls" required>
                            <div class="mt-2">
                                <a href="{{ route('ravitaillements.template.download') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download"></i> Télécharger le modèle Excel
                                </a>
                            </div>
                            <small class="form-text text-muted mt-2">
                                Le fichier doit contenir les colonnes suivantes :<br>
                                - ID Produit<br>
                                - Numéro de lot<br>
                                - Date d'expiration<br>
                                - Quantité disponible<br>
                                - Prix d'achat<br>
                                - Prix unitaire
                            </small>
                        </div>
                    </form>
                    <div id="previewContainer" class="preview-table d-none">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Produit</th>
                                    <th>Produit</th>
                                    <th>N° Lot</th>
                                    <th>Date d'expiration</th>
                                    <th>Quantité</th>
                                    <th>Prix d'achat</th>
                                    <th>Prix unitaire</th>
                                    <th>Variation prix</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="validateImport" style="display: none;">Valider l'importation</button>
                    <button type="button" class="btn btn-primary" id="submitImport">Prévisualiser</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour Ajouter -->
    <div class="modal fade" id="addRavitaillementModal" tabindex="-1" aria-labelledby="addRavitaillementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRavitaillementModalLabel">Ajouter un Ravitaillement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form action="{{ route('ravitaillements.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="fournisseur">Fournisseur</label>
                            <input type="text" class="form-control" id="fournisseur" name="fournisseur" required>
                        </div>

                        <div class="form-group">
                            <label for="lot_numero">N° Lot</label>
                            <input type="text" class="form-control" id="lot_numero" name="lot_numero" required>
                        </div>

                        <div class="form-group produit-autocomplete-container">
                            <label for="produit_ravitaille">Produit</label>
                            <input type="text" class="form-control produit-input" id="produit_ravitaille" name="produit_ravitaille" placeholder="Rechercher un produit..." required>
                            <input type="hidden" id="produit_id" name="produit_id">
                            <div class="autocomplete-list"></div>
                        </div>


                        <div class="form-group">
                            <label for="date_expiration">Date d'expiration</label>
                            <input type="date" class="form-control" id="date_expiration" name="date_expiration" required>
                        </div>

                        <div class="form-group">
                            <label for="quantite_disponible">Quantité </label>
                            <input type="number" class="form-control" id="quantite_disponible" name="quantite_disponible" required>
                        </div>

                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-success">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour la prévisualisation du ravitaillement -->
    <div class="modal fade" id="previewRavitaillementModal" tabindex="-1" aria-labelledby="previewRavitaillementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewRavitaillementModalLabel">Détails du Ravitaillement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="ravitaillementDetails">
                        <!-- Les détails seront chargés ici -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Fonction pour ouvrir la modal d'importation
        window.openImportModal = function() {
            var myModal = new bootstrap.Modal(document.getElementById('importExcelModal'));
            myModal.show();
        };

        // Fonction pour prévisualiser un ravitaillement
        function previewRavitaillement(ravitaillementId) {
            // Afficher un indicateur de chargement
            $('#ravitaillementDetails').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>');
            
            // Afficher la modal
            var previewModal = new bootstrap.Modal(document.getElementById('previewRavitaillementModal'));
            previewModal.show();

            // Charger les détails
            $.ajax({
                url: '/ravitaillements/' + ravitaillementId + '/preview',
                type: 'GET',
                success: function(response) {
                    $('#ravitaillementDetails').html(response);
                },
                error: function(xhr) {
                    $('#ravitaillementDetails').html('<div class="alert alert-danger">Erreur lors du chargement des détails.</div>');
                    console.error('Erreur:', xhr);
                }
            });
        }

        // Fonction pour confirmer et supprimer un ravitaillement
        function confirmDelete(ravitaillementId) {
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
                    // Soumettre le formulaire de suppression
                    document.getElementById('delete-form-' + ravitaillementId).submit();
                }
            });
        }

        // Gestion de la soumission du formulaire d'importation
        $('#submitImport').click(function() {
            var formData = new FormData($('#importForm')[0]);
            formData.append('_token', '{{ csrf_token() }}');
            
            $.ajax({
                url: $('#importForm').attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.error) {
                        Swal.fire('Erreur', response.error, 'error');
                        return;
                    }

                    // Vider le tableau existant
                    $('#previewTableBody').empty();
                    
                    // Remplir le tableau avec les données
                    if (response.preview && response.preview.length > 0) {
                        response.preview.forEach(function(row) {
                            var tr = $('<tr>');
                            tr.append($('<td>').text(row.id_produit));
                            tr.append($('<td>').text(row.nom_produit));
                            tr.append($('<td>').text(row.lot_numero));
                            tr.append($('<td>').text(row.date_expiration));
                            tr.append($('<td>').text(row.quantite_disponible));
                            tr.append($('<td>').text(row.prix_achat + ' FCFA'));
                            tr.append($('<td>').text(row.prix_unitaire + ' FCFA'));
                            tr.append($('<td>').text(row.variation_prix + '%'));
                            $('#previewTableBody').append(tr);
                        });
                        
                        $('#previewContainer').removeClass('d-none');
                        $('#submitImport').hide();
                        $('#validateImport').show();
                        
                        if (response.errors && Object.keys(response.errors).length > 0) {
                            let errorMessages = [];
                            for (let line in response.errors) {
                                errorMessages.push(`Ligne ${line}: ${response.errors[line].join(', ')}`);
                            }
                            Swal.fire({
                                icon: 'warning',
                                title: 'Attention',
                                html: 'Des erreurs ont été détectées:<br>' + errorMessages.join('<br>')
                            });
                            $('#validateImport').prop('disabled', true);
                        } else {
                            $('#validateImport').prop('disabled', false);
                        }
                    } else {
                        Swal.fire('Attention', 'Aucune donnée à prévisualiser', 'warning');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Une erreur est survenue lors de l\'importation.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    Swal.fire('Erreur', errorMessage, 'error');
                }
            });
        });

        // Gestion de la validation de l'importation
        $('#validateImport').click(function() {
            $.ajax({
                url: '{{ route("ravitaillements.import.process") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#importExcelModal').modal('hide');
                    Swal.fire('Succès', 'Importation réussie', 'success').then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Une erreur est survenue lors de l\'importation.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    Swal.fire('Erreur', errorMessage, 'error');
                }
            });
        });

        // Réinitialiser le modal lors de sa fermeture
        $('#importExcelModal').on('hidden.bs.modal', function () {
            $('#importForm')[0].reset();
            $('#previewTableBody').empty();
            $('#previewContainer').addClass('d-none');
            $('#submitImport').show();
            $('#validateImport').hide();
        });

        // Auto-hide des messages d'alerte
        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
            $('#errorMessage').fadeOut('slow');
        }, 3000);
    });

    function openImportModal() {
        window.location.href = "{{ route('ravitaillements.import') }}";
    }
</script>

@endsection
