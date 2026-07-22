<?php
function nav_item(string $page, string $title, string $icon): string {

    // 1. On récupère la page actuelle depuis l'URL
    //    Si $_GET['page'] n'existe pas, on met une valeur par défaut ('dashboard' par exemple)
    $current_page = $_GET['page'] ?? 'dashboard';

    // 2. On compare la page actuelle avec celle du lien qu'on génère
    $class = 'nav-item';
    if ($current_page === $page){
        $class = 'nav-item--active';
    }

    // 3. On retourne le HTML du lien (rôle UNIQUE de cette fonction)
    return <<<HTML
        <a href="{$page}" class="{$class}">
            <i class="{$icon}"></i>
            <span>{$title}</span>
        </a>
HTML;
}

?>

<!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <!-- Dans la sidebar, remplace les href -->
        <nav class="sidebar__nav">
            <a href="/?page=dashboard" class="nav-item<?php echo $_SERVER['REQUEST_URI'] === '/?page=dashboard' ? ' nav-item--active' : ''; ?>">
                <span class="nav-icon">◈</span> Tableau de bord
            </a>
            <a href="/?page=savings" class="nav-item<?php echo $_SERVER['REQUEST_URI'] === '/?page=savings' ? ' nav-item--active' : ''; ?>">
                <span class="nav-icon">◇</span> Épargnes
            </a>
            <a href="/?page=analytics" class="nav-item<?php echo $_SERVER['REQUEST_URI'] === '/?page=analytics' ? ' nav-item--active' : ''; ?>">
                <span class="nav-icon">◆</span> Analyses
            </a>
        </nav>

        <!-- Dans sidebar__user, change le logout -->
        <div class="sidebar__user">
            <div class="user-avatar">JM</div>
            <div class="user-info">
                <span class="user-name">Jean Martin</span>
                <a href="/?page=logout" class="user-logout">Déconnexion</a>
            </div>
        </div>
    </aside>


