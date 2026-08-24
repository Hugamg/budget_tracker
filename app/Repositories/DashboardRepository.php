<?php

namespace App\Repositories;

use App\Models\Action;
use App\Models\Category;
use App\Models\MonthlyBudget;
use PDO;
use DateTimeImmutable;

class DashboardRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getTotalSavings(int $userId): ?float {
        $stmt = $this->db->prepare("SELECT 
            SUM(CASE WHEN type = 'epargne_versement' THEN amount ELSE 0 END) 
            - 
            SUM(CASE WHEN type = 'epargne_retrait' THEN amount ELSE 0 END) 
            AS total_epargne
        FROM actions
        WHERE id_users = :user_id");

        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row && $row['total_epargne'] !== null ? (float) $row['total_epargne'] : null;
    }

    /**
     * Récupère le budget mensuel complet (salaire + objectif épargne) du mois en cours
     */
    public function getMonthlyBudgetNow(int $userId): ?MonthlyBudget {
        $currentDate = new DateTimeImmutable('now');
        $formattedMonth = $currentDate->format('Y-m-01');

        $stmt = $this->db->prepare("SELECT id, month_, salary, savings_goals, id_users
            FROM monthly_budget
            WHERE id_users = :user_id AND month_ = :month");

        $stmt->execute([
            'user_id' => $userId,
            'month' => $formattedMonth
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new MonthlyBudget(
            (int) $row['id'],
            $row['month_'],
            (float) $row['salary'],
            $row['savings_goals'] !== null ? (float) $row['savings_goals'] : null,
            (int) $row['id_users']
        );
    }

    /**
     * Somme des versements moins les retraits d'épargne du mois (agrégat SQL → float)
     */
    public function getMonthlyAmountSaving(int $userId): float {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT 
            SUM(CASE WHEN type = 'epargne_versement' THEN amount ELSE 0 END) 
            - 
            SUM(CASE WHEN type = 'epargne_retrait' THEN amount ELSE 0 END) 
            AS total_epargne
         FROM actions
         WHERE id_users = :user_id
           AND YEAR(action_date) = :year
           AND MONTH(action_date) = :month");

        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);

        $result = $stmt->fetch();

        return $result && $result['total_epargne'] !== null 
            ? (float) $result['total_epargne'] 
            : 0.0;
    }

    /**
     * Somme totale des dépenses du mois (agrégat SQL → float)
     */
    public function getMonthlyTotalExpenses(int $userId): float {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT SUM(amount) AS total_expense 
            FROM actions
            WHERE id_users = :user_id
            AND type = 'depense'
            AND YEAR(action_date) = :year
            AND MONTH(action_date) = :month");

        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);

        $result = $stmt->fetch();

        return $result && $result['total_expense'] !== null 
            ? (float) $result['total_expense'] 
            : 0.0;
    }

    /**
     * Somme des dépenses pour une catégorie donnée (agrégat SQL → float)
     */
    public function getTotalExpenseByCategories(int $userId, int $categoriesId): float {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT SUM(amount) AS total_expense
            FROM actions
            WHERE id_users = :user_id
            AND type = 'depense'
            AND id_categories = :id_categories
            AND YEAR(action_date) = :year
            AND MONTH(action_date) = :month");

        $stmt->execute([
            'user_id' => $userId,
            'id_categories' => $categoriesId,
            'year' => $year,
            'month' => $month
        ]);

        $result = $stmt->fetch();

        return $result && $result['total_expense'] !== null 
            ? (float) $result['total_expense'] 
            : 0.0;
    }

    /**
     * Retourne toutes les dépenses d'une catégorie → tableau d'objets Action
     * @return Action[]
     */
    public function getAllExpensesByCategories(int $userId, int $categoriesId): array {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT id, amount, action_date, type, id_categories, id_users, libelle
            FROM actions
            WHERE id_users = :user_id
            AND type = 'depense'
            AND id_categories = :id_categories
            AND YEAR(action_date) = :year
            AND MONTH(action_date) = :month
            ORDER BY action_date DESC");

        $stmt->execute([
            'user_id' => $userId,
            'id_categories' => $categoriesId,
            'year' => $year,
            'month' => $month
        ]);

        return $this->mapRowsToActions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Retourne toutes les dépenses du mois, toutes catégories confondues
     * @return Action[]
     */
    public function getAllExpenses(int $userId): array {
        $currentDate = new DateTimeImmutable('now');
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $stmt = $this->db->prepare("SELECT id, amount, action_date, type, id_categories, id_users, libelle
            FROM actions
            WHERE id_users = :user_id
            AND type = 'depense'
            AND YEAR(action_date) = :year
            AND MONTH(action_date) = :month
            ORDER BY action_date DESC");

        $stmt->execute([
            'user_id' => $userId,
            'year' => $year,
            'month' => $month
        ]);

        return $this->mapRowsToActions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function modifyExpense(int $expenseId, float $amount, string $actionDate, int $idCategories, string $libelle): bool {
        $stmt = $this->db->prepare("UPDATE actions 
            SET amount = :amount, action_date = :action_date, id_categories = :id_categories, libelle = :libelle 
            WHERE id = :id");

        return $stmt->execute([
            'id' => $expenseId,    
            'amount' => $amount,
            'action_date' => $actionDate,
            'id_categories' => $idCategories,
            'libelle' => $libelle
        ]);
    }

    public function deleteExpense(int $expenseId): bool {
        $stmt = $this->db->prepare("DELETE FROM actions WHERE id = :id");
        return $stmt->execute(['id' => $expenseId]);
    }

    public function addExpense(float $amount, string $actionDate, int $idCategories, int $idUsers, string $libelle): bool {
        $stmt = $this->db->prepare("INSERT INTO actions (amount, action_date, type, id_categories, id_users, libelle) 
                VALUES (:amount, :action_date, :type, :id_categories, :id_users, :libelle)");

        return $stmt->execute([
            'amount'        => $amount,
            'action_date'   => $actionDate,
            'type'          => 'depense',
            'id_categories' => $idCategories,
            'id_users'      => $idUsers,
            'libelle'       => $libelle,
        ]);
    }

    /**
     * @return Category[]
     */
    public function getAllCategories(): array {
        $stmt = $this->db->query("SELECT id, nom, color FROM categories ORDER BY nom ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => new Category(
            (int) $row['id'],
            $row['nom'],
            $row['color']
        ), $rows);
    }

    /**
     * @param array $rows
     * @return Action[]
     */
    private function mapRowsToActions(array $rows): array {
        return array_map(fn(array $row) => new Action(
            (int) $row['id'],
            (float) $row['amount'],
            $row['action_date'],
            $row['type'],
            $row['id_categories'] !== null ? (int) $row['id_categories'] : null,
            (int) $row['id_users'],
            $row['libelle']
        ), $rows);
    }
}