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
        <h1><i class="fas fa-users"></i> Gestion des Utilisateurs
            <span class="count-badge"><?= count($users) ?></span>
        </h1>
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showAddUser" class="btn-add">
            <i class="fas fa-plus"></i> Ajouter
        </a>
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
    </div>
</main>

<script>
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const role   = document.getElementById('roleFilter').value;
    const statut = document.getElementById('statusFilter').value;
    const rows   = document.querySelectorAll('#tableBody tr');

    rows.forEach(row => {
        const nom    = row.dataset.nom   || '';
        const email  = row.dataset.email || '';
        const rRole  = row.dataset.role  || '';
        const rStatut= row.dataset.statut|| '';

        const matchSearch = nom.includes(search) || email.includes(search);
        const matchRole   = !role   || rRole   === role;
        const matchStatut = !statut || rStatut === statut;

        row.style.display = (matchSearch && matchRole && matchStatut) ? '' : 'none';
    });
}
</script>
</body>
</html>