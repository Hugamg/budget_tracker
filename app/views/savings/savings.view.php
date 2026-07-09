<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Épargnes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="app-layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar__brand">
            <span class="brand-dot"></span>
            <h1>Budget<br>Tracker</h1>
        </div>

        <nav class="sidebar__nav">
            <a href="/?page=dashboard" class="nav-item">
                <span class="nav-icon">◈</span> Tableau de bord
            </a>
            <a href="/?page=savings" class="nav-item nav-item--active">
                <span class="nav-icon">◇</span> Épargnes
            </a>
            <a href="/?page=analytics" class="nav-item">
                <span class="nav-icon">◆</span> Analyses
            </a>
        </nav>

        <div class="sidebar__user">
            <div class="user-avatar">JM</div>
            <div class="user-info">
                <span class="user-name">Jean Martin</span>
                <a href="/?page=logout" class="user-logout">Déconnexion</a>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="main">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <p class="topbar__label">Gestion des épargnes</p>
                <h2 class="topbar__title">Suivi de votre épargne 💎</h2>
            </div>
            <button class="btn btn--primary" id="openSavingsModal">+ Ajouter une épargne</button>
        </header>

        <!-- ===== ÉPARGNE GLOBALE ===== -->
        <section class="stats-grid">
            <div class="stat-card stat-card--savings">
                <p class="stat-card__label">Total épargné</p>
                <p class="stat-card__value">450,00 €</p>
                <p class="stat-card__hint">Cumul de tous vos versements</p>
            </div>

            <div class="stat-card stat-card--balance">
                <p class="stat-card__label">Objectif mensuel</p>
                <p class="stat-card__value">150,00 €</p>
                <p class="stat-card__hint">Juillet : 100 % atteint ✓</p>
                <div class="progress">
                    <div class="progress__bar" style="width:100%"></div>
                </div>
            </div>

            <div class="stat-card stat-card--spent">
                <p class="stat-card__label">Épargne moyenne</p>
                <p class="stat-card__value">150,00 €</p>
                <p class="stat-card__hint">Par mois sur 3 mois</p>
            </div>

            <div class="stat-card stat-card--alert">
                <p class="stat-card__label">Depuis le dernier retrait</p>
                <p class="stat-card__value">3 mois</p>
                <p class="stat-card__hint">Aucun retrait cette année</p>
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
                    <li class="breakdown__item">
                        <span class="cat-tag" style="background: #C1654B; border-color: #1C1A17;"></span>
                        <span class="breakdown__name">Vacances</span>
                        <span class="breakdown__pct">45 %</span>
                        <span class="breakdown__amount">202,50 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag" style="background: #7A8B7F; border-color: #1C1A17;"></span>
                        <span class="breakdown__name">Maison</span>
                        <span class="breakdown__pct">35 %</span>
                        <span class="breakdown__amount">157,50 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag" style="background: #C9A66B; border-color: #1C1A17;"></span>
                        <span class="breakdown__name">Investissements</span>
                        <span class="breakdown__pct">20 %</span>
                        <span class="breakdown__amount">90,00 €</span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- ===== HISTORIQUE ===== -->
        <section class="panel">
            <div class="panel__head">
                <h3>Historique des versements</h3>
                <input type="month" class="month-picker">
            </div>

            <table class="expense-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Objectif</th>
                        <th>Montant</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>08/07/2026</td>
                        <td><span class="badge-small">Versement</span></td>
                        <td>Vacances</td>
                        <td class="amount">150,00 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <tr>
                        <td>08/06/2026</td>
                        <td><span class="badge-small">Versement</span></td>
                        <td>Vacances</td>
                        <td class="amount">150,00 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <tr>
                        <td>08/05/2026</td>
                        <td><span class="badge-small">Versement</span></td>
                        <td>Maison</td>
                        <td class="amount">150,00 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                </tbody>
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
        <form class="form">
            <div class="form__group">
                <label>Montant (€)</label>
                <input type="number" step="0.01" placeholder="0,00">
            </div>
            <div class="form__row">
                <div class="form__group">
                    <label>Date</label>
                    <input type="date">
                </div>
                <div class="form__group">
                    <label>Type</label>
                    <select>
                        <option value="versement">Versement</option>
                        <option value="retrait">Retrait</option>
                    </select>
                </div>
            </div>
            <div class="form__group">
                <label>Objectif</label>
                <select>
                    <option value="vacances">Vacances</option>
                    <option value="maison">Maison</option>
                    <option value="investissements">Investissements</option>
                </select>
            </div>
            <div class="form__actions">
                <button type="button" class="btn btn--ghost" id="cancelSavingsModal">Annuler</button>
                <button type="submit" class="btn btn--primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Chart.js Progression ---
    const ctx = document.getElementById('savingsChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet'],
            datasets: [{
                label: 'Épargne cumulative',
                data: [150, 300, 300, 300, 300, 450],
                borderColor: '#7A8B7F',
                backgroundColor: 'rgba(122, 139, 127, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#7A8B7F',
                pointBorderColor: '#1C1A17',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v + '€' }
                }
            }
        }
    });

    // --- Modal ---
    const savingsModal = document.getElementById('savingsModal');
    document.getElementById('openSavingsModal').onclick = () => savingsModal.classList.add('is-open');
    document.getElementById('closeSavingsModal').onclick = () => savingsModal.classList.remove('is-open');
    document.getElementById('cancelSavingsModal').onclick = () => savingsModal.classList.remove('is-open');
</script>

</body>
</html>