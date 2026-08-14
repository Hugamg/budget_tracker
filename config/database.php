<?php

declare(strict_types=1);

use Dotenv\Dotenv;

// Chargement des variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbName;charset=$charset",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Sécurité : vraies requêtes préparées
        ]
    );
} catch (PDOException $e) {
    // En prod : on ne montre JAMAIS le message d'erreur brut à l'utilisateur
    if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
        throw new PDOException("Erreur de connexion : " . $e->getMessage(), (int)$e->getCode());
    }

    error_log($e->getMessage()); // On log l'erreur côté serveur
    throw new PDOException("Une erreur est survenue. Veuillez réessayer plus tard.");
}

return $pdo;