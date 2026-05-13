<?php
require_once __DIR__ . '/partials/partials.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'Controller/AdminController.php?action=showLogin');
    exit();
}
// $users injecté par listUsers() — on filtre les passagers uniquement
$passagers = array_values(array_filter($users ?? [], fn($u) => ($u['role'] ?? '') === 'passager'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride — Gestion Passagers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:#0A1628; color:#fff; min-height:100vh; display:flex; }
        .main-content { margin-left:260px; padding:2rem; flex:1; }
        /* ── Page title ── */
        .page-title { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
        .page-title h1 { font-size:1.4rem; font-weight:700; display:flex; align-items:center; gap:10px; color:#fff; }
        .page-title h1 i { color:#1976D2; }
        .count-badge { background:rgba(25,118,210,0.2); color:#61B3FA; padding:0.2rem 0.7rem; border-radius:20px; font-size:0.8rem; font-weight:600; margin-left:0.5rem; }
        /* ── Alerts ── */
        .alert { padding:0.75rem 1rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:0.9rem; }
        .alert-success { background:rgba(0,200,100,0.15); border:1px solid rgba(0,200,100,0.4); color:#4cff9a; }
        .alert-error   { background:rgba(255,68,68,0.15);  border:1px solid rgba(255,68,68,0.4);  color:#ff6b6b; }
        /* ── Filters bar ── */
        .filters-bar { background:rgba(13,31,58,0.9); border-radius:16px; padding:1.2rem 1.5rem; border:1px solid rgba(25,118,210,0.2); margin-bottom:1.5rem; display:flex; gap:1rem; flex-wrap:wrap; align-items:center; }
        .search-wrap { position:relative; flex:1; min-width:220px; }
        .search-wrap i { position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:#A7A9AC; font-size:0.85rem; pointer-events:none; }
        .search-input { width:100%; padding:0.6rem 1rem 0.6rem 2.4rem; border-radius:25px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:#fff; font-size:0.9rem; }
        .search-input::placeholder { color:#A7A9AC; }
        .search-input:focus { outline:none; border-color:#1976D2; }
        .filter-select { padding:0.6rem 1rem; border-radius:25px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:#fff; font-size:0.9rem; cursor:pointer; min-width:160px; }
        .filter-select:focus { outline:none; border-color:#1976D2; }
        .btn-pdf { display:inline-flex; align-items:center; gap:8px; background:#e53e3e; color:#fff; padding:0.6rem 1.2rem; border-radius:25px; border:none; font-size:0.9rem; font-weight:600; cursor:pointer; text-decoration:none; transition:background 0.2s; white-space:nowrap; }
        .btn-pdf:hover { background:#c53030; }
        /* ── Section card ── */
        .section-card { background:rgba(13,31,58,0.9); border-radius:20px; padding:1.5rem; border:1px solid rgba(25,118,210,0.2); }
        .table-wrapper { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; font-size:0.9rem; }
        thead th { background:rgba(25,118,210,0.15); color:#1976D2; padding:0.9rem 1rem; text-align:left; font-weight:600; white-space:nowrap; }
        tbody tr { border-bottom:1px solid rgba(255,255,255,0.05); transition:background 0.2s; }
        tbody tr:hover { background:rgba(25,118,210,0.05); }
        td { padding:0.8rem 1rem; color:#A7A9AC; vertical-align:middle; }
        td strong { color:#F4F5F7; }
        /* ── Badges ── */
        .badge { display:inline-block; padding:0.25rem 0.8rem; border-radius:20px; font-size:0.78rem; font-weight:600; }
        .badge-actif   { background:rgba(0,200,100,0.2); color:#4cff9a; border:1px solid rgba(0,200,100,0.35); }
        .badge-inactif { background:rgba(255,68,68,0.2);  color:#ff6b6b; border:1px solid rgba(255,68,68,0.35); }
        /* ── Action buttons ── */
        .action-btns { display:flex; gap:0.4rem; }
        .btn-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; cursor:pointer; text-decoration:none; transition:all 0.2s; font-size:0.85rem; border:1px solid transparent; }
        .btn-view  { background:rgba(25,118,210,0.15); color:#61B3FA; border-color:rgba(25,118,210,0.3); }
        .btn-view:hover  { background:rgba(25,118,210,0.3); }
        .btn-edit  { background:rgba(25,118,210,0.15); color:#61B3FA; border-color:rgba(25,118,210,0.3); }
        .btn-edit:hover  { background:rgba(25,118,210,0.3); }
        .btn-ban   { background:rgba(255,68,68,0.15); color:#ff6b6b; border-color:rgba(255,68,68,0.3); }
        .btn-ban:hover   { background:rgba(255,68,68,0.3); }
        .btn-unban { background:rgba(0,200,100,0.15); color:#4cff9a; border-color:rgba(0,200,100,0.3); }
        .btn-unban:hover { background:rgba(0,200,100,0.3); }
        /* ── Empty state ── */
        .empty-state { text-align:center; padding:3rem; color:#A7A9AC; }
        .empty-state i { font-size:3rem; color:#1976D2; display:block; margin-bottom:1rem; }
        /* ── Pagination ── */
        .pagination-wrapper { display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; flex-wrap:wrap; gap:1rem; }
        .pagination-info { color:#A7A9AC; font-size:0.85rem; }
        .pagination-info span { color:#1976D2; font-weight:600; }
        .pagination-btns { display:flex; gap:0.4rem; align-items:center; }
        .page-btn { background:rgba(13,31,58,0.9); border:1px solid rgba(25,118,210,0.3); color:#A7A9AC; padding:0.4rem 0.8rem; border-radius:8px; cursor:pointer; font-size:0.85rem; transition:all 0.2s; min-width:36px; text-align:center; }
        .page-btn:hover  { background:rgba(25,118,210,0.15); color:#1976D2; border-color:#1976D2; }
        .page-btn.active { background:rgba(25,118,210,0.3); color:#1976D2; border-color:#1976D2; font-weight:700; }
        .page-btn:disabled { opacity:0.3; cursor:not-allowed; }
        /* ── Light mode ── */
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .section-card, body.light-mode .filters-bar { background:rgba(255,255,255,0.95) !important; }
        body.light-mode td { color:#1A2844 !important; }
        body.light-mode td strong { color:#0A1628 !important; }
        body.light-mode thead th { background:rgba(25,118,210,0.1) !important; }
    </style>
<?php render_nav_css(); ?>
</head>
<body>
<?php
$adminSuccess = $_SESSION['admin_success'] ?? '';
$adminError   = $_SESSION['admin_error']   ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>

<!-- SIDEBAR -->
<?php sidebar_dashboard('passagers'); ?>

<!-- MAIN -->
<main class="main-content">
<?php navbar_dashboard(); ?>

    <?php if ($adminSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($adminSuccess) ?></div>
    <?php endif; ?>
    <?php if ($adminError): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($adminError) ?></div>
    <?php endif; ?>

    <!-- TITRE -->
    <div class="page-title">
        <h1>
            <i class="fas fa-users"></i>
            Gestion des Passagers
            <span class="count-badge"><?= count($passagers) ?></span>
        </h1>
    </div>

    <!-- FILTRES -->
    <div class="filters-bar">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" class="search-input"
                   placeholder="Rechercher un passager..."
                   oninput="filterTable()">
        </div>
        <select id="statusFilter" class="filter-select" onchange="filterTable()">
            <option value="">Tous les statuts</option>
            <option value="actif">Actif</option>
            <option value="inactif">Banni</option>
        </select>
        <a href="<?= BASE_URL ?>Controller/AdminController.php?action=exportPassagersPDF"
           class="btn-pdf">
            <i class="fas fa-file-pdf"></i> Exporter PDF
        </a>
    </div>

    <!-- TABLE -->
    <div class="section-card">
        <div class="table-wrapper">
            <table id="passagersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($passagers)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p>Aucun passager trouvé</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($passagers as $p): ?>
                        <tr data-nom="<?= strtolower(htmlspecialchars($p['prenom'] . ' ' . $p['nom'])) ?>"
                            data-email="<?= strtolower(htmlspecialchars($p['email'])) ?>"
                            data-tel="<?= htmlspecialchars($p['telephone'] ?? '') ?>"
                            data-statut="<?= htmlspecialchars($p['statut']) ?>">
                            <td><?= (int)$p['id'] ?></td>
                            <td><strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($p['email']) ?></td>
                            <td><?= htmlspecialchars($p['telephone'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= $p['statut'] === 'actif' ? 'actif' : 'inactif' ?>">
                                    <?= $p['statut'] === 'actif' ? 'Actif' : 'Banni' ?>
                                </span>
                            </td>
                            <td><?= !empty($p['created_at']) ? htmlspecialchars(date('d/m/Y', strtotime($p['created_at']))) : '—' ?></td>
                            <td>
                                <div class="action-btns">
                                    <a href="<?= BASE_URL ?>Controller/AdminController.php?action=showPassagerDetailsPage&id=<?= (int)$p['id'] ?>"
                                       class="btn-icon btn-view" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>Controller/AdminController.php?action=showEditPassager&id=<?= (int)$p['id'] ?>"
                                       class="btn-icon btn-edit" title="Modifier">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <?php if ($p['statut'] === 'actif'): ?>
                                    <a href="<?= BASE_URL ?>Controller/AdminController.php?action=banPassager&id=<?= (int)$p['id'] ?>"
                                       class="btn-icon btn-ban" title="Bannir"
                                       onclick="return confirm('Bannir <?= addslashes(htmlspecialchars($p['prenom'] . ' ' . $p['nom'])) ?> ?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= BASE_URL ?>Controller/AdminController.php?action=unbanPassager&id=<?= (int)$p['id'] ?>"
                                       class="btn-icon btn-unban" title="Réactiver">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                    <?php endif; ?>
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
                Affichage <span id="pageStart">1</span>–<span id="pageEnd">5</span>
                sur <span id="totalVisible">0</span> passager(s)
            </div>
            <div class="pagination-btns" id="paginationBtns"></div>
        </div>
    </div>
</main>

<script>
const ROWS_PER_PAGE = 5;
let currentPage = 1;

function getDataRows() {
    return Array.from(document.querySelectorAll('#tableBody tr[data-nom]'));
}

function applyFiltersAndPaginate() {
    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    const statut = document.getElementById('statusFilter').value;
    const rows   = getDataRows();

    const filtered = rows.filter(row => {
        const nom   = row.dataset.nom   || '';
        const email = row.dataset.email || '';
        const tel   = row.dataset.tel   || '';
        const st    = row.dataset.statut|| '';
        const matchSearch = !search || nom.includes(search) || email.includes(search) || tel.includes(search);
        const matchStatut = !statut || st === statut;
        return matchSearch && matchStatut;
    });

    const totalPages = Math.max(1, Math.ceil(filtered.length / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    rows.forEach(r => r.style.display = 'none');

    const start    = (currentPage - 1) * ROWS_PER_PAGE;
    const pageRows = filtered.slice(start, start + ROWS_PER_PAGE);
    pageRows.forEach(r => r.style.display = '');

    document.getElementById('pageStart').textContent    = filtered.length ? start + 1 : 0;
    document.getElementById('pageEnd').textContent      = Math.min(start + ROWS_PER_PAGE, filtered.length);
    document.getElementById('totalVisible').textContent = filtered.length;

    renderPagination(totalPages);
}

function renderPagination(totalPages) {
    const container = document.getElementById('paginationBtns');
    container.innerHTML = '';

    const prev = document.createElement('button');
    prev.className = 'page-btn';
    prev.innerHTML = '<i class="fas fa-chevron-left"></i> Précédent';
    prev.disabled = currentPage === 1;
    prev.onclick = () => { currentPage--; applyFiltersAndPaginate(); };
    container.appendChild(prev);

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
        btn.textContent = i;
        btn.onclick = ((p) => () => { currentPage = p; applyFiltersAndPaginate(); })(i);
        container.appendChild(btn);
    }

    const next = document.createElement('button');
    next.className = 'page-btn';
    next.innerHTML = 'Suivant <i class="fas fa-chevron-right"></i>';
    next.disabled = currentPage === totalPages;
    next.onclick = () => { currentPage++; applyFiltersAndPaginate(); };
    container.appendChild(next);
}

function filterTable() {
    currentPage = 1;
    applyFiltersAndPaginate();
}

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