<?php
namespace App\Controllers;

use App\Repositories\SavingsRepository;
use App\Repositories\UserRepository;
use App\Repositories\DashboardRepository;
use PDO;

class SavingsController {

    private SavingsRepository $savingsRepo; 
    private UserRepository $userRepositery;
    private DashboardRepository $dashboardRepo;

    public function __construct(PDO $db) {
        $this->savingsRepo = new SavingsRepository($db);
        $this->userRepositery = new UserRepository($db);  
        $this->dashboardRepo = new DashboardRepository($db);
    }

    public function index(int $userId): ?array {

        $user = $this->userRepositery->findById($userId);

        // 1. Récupérer le total des épargnes
        $totalSavings = $this->savingsRepo->getTotalAmoutSaved($userId);

        // 2. Récupérer l'épargne du mois courant
        $monthSavings = $this->dashboardRepo->getMonthlyAmountSaving($userId);

        // 3. Récupérer toutes les catégories d'épargne
        $allCategories = $this->savingsRepo->getAllCategoriesEpargne();  

        // 4. Récupérer l'objectif mensuel
        $monthSavingsGoal = $this->savingsRepo->getMonthlyGoal($userId);

        // 5. Pour CHAQUE catégorie, calculer le total épargné
        $totalSavingsByCategories = [];
        foreach ($allCategories as $category) {
            $totalSavingsByCategories[$category->getId()] = $this->savingsRepo->getTotalAmountSaveByCategories($userId, $category->getId()) ?? 0;
        }

        // 6. Récupérer l'épargne MOYENNE des 3 derniers mois
        $averageMonthlySavings = $this->savingsRepo->getAverageMonthlySavings($userId);

        // 7. Récupérer le dernier retrait avec sa date
        $lastWithdrawalData = $this->savingsRepo->getLastWithdrawalWithDate($userId);

        // 8. Récupérer les dépôts d'épargne
        $savingsDeposits = $this->savingsRepo->getAllSavingsByTypeSavingDeposits($userId);

        // 9. Récupérer les retraits d'épargne
        $savingsWithdrawals = $this->savingsRepo->getAllSavingsByWithdrawals($userId);

        // 10. DONNÉES POUR LE GRAPHIQUE MULTI-CATÉGORIES (6 derniers mois)
        $chartData = $this->getChartData($userId, $allCategories);

        // 11. Retourner les données
        return [
            'user' => $user,
            'totalSavings' => $totalSavings,
            'monthSavings' => $monthSavings,
            'allCategories' => $allCategories,
            'monthSavingsGoal' => $monthSavingsGoal,
            'totalSavingsByCategories' => $totalSavingsByCategories,
            'averageMonthlySavings' => $averageMonthlySavings,  
            'lastWithdrawalData' => $lastWithdrawalData,
            'savingsDeposits' => $savingsDeposits,
            'savingsWithdrawals' => $savingsWithdrawals,
            'chartData' => $chartData,
        ];
    }

    /**
 * Construit les données pour le graphique CUMULÉ multi-catégories sur 6 mois
 */
private function getChartData(int $userId, array $categories): array {
    $start = new \DateTime();
    $start->modify('-5 months')->modify('first day of this month');
    $startKey = $start->format('Y-m'); // ex: "2026-05"

    $labels = [];
    $monthKeys = [];

    for ($i = 0; $i < 6; $i++) {
        $clone = clone $start;
        $clone->modify("+$i months");
        $labels[] = $this->getFrenchMonthAbbrev((int)$clone->format('n'));
        $monthKeys[] = $clone->format('Y-m');
    }

    $allDeposits = $this->savingsRepo->getAllSavingsByTypeSavingDeposits($userId);
    $allWithdrawals = $this->savingsRepo->getAllSavingsByWithdrawals($userId);

    $datasets = [];

    foreach ($categories as $category) {
        $catId = $category->getId();
        $catColor = $category->getColor();

        $monthlyNet = array_fill(0, 6, 0);
        $openingBalance = 0; // ✨ solde AVANT la fenêtre des 6 mois

        // Versements
        foreach ($allDeposits as $deposit) {
            if (($deposit['id_categories'] ?? null) != $catId) continue;

            $month = substr($deposit['action_date'], 0, 7);
            $amount = floatval($deposit['amount']);

            if ($month < $startKey) {
                $openingBalance += $amount;          // ✨ avant la fenêtre
            } else {
                $idx = array_search($month, $monthKeys);
                if ($idx !== false) $monthlyNet[$idx] += $amount;
            }
        }

        // Retraits
        foreach ($allWithdrawals as $withdrawal) {
            if (($withdrawal['id_categories'] ?? null) != $catId) continue;

            $month = substr($withdrawal['action_date'], 0, 7);
            $amount = floatval($withdrawal['amount']);

            if ($month < $startKey) {
                $openingBalance -= $amount;          // ✨ avant la fenêtre
            } else {
                $idx = array_search($month, $monthKeys);
                if ($idx !== false) $monthlyNet[$idx] -= $amount;
            }
        }

        // Cumul à partir du solde d'ouverture
        $cumulativeData = [];
        $runningTotal = $openingBalance; // ✨ plus de départ à 0
        foreach ($monthlyNet as $monthAmount) {
            $runningTotal += $monthAmount;
            $cumulativeData[] = round($runningTotal, 2); // ✨ plus de max(0, ...)
        }

        $hasData = end($cumulativeData) > 0;

        $datasets[] = [
            'label' => $category->getName(),
            'data' => $cumulativeData,
            'borderColor' => $catColor,
            'backgroundColor' => $catColor . '40',
            'borderWidth' => 3,
            'tension' => 0.3,
            'fill' => true,
            'pointRadius' => 4,
            'pointBackgroundColor' => $catColor,
            'pointBorderColor' => '#1C1A17',
            'pointBorderWidth' => 2,
            'pointHoverRadius' => 7,
            'hidden' => !$hasData,
        ];
    }

    return [
        'labels' => $labels,
        'datasets' => $datasets,
    ];
}

    /**
     * Retourne l'abréviation française d'un mois
     */
    private function getFrenchMonthAbbrev(int $monthNum): string {
        $months = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Aoû',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
        ];
        return $months[$monthNum] ?? '';
    }

}