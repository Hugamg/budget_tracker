<?php

namespace App\Repositories;
use App\Models\Action;
use App\Models\Category;
use App\Models\MonthlyBudget;
use PDO;
use DateTimeImmutable;


class SavingsRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getTotalAmoutSaved(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT 
            SUM(CASE WHEN actions.type = 'epargne_versement' THEN actions.amount ELSE 0 END) 
            -
            SUM(CASE WHEN actions.type = 'epargne_retrait' THEN actions.amount ELSE 0 END) 
            AS total_epargne 
        FROM actions 
        INNER JOIN users ON actions.id_users = users.id
        WHERE users.id = :user_id");

        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ? (int) $result['total_epargne'] : null;
    }

    public function getLastWithdrawal(int $userId): ?int {
        $currentDay = new DateTimeImmutable('now');
        $year = $currentDay->format('Y');
        $month = $currentDay->format('m');

        $stmt = $this->db->prepare("SELECT amount 
        FROM actions 
        WHERE id_users = :user_id 
        AND type = 'epargne_retrait' 
        AND YEAR(action_date) = :year 
        AND MONTH(action_date) = :month 
        ORDER BY action_date DESC 
        LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'year' => $year, 'month' => $month]);
        $result = $stmt->fetch();
        return $result ? (int) $result['amount'] : null;
    } 

    public function getTotalAmountSaveByCategories(int $userId, int $categoryId): ?int {
        $stmt = $this->db->prepare("SELECT SUM(a.amount) AS total 
        FROM actions AS a
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id
        WHERE u.id = :user_id 
        AND a.type = 'epargne_versement'
        AND c.id = :category_id");
        $stmt->execute(['user_id' => $userId, 'category_id' => $categoryId]);
        $result = $stmt->fetch();
        return $result ? (int) $result['total'] : null;
    }

    public function getAllSavingsByTypeSavingDeposits(int $userId): array {
        $stmt = $this->db->prepare("SELECT a.id, a.amount, a.action_date, a.id_categories, c.nom AS category_name, a.type AS saving_deposits
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id
        WHERE u.id = :user_id
        AND a.type = 'epargne_versement'
        ORDER BY a.action_date DESC 
        LIMIT 50"); // augmenté à 50 pour voir plus de données

        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSavingsByWithdrawals(int $userId): array {

        $stmt = $this->db->prepare("SELECT a.id, a.amount, a.action_date, a.id_categories, c.nom AS category_name, a.type AS saving_withdrawal
        FROM actions AS a 
        INNER JOIN users AS u ON a.id_users = u.id
        INNER JOIN categories AS c ON a.id_categories = c.id
        WHERE u.id = :user_id
        AND a.type = 'epargne_retrait'
        ORDER BY a.action_date DESC 
        LIMIT 5");

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyGoal(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT savings_goals 
        FROM monthly_budget
        WHERE id_users = :user_id 
        AND YEAR(month_) = :year 
        AND MONTH(month_) = :month 
        LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'year' => date('Y'), 'month' => date('m')]);
        $result = $stmt->fetch();
        return $result ? (int) $result['savings_goals'] : null;
    }

    /**
     * Récupère l'épargne moyenne mensuelle sur les 3 derniers mois
     * (on exclut le mois courant pour avoir une vrai moyenne)
     */
    public function getAverageMonthlySavings(int $userId): ?float {
        $stmt = $this->db->prepare("
            SELECT AVG(monthly_total) AS average_savings
            FROM (
                SELECT 
                    YEAR(a.action_date) AS year,
                    MONTH(a.action_date) AS month,
                    SUM(CASE WHEN a.type = 'epargne_versement' THEN a.amount ELSE 0 END)
                    -
                    SUM(CASE WHEN a.type = 'epargne_retrait' THEN a.amount ELSE 0 END) 
                    AS monthly_total
                FROM actions a
                WHERE a.id_users = :user_id
                AND a.type IN ('epargne_versement', 'epargne_retrait')
                AND DATE_FORMAT(a.action_date, '%Y-%m') < DATE_FORMAT(NOW(), '%Y-%m')
                GROUP BY year, month
                ORDER BY year DESC, month DESC
                LIMIT 3
            ) AS last_3_months
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result && $result['average_savings'] !== null ? (float) $result['average_savings'] : null;
    }

    /**
     * Récupère la date et le montant du dernier retrait d'épargne (tous les temps)
     */
    public function getLastWithdrawalWithDate(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT a.action_date, a.amount
            FROM actions a
            WHERE a.id_users = :user_id 
            AND a.type = 'epargne_retrait'
            ORDER BY a.action_date DESC 
            LIMIT 1
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * @return Category[]
     */
    public function getAllCategoriesEpargne(): array {
        $stmt = $this->db->query("SELECT id, nom, color FROM categories WHERE fonction = 'EPG' ORDER BY nom ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => new Category(
            (int) $row['id'],
            $row['nom'],
            $row['color']
        ), $rows);
    }


    /**
     * Récupère le solde cumulé mois par mois pour chaque catégorie d'épargne
     * Retourne: [categoryId => ['YYYY-MM' => soldeNet, ...], ...]
     */
    public function getMonthlyCumulativeSavingsByCategory(int $userId): array {
        // Récupère tous les mouvements (versements + retraits) des 6 derniers mois
        $stmt = $this->db->prepare("
            SELECT 
                a.id_categories,
                DATE_FORMAT(a.action_date, '%Y-%m') AS month_key,
                SUM(CASE 
                    WHEN a.type = 'epargne_versement' THEN a.amount 
                    WHEN a.type = 'epargne_retrait' THEN -a.amount 
                    ELSE 0 
                END) AS net_amount
            FROM actions a
            WHERE a.id_users = :user_id
            AND a.type IN ('epargne_versement', 'epargne_retrait')
            AND a.action_date >= DATE_FORMAT(NOW() - INTERVAL 5 MONTH, '%Y-%m-01')
            GROUP BY a.id_categories, DATE_FORMAT(a.action_date, '%Y-%m')
            ORDER BY month_key ASC
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Réorganiser: [catId => ['2026-04' => 150, '2026-05' => 200, ...]]
        $result = [];
        foreach ($rows as $row) {
            $catId = (int) $row['id_categories'];
            $monthKey = $row['month_key'];
            $amount = (float) $row['net_amount'];
            
            $result[$catId][$monthKey] = $amount;
        }
        
        return $result;
    }

    public function addSavings(
        int $userId,
        float $amount,
        string $date,
        string $type,
        int $categoryId
    ): bool {
        if (!in_array($type, ['epargne_versement', 'epargne_retrait'], true)) {
            throw new \InvalidArgumentException('Type de mouvement invalide.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO actions (
                amount,
                action_date,
                id_categories,
                id_users,
                type
            ) VALUES (
                :amount,
                :action_date,
                :category_id,
                :user_id,
                :type
            )
        ");

        return $stmt->execute([
            'amount' => $amount,
            'action_date' => $date,
            'category_id' => $categoryId,
            'user_id' => $userId,
            'type' => $type,
        ]);
    }
}
