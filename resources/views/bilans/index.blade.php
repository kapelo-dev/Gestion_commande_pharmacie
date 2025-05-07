@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bilan des Ventes et Achats</h3>
                </div>
                <div class="card-body">
                    <!-- Formulaire de sélection de période -->
                    <form id="bilanForm" class="mb-4">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_debut">Date de début</label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_fin">Date de fin</label>
                                    <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" id="generateBilanBtn" onclick="genererBilan()">
                                    <i class="mdi mdi-file-document" id="bilanBtnIcon"></i> 
                                    <span id="bilanBtnText">Générer le bilan</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Résultats du bilan -->
                    <div id="bilanResults" class="d-none">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total des Ventes</h5>
                                        <h3 class="mb-0" id="totalVentes">0 FCFA</h3>
                                        <small id="nombreVentes">0 ventes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Total des Achats</h5>
                                        <h3 class="mb-0" id="totalAchats">0 FCFA</h3>
                                        <small id="nombreAchats">0 achats</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title">Bénéfice Net</h5>
                                        <h3 class="mb-0" id="beneficeNet">0 FCFA</h3>
                                        <small id="periodeBilan">Période: -</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau des produits les plus vendus -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h4 class="card-title">Top 10 des Produits les Plus Vendus</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Produit</th>
                                                <th class="text-end">Quantité Vendue</th>
                                                <th class="text-end">Montant Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="produitsPopulairesBody">
                                            <!-- Les données seront insérées ici par JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message d'erreur -->
                    <div id="errorMessage" class="alert alert-danger d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fonction pour formater les montants
function formatMontant(montant) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(montant);
}

// Fonction pour formater les dates
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR');
}

// Fonction pour générer le bilan
function genererBilan() {
    const bilanResults = $('#bilanResults');
    const errorMessage = $('#errorMessage');
    const submitBtn = $('#generateBilanBtn');
    const btnIcon = $('#bilanBtnIcon');
    const btnText = $('#bilanBtnText');
    
    // Masquer les résultats précédents et les erreurs
    bilanResults.addClass('d-none');
    errorMessage.addClass('d-none');

    // Récupérer les dates
    const dateDebut = $('#date_debut').val();
    const dateFin = $('#date_fin').val();

    // Validation des dates
    if (!dateDebut || !dateFin) {
        errorMessage.text('Veuillez sélectionner les dates de début et de fin.').removeClass('d-none');
        return;
    }

    if (new Date(dateFin) < new Date(dateDebut)) {
        errorMessage.text('La date de fin doit être après la date de début.').removeClass('d-none');
        return;
    }

    // Afficher l'indicateur de chargement
    submitBtn.prop('disabled', true);
    btnIcon.removeClass('mdi-file-document').addClass('mdi-loading mdi-spin');
    btnText.text('Génération en cours...');

    // Appel AJAX
    $.ajax({
        url: '/bilans/generer',
        method: 'POST',
        data: {
            _token: $('input[name="_token"]').val(),
            date_debut: dateDebut,
            date_fin: dateFin
        },
        success: function(response) {
            if (response.success) {
                // Mettre à jour les totaux
                $('#totalVentes').text(formatMontant(response.data.total_ventes));
                $('#nombreVentes').text(`${response.data.nombre_ventes} ventes`);
                $('#totalAchats').text(formatMontant(response.data.total_achats));
                $('#nombreAchats').text(`${response.data.nombre_achats} achats`);
                $('#beneficeNet').text(formatMontant(response.data.benefice_net));
                $('#periodeBilan').text(`Période: ${formatDate(response.data.periode.debut)} - ${formatDate(response.data.periode.fin)}`);

                // Mettre à jour le tableau des produits populaires
                const tbody = $('#produitsPopulairesBody');
                tbody.empty();
                
                if (response.data.produits_populaires && response.data.produits_populaires.length > 0) {
                    response.data.produits_populaires.forEach(function(produit, index) {
                        tbody.append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${produit.nom}</td>
                                <td class="text-end">${produit.quantite}</td>
                                <td class="text-end">${formatMontant(produit.montant_total)}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="4" class="text-center">Aucune vente sur cette période</td></tr>');
                }

                // Afficher les résultats
                bilanResults.removeClass('d-none');
            } else {
                throw new Error(response.message || 'Une erreur est survenue');
            }
        },
        error: function(xhr) {
            console.error('Erreur:', xhr);
            let message = 'Une erreur est survenue lors de la génération du bilan.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            errorMessage.text(message).removeClass('d-none');
        },
        complete: function() {
            // Restaurer le bouton à son état initial
            submitBtn.prop('disabled', false);
            btnIcon.removeClass('mdi-loading mdi-spin').addClass('mdi-file-document');
            btnText.text('Générer le bilan');
        }
    });
}

// Initialiser les dates par défaut
$(document).ready(function() {
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    $('#date_debut').val(firstDayOfMonth.toISOString().split('T')[0]);
    $('#date_fin').val(today.toISOString().split('T')[0]);
});
</script>
@endpush 