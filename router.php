<?php
// router.php — pour lancer : php -S localhost:8000 router.php
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = __DIR__ . $uri;

// Si c'est un fichier existant (CSS, JS, images…), on le sert directement
if (file_exists($path) && is_file($path)) {
    return false;
}

// Sinon on passe par le routeur principal
require __DIR__ . '/index.php';