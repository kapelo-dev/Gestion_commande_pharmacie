@if(isset($preview_data))
    <!-- Affichage pour les ravitaillements importés via Excel -->
    <div class="mb-3">
        <strong>Date du ravitaillement :</strong> {{ $date_ravitaillement }}
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>N° Lot</th>
                    <th>Date d'expiration</th>
                    <th>Quantité</th>
                    <th>Prix d'achat</th>
                    <th>Prix unitaire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($preview_data as $row)
                <tr>
                    <td>{{ $row['nom_produit'] }}</td>
                    <td>{{ $row['lot_numero'] }}</td>
                    <td>{{ $row['date_expiration'] }}</td>
                    <td>{{ $row['quantite_disponible'] }}</td>
                    <td>{{ number_format($row['prix_achat'], 2) }} FCFA</td>
                    <td>{{ number_format($row['prix_unitaire'], 2) }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Affichage pour les ravitaillements manuels -->
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Date du ravitaillement :</strong>
                    <p>{{ $ravitaillement['date'] }}</p>
                </div>
                @if($ravitaillement['fichier'])
                <div class="col-md-6">
                    <strong>Fichier :</strong>
                    <p>{{ basename($ravitaillement['fichier']) }}</p>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Fournisseur :</strong> {{ $ravitaillement['fournisseur'] }}</p>
                    <p><strong>N° Lot :</strong> {{ $ravitaillement['lot_numero'] }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Produit :</strong> {{ $ravitaillement['produit'] }}</p>
                    <p><strong>Quantité :</strong> {{ $ravitaillement['quantite'] }}</p>
                </div>
            </div>
        </div>
    </div>
@endif 