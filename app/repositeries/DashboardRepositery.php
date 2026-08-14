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

        $stmt = $this->db->prepare("SELECT mb.salary AS salary FROM monthly_budget AS mb INNER JOIN users AS u ON mb.id_users = u.id
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

    public function getMonthlyTotalExpenses(int $userId): ?int{
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT SUM(a.amount) AS total_expense 
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        WHERE u.id = :user_id
        AND a.type ='depense'
        AND YEAR (a.actions_date) = :year
        AND MONTH (a.actions_date) = :month");
        
        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);
        
        $result = $stmt->fetch();
        
        return $result && $result['total_expense'] !== null 
        ? (int) $result['total_expense'] 
        : 0;
    }

    public function getTotalExpenseByCategories(int $userId, int $categoriesId): ?int{
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt =$this->db->prepare("SELECT SUM(a.amout) AS total_expense
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id_categories
        WHERE u.id = :user_id
        AND a.type ='depense'
        AND c.id = :id_categories
        AND YEAR (a.actions_date) = :year
        AND MONTH (a.actions_date) = :month");
        
        $stmt->execute([
            'user_id' => $userId,
            'id_categories' => $categoriesId,
            'year' => $year,
            'month' => $month
        ]);
        
        $result = $stmt->fetch();
        
        return $result && $result['total_expense'] !== null 
        ? (int) $result['total_expense'] 
        : 0;
    }

    function getAllExpensesByCategories(int $userId, int $categoriesId): array {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt =$this->db->prepare("SELECT a.id, a.amout, a.action_date, c.name, a.libelle as category_name
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id_categories
        WHERE u.id = :user_id
        AND a.type ='depense'
        AND c.id = :id_categories
        AND YEAR (a.actions_date) = :year
        AND MONTH (a.actions_date) = :month
        ORDER BY a.action_date DESC");
        
        $stmt->execute([
            'user_id' => $userId,
            'id_categories' => $categoriesId,
            'year' => $year,
            'month' => $month
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function modifyExpense(int $expenseId, float $amount, string $actionDate, int $idCategories, string $libelle): bool {
        $sql = "UPDATE actions SET amount = :amount, action_date = :action_date, id_categories = :id_categories, libelle = :libelle WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $expenseId,    
            'amount' => $amount,
            'action_date' => $actionDate,
            'id_categories' => $idCategories,
            'libelle' => $libelle
        ]);
    }

    public function deleteExpense(int $expenseId): bool {
        $sql = "DELETE FROM actions WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $expenseId
        ]);
    }


    public function getAllCategories(): array {
    $sql = "SELECT id_categories, name FROM categories ORDER BY name ASC";

    $stmt = $this->db->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addExpense(float $amount, string $actionDate, int $idCategories, int $idUsers, string $libelle): bool {
    $sql = "INSERT INTO actions (amount, action_date, type, id_categories, id_users, libelle) 
            VALUES (:amount, :action_date, :type, :id_categories, :id_users, :libelle)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        ':amount'        => $amount,
        ':action_date'   => $actionDate,
        ':type'          => 'depense',
        ':id_categories' => $idCategories,
        ':id_users'      => $idUsers,
        ':libelle'       => $libelle,
    ]);

    $result = $stmt->fetch();
    
    return $result ? true : false;
    }

}   
