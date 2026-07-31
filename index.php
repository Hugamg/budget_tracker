<?php

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
$view_file = __DIR__ . '/app/views/' . $folder . '/' . $page . '.view.php';

if (!file_exists($view_file)) {
    $folder = 'dashboard';
    $page   = 'dashboard';
    $view_file = __DIR__ . '/app/views/dashboard/dashboard.view.php';
}

// ─── 4. AFFICHAGE ────────────────────────────────────────────
require __DIR__ . '/app/views/nav/nav.php';
require $view_file;