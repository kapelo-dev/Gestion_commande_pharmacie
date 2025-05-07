<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID Produit</th>
                <th>Nom du Produit</th>
                <th>N° Lot</th>
                <th>Date d'expiration</th>
                <th>Quantité</th>
                <th>Prix d'achat</th>
                <th>Prix unitaire</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preview_data as $row)
                <tr class="{{ $row['status'] === 'error' ? 'table-danger' : 'table-success' }}">
                    <td>{{ $row['id_produit'] }}</td>
                    <td>{{ $row['nom_produit'] ?? 'Non trouvé' }}</td>
                    <td>{{ $row['lot_numero'] }}</td>
                    <td>{{ $row['date_expiration'] }}</td>
                    <td>{{ $row['quantite_disponible'] }}</td>
                    <td>{{ number_format($row['prix_achat'], 2) }} FCFA</td>
                    <td>{{ number_format($row['prix_unitaire'], 2) }} FCFA</td>
                    <td>
                        @if($row['status'] === 'error')
                            <span class="badge bg-danger">{{ $row['message'] }}</span>
                        @else
                            <span class="badge bg-success">Valide</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<input type="hidden" id="excel_path" value="{{ session('excel_path') }}"> 