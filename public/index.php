<?php
// Récupère l'URL
$page = $_GET['page'] ?? 'dashboard';

// Récupère l'action si elle existe (pour les modales, etc.)
$action = $_GET['action'] ?? null;

// Charge la bonne vue
switch ($page) {
    case 'savings':
        require '../app/views/savings/savings.view.php';
        break;
    case 'analytics':
        require '../app/views/analytics/analytics.view.php';
        break;
    case 'login':
        require '../app/views/auth/login.view.php';
        break;
    case 'register':
        require '../app/views/auth/register.view.php';
        break;
    case 'logout':
        // Tu gérerais la session ici
        require '../app/views/dashboard/dashboard.view.php';
        break;
    default:
        require '../app/views/dashboard/dashboard.view.php';
}
?>