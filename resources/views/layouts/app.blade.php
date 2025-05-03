<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title> Miabe Stock</title>
  <!-- plugins:css -->
   
  <link rel="stylesheet" href="vendors/feather/feather.css">
  <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="vendors/datatables.net-bs4/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" type="text/css" href="js/select.dataTables.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="css/vertical-layout-light/style.css">
  <link rel="stylesheet" href="vendors/mdi/css/materialdesignicons.min.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="images/image.png" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    @yield('content')
</div>
<!-- main-panel ends -->
      
    <!-- page-body-wrapper ends -->
  <!-- container-scroller -->

  <!-- plugins:js -->
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="vendors/chart.js/Chart.min.js"></script>
  <script src="vendors/datatables.net/jquery.dataTables.js"></script>
  <script src="vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
  <script src="js/dataTables.select.min.js"></script>

  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="js/off-canvas.js"></script>
  <script src="js/hoverable-collapse.js"></script>
  <script src="js/template.js"></script>
  <script src="js/settings.js"></script>
  <script src="js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="js/dashboard.js"></script>
  <script src="js/Chart.roundedBarCharts.js"></script>
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
            }, 3000);
        }

        // Masquez les messages d'erreur après 3 secondes
        errorMessages.forEach(function(errorMessage) {
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 3000);
        });
    };
</script>

</body>


</html>

