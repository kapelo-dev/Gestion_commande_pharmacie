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

    .table-responsive {
        max-height: 450px; /* Augmenter la hauteur maximale du tableau */
        overflow-y: auto;
    }

    .table th, .table td {
        vertical-align: middle; /* Centrer verticalement le contenu des cellules */
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
                    <h4 class="card-title">Liste des Produits</h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProduitModal">
                        <i class="mdi mdi-cart-plus"></i> Nouveau Produit
                    </button>
                </div>

                <!-- Zone de recherche pour les produits -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un produit" aria-label="Rechercher" aria-describedby="basic-addon1" style="border: 2px solid #6a11cb;">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon1" style="background-color: #6a11cb; color: white;">
                                <i class="mdi mdi-magnify"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Table des produits -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th>#</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Quantité en stock</th>
                                <th>Sur ordonnance</th>
                                <th>Prix Unitaire</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="produitsTableBody">
                            @forelse ($produits as $index => $produit)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $produit['nom'] }}</td>
                                    <td>{{ $produit['description'] }}</td>
                                    <td>{{ $produit['quantite_en_stock'] }}</td>
                                    <td>{{ $produit['sur_ordonnance'] ? 'Oui' : 'Non' }}</td>
                                    <td>{{ number_format($produit['prix_unitaire'], 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editProduitModal{{ $produit['id'] }}">
                                            <i class="mdi mdi-pencil"></i> Éditer
                                        </button>
                                        <form action="{{ route('produits.destroy', $produit['id']) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                                <i class="mdi mdi-delete"></i> Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Aucun produit trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Produit -->
<div class="modal fade" id="addProduitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau Produit</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('produits.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id">ID (optionnel)</label>
                        <input type="text" class="form-control" id="id" name="id">
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="quantite_en_stock">Quantité en stock</label>
                        <input type="number" class="form-control" id="quantite_en_stock" name="quantite_en_stock" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="sur_ordonnance">Sur ordonnance (booléen)</label>
                        <select class="form-control" id="sur_ordonnance" name="sur_ordonnance" required>
                            <option value="true">Oui</option>
                            <option value="false">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prix_unitaire">Prix Unitaire</label>
                        <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals Éditer Produit -->
@foreach ($produits as $produit)
    <div class="modal fade" id="editProduitModal{{ $produit['id'] }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Éditer Produit</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('produits.update', $produit['id']) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" value="{{ $produit['nom'] }}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required>{{ $produit['description'] }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="quantite_en_stock">Quantité en stock</label>
                            <input type="number" class="form-control" id="quantite_en_stock" name="quantite_en_stock" value="{{ $produit['quantite_en_stock'] }}" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="sur_ordonnance">Sur ordonnance (booléen)</label>
                            <select class="form-control" id="sur_ordonnance" name="sur_ordonnance" required>
                                <option value="true" {{ $produit['sur_ordonnance'] ? 'selected' : '' }}>Oui</option>
                                <option value="false" {{ !$produit['sur_ordonnance'] ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="prix_unitaire">Prix Unitaire</label>
                            <input type="number" class="form-control" id="prix_unitaire" name="prix_unitaire" value="{{ $produit['prix_unitaire'] }}" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
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

    // Filtrage des produits en temps réel
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#produitsTableBody tr');
        rows.forEach(row => {
            const nomProduit = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            if (nomProduit.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

@endsection
