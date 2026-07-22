<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = $_GET['page'] ?? 'dashboard'; // page par défaut si aucun paramètre
$folder = $_GET['folder'] ?? 'dashboard'; // folder par défaut si aucun paramètre

// Sécurité : on liste les pages autorisées pour éviter d'inclure n'importe quel fichier
$allowed_pages = ['dashboard', 'transactions', 'settings'];

$allowed_folders = ['analytics', 'auth', 'dashboard', 'savings'];

if (!in_array($folder, $allowed_folders)) {
    $folder = 'dashboard'; // fallback si page invalide
} 

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard'; // fallback si folder invalide
}

echo "<!-- AVANT NAV -->";


require __DIR__ . '/' . 'app/views/nav/nav.php';

echo "<!-- APRÈS NAV -->";
require __DIR__ . '/' . 'app/views/' . $folder . '/'. $page . '.view.php';

