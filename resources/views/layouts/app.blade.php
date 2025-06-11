<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title> Miabe Stock</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- plugins:css -->
  <link rel="stylesheet" href="/vendors/feather/feather.css">
  <link rel="stylesheet" href="/vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="/vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="/vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="/vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" type="text/css" href="/js/select.dataTables.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="/css/vertical-layout-light/style.css">
  <link rel="stylesheet" href="/vendors/mdi/css/materialdesignicons.min.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="/images/image.png" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

  <style>
    /* Style global pour les boutons d'action */
    .btn-action {
        position: relative;
        padding-left: 2.5rem !important; /* Espace pour l'icône */
    }

    .btn-action i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Espacement entre les boutons */
    .btn-action + .btn-action {
        margin-left: 0.5rem;
    }

    /* Tailles spécifiques pour les boutons */
    .btn-sm.btn-action {
        padding: 0.25rem 0.75rem 0.25rem 2.25rem;
    }

    /* Style personnalisé pour les boutons radio */
    .custom-radio {
        display: flex;
        align-items: center;
        margin-right: 1.5rem;
    }

    .custom-radio input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .custom-radio label {
        position: relative;
        padding-left: 2rem;
        cursor: pointer;
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0;
    }

    .custom-radio label:before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #6a11cb;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .custom-radio label:after {
        content: '';
        position: absolute;
        left: 0.25rem;
        top: 50%;
        transform: translateY(-50%) scale(0);
        width: 0.75rem;
        height: 0.75rem;
        background: #6a11cb;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .custom-radio input[type="radio"]:checked + label:before {
        border-color: #6a11cb;
        background: #ffffff;
    }

    .custom-radio input[type="radio"]:checked + label:after {
        transform: translateY(-50%) scale(1);
    }

    .custom-radio input[type="radio"]:focus + label:before {
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.2);
    }

    .custom-radio:hover label:before {
        border-color: #6a11cb;
    }

    /* Conteneur des boutons radio */
    .radio-container {
        display: flex;
        gap: 1rem;
        padding: 0.5rem 0;
    }

    body {
        overflow: hidden;
    }

    .container-scroller {
        height: 100vh;
    }

    .container-fluid.page-body-wrapper {
        height: calc(100vh - 60px); /* Ajustez selon la hauteur de votre navbar */
        overflow: hidden;
    }

    /* Style pour la zone de contenu principal */
    .main-panel {
        height: 100%;
        overflow-y: auto;
        padding-right: 0;
    }

    /* Personnalisation de la barre de défilement pour le contenu */
    .main-panel::-webkit-scrollbar {
        width: 6px;
    }

    .main-panel::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .main-panel::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .main-panel::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .border-primary-custom {
        border-color: #007bff !important; /* Couleur primaire de Bootstrap */
    }
    .form-check {
    display: flex;
    align-items: center;
}

.form-check-input {
    width: 60px; /* Largeur du bouton */
    height: 25px; /* Hauteur du bouton */
    cursor: pointer;
    -webkit-appearance: none;
    background-color: #ccc; /* Couleur de fond par défaut */
    border-radius: 15px; /* Coins arrondis */
    position: relative;
    outline: none;
    transition: background-color 0.3s, border 0.3s; /* Transition pour le contour */
    border: 2px solid violet; /* Contour violet */
}

.form-check-input:checked {
    background-color: #4CAF50; /* Couleur verte quand activé */
}

.form-check-input:checked::before {
    transform: translateX(20px); /* Ajuster pour correspondre à la nouvelle taille */
}

.form-check-input::before {
    content: '';
    position: absolute;
    width: 20px; /* Taille du cercle */
    height: 20px; /* Taille du cercle */
    border-radius: 50%;
    background: white; /* Couleur du cercle */
    transition: transform 0.3s;
}

.form-check-label {
    margin-left: 10px; /* Espace entre le bouton et le label */
    font-weight: bold;
    font-size: 14px; /* Taille de la police */
}
.description-cell {
        max-width: 200px; /* Définir la largeur maximale */
        overflow: hidden; /* Masquer le texte qui dépasse */
        text-overflow: ellipsis; /* Ajouter des points de suspension (...) */
        white-space: nowrap; /* Empêcher le retour à la ligne */
    }
  </style>
  

</head>

<body>
  <div class="container-scroller">
    
    <!-- partial:partials/_navbar.html -->
    @include('includes.navbar')
    
    <!-- partial -->
    

      <!-- partial -->
      <!-- partial:partials/_sidebar.html -->
    
    
    <div class="container-fluid page-body-wrapper">
    @include('includes.sidebar')
  <!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
    @yield('content')
    </div>
</div>
<!-- main-panel ends -->
      
    <!-- page-body-wrapper ends -->
  <!-- container-scroller -->

  <!-- plugins:js -->
  <script src="/vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="/vendors/chart.js/Chart.min.js"></script>
  <script src="/vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <script src="/js/dataTables.select.min.js"></script>

  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="/js/off-canvas.js"></script>
  <script src="/js/hoverable-collapse.js"></script>
  <script src="/js/template.js"></script>
  <script src="/js/settings.js"></script>
  <script src="/js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="/js/dashboard.js"></script>
  <script src="/js/Chart.roundedBarCharts.js"></script>
  <!-- End custom js for this page-->
  <script>
    // Fonction pour masquer les messages après 3 secondes
    window.onload = function() {
        // Sélectionnez les messages d'erreur et de succès
        var successMessage = document.getElementById('success-message');
        var errorMessages = document.querySelectorAll('.alert-danger');

        // Masquez le message de succès après 3 secondes
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 30000);
        }

        // Masquez les messages d'erreur après 3 secondes
        errorMessages.forEach(function(errorMessage) {
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 30000);
        });
    };
</script>

@stack('scripts')

</body>


</html>

