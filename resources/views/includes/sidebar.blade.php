<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="dashboard">
                <i class="mdi mdi-view-dashboard" style="margin-right: 10px;"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ Request::routeIs('ventes.*') ? 'active' : '' }}" href="{{ route('ventes.index') }}">
                <i class="mdi mdi-square-inc-cash" style="margin-right: 10px;"></i>
                <span class="menu-title">Ventes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('commandes.index') }}"> 
                <i class="mdi mdi-apple-keyboard-command" style="margin-right: 10px;"></i>
                <span class="menu-title">Commandes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('produits.index') }}">
                <i class="mdi mdi-pill" style="margin-right: 10px;"></i>
                <span class="menu-title">Produits</span>
            </a>
        </li>
        
    
        @if(session('pharmacien_role') === 'gérant')
        <li class="nav-item" >
        <a class="nav-link" href="{{ route('pharmaciens.index') }}">
                <i class="mdi mdi-file-chart" style="margin-right: 10px;"></i>
                <span class="menu-title"> Gérer le personnel</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('ravitaillements.index') }}"> 
                <i class="mdi mdi-playlist-plus" style="margin-right: 10px;"></i>
                <span class="menu-title">Ravitaillement</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('destockage.index') }}" class="nav-link {{ Request::routeIs('destockage.*') ? 'active' : '' }}">
                <i class="mdi mdi-delete-variant" style="margin-right: 10px;"></i>
                <span class="menu-title">Déstockage</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bilans.index') }}" class="nav-link {{ Request::routeIs('bilans.*') ? 'active' : '' }}">
                <i class="mdi mdi-chart-bar" style="margin-right: 10px;"></i>
                <span class="menu-title">Bilans et Rapports</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('preparation-achats.index') }}" class="nav-link {{ Request::routeIs('preparation-achats.*') ? 'active' : '' }}">
                <i class="mdi mdi-cart-plus" style="margin-right: 10px;"></i>
                <span class="menu-title">Préparation Achats</span>
            </a>
        </li>
        @endif

      
    </ul>
</nav>

<style>
/* Reset des styles par défaut */
.nav-link,
.nav-link:hover,
.nav-link:focus,
.nav-link:active {
    color: #333 !important;
    background: none !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.sidebar {
    background-color: #fff;
    border-right: 1px solid rgba(138, 92, 246, 0.89);
    box-shadow: 2px 0 5px rgba(138, 92, 246, 0.33);
}

.sidebar .nav .nav-item {
    margin-bottom: 5px;
}

.sidebar .nav .nav-item .nav-link {
    padding: 15px 25px;
    color: #333 !important;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 5px 15px;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.sidebar .nav .nav-item .nav-link:hover,
.sidebar .nav .nav-item .nav-link:focus {
    background: linear-gradient(135deg, rgba(138, 92, 246, 0.6), rgba(216, 70, 239, 0.34)) !important;
    color: #333 !important;
    transform: translateX(5px);
}

/* Style spécial pour le lien actif uniquement */
.sidebar .nav .nav-item .nav-link.active {
    background: linear-gradient(135deg, #7c3aed, #c026d3) !important;
    color: white !important;
}

.sidebar .nav .nav-item .nav-link i {
    color: #333 !important;
    transition: all 0.3s ease;
}

.sidebar .nav .nav-item .nav-link:hover i,
.sidebar .nav .nav-item .nav-link:focus i {
    color: #333 !important;
}

/* Icônes blanches uniquement pour le lien actif */
.sidebar .nav .nav-item .nav-link.active i {
    color: white !important;
}

/* Suppression de tous les effets de focus par défaut */
.sidebar .nav .nav-item .nav-link:focus {
    outline: none !important;
    box-shadow: none !important;
}

/* Animation au survol avec couleurs plus intenses */
.sidebar .nav .nav-item .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(138, 92, 246, 0.57), rgba(216, 70, 239, 0.66));
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
}

.sidebar .nav .nav-item .nav-link:hover::before {
    opacity: 1;
}

/* Style spécial pour le menu actif */
.sidebar .nav .nav-item .nav-link.active::before {
    display: none;
}

/* Override Bootstrap avec texte noir */
.nav-link:not(.active):hover,
.nav-link:not(.active):focus,
.nav-link:not(.active):active {
    background: transparent !important;
    color: #333 !important;
}

/* Effet de bordure au hover */
.sidebar .nav .nav-item .nav-link:hover {
    box-shadow: inset 0 0 0 1px rgb(138, 92, 246) !important;
}

/* Force le texte noir pour tous les états sauf actif */
.sidebar .nav .nav-item .nav-link:not(.active),
.sidebar .nav .nav-item .nav-link:not(.active):hover,
.sidebar .nav .nav-item .nav-link:not(.active):focus,
.sidebar .nav .nav-item .nav-link:not(.active) i {
    color: #333 !important;
}
</style>
