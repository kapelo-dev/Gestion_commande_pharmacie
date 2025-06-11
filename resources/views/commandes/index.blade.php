@extends('layouts.app')

@section('content')
<div class="content-wrapper">
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

        /* Styles pour le scanner QR */
        #reader {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .qr-scanner-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            padding: 20px;
        }
        
        .qr-scanner-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            margin: 40px auto;
        }

    .rejet-section {
        display: inline-block;
    }
    
    .rejet-form {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-top: 10px;
    }
</style>

    <!-- Scanner QR Modal -->
    <div id="qrScannerModal" class="qr-scanner-overlay">
        <div class="qr-scanner-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Scanner un code QR</h5>
                <button type="button" class="close" onclick="closeQrScanner()">
                    <span>&times;</span>
                </button>
            </div>
            <div id="reader"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
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
            <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" onclick="location.reload();">
            <i class="mdi mdi-refresh"></i> Actualiser
        </button>
    </div>

            <!-- Commandes en attente -->
            <div class="card mb-4">
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
                                <button class="btn" onclick="startQrScanner('searchPending')" style="background-color: #6a11cb; color: white; border: none;">
                                    <i class="mdi mdi-qrcode-scan"></i>
                                </button>
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

    <!-- Commandes validées -->
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
                                <button class="btn" onclick="startQrScanner('searchValidated')" style="background-color: #6a11cb; color: white; border: none;">
                                    <i class="mdi mdi-qrcode-scan"></i>
                                </button>
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
                        <div class="alert alert-{{ $commande['status_commande'] == 'validée' ? 'success' : ($commande['status_commande'] == 'rejetée' ? 'danger' : 'warning') }} mb-4">
                            <i class="mdi mdi-information-outline"></i>
                            Status actuel : <strong>{{ $commande['status_commande'] }}</strong>
                            @if($commande['status_commande'] == 'rejetée' && isset($commande['raison_rejet']))
                                <hr>
                                <p class="mb-0">
                                    <strong>Raison du rejet :</strong><br>
                                    {{ $commande['raison_rejet'] }}
                                </p>
                                <small class="text-muted">
                                    Rejetée le {{ \Carbon\Carbon::parse($commande['date_rejet'])->format('d/m/Y à H:i') }}
                                </small>
                            @endif
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

                            <!-- Section de rejet -->
                            <div class="rejet-section">
                                <button type="button" class="btn btn-danger show-rejet-form">
                                    <i class="mdi mdi-close"></i> Rejeter
                                </button>

                                <div class="rejet-form" style="display: none;">
                                    <form action="{{ route('commandes.updateStatus', ['commandeId' => $commande['id'], 'status' => 'rejetée']) }}" method="POST">
                                        @csrf
                                        <div class="form-group mt-3">
                                            <label for="raison_rejet">Raison du rejet <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="raison_rejet" rows="3" required></textarea>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-secondary cancel-rejet">
                                                <i class="mdi mdi-close"></i> Annuler
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="mdi mdi-check"></i> Confirmer le rejet
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
</div>
@endsection

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

@push('scripts')
<!-- HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    // Fonction de filtrage des commandes
    function filterTable(inputValue, tableId) {
        const value = inputValue.toLowerCase();
        let foundRow = null;
        
        $(`#${tableId} tbody tr`).each(function() {
            const row = $(this);
            const text = row.text().toLowerCase();
            const isVisible = text.indexOf(value) > -1;
            row.toggle(isVisible);
            
            // Si c'est une ligne correspondante, on la sauvegarde
            if (isVisible) {
                foundRow = row;
            }
        });
        
        // Si on a trouvé une ligne et que c'est après un scan
        return foundRow;
    }

    // Script pour le calcul des montants
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

    $(document).ready(function() {
        // Gestion du formulaire de rejet
        $(document).on('click', '.show-rejet-form', function() {
            console.log('Bouton rejeter cliqué');
            const rejetSection = $(this).closest('.rejet-section');
            $(this).hide();
            rejetSection.find('.rejet-form').show();
        });

        $(document).on('click', '.cancel-rejet', function() {
            console.log('Bouton annuler cliqué');
            const rejetSection = $(this).closest('.rejet-section');
            rejetSection.find('.rejet-form').hide();
            rejetSection.find('.show-rejet-form').show();
            rejetSection.find('form')[0].reset();
        });

        // Validation du formulaire de rejet
        $(document).on('submit', 'form', function(e) {
            const raisonRejet = $(this).find('textarea[name="raison_rejet"]');
            if (raisonRejet.length > 0 && !raisonRejet.val().trim()) {
                e.preventDefault();
                alert('Veuillez saisir une raison pour le rejet.');
                return false;
            }
        });

        // Filtrage des commandes en attente
        $('#searchPending').on('input', function() {
            filterTable($(this).val(), 'pendingOrdersTable');
        });

        // Filtrage des commandes validées
        $('#searchValidated').on('input', function() {
            filterTable($(this).val(), 'validatedOrdersTable');
        });

        // Gestion des montants perçus
        $(document).on('input', '.montant-percu', function() {
            const commandeId = $(this).data('commande-id');
            calculerMontantRendu(commandeId);
        });

        // Masquer les messages
        masquerMessages('#successMessage');
        masquerMessages('#errorMessage');
    });

    // Code Scanner QR
    let html5QrCode = null;
    let currentSearchInput = null;

    function startQrScanner(inputId) {
        if (html5QrCode === null) {
            html5QrCode = new Html5Qrcode("reader");
        }
        
        currentSearchInput = document.getElementById(inputId);
        document.getElementById('qrScannerModal').style.display = 'block';
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanError
        ).catch((err) => {
            console.error("Erreur lors du démarrage du scanner:", err);
            alert("Erreur lors du démarrage du scanner. Veuillez vérifier que vous avez autorisé l'accès à la caméra.");
        });
    }

    function closeQrScanner() {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().then(() => {
                document.getElementById('qrScannerModal').style.display = 'none';
            }).catch((err) => {
                console.error(err);
            });
        } else {
            document.getElementById('qrScannerModal').style.display = 'none';
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        console.log("Code scanné avec succès:", decodedText);
        if (currentSearchInput) {
            // Mettre à jour la valeur de l'input
            currentSearchInput.value = decodedText;
            
            // Déclencher le filtrage en fonction de l'ID de l'input
            const tableId = currentSearchInput.id === 'searchPending' ? 'pendingOrdersTable' : 'validatedOrdersTable';
            const foundRow = filterTable(decodedText, tableId);
            
            // Si une ligne est trouvée, ouvrir son modal
            if (foundRow) {
                // Trouver le bouton "Détails" dans la ligne et le cliquer
                const detailsButton = foundRow.find('button[data-toggle="modal"]');
                if (detailsButton.length > 0) {
                    detailsButton.click();
                }
            }
            
            // Fermer le scanner
            closeQrScanner();
        }
    }

    function onScanError(error) {
        console.warn("Erreur de scan:", error);
    }

    // Nettoyage lors de la fermeture de la page
    window.addEventListener('beforeunload', () => {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop().catch(console.error);
        }
    });
</script>
@endpush
