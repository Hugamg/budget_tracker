<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Tracker — Tableau de bord</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="app-layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <!-- Dans la sidebar, remplace les href -->
        <nav class="sidebar__nav">
            <a href="/?page=dashboard" class="nav-item nav-item--active">
                <span class="nav-icon">◈</span> Tableau de bord
            </a>
            <a href="/?page=savings" class="nav-item">
                <span class="nav-icon">◇</span> Épargnes
            </a>
            <a href="/?page=analytics" class="nav-item">
                <span class="nav-icon">◆</span> Analyses
            </a>
        </nav>

        <!-- Dans sidebar__user, change le logout -->
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
                <p class="topbar__label">Juillet 2026</p>
                <h2 class="topbar__title">Bonjour, Jean 👋</h2>
            </div>
            <button class="btn btn--primary" id="openAddModal">+ Nouvelle dépense</button>
        </header>

        <!-- ===== STAT CARDS ===== -->
        <section class="stats-grid">
            <div class="stat-card stat-card--balance">
                <p class="stat-card__label">Solde restant</p>
                <p class="stat-card__value">1 320,00 €</p>
                <p class="stat-card__hint">sur 1 650,00 € de paye</p>
                <div class="progress">
                    <div class="progress__bar" style="width:80%"></div>
                </div>
            </div>

            <div class="stat-card stat-card--savings">
                <p class="stat-card__label">Épargne du mois</p>
                <p class="stat-card__value">150,00 €</p>
                <p class="stat-card__hint">Objectif atteint ✓</p>
            </div>

            <div class="stat-card stat-card--spent">
                <p class="stat-card__label">Dépensé ce mois</p>
                <p class="stat-card__value">330,00 €</p>
                <p class="stat-card__hint">22 % de la paye</p>
            </div>

            <div class="stat-card stat-card--alert">
                <p class="stat-card__label">État épargne</p>
                <p class="stat-card__value">Intacte</p>
                <p class="stat-card__hint">Aucun retrait ce mois</p>
            </div>
        </section>

        <!-- ===== MIDDLE : CHART + BREAKDOWN ===== -->
        <section class="content-grid">

            <!-- Chart -->
            <div class="panel">
                <div class="panel__head">
                    <h3>Répartition des dépenses</h3>
                    <span class="badge">Juillet 2026</span>
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
                        <span class="breakdown__name">Alimentation</span>
                        <span class="breakdown__pct">38 %</span>
                        <span class="breakdown__amount">125,40 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--transports"></span>
                        <span class="breakdown__name">Transports</span>
                        <span class="breakdown__pct">18 %</span>
                        <span class="breakdown__amount">59,00 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--logements"></span>
                        <span class="breakdown__name">Logements</span>
                        <span class="breakdown__pct">15 %</span>
                        <span class="breakdown__amount">49,00 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--sante"></span>
                        <span class="breakdown__name">Santé</span>
                        <span class="breakdown__pct">12 %</span>
                        <span class="breakdown__amount">40,00 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--soins"></span>
                        <span class="breakdown__name">Soins et Hygiène</span>
                        <span class="breakdown__pct">10 %</span>
                        <span class="breakdown__amount">33,00 €</span>
                    </li>
                    <li class="breakdown__item">
                        <span class="cat-tag cat--loisirs"></span>
                        <span class="breakdown__name">Loisirs et Sortie</span>
                        <span class="breakdown__pct">7 %</span>
                        <span class="breakdown__amount">23,60 €</span>
                    </li>
                </ul>
            </div>
        </section>

        <!-- ===== EXPENSE LIST ===== -->
        <section class="panel">
            <div class="panel__head panel__head--filters">
                <h3>Dépenses du mois</h3>
                <div class="filters">
                    <button class="chip chip--active" data-cat="all">Toutes</button>
                    <button class="chip" data-cat="alimentation">Alimentation</button>
                    <button class="chip" data-cat="transports">Transports</button>
                    <button class="chip" data-cat="sante">Santé</button>
                    <button class="chip" data-cat="logements">Logements</button>
                    <button class="chip" data-cat="soins">Soins et Hygiène</button>
                    <button class="chip" data-cat="loisirs">Loisirs et Sortie</button>
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
                <tbody>
                    <tr>
                        <td>08/07/2026</td>
                        <td>Courses Carrefour</td>
                        <td><span class="pill cat--alimentation">Alimentation</span></td>
                        <td class="amount">54,20 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <tr>
                        <td>07/07/2026</td>
                        <td>Ticket de métro</td>
                        <td><span class="pill cat--transports">Transports</span></td>
                        <td class="amount">16,90 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <tr>
                        <td>05/07/2026</td>
                        <td>Pharmacie</td>
                        <td><span class="pill cat--sante">Santé</span></td>
                        <td class="amount">23,00 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <tr>
                        <td>03/07/2026</td>
                        <td>Cinéma</td>
                        <td><span class="pill cat--loisirs">Loisirs et Sortie</span></td>
                        <td class="amount">12,50 €</td>
                        <td class="col-actions">
                            <button class="icon-btn" title="Modifier">✎</button>
                            <button class="icon-btn icon-btn--danger" title="Supprimer">✕</button>
                        </td>
                    </tr>
                    <tr>
                        <td>02/07/2026</td>
                        <td>Gel douche & shampoing</td>
                        <td><span class="pill cat--soins">Soins et Hygiène</span></td>
                        <td class="amount">18,30 €</td>
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

<!-- ===== MODAL : ADD / EDIT EXPENSE ===== -->
<div class="modal-overlay" id="expenseModal">
    <div class="modal">
        <div class="modal__head">
            <h3>Nouvelle dépense</h3>
            <button class="icon-btn" id="closeAddModal">✕</button>
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
                <button type="button" class="btn btn--ghost" id="cancelModal">Annuler</button>
                <button type="submit" class="btn btn--primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Chart.js (données de démonstration) ---
    const ctx = document.getElementById('expenseChart');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Alimentation','Transports','Logements','Santé','Soins et Hygiène','Loisirs et Sortie'],
            datasets: [{
                data: [125.40, 59, 49, 40, 33, 23.60],
                backgroundColor: ['#C1654B','#7A8B7F','#C9A66B','#8E6C88','#6B8E9E','#B58B4C'],
                borderColor: '#F4EFE6',
                borderWidth: 4
            }]
        },
        options: {
            cutout: '62%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // --- Modal ---
    const modal = document.getElementById('expenseModal');
    document.getElementById('openAddModal').onclick = () => modal.classList.add('is-open');
    document.getElementById('closeAddModal').onclick = () => modal.classList.remove('is-open');
    document.getElementById('cancelModal').onclick = () => modal.classList.remove('is-open');

    // --- Filtres chips ---
    document.querySelectorAll('.chip').forEach(chip => {
        chip.onclick = () => {
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('chip--active'));
            chip.classList.add('chip--active');
        };
    });
</script>

</body>
</html>