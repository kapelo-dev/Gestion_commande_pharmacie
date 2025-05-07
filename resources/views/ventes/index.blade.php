@extends('layouts.app')

@section('content')
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
</style>

<div class="container">
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

    <div class="col-lg-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Liste des Ventes</h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addVenteModal">
                        <i class="mdi mdi-cart-plus"></i> Nouvelle Vente
                    </button>
                </div>

                <!-- Table des ventes -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th>#</th>
                                <th>Date</th>
                                <th>Montant Total</th>
                                <th>Montant Perçu</th>
                                <th>Montant Rendu</th>
                                <th>Nombre d'éléments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                    @forelse ($ventes as $index => $vente)
                        <tr class="text-center">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $vente['date'] }}</td>
                            <td>{{ number_format($vente['montant_total'], 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($vente['montant_percu'], 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($vente['montant_rendu'], 0, ',', ' ') }} FCFA</td>
                            <td>{{ count($vente['produits_vendus']) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsVenteModal{{ $vente['id'] }}">
                                    <i class="mdi mdi-eye"></i> Détails
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="imprimerDetails('{{ $vente['id'] }}')">
                                    <i class="mdi mdi-printer"></i> Imprimer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Aucune vente trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals Détails Vente -->
@foreach ($ventes as $vente)
    <div class="modal fade" id="detailsVenteModal{{ $vente['id'] }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la vente</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <img src="images/Symbole-Pharmacie.jpg" class="mr-2" style="max-height: 65px; width: auto; display: block;" alt="Pharmacie Logo" />
                    </a>
                    <p><strong>Pharmacie  {{ $pharmacyName }} </strong> </p>
                    <p>{{ $pharmacyAdresse }}  </p>
                    <p>Tél(228) {{ $pharmacyTel }}</p>
                    <p> {{ $vente['date'] }}</p>
                    <p><strong>TICKET N*: {{ $vente['id'] }}</strong> </p>
                    <h6>Produits vendus:</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Designation</th>
                                <th>Prix Unitaire</th>
                                <th>Quantité</th>
                                <th>Prix Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vente['produits_vendus'] as $produit)
                                <tr>
                                    <td>{{ $produit['nom_produit'] }}</td>
                                    <td>{{ number_format($produit['prix_unitaire'], 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $produit['quantite'] }}</td>
                                    <td>{{ number_format($produit['prix_total'], 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <hr>
                    <p><strong>Montant Total:</strong> {{ number_format($vente['montant_total'], 0, ',', ' ') }} FCFA</p>
                    <p><strong>Montant Perçu:</strong> {{ number_format($vente['montant_percu'], 0, ',', ' ') }} FCFA</p>
                    <p><strong>Montant Rendu:</strong> {{ number_format($vente['montant_rendu'], 0, ',', ' ') }} FCFA</p>
                    <p><strong>Nombre d'éléments:</strong> {{ count($vente['produits_vendus']) }}</p>
                    <p><strong>Vendeur :</strong> {{ $vente['vendeur'] }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addVenteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Vente</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('ventes.store') }}" method="POST" id="formVente">
                @csrf
                <div class="modal-body">
                    <div id="produits-container">
                    </div>
                    <button type="button" class="btn btn-info" id="add-produit">Ajouter un produit</button>
                    <div class="mt-3">
                        <h5>Total: <span id="montant-total">0 FCFA</span></h5>
                    </div>
                    <div class="form-group mt-3">
                        <label for="montant-percu">Montant Perçu</label>
                        <input type="number" class="form-control" id="montant-percu" name="montant_percu" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="montant-rendu">Montant Rendu</label>
                        <input type="number" class="form-control" id="montant-rendu" name="montant_rendu" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="valider-vente">Valider la vente</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function imprimerDetails(venteId) {
        // Cloner le contenu du modal
        const modalContent = document.querySelector(`#detailsVenteModal${venteId} .modal-body`).cloneNode(true);

        // Créer une nouvelle fenêtre pour l'impression
        const printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write(`
            <html>
                <head>
                    <title>Détails de la vente</title>
                    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
                    <style>
                        @media print {
                            body {
                                font-size: 12px;
                            }
                            .table {
                                width: 100%;
                                margin-bottom: 1rem;
                                background-color: transparent;
                            }
                            .table th,
                            .table td {
                                padding: 0.75rem;
                                vertical-align: top;
                                border-top: 1px solid #dee2e6;
                            }
                        }
                    </style>
                </head>
                <body onload="window.print(); window.onafterprint = function() { window.close(); }">
                    <div class="container">
                        ${modalContent.innerHTML}
                    </div>
                </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>

<script>
    const produits = @json($produits);
    let produitIndex = 0;

    function ajouterProduit() {
        const produitTemplate = `
            <div class="produit-item mb-3 border p-3 rounded">
                <div class="row">
                    <div class="col-md-4 produit-autocomplete-container"> Nom du produit
                        <input type="text" class="form-control produit-input" placeholder="Rechercher un produit..." required autofocus>
                        <input type="hidden" name="produits[${produitIndex}][id]" class="produit-id">
                        <input type="hidden" name="produits[${produitIndex}][nom]" class="produit-nom">
                        <div class="autocomplete-list"></div>
                    </div>
                    <div class="col-md-2"> Prix unitaire
                        <input type="text" class="form-control prix-unitaire" name="produits[${produitIndex}][prix_unitaire]" readonly>
                    </div>
                    <div class="col-md-2"> Quantité
                        <input type="number" class="form-control quantite" name="produits[${produitIndex}][quantite]" min="1" value="1" required>
                    </div>
                    <div class="col-md-2"> Total
                        <input type="text" class="form-control prix-total" name="produits[${produitIndex}][prix_total]" readonly>
                    </div>
                    <div class="col-md-2"> Sur ordonnance
                        <input type="text" class="form-control sur-ordonnance" name="produits[${produitIndex}][sur_ordonnance]" readonly>
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-produit">X</button>
                    </div>
                </div>
            </div>
        `;
        $('#produits-container').append(produitTemplate);
        produitIndex++;
        $('#produits-container .produit-input').last().focus();
    }

    function calculerTotal() {
        let totalMontant = 0;
        $('.produit-item').each(function() {
            const quantite = $(this).find('.quantite').val();
            const prixUnitaire = $(this).find('.prix-unitaire').val();
            const total = quantite * prixUnitaire;
            $(this).find('.prix-total').val(total);
            totalMontant += total;
        });
        $('#montant-total').text(`${totalMontant} FCFA`);
        calculerMontantRendu();
        verifierMontantPercu();
    }

    function calculerMontantRendu() {
        const montantTotal = parseFloat($('#montant-total').text().replace(' FCFA', '')) || 0;
        const montantPercu = parseFloat($('#montant-percu').val()) || 0;
        const montantRendu = montantPercu - montantTotal;
        $('#montant-rendu').val(montantRendu >= 0 ? montantRendu : 0);
        verifierMontantPercu();
    }

    function verifierMontantPercu() {
        const montantTotal = parseFloat($('#montant-total').text().replace(' FCFA', '')) || 0;
        const montantPercu = parseFloat($('#montant-percu').val()) || 0;
        const validerButton = $('#valider-vente');

        if (montantPercu >= montantTotal) {
            validerButton.prop('disabled', false);
        } else {
            validerButton.prop('disabled', true);
        }
    }

    $(document).on('click', '#add-produit', function() {
        ajouterProduit();
    });

    $(document).on('click', '.remove-produit', function() {
        $(this).closest('.produit-item').remove();
        calculerTotal();
    });

    $(document).on('input', '.produit-input', function() {
        const input = $(this);
        const searchTerm = input.val().trim();
        const listContainer = input.siblings('.autocomplete-list');

        if (searchTerm.length === 0) {
            listContainer.empty().hide();
            return;
        }

        const filteredProduits = produits.filter(produit => produit.nom.toLowerCase().includes(searchTerm.toLowerCase()));
        listContainer.empty().show();

        filteredProduits.forEach(produit => {
            const stockClass = produit.quantite_en_stock <= 0 ? 'text-danger' : '';
            const stockInfo = produit.quantite_en_stock <= 0 ? ' (Rupture de stock)' : ` (Stock: ${produit.quantite_en_stock})`;
            const item = $(`<div class="autocomplete-item ${stockClass}" 
                data-id="${produit.id}" 
                data-prix="${produit.prix_unitaire}" 
                data-stock="${produit.quantite_en_stock}"
                data-sur-ordonnance="${produit.sur_ordonnance}">
                ${produit.nom}${stockInfo}
            </div>`);
            listContainer.append(item);
        });
    });

    $(document).on('click', '.autocomplete-item', function() {
        const item = $(this);
        const container = item.closest('.produit-autocomplete-container');
        const input = container.find('.produit-input');
        const idInput = container.find('.produit-id');
        const nomInput = container.find('.produit-nom');
        const prixInput = item.closest('.produit-item').find('.prix-unitaire');
        const quantiteInput = item.closest('.produit-item').find('.quantite');
        const surOrdonnanceInput = item.closest('.produit-item').find('.sur-ordonnance');

        const stock = parseInt(item.data('stock'));
        
        // Mettre à jour les champs
        input.val(item.text().replace(/ \(.*\)$/, '')); // Enlever l'info de stock du nom
        idInput.val(item.data('id'));
        nomInput.val(item.text().replace(/ \(.*\)$/, '')); // Enlever l'info de stock du nom
        prixInput.val(item.data('prix'));
        surOrdonnanceInput.val(item.data('sur-ordonnance') ? 'Oui' : 'Non');

        // Gérer le champ quantité
        if (stock <= 0) {
            quantiteInput.prop('disabled', true);
            quantiteInput.val(0);
            Swal.fire({
                icon: 'warning',
                title: 'Rupture de stock',
                text: 'Ce produit n\'est plus disponible en stock.',
                confirmButtonText: 'OK'
            });
        } else {
            quantiteInput.prop('disabled', false);
            quantiteInput.attr('max', stock);
            quantiteInput.val(1);
        }

        item.closest('.autocomplete-list').hide();
        calculerTotal();
    });

    // Ajouter la vérification de la quantité maximale
    $(document).on('input', '.quantite', function() {
        const input = $(this);
        const max = parseInt(input.attr('max'));
        const value = parseInt(input.val());

        if (value > max) {
            input.val(max);
            Swal.fire({
                icon: 'warning',
                title: 'Stock insuffisant',
                text: `La quantité maximale disponible est de ${max} unités.`,
                confirmButtonText: 'OK'
            });
        }
        calculerTotal();
    });

    $(document).on('input', '#montant-percu', function() {
        calculerMontantRendu();
    });

    // Masquer les messages de succès après 3 secondes
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        setTimeout(function() {
            successMessage.style.display = 'none';
        }, 3000);
    }

    // Masquer les messages d'erreur après 3 secondes
    const errorMessages = document.querySelectorAll('#errorMessage');
    errorMessages.forEach(function(errorMessage) {
        setTimeout(function() {
            errorMessage.style.display = 'none';
        }, 3000);
    });

    // Gérer la soumission du formulaire
    $('#formVente').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const url = form.attr('action');
        const formData = form.serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Fermer le modal
                    $('#addVenteModal').modal('hide');
                    
                    // Afficher le message de succès
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Vente enregistrée avec succès',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Recharger la page pour afficher la nouvelle vente
                        window.location.reload();
                    });

                    // Vérifier s'il y a des alertes d'expiration
                    if (response.data.alertes_expiration && response.data.alertes_expiration.length > 0) {
                        let message = 'Les produits suivants sont proches de leur date d\'expiration :\n\n';
                        response.data.alertes_expiration.forEach(alerte => {
                            message += `- ${alerte.produit} (expire le ${alerte.date_expiration})\n`;
                        });

                        Swal.fire({
                            icon: 'warning',
                            title: 'Attention',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                }
            },
            error: function(xhr) {
                let message = 'Une erreur est survenue';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: message
                });
            }
        });
    });
</script>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: @json(session('success')),
                confirmButtonText: 'OK',
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
@endif

@endsection
