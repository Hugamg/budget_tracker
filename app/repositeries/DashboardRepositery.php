<?php

namespace App\Repositeries;


use PDO;
use DateTimeImmutable;

class DashboardRepositery {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getMonthlySalaryNow(int $userId): ?int {
        $currentDate = new DateTimeImmutable('now');
        $formattedMonth = $currentDate->format('Y-m-01');

        $stmt = $this->db->prepare("SELECT mb.salary FROM monthly_budget AS mb INNER JOIN users AS u ON mb.id_users = u.id
        WHERE mb.id_users = :user_id AND mb.month_ = :month");
        // La variable stmt est la préparation de la requête SQL ce au'on appel le statement. 
        // La préparation des requêtes SQL en PHP oou dans d'autres langages permet de les sécursier afin d'éviter les injections SQL
        
        // Maintenant avec ce code ci dessous nous allons exécuter la requête SQL avec ces vrais valeurs 
        $stmt->execute([
            'user_id' => $userId,
            'month' => $formattedMonth
        ]);
        
        // On récupère le résultat
        $result = $stmt->fetch();
        
        // On retourne le résultat
        return $result ? (int) $result['salary'] : null;
    }


    public function getMonthlyAmoutSaving(int $userId): ?int {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT 
            SUM(CASE WHEN a.type = 'epargne_versement' THEN a.amount ELSE 0 END) 
            - 
            SUM(CASE WHEN a.type = 'epargne_retrait' THEN a.amount ELSE 0 END) 
            AS total_epargne
         FROM action AS a
         INNER JOIN users AS u ON a.id_users = u.id
         WHERE u.id = :user_id
           AND YEAR(a.action_date) = :year
           AND MONTH(a.action_date) = :month");
        
        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
        
        $result = $stmt->fetch();
        
        return $result && $result['total_epargne'] !== null 
        ? (int) $result['total_epargne'] 
        : 0;
    }
}
