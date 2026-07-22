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



    <!-- ===== MAIN ===== -->
    <main class="main">

        <!-- Topbar -->
        <header class="topbar">
            <div>
                <p class="topbar__label">Analyses détaillées</p>
                <h2 class="topbar__title">Rapports et tendances 📈</h2>
            </div>
        </header>

        <!-- ===== FILTRES PÉRIODE ===== -->
        <section class="filter-section">
            <div class="filter-group">
                <label>Période</label>
                <div class="date-range">
                    <input type="date" value="2026-05-01">
                    <span class="date-separator">—</span>
                    <input type="date" value="2026-07-09">
                </div>
            </div>
            <button class="btn btn--primary">Appliquer</button>
        </section>

        <!-- ===== STATS COMPARATIVES ===== -->
        <section class="stats-grid">
            <div class="stat-card">
                <p class="stat-card__label">Total dépensé (période)</p>
                <p class="stat-card__value">990,00 €</p>
                <p class="stat-card__hint">3 mois analysés</p>
            </div>

            <div class="stat-card">
                <p class="stat-card__label">Dépense moyenne</p>
                <p class="stat-card__value">330,00 €</p>
                <p class="stat-card__hint">Par mois</p>
            </div>

            <div class="stat-card">
                <p class="stat-card__label">Catégorie top 1</p>
                <p class="stat-card__value">Alimentation</p>
                <p class="stat-card__hint">376,00 € (38 %)</p>
            </div>

            <div class="stat-card">
                <p class="stat-card__label">Dépense la plus élevée</p>
                <p class="stat-card__value">54,20 €</p>
                <p class="stat-card__hint">Courses Carrefour</p>
            </div>
        </section>

        <!-- ===== GRAPHIQUES ===== -->
        <section class="content-grid">
            <div class="panel">
                <div class="panel__head">
                    <h3>Dépenses par catégorie</h3>
                    <span class="badge">Mai - Juillet</span>
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
                    <li class="breakdown__item">
                        <span class="rank">1</span>
                        <span class="breakdown__name">Courses Carrefour</span>
                        <span class="breakdown__amount">54,20 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="rank">2</span>
                        <span class="breakdown__name">Ticket de métro</span>
                        <span class="breakdown__amount">16,90 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="rank">3</span>
                        <span class="breakdown__name">Pharmacie</span>
                        <span class="breakdown__amount">23,00 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="rank">4</span>
                        <span class="breakdown__name">Cinéma</span>
                        <span class="breakdown__amount">12,50 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="rank">5</span>
                        <span class="breakdown__name">Gel douche & shampoing</span>
                        <span class="breakdown__amount">18,30 €</span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- ===== TENDANCE MENSUELLE ===== -->
        <section class="panel">
            <div class="panel__head">
                <h3>Tendance mensuelle</h3>
                <span class="badge">Dernier trimestre</span>
            </div>
            <div class="chart-wrap">
                <canvas id="trendChart"></canvas>
            </div>
        </section>

        <!-- ===== TABLEAU DÉTAILLÉ ===== -->
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
                    <tr>
                        <td><span class="pill cat--alimentation">Alimentation</span></td>
                        <td>12</td>
                        <td class="amount">376,00 €</td>
                        <td>31,33 €</td>
                        <td>38 %</td>
                    </tr>
                    <tr>
                        <td><span class="pill cat--transports">Transports</span></td>
                        <td>8</td>
                        <td class="amount">178,00 €</td>
                        <td>22,25 €</td>
                        <td>18 %</td>
                    </tr>
                    <tr>
                        <td><span class="pill cat--logements">Logements</span></td>
                        <td>3</td>
                        <td class="amount">147,00 €</td>
                        <td>49,00 €</td>
                        <td>15 %</td>
                    </tr>
                    <tr>
                        <td><span class="pill cat--sante">Santé</span></td>
                        <td>4</td>
                        <td class="amount">119,00 €</td>
                        <td>29,75 €</td>
                        <td>12 %</td>
                    </tr>
                    <tr>
                        <td><span class="pill cat--soins">Soins et Hygiène</span></td>
                        <td>5</td>
                        <td class="amount">99,00 €</td>
                        <td>19,80 €</td>
                        <td>10 %</td>
                    </tr>
                    <tr>
                        <td><span class="pill cat--loisirs">Loisirs et Sortie</span></td>
                        <td>3</td>
                        <td class="amount">71,00 €</td>
                        <td>23,67 €</td>
                        <td>7 %</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>
</div>

<script>
    // --- Chart.js Catégories ---
    const categoryCtx = document.getElementById('categoryChart');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: ['Alimentation', 'Transports', 'Logements', 'Santé', 'Soins et Hygiène', 'Loisirs'],
            datasets: [{
                label: 'Montant (€)',
                data: [376, 178, 147, 119, 99, 71],
                backgroundColor: ['#C1654B', '#7A8B7F', '#C9A66B', '#8E6C88', '#6B8E9E', '#B58B4C'],
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

    // --- Chart.js Tendance ---
    const trendCtx = document.getElementById('trendChart');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Mai', 'Juin', 'Juillet'],
            datasets: [{
                label: 'Dépenses mensuelles',
                data: [330, 330, 330],
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