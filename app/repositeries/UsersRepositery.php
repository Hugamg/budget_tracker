<?php

namespace App\Repositeries;

use App\Models\User;
use PDO;

class UserRepositery
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un utilisateur par son email (utile pour le login)
     */
    public function findByEmail(string $mail): ?User
    {
        $sql = "SELECT * FROM users WHERE mail = :mail LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Récupère un utilisateur par son ID
     */
    public function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Transforme une ligne SQL (array) en objet User
     * Évite de dupliquer le new User(...) partout
     */
    private function hydrate(array $row): User
    {
        return new User(
            (int) $row['id'],
            $row['surname'],
            $row['mail'],
            $row['first_name'],
            $row['password']
        );
    }
}