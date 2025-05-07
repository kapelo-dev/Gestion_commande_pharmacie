<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #f8f9fa; border-right: 1px solid #ddd; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="dashboard">
                <i class="mdi mdi-view-dashboard" style="margin-right: 10px;"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('ventes.index') }}">
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
        
        

      
    </ul>
</nav>
