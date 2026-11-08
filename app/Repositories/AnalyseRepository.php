<?php
namespace App\Repositories;

use PDO;

class AnalyseRepository {

    private const EXPENSE_TYPE = 'depense';

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Répartition par catégorie (graphique, top catégorie, tableau, total)
    public function getExpensesByCategoryBetween(int $userId, string $start, string $end): array {
            $sql = "SELECT
                        c.id,
                        c.nom AS name,
                        c.color,
                        COUNT(a.id) AS nb_transactions,
                        COALESCE(SUM(a.amount), 0) AS total,
                        COALESCE(AVG(a.amount), 0) AS average
                    FROM categories c
                    LEFT JOIN actions a
                        ON a.id_categories = c.id
                        AND a.id_users = :userId
                        AND a.type = :type
                        AND a.action_date >= :start
                        AND a.action_date < DATE_ADD(:end, INTERVAL 1 DAY)
                    WHERE c.fonction = 'DPS'
                    GROUP BY c.id, c.nom, c.color
                    ORDER BY total DESC, c.nom ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'userId' => $userId,
                'type'   => self::EXPENSE_TYPE,
                'start'  => $start,
                'end'    => $end,
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    // Top N des dépenses les plus élevées
    public function getTopExpensesBetween(int $userId, string $start, string $end, int $limit = 5): array {
        $sql = "SELECT a.id,
                       a.amount,
                       a.action_date,
                       a.libelle  AS label,
                       c.nom      AS categorie,
                       c.color
                FROM actions a
                JOIN categories c ON c.id = a.id_categories
                WHERE a.id_users = :userId
                  AND a.type = :type
                  AND a.action_date >= :start
                  AND a.action_date <  DATE_ADD(:end, INTERVAL 1 DAY)
                ORDER BY a.amount DESC, a.action_date DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':type',   self::EXPENSE_TYPE);
        $stmt->bindValue(':start',  $start);
        $stmt->bindValue(':end',    $end);
        $stmt->bindValue(':limit',  $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Total par mois ('YYYY-MM' => montant) pour la tendance
    public function getMonthlyTotalsBetween(int $userId, string $start, string $end): array {
        $sql = "SELECT DATE_FORMAT(action_date, '%Y-%m') AS mois,
                       SUM(amount) AS total
                FROM actions
                WHERE id_users = :userId
                  AND type = :type
                  AND action_date >= :start
                  AND action_date <  DATE_ADD(:end, INTERVAL 1 DAY)
                GROUP BY mois
                ORDER BY mois ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':userId' => $userId,
            ':type'   => self::EXPENSE_TYPE,
            ':start'  => $start,
            ':end'    => $end,
        ]);

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}