// --- Chart.js (données de démonstration) ---
const ctx = document.getElementById('expenseChart');

// Utilise les données passées par PHP, avec fallback si absent
const labels = window.chartData?.labels || [];
const dataValues = window.chartData?.data || [];
const colors = window.chartData?.colors || [];

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: labels,
        datasets: [{
            data: dataValues,
            backgroundColor: colors, // Couleurs dynamiques depuis la BDD
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

    // ============================================
// Helper générique pour ouvrir/fermer une modal
// ============================================
function bindModal({ overlayId, openBtnId, closeBtnId, cancelBtnId }) {
    const overlay = document.getElementById(overlayId);
    if (!overlay) return null;

    const openBtn = openBtnId ? document.getElementById(openBtnId) : null;
    const closeBtn = closeBtnId ? document.getElementById(closeBtnId) : null;
    const cancelBtn = cancelBtnId ? document.getElementById(cancelBtnId) : null;

    if (openBtn) openBtn.onclick = () => overlay.classList.add('is-open');
    if (closeBtn) closeBtn.onclick = () => overlay.classList.remove('is-open');
    if (cancelBtn) cancelBtn.onclick = () => overlay.classList.remove('is-open');

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.classList.remove('is-open');
    });

    return overlay;
}

// --- Modal Ajouter Dépense ---
const addExpenseModal = bindModal({
    overlayId: 'addExpenseModal',
    openBtnId: 'openAddModal',
    closeBtnId: 'closeAddExpenseModal',
    cancelBtnId: 'cancelAddExpenseModal'
});

// --- Modal Salaire ---
bindModal({
    overlayId: 'salaryModal',
    openBtnId: 'openEditSalaryModal',
    closeBtnId: 'closeSalaryModal',
    cancelBtnId: 'cancelSalaryModal'
});

// --- Modal Modifier Dépense ---
const editExpenseModal = bindModal({
    overlayId: 'editExpenseModal',
    closeBtnId: 'closeEditExpenseModal',
    cancelBtnId: 'cancelEditExpeneModal'  // ⚠️ attention à la typo dans ton HTML
});

// ============================================
// Ouverture du modal Edit + remplissage
// ============================================
document.querySelectorAll('.icon-btn--edit').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('editExpenseId').value = btn.dataset.id || '';
        document.getElementById('editLibelle').value = btn.dataset.libelle || '';
        document.getElementById('editAmount').value = btn.dataset.amount || '';
        document.getElementById('editDate').value = btn.dataset.date || '';

        const categorySelect = document.getElementById('editCategory');
        if (btn.dataset.category) {
            categorySelect.value = btn.dataset.category;
        }

        editExpenseModal.classList.add('is-open');
    });
});

// ============================================
// Soumission du formulaire de modification
// ============================================
document.getElementById('editExpenseForm').addEventListener('submit', (e) => {
    e.preventDefault();

    const expenseId = document.getElementById('editExpenseId').value;
    const libelle = document.getElementById('editLibelle').value;
    const amount = document.getElementById('editAmount').value;
    const date = document.getElementById('editDate').value;
    const categoryId = document.getElementById('editCategory').value;

    // Validation basique
    if (!libelle || !amount || !date || !categoryId) {
        alert('Tous les champs sont obligatoires');
        return;
    }

});

// ============================================
// Soumission du formulaire d'ajout
// ============================================
document.querySelector('#addExpenseModal form').addEventListener('submit', (e) => {
    e.preventDefault();
    // À implémenter selon tes besoins
    console.log('Add expense form submitted');
});

// ============================================
// Filtres chips
// ============================================
    // --- Filtres chips (bascule des <tbody> entiers) ---
    document.querySelectorAll('.chip').forEach(chip => {
        chip.onclick = () => {
            const cat = chip.dataset.cat; // 'all' ou l'ID de catégorie en string

            document.querySelectorAll('.cat-group').forEach(group => {
                group.style.display = (group.dataset.cat === cat) ? '' : 'none';
            });

            document.querySelectorAll('.chip').forEach(c => c.classList.remove('chip--active'));
            chip.classList.add('chip--active');
        };
    });

// ============================================
// Suppression d'une dépense
// ============================================
document.querySelectorAll('.icon-btn--delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (confirm('Supprimer cette dépense ?')) {
            
        }
    });
});