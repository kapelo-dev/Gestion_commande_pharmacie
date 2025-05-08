@extends('layouts.app')

@section('content')
<!-- Ajout des CDN nécessaires -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    .btn-group {
        display: inline-flex;
    }

    .btn-group .btn {
        padding: 0.375rem 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        border: none;
        margin: 0;
    }

    .btn-group .btn:first-child {
        border-radius: 0;
        border-top-left-radius: 6px;
        border-bottom-left-radius: 6px;
    }

    .btn-group .btn:last-child {
        border-radius: 0;
        border-top-right-radius: 6px;
        border-bottom-right-radius: 6px;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white !important;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white !important;
    }

    .btn-info:hover {
        background-color: #138496;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }
</style>

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="col-lg-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title">Liste des Produits</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProduitModal">
                        <i class="fas fa-plus"></i> Nouveau Produit
                    </button>
                </div>

                <!-- Zone de recherche pour les produits -->
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Rechercher un produit" aria-label="Rechercher" aria-describedby="basic-addon1" style="border: 2px solid #6a11cb;">
                        <div class="input-group-append">
                            <span class="input-group-text" id="basic-addon1" style="background-color: #6a11cb; color: white;">
                                <i class="fas fa-search"></i>
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
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $produit['nom'] }}</td>
                                    <td class="description-cell">{{ $produit['description'] }}</td>
                                    <td class="text-center">{{ $produit['quantite_en_stock'] }}</td>
                                    <td class="text-center">{{ $produit['sur_ordonnance'] ? 'Oui' : 'Non' }}</td>
                                    <td class="text-end">{{ number_format($produit['prix_unitaire'], 0, ',', ' ') }} FCFA</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editProduitModal{{ $produit['id'] }}">
                                                <i class="fas fa-edit"></i> 
                                        </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $produit['id'] }}')" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $produit['id'] }}" action="{{ route('produits.destroy', $produit['id']) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Aucun produit trouvé</td>
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
                    <div class="row">
                        <div class="col-md-6">
                    <div class="form-group">
                        <label for="id">ID (optionnel)</label>
                                <input type="text" class="form-control form-control-sm" id="id" name="id">
                            </div>
                    </div>
                        <div class="col-md-6">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                                <input type="text" class="form-control form-control-sm" id="nom" name="nom" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control form-control-sm" id="description" name="description" required rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                    <div class="form-group">
                        <label for="quantite_en_stock">Quantité en stock</label>
                                <input type="number" class="form-control form-control-sm" id="quantite_en_stock" name="quantite_en_stock" min="0" value="0" readonly>
                                <small class="text-muted">La quantité en stock est gérée automatiquement via les ravitaillements et les ventes</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="prix_unitaire">Prix Unitaire</label>
                                <input type="number" class="form-control form-control-sm" id="prix_unitaire" name="prix_unitaire" min="0" step="0.01" required>
                            </div>
                    </div>
                        <div class="col-md-4">
                    <div class="form-group">
                                <label for="sur_ordonnance">Sur ordonnance</label>
                                <select class="form-control form-control-sm" id="sur_ordonnance" name="sur_ordonnance" required>
                            <option value="true">Oui</option>
                            <option value="false">Non</option>
                        </select>
                    </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
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
                <form action="{{ route('produits.update', $produit['id']) }}" method="POST" class="edit-produit-form">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                                    <input type="text" class="form-control form-control-sm" id="nom" name="nom" value="{{ $produit['nom'] }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control form-control-sm" id="description" name="description" required rows="2">{{ $produit['description'] }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantite_en_stock">Quantité en stock</label>
                                    <input type="number" class="form-control form-control-sm" id="quantite_en_stock" name="quantite_en_stock" value="{{ $produit['quantite_en_stock'] }}" readonly>
                                    <small class="text-muted">La quantité en stock est gérée automatiquement via les ravitaillements et les ventes</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="prix_unitaire">Prix Unitaire</label>
                                    <input type="number" class="form-control form-control-sm" id="prix_unitaire" name="prix_unitaire" value="{{ $produit['prix_unitaire'] }}" min="0" step="0.01" required>
                                </div>
                        </div>
                            <div class="col-md-4">
                        <div class="form-group">
                                    <label for="sur_ordonnance">Sur ordonnance</label>
                                    <select class="form-control form-control-sm" id="sur_ordonnance" name="sur_ordonnance" required>
                                <option value="true" {{ $produit['sur_ordonnance'] ? 'selected' : '' }}>Oui</option>
                                <option value="false" {{ !$produit['sur_ordonnance'] ? 'selected' : '' }}>Non</option>
                            </select>
                        </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary btn-sm">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
function confirmDelete(produitId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form-' + produitId);
            form.submit();
        }
    });
}

// Animation des messages de succès
const successMessage = document.querySelector('.alert-success');
    if (successMessage) {
    setTimeout(() => {
        successMessage.style.transition = 'opacity 0.5s ease';
        successMessage.style.opacity = '0';
        setTimeout(() => {
            successMessage.remove();
        }, 500);
        }, 3000);
    }

// Filtrage des produits
    const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#produitsTableBody tr');
        
        rows.forEach(row => {
            if (!row.querySelector('td[colspan]')) {
            const nomProduit = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const description = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (nomProduit.includes(searchTerm) || description.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
                }
            }
        });
    });
}
</script>
@endpush

@endsection
