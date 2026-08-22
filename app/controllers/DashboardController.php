<?php
namespace App\Controllers;

use App\Repositeries\DashboardRepositery;
use PDO;
use DateTimeImmutable;

class DashboardController {
    
    private DashboardRepositery $dashboardRepo;

    public function __construct(PDO $db) {
        $this->dashboardRepo = new DashboardRepositery($db);
    }

    public function index(int $userId): void {

        session_start();
        
        // Sécurité : redirige si pas connecté
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
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
        
        // 8. Charger la vue avec toutes ces variables
        require __DIR__ . '/../views/dashboard/dashboard.view.php';
    }
}



