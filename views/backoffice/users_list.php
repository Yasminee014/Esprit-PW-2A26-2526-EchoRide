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
        .sidebar { width:260px; background:#0D1F3A; position:fixed; height:100vh; padding:1.5rem 1rem; overflow-y:auto; border-right:1px solid #1976D2; display:flex; flex-direction:column; }
        .sidebar-logo { display:flex; align-items:center; gap:10px; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:2px solid #1976D2; }
        .sidebar-logo i { font-size:28px; color:#1976D2; }
        .sidebar-logo h2 { font-size:1.3rem; }
        .sidebar-logo span { color:#1976D2; }
        .admin-info { background:rgba(25,118,210,0.1); border-radius:12px; padding:0.8rem; margin-bottom:1.5rem; text-align:center; border:1px solid rgba(25,118,210,0.2); }
        .admin-info i { font-size:1.5rem; color:#1976D2; display:block; margin-bottom:0.3rem; }
        .admin-info small { color:#A7A9AC; font-size:0.75rem; display:block; }
        .nav-section { color:#A7A9AC; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:1rem 0 0.5rem 0.5rem; }
        .nav-item { display:flex; align-items:center; gap:12px; padding:0.75rem 1rem; color:#fff; text-decoration:none; border-radius:12px; margin-bottom:0.3rem; transition:all 0.3s; font-size:0.9rem; }
        .nav-item i { width:20px; color:#A7A9AC; transition:color 0.3s; }
        .nav-item:hover, .nav-item.active { background:rgba(25,118,210,0.2); color:#1976D2; }
        .nav-item:hover i, .nav-item.active i { color:#1976D2; }
        .sidebar-footer { margin-top:auto; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.1); }
        .logout-btn { display:flex; align-items:center; gap:10px; padding:0.75rem 1rem; color:#ff6b6b; text-decoration:none; border-radius:12px; transition:all 0.3s; font-size:0.9rem; }
        .logout-btn:hover { background:rgba(255,68,68,0.2); }
        .main-content { margin-left:260px; padding:2rem; flex:1; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.1); }
        .top-bar h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; }
        .top-bar h1 i { color:#1976D2; }
        .btn-add { background:linear-gradient(135deg,#1976D2,#1976D2); color:white; padding:0.5rem 1.2rem; border-radius:20px; text-decoration:none; font-size:0.85rem; transition:all 0.3s; display:flex; align-items:center; gap:8px; }
        .btn-add:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(25,118,210,0.3); }
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

        /* ── Top-bar style image ── */
        .top-bar {
            background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%) !important;
            border-radius: 16px !important;
            padding: 0.75rem 1.5rem !important;
            border: 1px solid rgba(97,179,250,0.18) !important;
            box-shadow: 0 4px 24px rgba(0,0,0,0.25) !important;
            border-bottom: none !important;
            margin-bottom: 2rem !important;
        }
        .navbar-logo { display:flex; flex-direction:column; line-height:1.2; }
        .navbar-logo strong { font-size:1.45rem; font-weight:800; color:#61B3FA; letter-spacing:0.05em; }
        .navbar-logo span { font-size:0.72rem; color:rgba(255,255,255,0.75); letter-spacing:0.08em; }
        .btn-top, .btn-nav.btn-home {
            background: transparent !important;
            color: #fff !important;
            padding: 0.4rem 1rem !important;
            border-radius: 20px !important;
            text-decoration: none !important;
            font-size: 0.9rem !important;
            font-weight: 500 !important;
            border: none !important;
            transition: background 0.2s !important;
            white-space: nowrap;
        }
        .btn-top:hover, .btn-nav.btn-home:hover { background: rgba(255,255,255,0.12) !important; }
        .btn-admin-profile, .btn-admin-active {
            display: inline-flex !important; align-items: center !important; gap: 8px !important;
            background: #922B21 !important; color: #fff !important;
            border: none !important;
            padding: 0.4rem 1.1rem 0.4rem 0.4rem !important;
            border-radius: 25px !important; font-size: 0.9rem !important;
            cursor: pointer !important; font-weight: 700 !important;
            transition: all 0.3s !important; text-decoration: none !important;
        }
        .btn-admin-profile:hover, .btn-admin-active:hover {
            background: #C0392B !important;
            box-shadow: 0 4px 15px rgba(192,57,43,0.45) !important;
        }
        .btn-admin-profile .admin-avatar-btn,
        .btn-admin-active .admin-avatar-btn {
            width:30px; height:30px; border-radius:50%; overflow:hidden;
            display:flex; align-items:center; justify-content:center;
            background:rgba(255,255,255,0.2); border:2px solid rgba(255,255,255,0.5); flex-shrink:0;
        }
        .btn-admin-profile .admin-avatar-btn img,
        .btn-admin-active .admin-avatar-btn img { width:100%; height:100%; object-fit:cover; }
        .btn-admin-profile .admin-avatar-btn i,
        .btn-admin-active .admin-avatar-btn i { font-size:0.85rem; color:#fff; }
        .btn-theme-toggle {
            width:34px; height:34px; border-radius:50%;
            background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.22);
            color:#fff; font-size:0.92rem;
            display:inline-flex; align-items:center; justify-content:center;
            cursor:pointer; transition:all 0.25s; flex-shrink:0;
        }
        .btn-theme-toggle:hover { background:rgba(255,255,255,0.18); }
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .section-card, body.light-mode .form-card,
        body.light-mode .stat-card { background:rgba(255,255,255,.95) !important; }
        body.light-mode td { color:#1A2844 !important; }

    </style>
</head>
<body>
<?php
$adminSuccess = $_SESSION['admin_success'] ?? '';
$adminError   = $_SESSION['admin_error']   ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <svg width="32" height="32" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 0 8px rgba(97,179,250,.45))"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="url(#lg_s)" opacity="0.95"/><path d="M22 38L22 12" stroke="rgba(255,255,255,0.3)" stroke-width="1.2" stroke-linecap="round"/><defs><linearGradient id="lg_s" x1="12" y1="4" x2="36" y2="38" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#61B3FA"/><stop offset="100%" stop-color="#1976D2"/></linearGradient></defs></svg>
        <h2>Eco<span>Ride</span></h2>
    </div>
    <div class="admin-info">
        <i class="fas fa-user-shield"></i>
        <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
        <small><?= htmlspecialchars($_SESSION['admin_email']) ?></small>
    </div>
    <span class="nav-section">Navigation</span>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="nav-item">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=listUsers" class="nav-item active">
        <i class="fas fa-users"></i> Utilisateurs
    </a>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showAddUser" class="nav-item">
        <i class="fas fa-user-plus"></i> Ajouter utilisateur
    </a>
    <span class="nav-section">Site</span>
    <a href="<?= BASE_URL ?>controllers/UserController.php?action=index" class="nav-item">
        <i class="fas fa-globe"></i> Voir le site
    </a>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-content">
    <div class="top-bar">
        <div class="navbar-logo">
            <strong>ECO RIDE</strong>
            <span>Covoiturage Intelligent</span>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#hero" class="btn-top">Accueil</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#evenements" class="btn-top">Événements</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#sponsors" class="btn-top">Sponsors</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=covoiturage" class="btn-top">Covoiturage</a>
                    <a href="<?= BASE_URL ?>controllers/UserController.php?action=lostFound" class="btn-top">Lost &amp; Found</a>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                        <div class="admin-avatar-btn">
                            <?php if (!empty($_SESSION['admin_photo'])): ?>
                                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="">
                            <?php else: ?>
                                <i class="fas fa-user-shield"></i>
                            <?php endif; ?>
                        </div>
                        Admin
                    </a>
                    <button class="btn-theme-toggle" onclick="toggleTheme()" title="Mode sombre / clair">
                        <i class="fas fa-moon themeIcon"></i>
                    </button>
            <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showAddUser" class="btn-add" style="margin-left:0.5rem;">
                <i class="fas fa-plus"></i> Ajouter
            </a>
        </div>
    </div>

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
</body>
</html>