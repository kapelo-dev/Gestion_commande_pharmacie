<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row custom-navbar" style="background-color: #6a11cb;">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
    <a href="/">
      <img src="images/Symbole-Pharmacie.png" class="mr-2" style="max-height: 80px; width: 80px; display: block;" alt="Pharmacie Logo" />
    </a>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
      <span class="icon-menu"></span>
    </button>

    <ul class="navbar-nav navbar-nav-right">
      @if(session('pharmacien_role') === 'gérant')
      <li class="nav-item" style="margin-right: auto;">
        <button type="button" class="btn btn-warning btn-sm" style="margin-left: -15px;" data-toggle="modal" data-target="#updateStockModal">
          <i class="fas fa-sync-alt"></i> Mise à jour des stocks
        </button>
      </li>
      @endif
      <li class="nav-item d-flex align-items-center">
        <span style="color: #6a11cb; font-weight: 600; font-size: 0.95rem; background-color: #ffffff; padding: 5px 10px; border-radius: 4px; margin-right: 10px;">
          {{ session('pharmacien_prenom')}}
        </span>
      </li>
      <li class="nav-item nav-profile dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
          <img src="images/faces/avatar.png" alt="profile" />
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
          <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="ti-power-off text-primary"></i> Déconnexion
          </a>
        </div>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="icon-menu"></span>
    </button>
  </div>
</nav>

<!-- Formulaire de déconnexion -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Modal de mise à jour des stocks -->
<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mise à jour des stocks</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Cette opération va mettre à jour les quantités en stock de tous les produits en fonction des lots disponibles.
                </div>
                <p>Voulez-vous procéder à la mise à jour des stocks ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" onclick="updateStock()">
                    <i class="fas fa-sync-alt"></i> Mettre à jour
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateStock() {
    // Afficher un indicateur de chargement
    Swal.fire({
        title: 'Mise à jour en cours...',
        html: 'Veuillez patienter pendant la mise à jour des stocks',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Appel AJAX pour mettre à jour les stocks
    $.ajax({
        url: '{{ route("produits.updateStock") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#updateStockModal').modal('hide');
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Les stocks ont été mis à jour avec succès',
                    showConfirmButton: true,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: response.message || 'Une erreur est survenue lors de la mise à jour des stocks',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            $('#updateStockModal').modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors de la mise à jour des stocks',
                confirmButtonText: 'OK'
            });
        }
    });
}
</script>
