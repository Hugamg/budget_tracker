<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Épargnes</title>
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
                <p class="topbar__label">Gestion des épargnes</p>
                <h2 class="topbar__title">Suivi de votre épargne</h2>
            </div>
            <button class="btn btn--primary" id="openSavingsModal">+ Ajouter une épargne</button>
        </header>

        <!-- ===== ÉPARGNE GLOBALE ===== -->
        <section class="stats-grid">
            <div class="stat-card stat-card--savings">
                <p class="stat-card__label">Total épargné</p>
                <p class="stat-card__value"><?= isset($totalSavings) ? $totalSavings : 0 ?> €</p>
                <p class="stat-card__hint">Cumul de tous vos versements</p>
            </div>

            <div class="stat-card stat-card--balance">
                <p class="stat-card__label">Objectif mensuel</p>
                <p class="stat-card__value"><?= isset($monthSavingsGoal) ? $monthSavingsGoal : 0 ?> €</p>
                <p class="stat-card__hint">
                    <?php
                        $goal = $monthSavingsGoal ?? 0;
                        $current = $monthSavings ?? 0;
                        $pct = $goal > 0 ? round(($current / $goal) * 100, 1) : 0;
                    ?>
                    <?= date('F') ?> : <?= $pct ?> % atteint <?= $pct >= 100 ? '✓' : '' ?>
                </p>
                <div class="progress">
                    <div class="progress__bar" style="width:<?= min($pct, 100) ?>%"></div>
                </div>
            </div>

            <div class="stat-card stat-card--spent">
                <p class="stat-card__label">Épargne moyenne</p>
                <p class="stat-card__value"><?= isset($averageMonthlySavings) ? number_format($averageMonthlySavings, 2, ',', ' ') : 0 ?> €</p>
                <p class="stat-card__hint">Par mois sur 3 mois</p>
            </div>

            <div class="stat-card stat-card--alert">
                <p class="stat-card__label">Depuis le dernier retrait</p>
                    <p class="stat-card__value"><?= isset($lastWithdrawalData) ? $lastWithdrawalData['action_date'] : '—' ?></p>
                    <p class="stat-card__hint">Dernier retrait : <?= isset($lastWithdrawalData) ? number_format($lastWithdrawalData['amount'], 2, ',', ' ') : '—' ?> €</p>
            </div>
        </section>

        <!-- ===== GRAPHIQUE PROGRESSION ===== -->
        <section class="content-grid">
            <div class="panel">
                <div class="panel__head">
                    <h3>Progression de l'épargne</h3>
                    <span class="badge">6 derniers mois</span>
                </div>
                <div class="chart-wrap chart-wrap--tall">
                    <canvas id="savingsChart"></canvas>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head">
                    <h3>Répartition par objectif</h3>
                </div>
                <ul class="breakdown">
                    <?php
                        // Calcul du total pour les pourcentages
                        $totalSavingsByCategories = is_array($totalSavingsByCategories ?? null) ? $totalSavingsByCategories : [];
                        $totalAllCategories = array_sum($totalSavingsByCategories);
                    ?>
                    <?php if (isset($allCategories) && !empty($allCategories)): ?>
                        <?php $hasSavings = false; ?>
                        <?php foreach ($allCategories as $category): ?>
                            <?php
                                $categoryAmount = $totalSavingsByCategories[$category->getId()] ?? 0;
                                if ($categoryAmount <= 0) continue; // on masque les objectifs sans versement
                                $hasSavings = true;
                                $percentage = $totalAllCategories > 0 
                                    ? round(($categoryAmount / $totalAllCategories) * 100, 1) 
                                    : 0;
                            ?>
                            <li class="breakdown__item">
                                <span class="cat-tag" style="background: <?= htmlspecialchars($category->getColor()) ?>; border-color: #1C1A17;"></span>
                                <span class="breakdown__name"><?= htmlspecialchars($category->getName()) ?></span>
                                <span class="breakdown__pct"><?= $percentage ?> %</span>
                                <span class="breakdown__amount"><?= number_format($categoryAmount, 2, ',', ' ') ?> €</span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (!$hasSavings): ?>
                            <li class="breakdown__item">
                                <span class="breakdown__name">Aucun versement enregistré</span>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="breakdown__item">
                            <span class="breakdown__name">Aucune catégorie disponible</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </section>

        <!-- ===== SAVINGS LIST ===== -->
    <section class="panel">
        <div class="panel__head panel__head--filters">
            <h3>Historique des versements</h3>
            <div class="filters">
                <button class="chip chip--active" style="background-color: var(--bg);" data-cat="all">
                    Tous
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
                    <th>Type</th>
                    <th>Catégorie</th>
                    <th>Montant</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>

            <!-- Bloc "Tous les mouvements" -->
            <tbody class="cat-group" data-cat="all">
                <?php
                    // Fusion des versements et retraits, triés par date décroissante
                    $allMovements = array_merge($savingsDeposits ?? [], $savingsWithdrawals ?? []);
                    usort($allMovements, fn($a, $b) => strtotime($b['action_date']) <=> strtotime($a['action_date']));
                ?>
                <?php if (!empty($allMovements)): ?>
                    <?php foreach ($allMovements as $movement): ?>
                        <?php
                            $isDeposit = ($movement['saving_deposits'] ?? $movement['saving_withdrawal'] ?? null) === 'epargne_versement';
                            $type = $isDeposit ? 'Versement' : 'Retrait';
                            $badgeClass = $isDeposit ? 'badge-small' : 'badge-small badge-small--danger';
                            
                            // Récupérer la catégorie pour la couleur
                            $categoryId = $movement['id_categories'] ?? null;
                            $categoryColor = '#999';
                            foreach ($allCategories as $cat) {
                                if ($cat->getId() == $categoryId) {
                                    $categoryColor = $cat->getColor();
                                    break;
                                }
                            }
                        ?>
                        <tr>
                            <td><?= (new DateTimeImmutable($movement['action_date']))->format('d/m/Y') ?></td>
                            <td><span class="<?= $badgeClass ?>"><?= $type ?></span></td>
                            <td>
                                <span style="background-color: <?= htmlspecialchars($categoryColor) ?>;" class="pill">
                                    <?= htmlspecialchars($movement['category_name']) ?>
                                </span>
                            </td>
                            <td class="amount"><?= number_format($movement['amount'], 2, ',', ' ') ?> €</td>
                            <td class="col-actions">
                                <button class="icon-btn icon-btn--edit" title="Modifier" data-id="<?= $movement['id'] ?>" data-amount="<?= $movement['amount'] ?>" data-date="<?= $movement['action_date'] ?>" data-category="<?= $categoryId ?>">✎</button>
                                <form method="POST" action="/savings/delete" style="display:inline;" onsubmit="return confirm('Supprimer cette épargne ?');">
                                    <input type="hidden" name="id" value="<?= $movement['id'] ?>">
                                    <button type="submit" class="icon-btn icon-btn--delete" title="Supprimer">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Aucun mouvement d'épargne enregistré.</td>
                    </tr>
                <?php endif; ?>
            </tbody>

            <!-- Un bloc par catégorie d'épargne -->
            <?php foreach ($allCategories as $category): ?>
                <?php 
                    // Filtrer les mouvements par catégorie
                    $catMovements = array_filter($allMovements, fn($m) => ($m['id_categories'] ?? null) == $category->getId());
                    usort($catMovements, fn($a, $b) => strtotime($b['action_date']) <=> strtotime($a['action_date']));
                ?>
                <tbody class="cat-group" data-cat="<?= $category->getId() ?>" style="display:none;">
                    <?php if (!empty($catMovements)): ?>
                        <?php foreach ($catMovements as $movement): ?>
                            <?php
                                $isDeposit = ($movement['saving_deposits'] ?? $movement['saving_withdrawal'] ?? null) === 'epargne_versement';
                                $type = $isDeposit ? 'Versement' : 'Retrait';
                                $badgeClass = $isDeposit ? 'badge-small' : 'badge-small badge-small--danger';
                                $categoryId = $movement['id_categories'] ?? null;
                            ?>
                            <tr>
                                <td><?= (new DateTimeImmutable($movement['action_date']))->format('d/m/Y') ?></td>
                                <td><span class="<?= $badgeClass ?>"><?= $type ?></span></td>
                                <td>
                                    <span style="background-color: <?= htmlspecialchars($category->getColor()) ?>;" class="pill">
                                        <?= htmlspecialchars($movement['category_name']) ?>
                                    </span>
                                </td>
                                <td class="amount"><?= number_format($movement['amount'], 2, ',', ' ') ?> €</td>
                                <td class="col-actions">
                                    <button class="icon-btn icon-btn--edit" title="Modifier" data-id="<?= $movement['id'] ?>" data-amount="<?= $movement['amount'] ?>" data-date="<?= $movement['action_date'] ?>" data-category="<?= $categoryId ?>">✎</button>
                                    <form method="POST" action="/savings/delete" style="display:inline;" onsubmit="return confirm('Supprimer cette épargne ?');">
                                        <input type="hidden" name="id" value="<?= $movement['id'] ?>">
                                        <button type="submit" class="icon-btn icon-btn--delete" title="Supprimer">✕</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucun mouvement dans cette catégorie.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            <?php endforeach; ?>

        </table>
    </section>

    </main>
</div>

<!-- ===== MODAL : AJOUTER ÉPARGNE ===== -->
<div class="modal-overlay" id="savingsModal">
    <div class="modal">
        <div class="modal__head">
            <h3>Enregistrer une épargne</h3>
            <button class="icon-btn" id="closeSavingsModal">✕</button>
        </div>
        <form class="form" method="POST" action="/savings/add">
            <div class="form__group">
                <label>Montant (€)</label>
                <input type="number" step="0.01" name="amount" placeholder="0,00" required>
            </div>
            <div class="form__row">
                <div class="form__group">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>
                <div class="form__group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="epargne_versement">Versement</option>
                        <option value="epargne_retrait">Retrait</option>
                    </select>
                </div>
            </div>
            <div class="form__group">
                <label>Objectif</label>
                <select name="category" required>
                    <?php foreach ($allCategories as $category): ?>
                        <option value="<?= $category->getId() ?>"><?= htmlspecialchars($category->getName()) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__actions">
                <button type="button" class="btn btn--ghost" id="cancelSavingsModal">Annuler</button>
                <button type="submit" class="btn btn--primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- DONNÉES GRAPHIQUE DEPUIS LE CONTRÔLEUR -->
<script>
    window.chartData = <?= json_encode($chartData ?? ['labels' => [], 'datasets' => []], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<script>
    // ============================================
    // GRAPHIQUE MULTI-CATÉGORIES
    // ============================================
    const ctx = document.getElementById('savingsChart');
    
    new Chart(ctx, {
    type: 'line',
    data: window.chartData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            title: {
                display: true,
                text: 'Évolution cumulée de l\'épargne',
                font: { family: 'Space Grotesk', size: 16, weight: '600' },
                color: '#1C1A17'
            },
            subtitle: {
                display: true,
                text: 'Total épargné par objectif, mois après mois',
                font: { family: 'DM Sans', size: 12 },
                color: '#6B655C',
                padding: { bottom: 16 }
            },
            legend: {
                position: 'bottom',
                labels: {
                    font: { family: 'DM Sans', size: 12 },
                    usePointStyle: true,
                    padding: 16
                }
            },
            tooltip: {
                backgroundColor: '#1C1A17',
                titleFont: { family: 'Space Grotesk' },
                bodyFont: { family: 'DM Sans' },
                padding: 12,
                cornerRadius: 4,
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + 
                            context.parsed.y.toLocaleString('fr-FR', {
                                minimumFractionDigits: 2
                            }) + ' €';
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { color: '#E5DFD3', borderColor: '#1C1A17' },
                ticks: { font: { family: 'DM Sans' } }
            },
            y: {
                // PAS DE stacked: true ! Les courbes sont indépendantes
                beginAtZero: true,
                grid: { color: '#E5DFD3', borderColor: '#1C1A17' },
                ticks: {
                    font: { family: 'Space Grotesk' },
                    callback: function(value) {
                        return value.toLocaleString('fr-FR') + ' €';
                    }
                }
            }
        }
    }
});

    // --- Modal ---
    const savingsModal = document.getElementById('savingsModal');
    document.getElementById('openSavingsModal').onclick = () => savingsModal.classList.add('is-open');
    document.getElementById('closeSavingsModal').onclick = () => savingsModal.classList.remove('is-open');
    document.getElementById('cancelSavingsModal').onclick = () => savingsModal.classList.remove('is-open');

    // --- Filtrage ---
    document.querySelectorAll('.panel__head--filters .chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const selectedCat = this.dataset.cat;
            document.querySelectorAll('.panel__head--filters .chip').forEach(c => c.classList.remove('chip--active'));
            this.classList.add('chip--active');
            document.querySelectorAll('.cat-group').forEach(group => {
                group.style.display = group.dataset.cat === selectedCat ? '' : 'none';
            });
        });
    });
</script>

</body>
</html>