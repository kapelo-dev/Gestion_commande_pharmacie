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

    <h1>Liste des Ravitaillements</h1>

    <!-- Bouton Ajouter -->
    <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addRavitaillementModal">
        Faire un ravitaillement
    </button>

    <!-- Liste des ravitaillements -->
    <div class="table-responsive mb-5" style="max-height: 400px; overflow-y: auto; overflow-x: auto; border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
        <table class="table table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Fournisseur</th>
                    <th>N° Lot</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ravitaillements as $index => $ravitaillement)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $ravitaillement['date'] }}</td>
                        <td>{{ $ravitaillement['fournisseur'] }}</td>
                        <td>{{ $ravitaillement['lot_numero'] }}</td>
                        <td>{{ $ravitaillement['produit'] }}</td>
                        <td>{{ $ravitaillement['quantite_ravitailler'] }}</td>
                        <td>
                            <button type="button"
                                class="btn btn-sm btn-danger"
                                onclick="if(confirm('Êtes-vous sûr de vouloir supprimer ce ravitaillement ?')) {
                                    document.getElementById('delete-form-{{ $ravitaillement['id'] }}').submit();
                                }">
                                <i class="mdi mdi-delete"></i>
                                Supprimer
                            </button>
                            <form id="delete-form-{{ $ravitaillement['id'] }}"
                                action="{{ url('ravitaillements/'.$ravitaillement['id'].'/delete') }}"
                                method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal pour Ajouter -->
    <div class="modal fade" id="addRavitaillementModal" tabindex="-1" aria-labelledby="addRavitaillementModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRavitaillementModalLabel">Ajouter un Ravitaillement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form action="{{ route('ravitaillements.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="fournisseur">Fournisseur</label>
                            <input type="text" class="form-control" id="fournisseur" name="fournisseur" required>
                        </div>

                        <div class="form-group">
                            <label for="lot_numero">N° Lot</label>
                            <input type="text" class="form-control" id="lot_numero" name="lot_numero" required>
                        </div>

                        <div class="form-group produit-autocomplete-container">
                            <label for="produit_ravitaille">Produit</label>
                            <input type="text" class="form-control produit-input" id="produit_ravitaille" name="produit_ravitaille" placeholder="Rechercher un produit..." required>
                            <input type="hidden" id="produit_id" name="produit_id">
                            <div class="autocomplete-list"></div>
                        </div>


                        <div class="form-group">
                            <label for="date_expiration">Date d'expiration</label>
                            <input type="date" class="form-control" id="date_expiration" name="date_expiration" required>
                        </div>

                        <div class="form-group">
                            <label for="quantite_disponible">Quantité </label>
                            <input type="number" class="form-control" id="quantite_disponible" name="quantite_disponible" required>
                        </div>

                        <div class="form-group text-right">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-success">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const produits = @json($produits);

    $(document).ready(function() {
        $('#produit_ravitaille').on('input', function() {
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
                const item = $(`<div class="autocomplete-item" data-id="${produit.id}">${produit.nom}</div>`);
                listContainer.append(item);
            });
        });

        $(document).on('click', '.autocomplete-item', function() {
            const item = $(this);
            const input = $('#produit_ravitaille');
            const idInput = $('#produit_id');

            input.val(item.text());
            idInput.val(item.data('id'));
            item.closest('.autocomplete-list').hide();
        });

        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
            $('#errorMessage').fadeOut('slow');
        }, 3000);
    });
</script>

@endsection
