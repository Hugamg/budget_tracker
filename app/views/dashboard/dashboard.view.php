<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Tableau de bord</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="app-layout">

    <!-- ===== MAIN ===== -->
    <main class="main">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <p class="topbar__label"><?= date('F Y'); ?></p>
                <h2 class="topbar__title">
                    Bonjour, <?= isset($user) && $user !== null ? htmlspecialchars($user->getFirstName()) : 'Utilisateur' ?>
                </h2>
            </div>
            <button class="btn btn--primary" id="openAddModal">+ Nouvelle dépense</button>
        </header>

        <!-- ===== STAT CARDS ===== -->
        <section class="stats-grid">

            <div class="stat-card stat-card--balance">
                <p class="stat-card__label">Solde restant</p>
                <p class="stat-card__value">
                    <?= isset($remainingBalance) && $remainingBalance != null ? $remainingBalance : 0 ?> €
                </p>
                <p class="stat-card__hint">
                    sur <?= isset($budget) && $budget != null ? $budget->getSalary() : 0 ?> € de paye
                </p>
                <div class="progress">
                    <div class="progress__bar" style="width:80%"></div>
                </div>
                <button class="btn btn--primary btn--small" id="openEditSalaryModal">
                    Modifier vos informations du mois
                </button>
            </div>

            <div class="stat-card stat-card--savings">
                <p class="stat-card__label">Épargne du mois</p>
                <p class="stat-card__value">
                    <?= isset($monthSavings) && $monthSavings != null ? $monthSavings : 0 ?> €
                </p>
                <p class="stat-card__hint">
                    <?php
                    switch (true) {
                        case isset($monthSavings) && $monthSavings == null:
                            echo "Aucune valeur n'as encore été enregistrée";
                            break;
                        case isset($monthSavings) && isset($budget) && $budget != null && $monthSavings < $budget->getSavingsGoals():
                            echo "Reste " . "<b>" . (isset($budget) && $budget != null ? ($budget->getSavingsGoals() - $monthSavings) : 0) . "</b>" . " € à épargner";
                            break;
                        default:
                            echo "Objectif atteint ✔";
                            break;
                    }
                    ?>
                </p>
            </div>

            <div class="stat-card stat-card--spent">
                <p class="stat-card__label">Dépensé ce mois</p>
                <p class="stat-card__value">
                    <?= isset($totalExpenses) && $totalExpenses != null ? $totalExpenses : 0 ?> €
                </p>
                <p class="stat-card__hint">
                    <?= isset($budget) && $budget != null && $budget->getSalary() != 0 && isset($totalExpenses) && $totalExpenses != null
                        ? round(($totalExpenses / $budget->getSalary()) * 100, 1)
                        : 0 ?> % de la paye
                </p>
            </div>

            <div class="stat-card stat-card--alert">
                <p class="stat-card__label">État épargne</p>
                <p class="stat-card__value">
                    <?= isset($totalSavings) && $totalSavings == null
                        ? "Aucune épargne présente"
                        : number_format($totalSavings ?? 0, 2, ',', ' ') . " €" ?>
                </p>
            </div>

        </section>

        <!-- ===== MIDDLE : CHART + BREAKDOWN ===== -->
        <section class="content-grid">

            <!-- Chart -->
            <div class="panel">
                <div class="panel__head">
                    <h3>Répartition des dépenses</h3>
                    <span class="badge"><?= date('F Y'); ?></span>
                </div>
                <div class="chart-wrap">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>

            <!-- Breakdown list -->
            <div class="panel">
                <div class="panel__head">
                    <h3>Par catégorie</h3>
                </div>
                <ul class="breakdown">
                    <?php
                        $hasExpenses = false;
                    ?>
                    <?php if (isset($allCategories) && !empty($allCategories)): ?>
                        <?php foreach ($allCategories as $category): ?>
                            <?php
                                $categoryAmount = $totalExpensesByCategory[$category->getId()] ?? 0;
                                if ($categoryAmount <= 0) continue; // on saute les catégories sans dépense
                                $hasExpenses = true;
                                $percentage = isset($totalExpenses) && $totalExpenses > 0
                                    ? round(($categoryAmount / $totalExpenses) * 100, 1)
                                    : 0;
                            ?>
                            <li class="breakdown__item">
                                <span class="cat-tag" style="background-color: <?= htmlspecialchars($category->getColor()) ?>;"></span>
                                <span class="breakdown__name"><?= htmlspecialchars($category->getName()) ?></span>
                                <span class="breakdown__pct"><?= $percentage ?> %</span>
                                <span class="breakdown__amount"><?= number_format($categoryAmount, 2, ',', ' ') ?> €</span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!$hasExpenses): ?>
                        <li class="breakdown__item">
                            <span class="breakdown__name">Aucune dépense ce mois-ci</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

        </section>

        <!-- ===== EXPENSE LIST ===== -->
        <section class="panel">

            <div class="panel__head panel__head--filters">
                <h3>Dépenses du mois</h3>
                <div class="filters">
                    <button class="chip chip--active" style="background-color: var(--bg);" data-cat="all">
                        Toutes
                    </button>
                    <?php foreach ($allCategories as $category): ?>
                        <button
                            class="chip"
                            style="background-color: <?= htmlspecialchars($category->getColor()) ?>;"
                            data-cat="<?= $category->getId() ?>"
                        >
                            <?= htmlspecialchars($category->getName()) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <table class="expense-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Libellé</th>
                        <th>Catégorie</th>
                        <th>Montant</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>

                <!-- Bloc "Toutes les dépenses" -->
                <tbody class="cat-group" data-cat="all">
                    <?php if (isset($expenses) && !empty($expenses)): ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?= $expense->getFormattedDate() ?></td>
                                <td><?= htmlspecialchars($expense->getLibelle()) ?></td>
                                <td>
                                    <span style="background-color: <?= $expense->getCategoryColor() ?>;" class="pill cat--<?= strtolower(str_replace(' ', '-', $expense->getCategoryName())) ?>">
                                        <?= htmlspecialchars($expense->getCategoryName()) ?>
                                    </span>
                                </td>
                                <td class="amount"><?= number_format($expense->getAmount(), 2, ',', ' ') ?> €</td>
                                <td class="col-actions">
                                    <button class="icon-btn icon-btn--edit" title="Modifier" data-id="<?= $expense->getId()?>" data-libelle="<?= $expense->getLibelle() ?>" data-amount="<?= $expense->getAmount() ?>" data-date="<?= $expense->getActionDate() ?>" data-category="<?= $expense->getIdCategories()?>">✎</button>
                                    <form method="POST" action="/expense/delete" style="display:inline;" onsubmit="return confirm('Supprimer cette dépense ?');">
                                        <input type="hidden" name="id" value="<?= $expense->getId() ?>">
                                        <button type="submit" class="icon-btn icon-btn--delete" title="Supprimer">✕</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucune dépense enregistrée ce mois-ci.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <!-- Un bloc par catégorie -->
                <?php foreach ($allCategories as $category): ?>
                    <?php $catExpenses = $expensesByCategory[$category->getId()] ?? []; ?>
                    <tbody class="cat-group" data-cat="<?= $category->getId() ?>" style="display:none;">
                        <?php if (!empty($catExpenses)): ?>
                            <?php foreach ($catExpenses as $expense): ?>
                                <tr>
                                    <td><?= $expense->getFormattedDate() ?></td>
                                    <td><?= htmlspecialchars($expense->getLibelle()) ?></td>
                                    <td>
                                        <span style="background-color: <?= htmlspecialchars($category->getColor()) ?>;" class="pill cat--<?= strtolower(str_replace(' ', '-', $expense->getCategoryName())) ?>">
                                            <?= htmlspecialchars($expense->getCategoryName()) ?>
                                        </span>
                                    </td>
                                    <td class="amount"><?= number_format($expense->getAmount(), 2, ',', ' ') ?> €</td>
                                    <td class="col-actions">
                                        <button class="icon-btn icon-btn--edit" title="Modifier" data-id="<?= $expense->getId() ?>" data-libelle="<?= $expense->getLibelle() ?>" data-amount="<?= $expense->getAmount() ?>" data-date="<?= $expense->getActionDate() ?>" data-category="<?= $expense->getIdCategories() ?>">✎</button>
                                        <form method="POST" action="/expense/delete" style="display:inline;" onsubmit="return confirm('Supprimer cette dépense ?');">
                                            <input type="hidden" name="id" value="<?= $expense->getId() ?>">
                                            <button type="submit" class="icon-btn icon-btn--delete" title="Supprimer">✕</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Aucune dépense dans cette catégorie ce mois-ci.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php endforeach; ?>

            </table>

        </section>

    </main>
</div>

<!-- ===== MODAL : EDIT SALARY ===== -->
<div class="modal-overlay" id="salaryModal">
    <div class="modal">
        <div class="modal__head">
            <h3>Modifier votre salaire du mois</h3>
            <button class="icon-btn" id="closeSalaryModal">✕</button>
        </div>
        <form class="form" id="salaryForm" method="POST" action="/budget/update">

            <div class="form__group">
                <label>Salaire du mois (€)</label>
                <input type="number" step="0.01" name="salary" id="salaryInput" placeholder="0,00" required>
            </div>

            <div class="form__group">
                <label>Objectif d'épargne (€)</label>
                <input type="number" step="0.01" name="savings_goals" id="savingsGoalsInput" placeholder="0,00" required>
            </div>

            <div class="form__actions">
                <button type="button" class="btn btn--ghost" id="cancelSalaryModal">Annuler</button>
                <button type="add-salary-submit" class="btn btn--primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL : ADD EXPENSE ===== -->
<div class="modal-overlay" id="addExpenseModal">
    <div class="modal">
        <div class="modal__head">
            <h3>Nouvelle dépense</h3>
            <button class="icon-btn" id="closeAddExpenseModal">✕</button>
        </div>
        <form class="form">
            <div class="form__group">
                <label>Libellé</label>
                <input type="text" placeholder="Ex : Courses du samedi">
            </div>
            <div class="form__row">
                <div class="form__group">
                    <label>Montant (€)</label>
                    <input type="number" step="0.01" placeholder="0,00">
                </div>
                <div class="form__group">
                    <label>Date</label>
                    <input type="date">
                </div>
            </div>
            <div class="form__group">
                <label>Catégorie</label>
                <select>
                    <?php foreach ($allCategories as $category): ?>
                        <option value="<?= $category->getId() ?>"><?= htmlspecialchars($category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__actions">
                <button type="button" class="btn btn--ghost" id="cancelAddExpenseModal">Annuler</button>
                <button type="add-expense-submit" class="btn btn--primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL : EDIT EXPENSE ===== -->                            
<div class="modal-overlay" id="editExpenseModal">
    <div class="modal">
        <div class="modal__head">
            <h3>Modifier la dépense</h3>
            <button class="icon-btn" id="closeEditExpenseModal">✕</button>
        </div>
        <form class="form" id="editExpenseForm">
            <input type="hidden" id="editExpenseId" name="id">

            <div class="form__group">
                <label>Libellé</label>
                <input type="text" id="editLibelle" name="libelle">
            </div>
            <div class="form__row">
                <div class="form__group">
                    <label>Montant (€)</label>
                    <input type="number" step="0.01" id="editAmount" name="amount">
                </div>
                <div class="form__group">
                    <label>Date</label>
                    <input type="date" id="editDate" name="date">
                </div>
            </div>
            <div class="form__group">
                <label>Catégorie</label>
                <select id="editCategory" name="category">
                    <?php foreach ($allCategories as $category): ?>
                        <option value="<?= $category->getId() ?>"><?= htmlspecialchars($category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>  
            <div class="form__actions">
                <button type="button" class="btn btn--ghost" id="cancelEditExpeneModal">Annuler</button>
                <button type="submit" class="btn btn--primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    // On garde toutes les catégories pour le chart (même à 0),
    // OU seulement celles avec dépenses > 0, selon ton préférence UX.
    // Ici, comme demandé : uniquement celles avec des dépenses réelles.
    <?php
        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        foreach ($allCategories as $c) {
            $amount = $totalExpensesByCategory[$c->getId()] ?? 0;
            if ($amount > 0) {
                $chartLabels[] = $c->getName();
                $chartData[] = $amount;
                $chartColors[] = $c->getColor();
            }
        }
    ?>
    window.chartData = {
        labels: <?= json_encode($chartLabels) ?>,
        data: <?= json_encode($chartData) ?>,
        colors: <?= json_encode($chartColors) ?>
    };
</script>
<script src="./public/js/dashboard.js"></script>

</body>
</html>