<?php

namespace App\Controllers;

use App\Repositories\DashboardRepository;
use App\Repositories\UserRepository;
use DateTimeImmutable;
use PDO;

class DashboardController
{
    private DashboardRepository $dashboardRepo;
    private UserRepository $userRepository;

    public function __construct(PDO $db)
    {
        $this->dashboardRepo = new DashboardRepository($db);
        $this->userRepository = new UserRepository($db);
    }

    public function index(int $userId): array
    {
        $user = $this->userRepository->findById($userId);
        $budget = $this->dashboardRepo->getMonthlyBudgetNow($userId);
        $totalExpenses = $this->dashboardRepo->getMonthlyTotalExpenses($userId);

        $remainingBalance = $budget !== null
            ? $budget->getSalary() - $totalExpenses
            : -$totalExpenses;

        $monthSavings = $this->dashboardRepo->getMonthlyAmountSaving($userId);
        $totalSavings = $this->dashboardRepo->getTotalSavings($userId);
        $allCategories = $this->dashboardRepo->getAllCategoriesDepense();

        $totalExpensesByCategory = [];
        $expensesByCategory = [];

        foreach ($allCategories as $category) {
            $categoryId = $category->getId();

            $totalExpensesByCategory[$categoryId] =
                $this->dashboardRepo->getTotalExpenseByCategories($userId, $categoryId);

            $expensesByCategory[$categoryId] =
                $this->dashboardRepo->getAllExpensesByCategories($userId, $categoryId);
        }

        $expenses = $this->dashboardRepo->getAllExpenses($userId);

        // Récupère le message temporaire, puis le supprime de la session.
        $flash = $_SESSION['dashboard_flash'] ?? null;
        unset($_SESSION['dashboard_flash']);

        return [
            'user' => $user,
            'budget' => $budget,
            'totalExpenses' => $totalExpenses,
            'remainingBalance' => $remainingBalance,
            'monthSavings' => $monthSavings,
            'totalSavings' => $totalSavings,
            'allCategories' => $allCategories,
            'totalExpensesByCategory' => $totalExpensesByCategory,
            'expensesByCategory' => $expensesByCategory,
            'expenses' => $expenses,
            'flash' => $flash,
        ];
    }

    public function handlePost(int $userId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        $action = $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'add_expense':
                    $amount = (float) ($_POST['amount'] ?? 0);
                    $date = trim($_POST['date'] ?? '');
                    $categoryId = (int) ($_POST['category'] ?? 0);
                    $libelle = trim($_POST['libelle'] ?? '');

                    if (
                        $amount <= 0
                        || $categoryId <= 0
                        || $libelle === ''
                        || !$this->isValidDate($date)
                    ) {
                        http_response_code(400);
                        exit('Les informations de la dépense sont invalides.');
                    }

                    $saved = $this->dashboardRepo->addExpense(
                        $amount,
                        $date,
                        $categoryId,
                        $userId,
                        $libelle
                    );

                    if (!$saved) {
                        throw new \RuntimeException(
                            'La dépense n’a pas pu être enregistrée.'
                        );
                    }

                    $_SESSION['dashboard_flash'] = [
                        'modal' => 'add',
                        'message' => 'Dépense enregistrée avec succès.',
                    ];
                    break;

                case 'update_expense':
                    $saved = $this->dashboardRepo->modifyExpense(
                        (int) ($_POST['id'] ?? 0),
                        (float) ($_POST['amount'] ?? 0),
                        trim($_POST['date'] ?? ''),
                        (int) ($_POST['category'] ?? 0),
                        trim($_POST['libelle'] ?? ''),
                        $userId
                    );

                    if (!$saved) {
                        throw new \RuntimeException(
                            'La dépense n’a pas pu être modifiée.'
                        );
                    }

                    $_SESSION['dashboard_flash'] = [
                        'modal' => 'edit',
                        'message' => 'Dépense modifiée avec succès.',
                    ];
                    break;

                case 'delete_expense':
                    $deleted = $this->dashboardRepo->deleteExpense(
                        (int) ($_POST['id'] ?? 0),
                        $userId
                    );

                    if (!$deleted) {
                        throw new \RuntimeException(
                            'La dépense n’a pas pu être supprimée.'
                        );
                    }

                    $_SESSION['dashboard_flash'] = [
                        'modal' => null,
                        'message' => 'Dépense supprimée avec succès.',
                    ];
                    break;

                case 'update_budget':
                    $saved = $this->dashboardRepo->saveMonthlyBudget(
                        $userId,
                        (float) ($_POST['salary'] ?? 0),
                        (float) ($_POST['savings_goals'] ?? 0)
                    );

                    if (!$saved) {
                        throw new \RuntimeException(
                            'Les informations du budget n’ont pas pu être enregistrées.'
                        );
                    }

                    $_SESSION['dashboard_flash'] = [
                        'modal' => 'salary',
                        'message' => 'Informations du mois enregistrées avec succès.',
                    ];
                    break;

                default:
                    http_response_code(400);
                    exit(
                        'Action inconnue : '
                        . htmlspecialchars((string) $action, ENT_QUOTES, 'UTF-8')
                    );
            }
        } catch (\Throwable $e) {
            error_log('Erreur DashboardController : ' . $e->getMessage());
            http_response_code(500);
            exit('Une erreur est survenue lors de l’enregistrement.');
        }

        header('Location: /dashboard');
        exit;
    }

    private function isValidDate(string $date): bool
    {
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsedDate !== false
            && $parsedDate->format('Y-m-d') === $date;
    }
}