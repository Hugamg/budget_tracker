<?php

namespace App\Repositories;

use App\Models\Action;
use App\Models\Category;
use App\Models\MonthlyBudget;
use DateTimeImmutable;
use PDO;

class DashboardRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getTotalSavings(int $userId): ?float
    {
        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN type = 'epargne_versement' THEN amount ELSE 0 END)
                -
                SUM(CASE WHEN type = 'epargne_retrait' THEN amount ELSE 0 END)
                AS total_epargne
             FROM actions
             WHERE id_users = :user_id"
        );

        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row && $row['total_epargne'] !== null
            ? (float) $row['total_epargne']
            : null;
    }

    public function getMonthlyBudgetNow(int $userId): ?MonthlyBudget
    {
        $month = (new DateTimeImmutable('now'))->format('Y-m-01');

        $stmt = $this->db->prepare(
            "SELECT id, month_, salary, savings_goals, id_users
             FROM monthly_budget
             WHERE id_users = :user_id AND month_ = :month"
        );

        $stmt->execute([
            'user_id' => $userId,
            'month' => $month,
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

    public function getMonthlyAmountSaving(int $userId): float
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN type = 'epargne_versement' THEN amount ELSE 0 END)
                -
                SUM(CASE WHEN type = 'epargne_retrait' THEN amount ELSE 0 END)
                AS total_epargne
             FROM actions
             WHERE id_users = :user_id
               AND YEAR(action_date) = :year
               AND MONTH(action_date) = :month"
        );

        $stmt->execute([
            'user_id' => $userId,
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['total_epargne'] !== null
            ? (float) $result['total_epargne']
            : 0.0;
    }

    public function getMonthlyTotalExpenses(int $userId): float
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total_expense
             FROM actions
             WHERE id_users = :user_id
               AND type = 'depense'
               AND YEAR(action_date) = :year
               AND MONTH(action_date) = :month"
        );

        $stmt->execute([
            'user_id' => $userId,
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
        ]);

        return (float) $stmt->fetchColumn();
    }

    public function getTotalExpenseByCategories(int $userId, int $categoriesId): float
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total_expense
             FROM actions
             WHERE id_users = :user_id
               AND type = 'depense'
               AND id_categories = :id_categories
               AND YEAR(action_date) = :year
               AND MONTH(action_date) = :month"
        );

        $stmt->execute([
            'user_id' => $userId,
            'id_categories' => $categoriesId,
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
        ]);

        return (float) $stmt->fetchColumn();
    }

    /**
     * @return Action[]
     */
    public function getAllExpensesByCategories(int $userId, int $categoriesId): array
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->db->prepare(
            "SELECT
                a.id,
                a.amount,
                a.action_date,
                a.type,
                a.id_categories,
                c.nom AS category_name,
                c.color AS category_color,
                a.id_users,
                a.libelle
             FROM actions AS a
             INNER JOIN categories AS c ON a.id_categories = c.id
             WHERE a.id_users = :user_id
               AND a.type = 'depense'
               AND c.fonction = 'DPS'
               AND a.id_categories = :id_categories
               AND YEAR(a.action_date) = :year
               AND MONTH(a.action_date) = :month
             ORDER BY a.action_date DESC"
        );

        $stmt->execute([
            'user_id' => $userId,
            'id_categories' => $categoriesId,
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
        ]);

        return $this->mapRowsToActions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * @return Action[]
     */
    public function getAllExpenses(int $userId): array
    {
        $now = new DateTimeImmutable('now');

        $stmt = $this->db->prepare(
            "SELECT
                a.id,
                a.amount,
                a.action_date,
                a.type,
                a.id_categories,
                c.nom AS category_name,
                c.color AS category_color,
                a.id_users,
                a.libelle
             FROM actions AS a
             INNER JOIN categories AS c ON a.id_categories = c.id
             WHERE a.id_users = :user_id
               AND a.type = 'depense'
               AND c.fonction = 'DPS'
               AND YEAR(a.action_date) = :year
               AND MONTH(a.action_date) = :month
             ORDER BY a.action_date DESC"
        );

        $stmt->execute([
            'user_id' => $userId,
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
        ]);

        return $this->mapRowsToActions($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function modifyExpense(
        int $expenseId,
        float $amount,
        string $actionDate,
        int $idCategories,
        string $libelle,
        int $userId
    ): bool {
        $stmt = $this->db->prepare(
            "UPDATE actions
             SET amount = :amount,
                 action_date = :action_date,
                 id_categories = :id_categories,
                 libelle = :libelle
             WHERE id = :id
               AND id_users = :user_id
               AND type = 'depense'"
        );

        return $stmt->execute([
            'id' => $expenseId,
            'user_id' => $userId,
            'amount' => $amount,
            'action_date' => $actionDate,
            'id_categories' => $idCategories,
            'libelle' => $libelle,
        ]);
    }

    public function deleteExpense(int $expenseId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM actions
             WHERE id = :id
               AND id_users = :user_id
               AND type = 'depense'"
        );

        return $stmt->execute([
            'id' => $expenseId,
            'user_id' => $userId,
        ]);
    }

    public function addExpense(
        float $amount,
        string $actionDate,
        int $idCategories,
        int $idUsers,
        string $libelle
    ): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO actions
                (amount, action_date, type, id_categories, id_users, libelle)
             VALUES
                (:amount, :action_date, 'depense', :id_categories, :id_users, :libelle)"
        );

        return $stmt->execute([
            'amount' => $amount,
            'action_date' => $actionDate,
            'id_categories' => $idCategories,
            'id_users' => $idUsers,
            'libelle' => $libelle,
        ]);
    }

    /**
     * Crée ou met à jour le budget du mois courant.
     */
    public function saveMonthlyBudget(
        int $userId,
        float $salary,
        float $savingsGoals
    ): bool {
        $month = (new DateTimeImmutable('now'))->format('Y-m-01');

        $find = $this->db->prepare(
            "SELECT id
             FROM monthly_budget
             WHERE id_users = :user_id AND month_ = :month"
        );

        $find->execute([
            'user_id' => $userId,
            'month' => $month,
        ]);

        $id = $find->fetchColumn();

        if ($id !== false) {
            $stmt = $this->db->prepare(
                "UPDATE monthly_budget
                 SET salary = :salary,
                     savings_goals = :savings_goals
                 WHERE id = :id AND id_users = :user_id"
            );

            return $stmt->execute([
                'id' => (int) $id,
                'user_id' => $userId,
                'salary' => $salary,
                'savings_goals' => $savingsGoals,
            ]);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO monthly_budget
                (month_, salary, savings_goals, id_users)
             VALUES
                (:month, :salary, :savings_goals, :user_id)"
        );

        return $stmt->execute([
            'month' => $month,
            'salary' => $salary,
            'savings_goals' => $savingsGoals,
            'user_id' => $userId,
        ]);
    }

    /**
     * @return Category[]
     */
    public function getAllCategoriesDepense(): array
    {
        $stmt = $this->db->query(
            "SELECT id, nom, color
             FROM categories
             WHERE fonction = 'DPS'
             ORDER BY nom ASC"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn(array $row) => new Category(
                (int) $row['id'],
                $row['nom'],
                $row['color']
            ),
            $rows
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return Action[]
     */
    private function mapRowsToActions(array $rows): array
    {
        return array_map(
            fn(array $row) => new Action(
                (int) $row['id'],
                (float) $row['amount'],
                $row['action_date'],
                $row['type'],
                isset($row['id_categories']) ? (int) $row['id_categories'] : null,
                (int) $row['id_users'],
                $row['libelle'],
                $row['category_name'] ?? null,
                $row['category_color'] ?? null
            ),
            $rows
        );
    }
}