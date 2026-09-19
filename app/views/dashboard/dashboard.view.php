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
                    <li class="breakdown__item">
                        <span class="cat-tag cat--alimentation"></span>
                        <span class="breakdown__name"><?= isset($allCategories[0]) ? $allCategories[0]->getName() : 'Alimentation' ?></span>
                        <span class="breakdown__pct"><?= isset($totalExpensesByCategory[1]) ? round(($totalExpensesByCategory[1] / $totalExpenses) * 100, 1) : 0 ?> %</span>
                        <span class="breakdown__amount"><?= isset($totalExpensesByCategory[1]) ? number_format($totalExpensesByCategory[1], 2, ',', ' ') . " €" : "0,00 €" ?></span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--transports"></span>
                        <span class="breakdown__name"><?= isset($allCategories[1]) ? $allCategories[1]->getName() : 'Logements' ?></span>
                        <span class="breakdown__pct"><?= isset($totalExpensesByCategory[3]) ? round(($totalExpensesByCategory[3] / $totalExpenses) * 100, 1) : 0 ?> %</span>
                        <span class="breakdown__amount"><?= isset($totalExpensesByCategory[3]) ? number_format($totalExpensesByCategory[3], 2, ',', ' ') . " €" : "0,00 €" ?></span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--logements"></span>
                        <span class="breakdown__name"><?= isset($allCategories[2]) ? $allCategories[2]->getName() : 'Loisirs et Sorties' ?></span>
                        <span class="breakdown__pct"><?= isset($totalExpensesByCategory[6]) ? round(($totalExpensesByCategory[6] / $totalExpenses) * 100, 1) : 0 ?> %</span>
                        <span class="breakdown__amount"><?= isset($totalExpensesByCategory[6]) ? number_format($totalExpensesByCategory[6], 2, ',', ' ') . " €" : "0,00 €" ?></span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--sante"></span>
                        <span class="breakdown__name"><?= isset($allCategories[3]) ? $allCategories[3]->getName() : 'Santé' ?></span>
                        <span class="breakdown__pct"><?= isset($totalExpensesByCategory[4]) ? round(($totalExpensesByCategory[4] / $totalExpenses) * 100, 1) : 0 ?> %</span>
                        <span class="breakdown__amount"><?= isset($totalExpensesByCategory[4]) ? number_format($totalExpensesByCategory[4], 2, ',', ' ') . " €" : "0,00 €" ?></span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--soins"></span>
                        <span class="breakdown__name"><?= isset($allCategories[4]) ? $allCategories[4]->getName() : 'Soins et Hygiène' ?></span>
                        <span class="breakdown__pct"><?= isset($totalExpensesByCategory[5]) ? round(($totalExpensesByCategory[5] / $totalExpenses) * 100, 1) : 0 ?> %</span>
                        <span class="breakdown__amount"><?= isset($totalExpensesByCategory[5]) ? number_format($totalExpensesByCategory[5], 2, ',', ' ') . " €" : "0,00 €" ?></span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--loisirs"></span>
                        <span class="breakdown__name"><?= isset($allCategories[5]) ? $allCategories[5]->getName() : 'Transports' ?></span>
                        <span class="breakdown__pct"><?= isset($totalExpensesByCategory[2]) ? round(($totalExpensesByCategory[2] / $totalExpenses) * 100, 1) : 0 ?> %</span>
                        <span class="breakdown__amount"><?= isset($totalExpensesByCategory[2]) ? number_format($totalExpensesByCategory[2], 2, ',', ' ') . " €" : "0,00 €" ?></span>
                    </li>
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
                    <option value="alimentation">Alimentation</option>
                    <option value="transports">Transports</option>
                    <option value="sante">Santé</option>
                    <option value="logements">Logements</option>
                    <option value="soins">Soins et Hygiène</option>
                    <option value="loisirs">Loisirs et Sortie</option>
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
    window.chartData = {
        labels: <?= json_encode(array_map(fn($c) => $c->getName(), $allCategories)) ?>,
        data: <?= json_encode(array_map(fn($c) => $totalExpensesByCategory[$c->getId()] ?? 0, $allCategories)) ?>,
        colors: <?= json_encode(array_map(fn($c) => $c->getColor(), $allCategories)) ?>
    };
</script>
<script src="./public/js/dashboard.js"></script>


</body>
</html>