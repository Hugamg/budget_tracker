<?php
// Helpers d'affichage
function money($v): string {
    return number_format((float) $v, 2, ',', ' ') . ' €';
}
function e($s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// Sécurisation des variables
$start          = isset($start)          ? $start          : date('Y-m-01');
$end            = isset($end)            ? $end            : date('Y-m-d');
$total          = isset($total)          ? $total          : 0;
$nbMonths       = isset($nbMonths)       ? $nbMonths       : 0;
$averageMonthly = isset($averageMonthly) ? $averageMonthly : 0;
$topCategory    = isset($topCategory)    ? $topCategory    : null;
$highestExpense = isset($highestExpense) ? $highestExpense : null;
$byCategory     = isset($byCategory)     ? $byCategory     : [];
$top5           = isset($top5)           ? $top5           : [];
$trendLabels    = isset($trendLabels)    ? $trendLabels    : [];
$trendData      = isset($trendData)      ? $trendData      : [];

// Libellé de période pour les badges
$periodLabel = '';
if (!empty($trendLabels)) {
    $periodLabel = count($trendLabels) > 1
        ? reset($trendLabels) . ' - ' . end($trendLabels)
        : $trendLabels[0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Analyses</title>
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
                <p class="topbar__label">Analyses détaillées</p>
                <h2 class="topbar__title">Rapports et tendances 📈</h2>
            </div>
        </header>

        <!-- Filtre période -->
        <form method="GET" action="/analytics">
            <section class="filter-section">
                <div class="filter-group">
                    <label for="start">Période</label>
                    <div class="date-range">
                        <input id="start" type="date" name="start" value="<?= e($start) ?>">
                        <span class="date-separator">—</span>
                        <input id="end" type="date" name="end" value="<?= e($end) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn--primary">Appliquer</button>
            </section>
        </form>

        <!-- Cartes de stats -->
        <section class="stats-grid">
            <div class="stat-card">
                <p class="stat-card__label">Total dépensé (période)</p>
                <p class="stat-card__value"><?= money($total) ?></p>
                <p class="stat-card__hint"><?= (int) $nbMonths ?> mois analysé<?= $nbMonths > 1 ? 's' : '' ?></p>
            </div>

            <div class="stat-card">
                <p class="stat-card__label">Dépense moyenne</p>
                <p class="stat-card__value"><?= money($averageMonthly) ?></p>
                <p class="stat-card__hint">Par mois</p>
            </div>

            <div class="stat-card">
                <p class="stat-card__label">Catégorie top 1</p>
                <?php if (isset($topCategory) && !empty($topCategory)): ?>
                    <p class="stat-card__value"><?= e(isset($topCategory['name']) ? $topCategory['name'] : '—') ?></p>
                    <p class="stat-card__hint">
                        <?= money(isset($topCategory['total']) ? $topCategory['total'] : 0) ?>
                        (<?= isset($topCategory['percent']) ? $topCategory['percent'] : 0 ?> %)
                    </p>
                <?php else: ?>
                    <p class="stat-card__value">—</p>
                    <p class="stat-card__hint">Aucune dépense</p>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <p class="stat-card__label">Dépense la plus élevée</p>
                <?php if (isset($highestExpense) && !empty($highestExpense)): ?>
                    <p class="stat-card__value"><?= money(isset($highestExpense['amount']) ? $highestExpense['amount'] : 0) ?></p>
                    <p class="stat-card__hint"><?= e(isset($highestExpense['label']) ? $highestExpense['label'] : '') ?></p>
                <?php else: ?>
                    <p class="stat-card__value">—</p>
                    <p class="stat-card__hint">Aucune dépense</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Graphique catégories + Top 5 -->
        <section class="content-grid">
            <div class="panel">
                <div class="panel__head">
                    <h3>Dépenses par catégorie</h3>
                    <span class="badge"><?= e($periodLabel) ?></span>
                </div>
                <div class="chart-wrap chart-wrap--tall">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head">
                    <h3>Top 5 des dépenses</h3>
                </div>
                <ul class="breakdown">
                    <?php foreach ($top5 as $i => $expense): ?>
                        <li class="breakdown__item">
                            <span class="rank"><?= $i + 1 ?></span>
                            <span class="breakdown__name"><?= e(isset($expense['label']) ? $expense['label'] : '') ?></span>
                            <span class="breakdown__amount"><?= money(isset($expense['amount']) ? $expense['amount'] : 0) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <!-- Tendance mensuelle -->
        <section class="panel">
            <div class="panel__head">
                <h3>Tendance mensuelle</h3>
                <span class="badge"><?= e($periodLabel) ?></span>
            </div>
            <div class="chart-wrap">
                <canvas id="trendChart"></canvas>
            </div>
        </section>

        <!-- Tableau détaillé -->
        <section class="panel">
            <div class="panel__head">
                <h3>Détail par catégorie</h3>
            </div>

            <table class="expense-table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Nombre de transactions</th>
                        <th>Total</th>
                        <th>Moyenne</th>
                        <th>% du budget</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byCategory as $cat): ?>
                        <tr>
                            <td>
                                <span class="pill" style="background: <?= e(isset($cat['color']) ? $cat['color'] : '#999') ?>">
                                    <?= e(isset($cat['name']) ? $cat['name'] : '') ?>
                                </span>
                            </td>
                            <td><?= isset($cat['nb_transactions']) ? (int) $cat['nb_transactions'] : 0 ?></td>
                            <td class="amount"><?= money(isset($cat['total']) ? $cat['total'] : 0) ?></td>
                            <td><?= money(isset($cat['average']) ? $cat['average'] : 0) ?></td>
                            <td><?= isset($cat['percent']) ? $cat['percent'] : 0 ?> %</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

    </main>
</div>

<script>
    // Graphique catégories
    const categoryCtx = document.getElementById('categoryChart');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($byCategory, 'name')) ?>,
            datasets: [{
                label: 'Montant (€)',
                data: <?= json_encode(array_map('floatval', array_column($byCategory, 'total'))) ?>,
                backgroundColor: <?= json_encode(array_column($byCategory, 'color')) ?>,
                borderColor: '#1C1A17',
                borderWidth: 2
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { callback: v => v + '€' }
                }
            }
        }
    });

    // Graphique tendance
    const trendCtx = document.getElementById('trendChart');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($trendLabels) ?>,
            datasets: [{
                label: 'Dépenses mensuelles',
                data: <?= json_encode($trendData) ?>,
                borderColor: '#C1654B',
                backgroundColor: 'rgba(193, 101, 75, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointBackgroundColor: '#C1654B',
                pointBorderColor: '#1C1A17',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v + '€' }
                }
            }
        }
    });
</script>

</body>
</html>