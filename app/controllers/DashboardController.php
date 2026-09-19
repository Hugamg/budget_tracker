<?php
namespace App\Controllers;

use App\Repositories\DashboardRepository;
use App\Repositories\UserRepository;
use PDO;


class DashboardController {
    
    private DashboardRepository $dashboardRepo; 
    private UserRepository $userRepositery;

    public function __construct(PDO $db) {
        $this->dashboardRepo = new DashboardRepository($db);
        $this->userRepositery = new UserRepository($db);  
    }

    public function index(int $userId): ?array {
        
        // Sécurité : redirige si pas connecté
        // if (!isset($_SESSION['user_id'])) {
        //     header('Location: /dashboard.view.php');
        //     exit;
        // }
        
        // Récupérer l'utilisateur
        $user = $this->userRepositery->findById($userId);
        
        // ici on va construire les données
        // 1. Récupérer le budget du mois
        $budget = $this->dashboardRepo->getMonthlyBudgetNow($userId);
        
        // 2. Récupérer le total des dépenses du mois
        $totalExpenses = $this->dashboardRepo->getMonthlyTotalExpenses($userId);
        
        // 3. Calculer le solde restant
        $remainingBalance = $budget !== null ? $budget->getSalary() - $totalExpenses : -$totalExpenses;
        
        // 4. Récupérer l'épargne du mois
        $monthSavings = $this->dashboardRepo->getMonthlyAmountSaving($userId);

        $totalSavings = $this->dashboardRepo->getTotalSavings($userId);
        
        // 5. Récupérer toutes les catégories
        $allCategories = $this->dashboardRepo->getAllCategories();
        
        // 6. Pour chaque catégorie, calculer le total dépensé (pour le graphique)
        $totalExpensesByCategory = [];
        foreach($allCategories as $category){
            $totalExpensesByCategory[$category->getId()] = $this->dashboardRepo->getTotalExpenseByCategories($userId, $category->getId());
        }

        // 7. Récupérer les dépenses par catégorie
        $expensesByCategory = [];
        foreach($allCategories as $category){
            $expensesByCategory[$category->getId()] = $this->dashboardRepo->getAllExpensesByCategories($userId, $category->getId());
        }
        
        // 8. Récupérer la liste des dépenses (tableau du bas)
        $expenses = $this->dashboardRepo->getAllExpenses($userId);
        
        // 9. Retourner les données
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
            'expenses' => $expenses
        ];
    }

    public function updateExpense(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->dashboardRepo->modifyExpense(
            (int)$_POST['id'],
            (float)$_POST['amount'],
            $_POST['date'],
            (int)$_POST['category'],
            $_POST['libelle']
        );
    }
    header('Location: /dashboard');
    exit;
    }

    public function deleteExpense(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->dashboardRepo->deleteExpense((int)$_POST['id']);
        }
        header('Location: /dashboard');
        exit;
    }

    public function addExpense(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? 1; // adapte selon ta gestion de session
            $this->dashboardRepo->addExpense(
                (float)$_POST['amount'],
                $_POST['date'],
                (int)$_POST['category'],
                $userId,
                $_POST['libelle']
            );
        }
        header('Location: /dashboard');
        exit;
    }
}