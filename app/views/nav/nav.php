<?php

// On détecte la section active une seule fois
$request_uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri  = trim($request_uri, '/');
$active_folder = explode('/', $request_uri)[0] ?: 'dashboard';

function nav_item(string $route, string $title, string $icon): string {
    global $active_folder;
    
    $class = 'nav-item';
    if ($active_folder === $route) {
        $class .= ' nav-item--active';
    }
    
    return <<<HTML
        <a href="/{$route}" class="{$class}">
            <span class="nav-icon">{$icon}</span>
            <span>{$title}</span>
        </a>
HTML;
}

?>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <nav class="sidebar__nav">
        <?= nav_item('dashboard', 'Tableau de bord', '◈') ?>
        <?= nav_item('savings', 'Épargnes', '◇') ?>
        <?= nav_item('analytics', 'Analyses', '◆') ?>
    </nav>

    <div class="sidebar__user">
        <div class="user-avatar">JM</div>
        <div class="user-info">
            <span class="user-name">Jean Martin</span>
            <a href="/logout" class="user-logout">Déconnexion</a>
        </div>
    </div>
</aside>