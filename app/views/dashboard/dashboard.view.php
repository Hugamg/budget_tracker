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

    <main class="main">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <p class="topbar__label"><?= date('F Y'); ?></p>
                <h2 class="topbar__title">
                    Bonjour,
                    <?= isset($user) && $user !== null
                        ? htmlspecialchars($user->getFirstName(), ENT_QUOTES, 'UTF-8')
                        : 'Utilisateur' ?>
                </h2>
            </div>

            <button class="btn btn--primary" id="openAddModal" type="button">
                + Nouvelle dépense
            </button>
        </header>

        <!-- Message après suppression -->
        <?php if (!empty($flash['message']) && empty($flash['modal'])): ?>
            <div class="form-message form-message--success" role="status">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- ===== STAT CARDS ===== -->
        <section class="stats-grid">

            <div class="stat-card stat-card--balance">
                <p class="stat-card__label">Solde restant</p>
                <p class="stat-card__value">
                    <?= isset($remainingBalance) && $remainingBalance !== null
                        ? number_format((float) $remainingBalance, 2, ',', ' ')
                        : '0,00' ?> €
                </p>
                <p class="stat-card__hint">
                    sur
                    <?= isset($budget) && $budget !== null
                        ? number_format((float) $budget->getSalary(), 2, ',', ' ')
                        : '0,00' ?>
                    € de paye
                </p>

                <button
                    class="btn btn--primary btn--small"
                    id="openEditSalaryModal"
                    type="button"
                >
                    Modifier vos informations du mois
                </button>
            </div>

            <div class="stat-card stat-card--savings">
                <p class="stat-card__label">Épargne du mois</p>
                <p class="stat-card__value">
                    <?= isset($monthSavings) && $monthSavings !== null
                        ? number_format((float) $monthSavings, 2, ',', ' ')
                        : '0,00' ?> €
                </p>

                <p class="stat-card__hint">
                    <?php
                    if (!isset($monthSavings) || $monthSavings === null) {
                        echo "Aucune valeur n'a encore été enregistrée";
                    } elseif (
                        isset($budget)
                        && $budget !== null
                        && $monthSavings < $budget->getSavingsGoals()
                    ) {
                        $remainingSavingsGoal =
                            $budget->getSavingsGoals() - $monthSavings;

                        echo 'Reste '
                            . number_format((float) $remainingSavingsGoal, 2, ',', ' ')
                            . ' € à épargner';
                    } else {
                        echo 'Objectif atteint ✔';
                    }
                    ?>
                </p>
            </div>

            <div class="stat-card stat-card--spent">
                <p class="stat-card__label">Dépensé ce mois</p>
                <p class="stat-card__value">
                    <?= isset($totalExpenses)
                        ? number_format((float) $totalExpenses, 2, ',', ' ')
                        : '0,00' ?> €
                </p>
                <p class="stat-card__hint">
                    <?php
                    $salary = isset($budget) && $budget !== null
                        ? (float) $budget->getSalary()
                        : 0;

                    $expensesTotal = isset($totalExpenses)
                        ? (float) $totalExpenses
                        : 0;

                    echo $salary > 0
                        ? round(($expensesTotal / $salary) * 100, 1)
                        : 0;
                    ?> % de la paye
                </p>
            </div>

            <div class="stat-card stat-card--alert">
                <p class="stat-card__label">État épargne</p>
                <p class="stat-card__value">
                    <?= !isset($totalSavings) || $totalSavings === null
                        ? 'Aucune épargne présente'
                        : number_format((float) $totalSavings, 2, ',', ' ') . ' €' ?>
                </p>
            </div>

        </section>

        <!-- ===== CHART + BREAKDOWN ===== -->
        <section class="content-grid">

            <div class="panel">
                <div class="panel__head">
                    <h3>Répartition des dépenses</h3>
                    <span class="badge"><?= date('F Y'); ?></span>
                </div>

                <div class="chart-wrap">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head">
                    <h3>Par catégorie</h3>
                </div>

                <ul class="breakdown">
                    <?php if (!empty($allCategories)): ?>
                        <?php foreach ($allCategories as $category): ?>
                            <?php
                            $categoryAmount =
                                $totalExpensesByCategory[$category->getId()] ?? 0;

                            $percentage = (float) $totalExpenses > 0
                                ? round(($categoryAmount / $totalExpenses) * 100, 1)
                                : 0;
                            ?>

                            <li class="breakdown__item">
                                <span
                                    class="cat-tag"
                                    style="background-color: <?= htmlspecialchars(
                                        $category->getColor(),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>;"
                                ></span>

                                <span class="breakdown__name">
                                    <?= htmlspecialchars(
                                        $category->getName(),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>

                                <span class="breakdown__pct">
                                    <?= $percentage ?> %
                                </span>

                                <span class="breakdown__amount">
                                    <?= number_format((float) $categoryAmount, 2, ',', ' ') ?> €
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="breakdown__item">
                            <span class="breakdown__name">Aucune catégorie disponible</span>
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
                    <button
                        class="chip chip--active"
                        style="background-color: var(--bg);"
                        data-cat="all"
                        type="button"
                    >
                        Toutes
                    </button>

                    <?php foreach ($allCategories as $category): ?>
                        <button
                            class="chip"
                            style="background-color: <?= htmlspecialchars($category->getColor(), ENT_QUOTES, 'UTF-8') ?>;"
                            data-cat="<?= (int) $category->getId() ?>"
                            type="button"
                        >
                            <?= htmlspecialchars($category->getName(), ENT_QUOTES, 'UTF-8') ?>
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

                <!-- Toutes les dépenses -->
                <tbody class="cat-group" data-cat="all">
                    <?php if (isset($expenses) && !empty($expenses)): ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?= htmlspecialchars($expense->getFormattedDate(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($expense->getLibelle(), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span
                                        style="background-color: <?= htmlspecialchars($expense->getCategoryColor(), ENT_QUOTES, 'UTF-8') ?>;"
                                        class="pill"
                                    >
                                        <?= htmlspecialchars($expense->getCategoryName(), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="amount">
                                    <?= number_format((float) $expense->getAmount(), 2, ',', ' ') ?> €
                                </td>
                                <td class="col-actions">
                                    <button
                                        class="icon-btn icon-btn--edit"
                                        type="button"
                                        title="Modifier"
                                        data-id="<?= (int) $expense->getId() ?>"
                                        data-libelle="<?= htmlspecialchars($expense->getLibelle(), ENT_QUOTES, 'UTF-8') ?>"
                                        data-amount="<?= htmlspecialchars((string) $expense->getAmount(), ENT_QUOTES, 'UTF-8') ?>"
                                        data-date="<?= htmlspecialchars($expense->getActionDate(), ENT_QUOTES, 'UTF-8') ?>"
                                        data-category="<?= (int) $expense->getIdCategories() ?>"
                                    >✎</button>

                                    <form
                                        method="POST"
                                        action="/expense/delete"
                                        style="display:inline;"
                                        onsubmit="return confirm('Supprimer cette dépense ?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $expense->getId() ?>"
                                        >
                                        <button
                                            type="submit"
                                            class="icon-btn icon-btn--delete"
                                            title="Supprimer"
                                        >✕</button>
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

                <!-- Dépenses par catégorie -->
                <?php foreach ($allCategories as $category): ?>
                    <?php $catExpenses = $expensesByCategory[$category->getId()] ?? []; ?>

                    <tbody
                        class="cat-group"
                        data-cat="<?= (int) $category->getId() ?>"
                        style="display:none;"
                    >
                        <?php if (!empty($catExpenses)): ?>
                            <?php foreach ($catExpenses as $expense): ?>
                                <tr>
                                    <td><?= htmlspecialchars($expense->getFormattedDate(), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($expense->getLibelle(), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span
                                            style="background-color: <?= htmlspecialchars($category->getColor(), ENT_QUOTES, 'UTF-8') ?>;"
                                            class="pill"
                                        >
                                            <?= htmlspecialchars($expense->getCategoryName(), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="amount">
                                        <?= number_format((float) $expense->getAmount(), 2, ',', ' ') ?> €
                                    </td>
                                    <td class="col-actions">
                                        <button
                                            class="icon-btn icon-btn--edit"
                                            type="button"
                                            title="Modifier"
                                            data-id="<?= (int) $expense->getId() ?>"
                                            data-libelle="<?= htmlspecialchars($expense->getLibelle(), ENT_QUOTES, 'UTF-8') ?>"
                                            data-amount="<?= htmlspecialchars((string) $expense->getAmount(), ENT_QUOTES, 'UTF-8') ?>"
                                            data-date="<?= htmlspecialchars($expense->getActionDate(), ENT_QUOTES, 'UTF-8') ?>"
                                            data-category="<?= (int) $expense->getIdCategories() ?>"
                                        >✎</button>

                                        <form
                                            method="POST"
                                            action="/expense/delete"
                                            style="display:inline;"
                                            onsubmit="return confirm('Supprimer cette dépense ?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $expense->getId() ?>"
                                            >
                                            <button
                                                type="submit"
                                                class="icon-btn icon-btn--delete"
                                                title="Supprimer"
                                            >✕</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    Aucune dépense dans cette catégorie ce mois-ci.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php endforeach; ?>

            </table>
        </section>

    </main>
</div>

<!-- ===== MODAL : MODIFIER LE BUDGET ===== -->
<div
    class="modal-overlay <?= ($flash['modal'] ?? null) === 'salary' ? 'is-open' : '' ?>"
    id="salaryModal"
>
    <div class="modal">
        <div class="modal__head">
            <h3>Modifier votre salaire du mois</h3>
            <button class="icon-btn" id="closeSalaryModal" type="button">✕</button>
        </div>

        <?php if (($flash['modal'] ?? null) === 'salary'): ?>
            <div class="form-message form-message--success" role="status">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="form" id="salaryForm" method="POST" action="/dashboard">
            <input type="hidden" name="action" value="update_budget">

            <div class="form__group">
                <label for="salaryInput">Salaire du mois (€)</label>
                <input
                    type="number"
                    step="0.01"
                    name="salary"
                    id="salaryInput"
                    placeholder="0.00"
                    required
                >
            </div>

            <div class="form__group">
                <label for="savingsGoalsInput">Objectif d'épargne (€)</label>
                <input
                    type="number"
                    step="0.01"
                    name="savings_goals"
                    id="savingsGoalsInput"
                    placeholder="0.00"
                    required
                >
            </div>

            <div class="form__actions">
                <button
                    type="button"
                    class="btn btn--ghost"
                    id="cancelSalaryModal"
                >
                    Annuler
                </button>
                <button type="submit" class="btn btn--primary">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL : AJOUTER UNE DÉPENSE ===== -->
<div
    class="modal-overlay <?= ($flash['modal'] ?? null) === 'add' ? 'is-open' : '' ?>"
    id="addExpenseModal"
>
    <div class="modal">
        <div class="modal__head">
            <h3>Nouvelle dépense</h3>
            <button class="icon-btn" id="closeAddExpenseModal" type="button">✕</button>
        </div>

        <?php if (($flash['modal'] ?? null) === 'add'): ?>
            <div class="form-message form-message--success" role="status">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form class="form" method="POST" action="/dashboard">
            <input type="hidden" name="action" value="add_expense">

            <div class="form__group">
                <label for="addLibelle">Libellé</label>
                <input
                    type="text"
                    id="addLibelle"
                    name="libelle"
                    placeholder="Ex : Courses du samedi"
                    required
                >
            </div>

            <div class="form__row">
                <div class="form__group">
                    <label for="addAmount">Montant (€)</label>
                    <input
                        type="number"
                        id="addAmount"
                        name="amount"
                        step="0.01"
                        min="0.01"
                        placeholder="0.00"
                        required
                    >
                </div>

                <div class="form__group">
                    <label for="addDate">Date</label>
                    <input
                        type="date"
                        id="addDate"
                        name="date"
                        required
                    >
                </div>
            </div>

            <div class="form__group">
                <label for="addCategory">Catégorie</label>
                <select id="addCategory" name="category" required>
                    <?php foreach ($allCategories as $category): ?>
                        <option value="<?= (int) $category->getId() ?>">
                            <?= htmlspecialchars($category->getName(), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__actions">
                <button
                    type="button"
                    class="btn btn--ghost"
                    id="cancelAddExpenseModal"
                >
                    Annuler
                </button>
                <button type="submit" class="btn btn--primary">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL : MODIFIER UNE DÉPENSE ===== -->
<div
    class="modal-overlay <?= ($flash['modal'] ?? null) === 'edit' ? 'is-open' : '' ?>"
    id="editExpenseModal"
>
    <div class="modal">
        <div class="modal__head">
            <h3>Modifier la dépense</h3>
            <button class="icon-btn" id="closeEditExpenseModal" type="button">✕</button>
        </div>

        <?php if (($flash['modal'] ?? null) === 'edit'): ?>
            <div class="form-message form-message--success" role="status">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form
            class="form"
            id="editExpenseForm"
            method="POST"
            action="/dashboard"
        >
            <input type="hidden" name="action" value="update_expense">
            <input type="hidden" id="editExpenseId" name="id">

            <div class="form__group">
                <label for="editLibelle">Libellé</label>
                <input type="text" id="editLibelle" name="libelle" required>
            </div>

            <div class="form__row">
                <div class="form__group">
                    <label for="editAmount">Montant (€)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        id="editAmount"
                        name="amount"
                        required
                    >
                </div>

                <div class="form__group">
                    <label for="editDate">Date</label>
                    <input
                        type="date"
                        id="editDate"
                        name="date"
                        required
                    >
                </div>
            </div>

            <div class="form__group">
                <label for="editCategory">Catégorie</label>
                <select id="editCategory" name="category" required>
                    <?php foreach ($allCategories as $category): ?>
                        <option value="<?= (int) $category->getId() ?>">
                            <?= htmlspecialchars($category->getName(), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__actions">
                <button
                    type="button"
                    class="btn btn--ghost"
                    id="cancelEditExpeneModal"
                >
                    Annuler
                </button>
                <button type="submit" class="btn btn--primary">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    <?php
    $chartLabels = [];
    $chartData = [];
    $chartColors = [];

    foreach ($allCategories as $category) {
        $amount = $totalExpensesByCategory[$category->getId()] ?? 0;

        if ($amount > 0) {
            $chartLabels[] = $category->getName();
            $chartData[] = (float) $amount;
            $chartColors[] = $category->getColor();
        }
    }
    ?>

    window.chartData = {
        labels: <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        data: <?= json_encode($chartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        colors: <?= json_encode($chartColors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
</script>

<script src="./public/js/dashboard.js"></script>

</body>
</html>