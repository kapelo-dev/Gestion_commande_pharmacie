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
      <li>
        <div class="col-12 col-xl-4">
          <div class="justify-content-end d-flex">
            <div class="form-check form-switch">
              <p> <strong> {{ session('pharmacien_prenom')}} </strong> </p>
            </div>
          </div>
        </div>
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
