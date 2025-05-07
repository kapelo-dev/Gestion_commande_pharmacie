@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Prévisualisation du Ravitaillement</h3>
                    <div>
                        <button type="button" class="btn btn-secondary" onclick="window.history.back()">Retour</button>
                        <button type="button" class="btn btn-primary" id="btn-confirm-import" @if($errors) disabled @endif>
                            Confirmer l'importation
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors)
                        <div class="alert alert-danger">
                            <h5>Erreurs détectées :</h5>
                            <ul class="mb-0">
                                @foreach($errors as $ligne => $erreurs)
                                    <li>
                                        <strong>{{ $ligne }}</strong>
                                        <ul>
                                            @foreach($erreurs as $erreur)
                                                <li>{{ $erreur }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID Produit</th>
                                    <th>Nom du Produit</th>
                                    <th>N° Lot</th>
                                    <th>Date d'expiration</th>
                                    <th>Quantité</th>
                                    <th>Prix d'achat</th>
                                    <th>Prix unitaire actuel</th>
                                    <th>Nouveau prix unitaire</th>
                                    <th>Variation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview as $row)
                                    <tr>
                                        <td>{{ $row['id_produit'] }}</td>
                                        <td>{{ $row['nom_produit'] }}</td>
                                        <td>{{ $row['lot_numero'] }}</td>
                                        <td>{{ date('d/m/Y', strtotime($row['date_expiration'])) }}</td>
                                        <td class="text-right">{{ number_format($row['quantite_disponible'], 0, ',', ' ') }}</td>
                                        <td class="text-right">{{ number_format($row['prix_achat'], 2, ',', ' ') }} €</td>
                                        <td class="text-right">{{ number_format($row['prix_actuel'], 2, ',', ' ') }} €</td>
                                        <td class="text-right">{{ number_format($row['prix_unitaire'], 2, ',', ' ') }} €</td>
                                        <td class="text-right @if($row['variation_prix'] > 0) text-success @elseif($row['variation_prix'] < 0) text-danger @endif">
                                            {{ number_format($row['variation_prix'], 2, ',', ' ') }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <p class="mb-0">
                            <strong>Total des lignes :</strong> {{ $total_rows }}
                            <br>
                            <strong>Lignes valides :</strong> {{ $valid_rows }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btn-confirm-import').addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
    
    fetch('{{ route("ravitaillements.import.process") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: data.message,
                showCancelButton: false,
                confirmButtonText: 'OK'
            }).then((result) => {
                window.location.href = '{{ route("ravitaillements.index") }}';
            });
        } else {
            throw new Error(data.error || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message
        });
        this.disabled = false;
        this.innerHTML = 'Confirmer l\'importation';
    });
});
</script>
@endpush
@endsection 