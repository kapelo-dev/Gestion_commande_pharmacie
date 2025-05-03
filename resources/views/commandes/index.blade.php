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

    <!-- Bouton pour actualiser la page -->
    <div class="d-flex justify-content-end mb-3" style="margin-top: 10px;">
        <button class="btn btn-primary" onclick="location.reload();">
            <i class="mdi mdi-refresh"></i> Actualiser
        </button>
    </div>

    <div class="col-lg-12 stretch-card mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">
                        <i class="mdi mdi-clock-outline text-warning"></i>
                        Commandes en attente de validation
                    </h4>
                    <span class="badge badge-warning">{{ count($commandesEnCours) }} commande(s)</span>
                </div>
                <!-- Zone de recherche pour les commandes en attente -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchPending" class="form-control" placeholder="Rechercher une commande" aria-label="Rechercher" aria-describedby="basic-addon1" style="border: 2px solid #6a11cb;">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon1" style="background-color: #6a11cb; color: white;">
                                <i class="mdi mdi-magnify"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-bordered table-striped table-fixed-header mb-0" id="pendingOrdersTable">
                        <thead>
                            <tr>
                                <th>Code Commande</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commandesEnCours as $commande)
                                <tr>
                                    <td>{{ $commande['code_commande'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($commande['date_commande'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-warning">{{ $commande['status_commande'] }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm btn-action" data-toggle="modal" data-target="#detailsCommandeModal{{ $commande['id'] }}">
                                            <i class="mdi mdi-eye"></i> Détails
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucune commande en attente</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Commandes validées -->
    <div class="col-lg-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">
                        <i class="mdi mdi-check-circle text-success"></i>
                        Commandes validées
                    </h4>
                    <span class="badge badge-success">{{ count($commandesValidees) }} commande(s)</span>
                </div>
                <!-- Zone de recherche pour les commandes validées -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchValidated" class="form-control" placeholder="Rechercher une commande" aria-label="Rechercher" aria-describedby="basic-addon1" style="border: 2px solid #6a11cb;">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon1" style="background-color: #6a11cb; color: white;">
                                <i class="mdi mdi-magnify"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="height: 320px;">
                    <table class="table table-bordered table-striped" id="validatedOrdersTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Code Commande</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commandesValidees as $commande)
                                <tr>
                                    <td>{{ $commande['code_commande'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($commande['date_commande'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge badge-success">{{ $commande['status_commande'] }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailsCommandeModal{{ $commande['id'] }}">
                                            <i class="mdi mdi-eye"></i> Détails
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Aucune commande validée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals Détails Commande -->
    @foreach (array_merge($commandesEnCours, $commandesValidees) as $commande)
        <div class="modal fade" id="detailsCommandeModal{{ $commande['id'] }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable modal-lg mt-0">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-clipboard-text"></i>
                            Détails de la commande #{{ $commande['code_commande'] }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Informations générales -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="mdi mdi-calendar"></i> Date de commande
                                        </h6>
                                        <p class="card-text">
                                            {{ \Carbon\Carbon::parse($commande['date_commande'])->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            <i class="mdi mdi-account"></i> Client
                                        </h6>
                                        <p class="card-text">{{ $commande['utilisateur'] ?? 'Non spécifié' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statut de la commande -->
                        <div class="alert alert-{{ $commande['status_commande'] == 'validée' ? 'success' : 'warning' }} mb-4">
                            <i class="mdi mdi-information-outline"></i>
                            Status actuel : <strong>{{ $commande['status_commande'] }}</strong>
                        </div>

                        <!-- Liste des produits -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="mdi mdi-cart"></i> Produits commandés
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Sur ordonnance</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($commande['produits'] as $produit)
                                                <tr>
                                                    <td>{{ $produit['nom'] ?? $produit['produit_id'] }}</td>
                                                    <td>{{ $produit['sur_ordonnance'] ?? false ? 'oui' : 'non' }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">
                                                            {{ $produit['quantite'] }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        {{ number_format($produit['prix_unitaire'] ?? 0, 0, ',', ' ') }} FCFA
                                                    </td>
                                                    <td class="text-right">
                                                        {{ number_format(($produit['prix_unitaire'] ?? 0) * $produit['quantite'], 0, ',', ' ') }} FCFA
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="4" class="text-right">
                                                    <strong>Total</strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong>{{ number_format($commande['montant_total'] ?? 0, 0, ',', ' ') }} FCFA</strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Champs Montant Perçu et Montant Rendu -->
                        @if($commande['status_commande'] == 'validée')
                        <div class="form-group mt-3">
                        <label for="montant-percu">Montant Perçu</label>
                        <input type="number" class="form-control montant-percu" id="montant-percu-{{ $commande['id'] }}" data-commande-id="{{ $commande['id'] }}" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="montant-rendu">Montant Rendu</label>
                        <input type="number" class="form-control" id="montant-rendu-{{ $commande['id'] }}" name="montant_rendu" readonly>
                    </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($commande['status_commande'] == 'en_cours')
                            <form action="{{ route('commandes.updateStatus', ['commandeId' => $commande['id'], 'status' => 'validée']) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="mdi mdi-check"></i> Valider
                                </button>
                            </form>
                            <form action="{{ route('commandes.updateStatus', ['commandeId' => $commande['id'], 'status' => 'rejetée']) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="mdi mdi-close"></i> Rejeter
                                </button>
                            </form>
                        @endif
                        @if($commande['status_commande'] == 'validée')
                            <form action="{{ route('commandes.updateStatus', ['commandeId' => $commande['id'], 'status' => 'récupérée']) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" id="montant-total-{{ $commande['id'] }}" value="{{ $commande['montant_total'] ?? 0 }}">
                                <input type="number" name="montant_percu" id="hidden-montant-percu-{{ $commande['id'] }}" hidden>
                                <input type="number" name="montant_rendu" id="hidden-montant-rendu-{{ $commande['id'] }}" hidden>
                                <button type="submit" class="btn btn-warning btn-sm" id="marquer-recuperee-{{ $commande['id'] }}" disabled>
                                    <i class="mdi mdi-package-variant"></i> Marquer comme récupérée
                                </button>
                            </form>
                        @endif
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="mdi mdi-close-circle"></i> Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }

    .table-responsive::-webkit-scrollbar,
    .modal-body::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track,
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb,
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover,
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .badge {
        padding: 0.5em 1em;
    }

    .modal-body {
        max-height: 80vh;
        overflow-y: auto;
    }

    .btn {
        margin: 0 2px;
    }

    .card {
        margin-bottom: 1rem;
    }
</style>
@endpush

<script>
    function calculerMontantRendu(commandeId) {
        const montantTotal = parseFloat($('#montant-total-' + commandeId).val().replace(',', '')) || 0;
        const montantPercu = parseFloat($('#montant-percu-' + commandeId).val()) || 0;
        const montantRendu = montantPercu - montantTotal;

        // Mettre à jour le champ montant rendu
        $('#hidden-montant-rendu-' + commandeId).val(montantRendu >= 0 ? montantRendu : 0);
        $('#montant-rendu-' + commandeId).val(montantRendu >= 0 ? montantRendu : 0);

        // Mettre à jour le champ montant perçu caché
        $('#hidden-montant-percu-' + commandeId).val(montantPercu);

        verifierMontantPercu(commandeId);
    }

    function verifierMontantPercu(commandeId) {
        const montantTotal = parseFloat($('#montant-total-' + commandeId).val().replace(',', '')) || 0;
        const montantPercu = parseFloat($('#montant-percu-' + commandeId).val()) || 0;
        const validerButton = $('#marquer-recuperee-' + commandeId);

        if (montantPercu >= montantTotal) {
            validerButton.prop('disabled', false);
        } else {
            validerButton.prop('disabled', true);
        }
    }

    $(document).on('input', '.montant-percu', function() {
        const commandeId = $(this).data('commande-id');
        calculerMontantRendu(commandeId);
    });

    // Masquer les messages de succès et d'erreur après 3 secondes
    function masquerMessages(selector, delay = 3000) {
        const messages = document.querySelectorAll(selector);
        messages.forEach(function(message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, delay);
        });
    }

    // Filtrage des commandes en attente
    $('#searchPending').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#pendingOrdersTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Filtrage des commandes validées
    $('#searchValidated').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#validatedOrdersTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Masquer les messages de succès et d'erreur après 3 secondes
    masquerMessages('#successMessage');
    masquerMessages('#errorMessage');
</script>

@endsection
