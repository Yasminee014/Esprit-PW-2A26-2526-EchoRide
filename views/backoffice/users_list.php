<?php require_once __DIR__ . '/partials/partials.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Gestion Utilisateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:#0A1628; color:#fff; min-height:100vh; display:flex; }
        .main-content { margin-left:260px; padding:2rem; flex:1; }
        .filters-bar { background:rgba(13,31,58,0.9); border-radius:16px; padding:1.2rem 1.5rem; border:1px solid rgba(25,118,210,0.2); margin-bottom:1.5rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .search-input { flex:1; min-width:200px; padding:0.6rem 1rem; border-radius:25px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:0.9rem; }
        .search-input:focus { outline:none; border-color:#1976D2; }
        .filter-select { padding:0.6rem 1rem; border-radius:25px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:0.9rem; cursor:pointer; }
        .section-card { background:rgba(13,31,58,0.9); border-radius:20px; padding:1.5rem; border:1px solid rgba(25,118,210,0.2); }
        .table-wrapper { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:0.9rem; }
        thead th { background:rgba(25,118,210,0.15); color:#1976D2; padding:0.9rem 1rem; text-align:left; font-weight:600; }
        tbody tr { border-bottom:1px solid rgba(255,255,255,0.05); transition:background 0.2s; }
        tbody tr:hover { background:rgba(25,118,210,0.05); }
        td { padding:0.8rem 1rem; color:#A7A9AC; vertical-align:middle; }
        .badge { display:inline-block; padding:0.25rem 0.75rem; border-radius:20px; font-size:0.75rem; font-weight:600; }
        .badge-actif     { background:rgba(0,200,100,0.2); color:#4cff9a; border:1px solid rgba(0,200,100,0.3); }
        .badge-inactif   { background:rgba(255,68,68,0.2);  color:#ff6b6b; border:1px solid rgba(255,68,68,0.3);  }
        .badge-passager  { background:rgba(25,118,210,0.2); color:#1976D2; border:1px solid rgba(25,118,210,0.3); }
        .badge-conducteur{ background:rgba(255,165,0,0.2); color:#ffa500; border:1px solid rgba(255,165,0,0.3); }
        .action-btns { display:flex; gap:0.5rem; }
        .btn-icon { background:none; border:1px solid rgba(25,118,210,0.3); color:#1976D2; padding:0.4rem 0.7rem; border-radius:8px; cursor:pointer; transition:all 0.3s; text-decoration:none; font-size:0.85rem; display:inline-flex; align-items:center; gap:4px; }
        .btn-icon.delete { border-color:rgba(255,68,68,0.3); color:#ff6b6b; }
        .btn-icon:hover { background:rgba(25,118,210,0.2); }
        .btn-icon.delete:hover { background:rgba(255,68,68,0.2); }
        .alert { padding:0.7rem 1rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:0.9rem; }
        .alert-success { background:rgba(0,200,100,0.15); border:1px solid rgba(0,200,100,0.4); color:#4cff9a; }
        .alert-error   { background:rgba(255,68,68,0.15);  border:1px solid rgba(255,68,68,0.4);  color:#ff6b6b; }
        .empty-state { text-align:center; padding:3rem; color:#A7A9AC; }
        .empty-state i { font-size:3rem; color:#1976D2; display:block; margin-bottom:1rem; }
        .count-badge { background:rgba(25,118,210,0.2); color:#1976D2; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.8rem; margin-left:0.5rem; }
        .pagination-wrapper { display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; flex-wrap:wrap; gap:1rem; }
        .pagination-info { color:#A7A9AC; font-size:0.85rem; }
        .pagination-info span { color:#1976D2; font-weight:600; }
        .pagination-btns { display:flex; gap:0.4rem; align-items:center; }
        .page-btn { background:rgba(13,31,58,0.9); border:1px solid rgba(25,118,210,0.3); color:#A7A9AC; padding:0.4rem 0.8rem; border-radius:8px; cursor:pointer; font-size:0.85rem; transition:all 0.2s; min-width:36px; text-align:center; }
        .page-btn:hover { background:rgba(25,118,210,0.15); color:#1976D2; border-color:#1976D2; }
        .page-btn.active { background:rgba(25,118,210,0.3); color:#1976D2; border-color:#1976D2; font-weight:700; }
        .page-btn:disabled { opacity:0.3; cursor:not-allowed; }
        .page-btn.nav { padding:0.4rem 0.7rem; }

        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .stat-card { background:rgba(255,255,255,.95) !important; }
        body.light-mode td { color:#1A2844 !important; }

    </style>
<?php render_nav_css(); ?>
<?php require_once __DIR__ . '/partials/partials.php'; ?>
</head>
<body>
<?php
$adminSuccess = $_SESSION['admin_success'] ?? '';
$adminError   = $_SESSION['admin_error']   ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>

<!-- SIDEBAR -->
<?php require_once __DIR__ . '/partials/partials.php'; ?>
<?php sidebar_compact('users'); ?>

<!-- MAIN -->
<main class="main-content">
<?php navbar_compact('<a href="' . BASE_URL . 'controllers/AdminController.php?action=showAddUser" class="btn-add" style="margin-left:0.5rem;"><i class="fas fa-plus"></i> Ajouter</a>'); ?>

    <?php if ($adminSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($adminSuccess) ?></div>
    <?php endif; ?>
    <?php if ($adminError): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($adminError) ?></div>
    <?php endif; ?>

    <!-- FILTRES -->
    <div class="filters-bar">
        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Rechercher par nom, email..." onkeyup="filterTable()">
        <select id="roleFilter" class="filter-select" onchange="filterTable()">
            <option value="">Tous les rôles</option>
            <option value="passager">Passagers</option>
            <option value="conducteur">Conducteurs</option>
        </select>
        <select id="statusFilter" class="filter-select" onchange="filterTable()">
            <option value="">Tous les statuts</option>
            <option value="actif">Actifs</option>
            <option value="inactif">Inactifs</option>
        </select>
    </div>

    <!-- TABLE -->
    <div class="section-card">
        <div class="table-wrapper">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="fas fa-user"></i> Nom complet</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-phone"></i> Téléphone</th>
                        <th><i class="fas fa-id-badge"></i> Rôle</th>
                        <th><i class="fas fa-circle"></i> Statut</th>
                        <th><i class="fas fa-calendar"></i> Inscrit le</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p>Aucun utilisateur trouvé</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                        <tr data-nom="<?= strtolower(htmlspecialchars($u['prenom'] . ' ' . $u['nom'])) ?>"
                            data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>"
                            data-role="<?= htmlspecialchars($u['role']) ?>"
                            data-statut="<?= htmlspecialchars($u['statut']) ?>">
                            <td><?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['telephone']) ?></td>
                            <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst(htmlspecialchars($u['role'])) ?></span></td>
                            <td><span class="badge badge-<?= $u['statut'] ?>"><?= ucfirst(htmlspecialchars($u['statut'])) ?></span></td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['created_at']))) ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showEditUser&id=<?= $u['id'] ?>" class="btn-icon">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=deleteUser&id=<?= $u['id'] ?>" class="btn-icon delete"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer <?= addslashes($u['prenom'] . ' ' . $u['nom']) ?> ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- PAGINATION -->
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Affichage <span id="pageStart">1</span>–<span id="pageEnd">5</span> sur <span id="totalVisible">0</span> utilisateur(s)
            </div>
            <div class="pagination-btns" id="paginationBtns"></div>
        </div>
    </div>
</main>

<script>
const ROWS_PER_PAGE = 5;
let currentPage = 1;

function getVisibleRows() {
    return Array.from(document.querySelectorAll('#tableBody tr')).filter(r => r.dataset.nom !== undefined);
}

function applyFiltersAndPaginate() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const role   = document.getElementById('roleFilter').value;
    const statut = document.getElementById('statusFilter').value;
    const rows   = getVisibleRows();

    // Filtrage
    const filtered = rows.filter(row => {
        const nom     = row.dataset.nom   || '';
        const email   = row.dataset.email || '';
        const rRole   = row.dataset.role  || '';
        const rStatut = row.dataset.statut|| '';
        return (nom.includes(search) || email.includes(search))
            && (!role   || rRole   === role)
            && (!statut || rStatut === statut);
    });

    // Réinitialiser page si hors limites
    const totalPages = Math.max(1, Math.ceil(filtered.length / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    // Masquer toutes les lignes
    rows.forEach(r => r.style.display = 'none');

    // Afficher uniquement la page courante des lignes filtrées
    const start = (currentPage - 1) * ROWS_PER_PAGE;
    const pageRows = filtered.slice(start, start + ROWS_PER_PAGE);
    pageRows.forEach(r => r.style.display = '');

    // Infos
    document.getElementById('pageStart').textContent   = filtered.length ? start + 1 : 0;
    document.getElementById('pageEnd').textContent     = Math.min(start + ROWS_PER_PAGE, filtered.length);
    document.getElementById('totalVisible').textContent = filtered.length;

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const container = document.getElementById('paginationBtns');
    container.innerHTML = '';

    // Bouton Précédent
    const prev = document.createElement('button');
    prev.className = 'page-btn nav';
    prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prev.disabled = currentPage === 1;
    prev.onclick = () => { currentPage--; applyFiltersAndPaginate(); };
    container.appendChild(prev);

    // Numéros de page
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
        btn.textContent = i;
        btn.onclick = ((p) => () => { currentPage = p; applyFiltersAndPaginate(); })(i);
        container.appendChild(btn);
    }

    // Bouton Suivant
    const next = document.createElement('button');
    next.className = 'page-btn nav';
    next.innerHTML = '<i class="fas fa-chevron-right"></i>';
    next.disabled = currentPage === totalPages;
    next.onclick = () => { currentPage++; applyFiltersAndPaginate(); };
    container.appendChild(next);
}

function filterTable() {
    currentPage = 1;
    applyFiltersAndPaginate();
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => applyFiltersAndPaginate());

    function toggleTheme() {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        document.querySelectorAll('.themeIcon').forEach(i => {
            i.className = isLight ? 'fas fa-sun themeIcon' : 'fas fa-moon themeIcon';
        });
        localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
    }
    (function() {
        if (localStorage.getItem('ecoride_theme') === 'light') {
            document.body.classList.add('light-mode');
            document.querySelectorAll('.themeIcon').forEach(i => { i.className = 'fas fa-sun themeIcon'; });
        }
    })();

    </script>
<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>