<?php

namespace App\Repositories;


use PDO;
use DateTimeImmutable;

class AnalyseRepository {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }



    public function totalExpensesBetweenDates(int $userId, DateTimeImmutable $firstDate, DateTimeImmutable $secondDate): ?int {
        $stmt = $this->db->prepare("SELECT SUM(a.amount) AS total_expense
        FROM actions AS a
        INNER JOIN users AS u ON a.user_id = u.id
        WHERE u.id = :user_id
        AND a.date BETWEEN :first_date AND :second_date");
        $stmt->execute([
            'user_id' => $userId,
            'first_date' => $firstDate->format('Y-m-d'),
            'second_date' => $secondDate->format('Y-m-d')
        ]);
        $result = $stmt->fetchColumn();

        return $result && $result['total_expense'] !== null 
        ? (int) $result['total_expense'] 
        : 0;
    }
    
    public function getTopExpenseCategoryBetweenDates(int $userId, DateTimeImmutable $startDate, DateTimeImmutable $endDate): ?array {
    $stmt = $this->db->prepare("SELECT c.nom AS categories
            FROM actions AS a
            INNER JOIN users AS u ON a.users_id = u.id
            INNER JOIN categories AS c ON a.categories_id = c.id
            WHERE u.id = :user_id
            AND a.type = 'depense'
            AND a.action_date BETWEEN :start_date AND :end_date
            GROUP BY c.id, c.nom
            ORDER BY total_expense DESC
            LIMIT 1");

   
    $stmt->execute([
        ':user_id'    => $userId,
        ':start_date' => $startDate->format('Y-m-d'),
        ':end_date'   => $endDate->format('Y-m-d'),
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
    }



public function getTopExpenseBetweenDates(int $userId, DateTimeImmutable $firstDate, DateTimeImmutable $secondDate): ?int {
    $stmt = $this->db->prepare("SELECT MAX(a.amount) AS max_amount
        FROM actions AS a
        INNER JOIN users AS u ON a.user_id = u.id
        WHERE u.id = :user_id
        AND a.type = 'depense'
        AND a.date BETWEEN :first_date AND :second_date");
    $stmt->execute([
        'user_id' => $userId,
        'first_date' => $firstDate->format('Y-m-d'),
        'second_date' => $secondDate->format('Y-m-d')
    ]);
    $result = $stmt->fetchColumn();

    return $result && $result['max_amount'] !== null 
    ? (int) $result['max_amount'] 
    : 0;
} 

public function getTop5ExpenseBetweenDates(int $userId, DateTimeImmutable $firstDate, DateTimeImmutable $secondDate): ?array {
    $stmt = $this->db->prepare("SELECT a.libelle, a.amount
        FROM actions AS a
        INNER JOIN users AS u ON a.user_id = u.id
        WHERE u.id = :user_id
        AND a.type = 'depense'
        AND a.date BETWEEN :first_date AND :second_date
        ORDER BY a.amount DESC
        LIMIT 5");
    $stmt->execute([
        'user_id' => $userId,
        'first_date' => $firstDate->format('Y-m-d'),
        'second_date' => $secondDate->format('Y-m-d')
    ]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result ?: null;
}
}

