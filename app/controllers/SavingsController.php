<?php

namespace App\Controllers;

use App\Repositories\SavingsRepository;
use App\Repositories\UserRepository;
use App\Repositories\DashboardRepository;
use DateTimeImmutable;
use PDO;
use Throwable;

class SavingsController
{
    private SavingsRepository $savingsRepo;
    private UserRepository $userRepository;
    private DashboardRepository $dashboardRepo;

    public function __construct(PDO $db)
    {
        $this->savingsRepo = new SavingsRepository($db);
        $this->userRepository = new UserRepository($db);
        $this->dashboardRepo = new DashboardRepository($db);
    }

    public function index(int $userId): array
    {
        $user = $this->userRepository->findById($userId);

        $totalSavings = $this->savingsRepo->getTotalAmoutSaved($userId);
        $monthSavings = $this->dashboardRepo->getMonthlyAmountSaving($userId);
        $allCategories = $this->savingsRepo->getAllCategoriesEpargne();
        $monthSavingsGoal = $this->savingsRepo->getMonthlyGoal($userId);

        $totalSavingsByCategories = [];

        foreach ($allCategories as $category) {
            $categoryId = $category->getId();

            $totalSavingsByCategories[$categoryId] =
                $this->savingsRepo->getTotalAmountSaveByCategories(
                    $userId,
                    $categoryId
                ) ?? 0;
        }

        $averageMonthlySavings =
            $this->savingsRepo->getAverageMonthlySavings($userId);

        $lastWithdrawalData =
            $this->savingsRepo->getLastWithdrawalWithDate($userId);

        $savingsDeposits =
            $this->savingsRepo->getAllSavingsByTypeSavingDeposits($userId);

        $savingsWithdrawals =
            $this->savingsRepo->getAllSavingsByWithdrawals($userId);

        $chartData = $this->getChartData($userId, $allCategories);

        // Message temporaire affiché après l'ajout.
        $flash = $_SESSION['savings_flash'] ?? null;
        unset($_SESSION['savings_flash']);

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
            'flash' => $flash,
        ];
    }

    public function handlePost(int $userId): void
    {
        $action = $_POST['action'] ?? '';

        if ($action !== 'add_saving') {
            header('Location: /savings');
            exit;
        }

        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $date = $_POST['date'] ?? '';
        $type = $_POST['type'] ?? '';
        $categoryId = filter_var(
            $_POST['category'] ?? null,
            FILTER_VALIDATE_INT
        );

        $validDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if (
            $amount === false
            || $amount <= 0
            || $categoryId === false
            || $categoryId <= 0
            || !$validDate
            || $validDate->format('Y-m-d') !== $date
            || !in_array($type, ['epargne_versement', 'epargne_retrait'], true)
        ) {
            $_SESSION['savings_flash'] = [
                'message' => 'Vérifie les informations saisies.',
                'success' => false,
            ];

            header('Location: /savings');
            exit;
        }

        try {
            $this->savingsRepo->addSavings(
                $userId,
                (float) $amount,
                $date,
                $type,
                (int) $categoryId
            );

            $_SESSION['savings_flash'] = [
                'message' => 'Mouvement d’épargne enregistré avec succès.',
                'success' => true,
            ];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());

            $_SESSION['savings_flash'] = [
                'message' => 'Impossible d’enregistrer ce mouvement.',
                'success' => false,
            ];
        }

        header('Location: /savings');
        exit;
    }

    /**
     * Construit les données du graphique cumulé sur les six derniers mois.
     */
    private function getChartData(int $userId, array $categories): array
    {
        $start = new \DateTimeImmutable('first day of this month');
        $start = $start->modify('-5 months');
        $startKey = $start->format('Y-m');

        $labels = [];
        $monthKeys = [];

        for ($i = 0; $i < 6; $i++) {
            $month = $start->modify("+{$i} months");
            $labels[] = $this->getFrenchMonthAbbrev((int) $month->format('n'));
            $monthKeys[] = $month->format('Y-m');
        }

        $deposits =
            $this->savingsRepo->getAllSavingsByTypeSavingDeposits($userId);

        $withdrawals =
            $this->savingsRepo->getAllSavingsByWithdrawals($userId);

        $datasets = [];

        foreach ($categories as $category) {
            $categoryId = $category->getId();
            $monthlyNet = array_fill(0, 6, 0.0);
            $openingBalance = 0.0;

            foreach ($deposits as $deposit) {
                if ((int) $deposit['id_categories'] !== $categoryId) {
                    continue;
                }

                $month = substr($deposit['action_date'], 0, 7);
                $amount = (float) $deposit['amount'];

                if ($month < $startKey) {
                    $openingBalance += $amount;
                } else {
                    $index = array_search($month, $monthKeys, true);
                    if ($index !== false) {
                        $monthlyNet[$index] += $amount;
                    }
                }
            }

            foreach ($withdrawals as $withdrawal) {
                if ((int) $withdrawal['id_categories'] !== $categoryId) {
                    continue;
                }

                $month = substr($withdrawal['action_date'], 0, 7);
                $amount = (float) $withdrawal['amount'];

                if ($month < $startKey) {
                    $openingBalance -= $amount;
                } else {
                    $index = array_search($month, $monthKeys, true);
                    if ($index !== false) {
                        $monthlyNet[$index] -= $amount;
                    }
                }
            }

            $runningTotal = $openingBalance;
            $cumulativeData = [];

            foreach ($monthlyNet as $monthlyAmount) {
                $runningTotal += $monthlyAmount;
                $cumulativeData[] = round($runningTotal, 2);
            }

            $hasData = count(array_filter(
                $cumulativeData,
                static fn($value) => $value != 0
            )) > 0;

            $color = $category->getColor();

            $datasets[] = [
                'label' => $category->getName(),
                'data' => $cumulativeData,
                'borderColor' => $color,
                'backgroundColor' => $color . '40',
                'borderWidth' => 3,
                'tension' => 0.3,
                'fill' => true,
                'pointRadius' => 4,
                'pointBackgroundColor' => $color,
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

    private function getFrenchMonthAbbrev(int $monthNum): string
    {
        $months = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Aoû',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc',
        ];

        return $months[$monthNum] ?? '';
    }
}