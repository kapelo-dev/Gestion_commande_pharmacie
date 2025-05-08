@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Préparation des Achats</h4>
                    <button id="copyIds" class="btn btn-primary">
                        <i class="mdi mdi-content-copy"></i> Copier les IDs sélectionnés
                    </button>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="produitsTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="selectAll">
                                            <label class="custom-control-label" for="selectAll"></label>
                                        </div>
                                    </th>
                                    <th>ID Produit</th>
                                    <th>Nom du Produit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($produits as $produit)
                                <tr>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input produit-checkbox" 
                                                   data-id="{{ $produit['id'] }}" 
                                                   data-nom="{{ $produit['nom'] }}"
                                                   id="produit_{{ $produit['id'] }}">
                                            <label class="custom-control-label" for="produit_{{ $produit['id'] }}"></label>
                                        </div>
                                    </td>
                                    <td>{{ $produit['id'] }}</td>
                                    <td>{{ $produit['nom'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser DataTables
    $('#produitsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "pageLength": 25,
        "order": [[2, "asc"]], // Trier par nom de produit
        "columnDefs": [
            { "orderable": false, "targets": 0 } // Désactiver le tri sur la colonne des checkboxes
        ]
    });

    // Sélectionner/Désélectionner tout
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.produit-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // Copier les IDs
    document.getElementById('copyIds').addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.produit-checkbox:checked');
        const selectedProducts = Array.from(selectedCheckboxes).map(checkbox => ({
            id: checkbox.dataset.id,
            nom: checkbox.dataset.nom
        }));
        
        if (selectedProducts.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'Veuillez sélectionner au moins un produit',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Créer le texte avec les IDs et noms sur des lignes séparées
        const textToCopy = selectedProducts.map(product => `${product.id}`).join('\n');

        navigator.clipboard.writeText(textToCopy)
            .then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    html: `<p>${selectedProducts.length} ID${selectedProducts.length > 1 ? 's' : ''} ${selectedProducts.length > 1 ? 'ont' : 'a'} été copié${selectedProducts.length > 1 ? 's' : ''} dans le presse-papiers</p>
                          <p class="text-muted small">Les IDs sont copiés un par ligne</p>`,
                    confirmButtonText: 'OK'
                });
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors de la copie: ' + err,
                    confirmButtonText: 'OK'
                });
            });
    });
});
</script>

<style>
.custom-control-input {
    position: absolute;
    left: 0;
    z-index: -1;
    width: 1rem;
    height: 1.25rem;
    opacity: 0;
}

.custom-control-label {
    position: relative;
    margin-bottom: 0;
    vertical-align: top;
    cursor: pointer;
}

.custom-control-label::before {
    position: absolute;
    top: 0.25rem;
    left: -1.5rem;
    display: block;
    width: 1rem;
    height: 1rem;
    pointer-events: none;
    content: "";
    background-color: #fff;
    border: 1px solid #adb5bd;
    border-radius: 0.25rem;
}

.custom-control-label::after {
    position: absolute;
    top: 0.25rem;
    left: -1.5rem;
    display: block;
    width: 1rem;
    height: 1rem;
    content: "";
    background: no-repeat 50% / 50% 50%;
}

.custom-control-input:checked ~ .custom-control-label::before {
    color: #fff;
    border-color: #4B49AC;
    background-color: #4B49AC;
}

.custom-control-input:checked ~ .custom-control-label::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3e%3c/svg%3e");
}

.custom-control {
    position: relative;
    z-index: 1;
    display: block;
    min-height: 1.5rem;
    padding-left: 1.5rem;
    color-adjust: exact;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    padding: 1rem;
}

.btn-primary {
    background-color: #4B49AC;
    border-color: #4B49AC;
}

.btn-primary:hover {
    background-color: #3c3a8c;
    border-color: #3c3a8c;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.text-muted.small {
    font-size: 0.875rem;
}
</style>
@endpush
@endsection 