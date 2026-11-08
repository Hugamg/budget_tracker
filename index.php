<?php

//Repositories
use App\Repositories\UserRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\SavingsRepository;

//Controllers
use App\Controllers\DashboardController;
use App\Controllers\AuthController;
use App\Controllers\SavingsController;
use App\Controllers\AnalyseController;

session_start();

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';


// ─── 1. PARSING DE L'URL ─────────────────────────────────────
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Ici on garde juste le chemin avec parse_url (ex: http://localhost:8000/savings?edit=1 -> savings)
$uri = trim($uri, '/'); // On enlève les slashes au début et à la fin pour avoir savings au lieu de /savings

// Route par défaut
if ($uri === '' || $uri === 'index.php') {
    $uri = 'dashboard';

}

$segments = explode('/', $uri); //explode('/', 'savings') donne un tableau avec un seul élément : ['savings'].
$folder = $segments[0] ?? 'dashboard'; //1 er élément du tableau (dossier)
$page   = $segments[1] ?? $folder; //2 eme élément du tableau (page)

// ─── 2. SÉCURITÉ (whitelist) ─────────────────────────────────
$allowed_folders = ['analytics', 'auth', 'dashboard', 'savings'];
$allowed_pages   = ['dashboard', 'transactions', 'settings', 'analytics', 'savings', 'login', 'register'];

if (!in_array($folder, $allowed_folders)) {
    $folder = 'dashboard';
    $page   = 'dashboard';
}

if (!in_array($page, $allowed_pages)) {
    $page = $folder; // fallback sur la page principale du dossier
}

// ─── 3. VÉRIFICATION FICHIER ─────────────────────────────────
$view_file = __DIR__ . '/app/Views/' . $folder . '/' . $page . '.view.php';

if (!file_exists($view_file)) {
    $folder = 'dashboard';
    $page   = 'dashboard';
    $view_file = __DIR__ . '/app/Views/dashboard/dashboard.view.php';
}

// ─── 4. INSTANCIATION DES REPOS ─────────────────────────────────
$userRepo = new UserRepository($pdo);   
$dashboardRepo = new DashboardRepository($pdo);


// ─── Récupération de l'ID utilisateur connecté (mis en session par AuthController::login)
$userId = (int) ($_SESSION['user_id'] ?? 1); // Mettre sur 0 pour tester sans connexion

// ─── 5. ROUTÂGE VERS LES CONTROLLEURS ─────────────────────────────────

switch ($folder) {

    case 'dashboard':
        $controller = new DashboardController($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handlePost($userId);
        }

        $data = $controller->index($userId);
        break;

    case 'analytics':
        $controller = new AnalyseController($pdo);
        $data = $controller->index(
            $userId,
            $_GET['start'] ?? null,
            $_GET['end'] ?? null
        );
        break;
    
    case 'savings':
        $controller = new SavingsController($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handlePost($userId);
        }

        $data = $controller->index($userId);
        break;

    case 'auth':
        $controller = new AuthController($userRepo);

        if ($page === 'login') {
            $controller->login();
        } elseif ($page === 'register') {
            $controller->register();
        }
        break;

    default:
        $controller = new DashboardController($pdo);
        $data = $controller->index($userId);
        break;
}

// ─── AFFICHAGE ────────────────────────────────────────────
if (isset($data)) {
    extract($data); // transforme chaque clé du tableau en variable ($user, $budget, etc.)
}

// ─── 4. AFFICHAGE ────────────────────────────────────────────
require __DIR__ . '/app/Views/nav/nav.php';
require $view_file;