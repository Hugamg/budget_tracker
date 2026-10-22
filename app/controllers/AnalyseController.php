<?php
namespace App\Controllers;

use App\Repositories\AnalyseRepository;
use App\Repositories\UserRepository;
use PDO;

class AnalyseController {

    private AnalyseRepository $analyseRepo;
    private UserRepository $userRepositery;

    public function __construct(PDO $db) {
        $this->analyseRepo    = new AnalyseRepository($db);
        $this->userRepositery = new UserRepository($db);
    }

    public function index(int $userId, ?string $start = null, ?string $end = null): array {

        $user = $this->userRepositery->findById($userId);

        // Dates : valeur du filtre, sinon mois en cours
        $start = $this->sanitizeDate($start) ?? date('Y-m-01');
        $end   = $this->sanitizeDate($end)   ?? date('Y-m-d');

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        // Requêtes SQL
        $byCategory = $this->analyseRepo->getExpensesByCategoryBetween($userId, $start, $end);
        $top5       = $this->analyseRepo->getTopExpensesBetween($userId, $start, $end, 5);
        $monthly    = $this->analyseRepo->getMonthlyTotalsBetween($userId, $start, $end);

        // Total global
        $total = array_sum(array_column($byCategory, 'total'));

        // Tendance mensuelle (mois sans dépense = 0)
        $trendLabels = [];
        $trendData   = [];
        $cursor = new \DateTime($start);
        $cursor->modify('first day of this month');
        $last = new \DateTime($end);

        while ($cursor <= $last) {
            $key = $cursor->format('Y-m');
            $trendLabels[] = $this->getFrenchMonthAbbrev((int) $cursor->format('n'));
            $trendData[]   = (float) ($monthly[$key] ?? 0);
            $cursor->modify('+1 month');
        }
        $nbMonths = count($trendLabels);

        // Pourcentage par catégorie
        foreach ($byCategory as &$cat) {
            $cat['percent'] = $total > 0 ? round($cat['total'] / $total * 100) : 0;
        }
        unset($cat);

        return [
            'user'           => $user,
            'start'          => $start,
            'end'            => $end,
            'total'          => $total,
            'nbMonths'       => $nbMonths,
            'averageMonthly' => $nbMonths > 0 ? $total / $nbMonths : 0,
            'topCategory'    => ($byCategory[0]['total'] ?? 0) > 0 ? $byCategory[0] : null,
            'highestExpense' => $top5[0] ?? null,
            'byCategory'     => $byCategory,
            'top5'           => $top5,
            'trendLabels'    => $trendLabels,
            'trendData'      => $trendData,
        ];
    }

    // Valide un format Y-m-d (null si invalide)
    private function sanitizeDate(?string $date): ?string {
        if ($date === null || $date === '') {
            return null;
        }
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return ($d && $d->format('Y-m-d') === $date) ? $date : null;
    }

    private function getFrenchMonthAbbrev(int $monthNum): string {
        $months = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Aoû',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
        ];
        return $months[$monthNum] ?? '';
    }
}