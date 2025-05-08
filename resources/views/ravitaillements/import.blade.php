@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Importation de Ravitaillement</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="importForm" action="{{ route('ravitaillements.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="fichier_excel">Fichier Excel</label>
                            <input type="file" class="form-control" id="fichier_excel" name="fichier_excel" accept=".xlsx,.xls">
                        </div>
                        <div class="form-group">
                            <label for="fournisseur">Fournisseur</label>
                            <input type="text" class="form-control" id="fournisseur" name="fournisseur" required>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" id="previewBtn">
                                Prévisualiser
                            </button>
                            <a href="{{ route('ravitaillements.template.download') }}" class="btn btn-secondary">
                                <i class="mdi mdi-download"></i> Télécharger le modèle
                            </a>
                        </div>
                    </form>

                    <!-- Conteneur de prévisualisation avec spinner intégré -->
                    <div class="preview-section mt-4">
                        <h5>Prévisualisation des données</h5>
                        
                        <!-- Spinner de chargement -->
                        <div id="loading-overlay" class="loading-overlay d-none">
                            <div class="loading-content">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Chargement...</span>
                                </div>
                                <div class="mt-3">Traitement en cours...</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
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
                                <tbody id="preview-content">
                                    @if(isset($preview))
                                        @foreach($preview as $row)
                                        <tr>
                                            <td>{{ $row['id_produit'] }}</td>
                                            <td>{{ $row['nom_produit'] }}</td>
                                            <td>{{ $row['lot_numero'] }}</td>
                                            <td>{{ $row['date_expiration'] }}</td>
                                            <td>{{ $row['quantite_disponible'] }}</td>
                                            <td>{{ $row['prix_achat'] }}</td>
                                            <td>{{ $row['prix_unitaire'] }}</td>
                                            <td>
                                                @if($row['produit_trouve'])
                                                    <span class="badge badge-success">Produit trouvé</span>
                                                @else
                                                    <span class="badge badge-danger">Produit non trouvé</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr id="empty-message">
                                            <td colspan="8" class="text-center py-4">
                                                Aucune donnée à afficher. Veuillez importer un fichier Excel.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        @if(isset($preview))
                        <form id="processForm" action="{{ route('ravitaillements.import.process') }}" method="POST">
                            @csrf
                            <input type="hidden" name="fournisseur" value="{{ $fournisseur }}">
                            <button type="submit" class="btn btn-success btn-lg mt-3" id="validateBtn">
                                Valider l'importation
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    const loadingOverlay = document.getElementById('loading-overlay');
    const emptyMessage = document.getElementById('empty-message');

    if (importForm) {
        importForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('fichier_excel');
            const fournisseurInput = document.getElementById('fournisseur');
            
            if (!fileInput.files.length) {
                alert('Veuillez sélectionner un fichier Excel');
                return;
            }

            if (!fournisseurInput.value.trim()) {
                alert('Veuillez entrer le nom du fournisseur');
                return;
            }

            try {
                // Afficher le spinner
                loadingOverlay.classList.remove('d-none');
                if (emptyMessage) {
                    emptyMessage.style.display = 'none';
                }

                const formData = new FormData(importForm);
                const response = await fetch(importForm.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }

                const html = await response.text();
                document.documentElement.innerHTML = html;

                // Réexécuter les scripts
                const scripts = document.getElementsByTagName('script');
                Array.from(scripts).forEach(script => {
                    if (script.innerHTML) {
                        eval(script.innerHTML);
                    }
                });
            } catch (error) {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la prévisualisation');
                loadingOverlay.classList.add('d-none');
                if (emptyMessage) {
                    emptyMessage.style.display = '';
                }
            }
        });
    }

    // Gestion du formulaire de validation
    const processForm = document.getElementById('processForm');
    if (processForm) {
        processForm.addEventListener('submit', function(e) {
            const validateBtn = document.getElementById('validateBtn');
            if (validateBtn) {
                validateBtn.disabled = true;
                validateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validation en cours...';
            }
        });
    }
});
</script>

<style>
.preview-section {
    position: relative;
    min-height: 400px;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.loading-content {
    text-align: center;
}

.loading-content .spinner-border {
    width: 4rem;
    height: 4rem;
    border-width: 0.3em;
}

.loading-content .mt-3 {
    font-size: 1.2rem;
    color: #333;
    margin-top: 1rem;
}

.table {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    font-size: 1.1rem;
    padding: 1rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
}

.badge {
    padding: 0.5em 0.8em;
    font-size: 90%;
}

.badge-success {
    background-color: #4CAF50;
    color: white;
}

.badge-danger {
    background-color: #f44336;
    color: white;
}

.btn {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.form-control {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
}
</style>
@endpush
@endsection 