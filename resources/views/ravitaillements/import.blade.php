@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Importation de Ravitaillement</h2>
                </div>
                <div class="card-body">
                    <!-- Téléchargement du modèle -->
                    <div class="mb-4">
                        <h4>1. Télécharger le modèle Excel</h4>
                        <a href="{{ url('ravitaillements/template/download') }}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Télécharger le modèle
                        </a>
                    </div>

                    <!-- Formulaire d'import -->
                    <div class="mb-4">
                        <h4>2. Importer votre fichier</h4>
                        <form id="importForm" action="{{ url('ravitaillements/import/preview') }}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="input-group">
                                <input type="file" class="form-control" name="fichier_excel" accept=".xlsx,.xls" required>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Prévisualiser
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Zone de prévisualisation -->
                    <div id="previewZone" class="mt-4" style="display: none;">
                        <h4>3. Prévisualisation des données</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Produit</th>
                                        <th>Produit</th>
                                        <th>Lot</th>
                                        <th>Date d'expiration</th>
                                        <th>Quantité</th>
                                        <th>Prix d'achat</th>
                                        <th>Prix unitaire</th>
                                        <th>Variation prix</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="previewData">
                                </tbody>
                            </table>
                        </div>
                        <div id="errorZone" class="alert alert-danger" style="display: none;"></div>
                        <button id="processImport" class="btn btn-primary" style="display: none;">
                            Confirmer l'importation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#importForm').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#previewZone').show();
                $('#previewData').empty();
                $('#errorZone').hide();
                
                if (response.preview && response.preview.length > 0) {
                    response.preview.forEach(function(item) {
                        const rowClass = item.produit_trouve ? 'table-success' : 'table-warning';
                        const statusBadgeClass = item.produit_trouve ? 'badge bg-success' : 'badge bg-warning text-dark';
                        const statusText = item.produit_trouve ? 'Trouvé' : 'Non trouvé';
                        const variationText = item.produit_trouve ? `${item.variation_prix}%` : '-';
                        
                        let row = `<tr class="${rowClass}">
                            <td>${item.id_produit}</td>
                            <td>${item.nom_produit}</td>
                            <td>${item.lot_numero}</td>
                            <td>${item.date_expiration}</td>
                            <td>${item.quantite_disponible}</td>
                            <td>${item.prix_achat} FCFA</td>
                            <td>${item.prix_unitaire} FCFA</td>
                            <td>${variationText}</td>
                            <td><span class="${statusBadgeClass}">${statusText}</span></td>
                        </tr>`;
                        $('#previewData').append(row);
                    });
                    $('#processImport').show();
                }
                
                if (response.errors && Object.keys(response.errors).length > 0) {
                    let errorHtml = '<ul class="mb-0">';
                    Object.entries(response.errors).forEach(([key, errors]) => {
                        errors.forEach(error => {
                            errorHtml += `<li>${error}</li>`;
                        });
                    });
                    errorHtml += '</ul>';
                    $('#errorZone').html(errorHtml).show();
                }
            },
            error: function(xhr) {
                let message = 'Une erreur est survenue';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                }
                $('#errorZone').html(message).show();
            }
        });
    });

    $('#processImport').on('click', function() {
        $.ajax({
            url: "{{ route('ravitaillements.import.process') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Importation réussie !');
                    window.location.href = "{{ route('ravitaillements.index') }}";
                }
            },
            error: function(xhr) {
                let message = 'Une erreur est survenue lors de l\'importation';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                }
                $('#errorZone').html(message).show();
            }
        });
    });
});
</script>
@endpush
@endsection 