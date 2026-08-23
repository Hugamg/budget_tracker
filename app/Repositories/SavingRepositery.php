<?php

namespace App\Repositories;

use PDO;
use DateTimeImmutable;


class SavingRepositery {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getTotalAmoutSaved(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT SUM(CASE WHEN type = 'epargne_versement' THEN amount ELSE 0 END) 
        -
        SUM(CASE WHEN type = 'epargne_retrait' THEN amount ELSE 0 END) 
        AS total-epargne 
        FROM action 
        INNER JOIN users ON action.id_users = users.id
        WHERE users.id = :user_id");
        
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ? (int) $result['total-epargne'] : null;
    }

    public function getLastWithdrawal(int $userId): ?int {
        $currentDay = new DateTimeImmutable('now');
        $year = $currentDay->format('Y');
        $month = $currentDay->format('m');

        $stmt = $this->db->prepare("SELECT amount FROM action WHERE id_users = :user_id AND type = 'epargne_retrait' AND YEAR(date) = :year AND MONTH(date) = :month ORDER BY date DESC LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'year' => $year, 'month' => $month]);
        $result = $stmt->fetch();
        return $result ? (int) $result['amount'] : null;
    } 
    
    public function getTotalAmountSaveByCategories(int $userId, int $categoryId): ?int {
        $stmt = $this->db->prepare("SELECT SUM(amount) AS total 
        FROM action 
        INNER JOIN users AS u ON actions.id_users = u.id
        INNER JOIN categories AS c ON actions.id_categories = c.id
        WHERE u.id = :user_id 
        AND actions.id_categories = c.id
        AND c.type = 'epargne_versement'
        AND c.id = :category_id");
        $stmt->execute(['user_id' => $userId, 'category_id' => $categoryId]);
        $result = $stmt->fetch();
        return $result ? (int) $result['total'] : null;
    }

    function getAllSavingsByTypeSavingDeposits(int $userId): array {

        $stmt =$this->db->prepare("SELECT a.id, a.amout, a.action_date, c.name, a.type as saving_deposits
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id
        WHERE u.id = :user_id
        AND a.type ='epargnes_versement'
        ORDER BY a.action_date DESC LIMIT 5");
        
        $stmt->execute([
            'user_id' => $userId,
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getAllSavingsByWithdrawals(int $userId): array {

        $stmt =$this->db->prepare("SELECT a.id, a.amout, a.action_date, c.name, a.type as saving_deposits
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id
        WHERE u.id = :user_id
        AND a.type ='retrait_epargnes'
        ORDER BY a.action_date DESC LIMIT 5");
        
        $stmt->execute([
            'user_id' => $userId,
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
