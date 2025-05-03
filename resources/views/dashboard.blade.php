@extends('layouts.app')

@section('content') 

<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="row">
                <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                  
                    <h6 class="font-weight-normal mb-0">Pharmacie :  {{ $pharmacyName }} </h6>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="justify-content-end d-flex">
                        <div class="date-display">
                            <button class="btn btn-sm btn-light bg-white" type="button">
                                <i class="mdi mdi-calendar"></i> Aujourd'hui ({{ \Carbon\Carbon::now()->format('d M Y') }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Carte du Montant Total des Ventes d'Aujourd'hui -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="card-title">Montant Total des Ventes d'Aujourd'hui</h4>
                    <h3 class="fs-30 font-weight-medium">{{ number_format($montantTotalAujourdHui, 2, ',', ' ') }}  FCFA</h3>
                </div>
            </div>
        </div>

        <!-- Carte des Statistiques de la Pharmacie -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card border border-primary-custom">
                <div class="card-body">
                    <h4 class="card-title"> Commandes</h4>
                    <div class="d-flex flex-wrap mb-4">
                    <div class="mr-5 mt-3">
                            <p class="text-muted">Commandes en attente de récupération</p>
                            <h3 class="text-primary fs-30 font-weight-medium">{{ $commandesEnAttenteRecuperation }}</h3>
                        </div>
                        <div class="mr-5 mt-3">
                            <p class="text-muted">Commandes en attente de validation</p>
                            <h3 class="text-primary fs-30 font-weight-medium">{{ $commandesEnAttenteValidation }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Carte des Produits Proches de la Rupture -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card border border-primary-custom" style="height: 100%;">
                <div class="card-body">
                    <h4 class="card-title">Produits proches de la rupture <span class="badge badge-warning">({{ $countProduitsSeuil }}) produit(s)</span></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom du Produit</th>
                                    <th>Quantité en Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($produitsSeuil) > 0)
                                    @foreach($produitsSeuil as $produit)
                                        <tr>
                                            <td class="text-dark">{{ $produit['nom'] }}</td>
                                            <td class="text-dark">{{ $produit['quantite_en_stock'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2" class="text-center text-dark">Aucun produit proche de la rupture</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte des Produits en Rupture de Stock -->
        <div class="col-md-6 grid-margin stretch-card" style="height: 100%;">
            <div class="card border border-primary-custom">
                <div class="card-body">
                    <h4 class="card-title">Produits en rupture de stock <span class="badge badge-danger">({{ $countMedicamentsEnRupture }}) produit(s)</span></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom du Produit</th>
                                    <th>Quantité en Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($medicamentsEnRupture) > 0)
                                    @foreach($medicamentsEnRupture as $produit)
                                        <tr>
                                            <td class="text-dark">{{ $produit['nom'] }}</td>
                                            <td class="text-dark">{{ $produit['quantite_en_stock'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="2" class="text-center text-dark">Aucun produit en rupture de stock</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <div class="row">
        <!-- Carte des Produits Expirés -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card border border-danger">
                <div class="card-body">
                    <h4 class="card-title">Produits Expirés <span class="badge badge-danger">({{ $countProduitsExpirés }}) produit(s)</span></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom du Produit</th>
                                    <th>Date d'Expiration</th>
                                    <th>Quantité Disponible</th>
                                    <th>Numéro de Lot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($produitsExpirés) > 0)
                                    @foreach($produitsExpirés as $produit)
                                        <tr>
                                            <td class="text-dark">{{ $produit['nom'] }}</td>
                                            <td class="text-dark">{{ \Carbon\Carbon::parse($produit['date_expiration'])->format('d M Y') }}</td>
                                            <td class="text-dark">{{ $produit['quantite_disponible'] }}</td>
                                            <td class="text-dark">{{ $produit['lot_numero'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-dark">Aucun produit expiré</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte des Produits Expirant dans 3 Mois -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card border border-warning">
                <div class="card-body">
                    <h4 class="card-title">Produits Expirant dans 3 Mois <span class="badge badge-warning">({{ $countProduitsExpirantDansTroisMois }}) produit(s)</span></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom du Produit</th>
                                    <th>Date d'Expiration</th>
                                    <th>Quantité Disponible</th>
                                    <th>Numéro de Lot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($produitsExpirantDansTroisMois) > 0)
                                    @foreach($produitsExpirantDansTroisMois as $produit)
                                        <tr>
                                            <td class="text-dark">{{ $produit['nom'] }}</td>
                                            <td class="text-dark">{{ \Carbon\Carbon::parse($produit['date_expiration'])->format('d M Y') }}</td>
                                            <td class="text-dark">{{ $produit['quantite_disponible'] }}</td>
                                            <td class="text-dark">{{ $produit['lot_numero'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-dark">Aucun produit expirant dans 3 mois</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <!-- Carte des Produits Expirant dans 3 Mois -->
        
    </div>
</div>

<script>
    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
        $('#errorMessage').fadeOut('slow');
    }, 5000);
</script>

@endsection