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
        $savings = $this->dashboardRepo->getMonthlyAmountSaving($userId);
        
        // 5. Récupérer toutes les catégories
        $allCategories = $this->dashboardRepo->getAllCategories();
        
        // 6. Pour chaque catégorie, calculer le total dépensé (pour le graphique)
        $totalExpensesByCategory = [];
        foreach($allCategories as $category){
            $totalExpensesByCategory[$category->getId()] = $this->dashboardRepo->getTotalExpenseByCategories($userId, $category->getId());
        }
        
        // 7. Récupérer la liste des dépenses (tableau du bas)
        $expenses = $this->dashboardRepo->getAllExpenses($userId);
        
        // 8. Retourner les données
        return [
            'user' => $user,
            'budget' => $budget,
            'totalExpenses' => $totalExpenses,
            'remainingBalance' => $remainingBalance,
            'savings' => $savings,
            'allCategories' => $allCategories,
            'totalExpensesByCategory' => $totalExpensesByCategory,
            'expenses' => $expenses
        ];
    }
}



