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