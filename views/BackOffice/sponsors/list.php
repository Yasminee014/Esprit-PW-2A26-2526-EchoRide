<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Sponsor.php';

use Model\Sponsor;

$sponsorModel = new Sponsor();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;

$sponsors = $sponsorModel->getAllAdmin($search, $sort, $order, $page, $limit);
$totalSponsors = $sponsorModel->countAllAdmin($search);
$totalPages = ceil($totalSponsors / $limit);

if(isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="sponsors_'.date('Y-m-d').'.xls"');
    echo "<table border='1'> <table><th>ID</th><th>Entreprise</th><th>Montant</th><th>Type</th><th>Statut</th><th>Événement</th></tr>";
    foreach($sponsors as $s) echo "<tr><td>{$s['id']}</td><td>{$s['nom_entreprise']}</td><td>{$s['montant_sponsoring']}</td><td>{$s['type_sponsor']}</td><td>{$s['statut']}</td><td>{$s['event_titre']}</td></tr>";
    echo "</table>";
    exit();
}

if(isset($_GET['delete'])) {
    $sponsorModel->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit();
}

$nextOrder = ($order === 'ASC') ? 'DESC' : 'ASC';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Gestion Sponsors</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link rel="stylesheet" href="../../../style.css">
<style>
.btn-details {
    background: rgba(52, 152, 219, 0.15);
    color: #3498db;
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    text-decoration: none;
    font-size: 0.75rem;
    display: inline-block;
    margin: 0 0.2rem;
}
.btn-details:hover {
    background: #3498db;
    color: white;
}
.actions {
    white-space: nowrap;
}
.navbar-backoffice {
    background: linear-gradient(90deg, #1976D2, #0F3B6E);
    padding: 0.8rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
    position: sticky;
    top: 0;
    z-index: 100;
}
.navbar-backoffice .nav-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}
.navbar-backoffice .logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
}
.navbar-backoffice .logo i {
    color: #61B3FA;
    font-size: 1.5rem;
}
.navbar-backoffice .dropdown {
    position: relative;
    display: inline-block;
}
.navbar-backoffice .dropdown-btn {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 0.6rem 1.2rem;
    border: 1px solid rgba(97,179,250,.4);
    border-radius: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}
.navbar-backoffice .dropdown-content {
    display: none;
    position: absolute;
    top: 110%;
    left: 0;
    min-width: 220px;
    background: linear-gradient(145deg, #0D1F3A, #122A4A);
    border: 1px solid rgba(97,179,250,.3);
    border-radius: 12px;
    z-index: 200;
}
.navbar-backoffice .dropdown-content.show {
    display: block;
    animation: fadeInDown 0.25s ease;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.navbar-backoffice .dropdown-content a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1.2rem;
    color: #fff;
    text-decoration: none;
    font-size: 0.85rem;
}
.navbar-backoffice .dropdown-content a i {
    width: 20px;
    color: #61B3FA;
}
.navbar-backoffice .dropdown-content a:hover {
    background: rgba(97,179,250,.15);
    padding-left: 1.5rem;
}
.navbar-backoffice .dropdown-divider {
    height: 1px;
    background: rgba(97,179,250,.2);
    margin: 0.3rem 0;
}
.navbar-backoffice .nav-right .user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.1);
    padding: 0.4rem 1rem;
    border-radius: 30px;
}
.navbar-backoffice .nav-right .user-info i {
    color: #61B3FA;
}
.sidebar {
    position: fixed;
    left: 0;
    top: 70px;
    width: 280px;
    height: calc(100vh - 70px);
    background: #0D1F3A;
    border-right: 1px solid rgba(97,179,250,0.15);
    overflow-y: auto;
}
.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(97,179,250,0.15);
}
.sidebar-header .logo {
    display: flex;
    align-items: center;
    gap: 10px;
}
.sidebar-header .logo i {
    color: #61B3FA;
    font-size: 28px;
}
.sidebar-header .logo h2 {
    font-size: 1.3rem;
    background: linear-gradient(135deg, #61B3FA, #1976D2);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.sidebar-nav {
    padding: 0 1rem;
}
.sidebar-nav .nav-section {
    margin-bottom: 1.5rem;
}
.sidebar-nav .nav-section-title {
    color: #A7A9AC;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.8rem;
    padding-left: 0.5rem;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1rem;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s;
    margin-bottom: 0.3rem;
}
.sidebar-nav a i {
    width: 24px;
    color: #A7A9AC;
}
.sidebar-nav a:hover {
    background: rgba(97,179,250,0.1);
}
.sidebar-nav a:hover i {
    color: #61B3FA;
}
.sidebar-nav a.active {
    background: linear-gradient(135deg, #1976D2, #0F3B6E);
}
.main-content {
    margin-left: 280px;
    padding: 1.5rem;
    min-height: 100vh;
}
@media (max-width: 768px) {
    .sidebar {
        display: none;
    }
    .main-content {
        margin-left: 0;
    }
}
</style>
</head>
<body>

<nav class="navbar-backoffice">
    <div class="nav-left">
        <a href="../../../index.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>EcoRide - Admin</span>
        </a>
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </button>
            <div class="dropdown-content" id="dropdownMenu">
                <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="../events/list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
                <a href="list.php" class="active"><i class="fas fa-handshake"></i> Sponsors</a>
                <div class="dropdown-divider"></div>
                <a href="../../../index.php"><i class="fas fa-globe"></i> Voir le site</a>
            </div>
        </div>
    </div>
    <div class="nav-right">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span>Administrateur</span>
        </div>
    </div>
</nav>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h2>ECO RIDE</h2>
        </div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">ADMINISTRATION</div>
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="../events/list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="list.php" class="active"><i class="fas fa-handshake"></i> Sponsors</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">GESTION</div>
            <a href="#"><i class="fas fa-car"></i> Véhicules</a>
            <a href="#"><i class="fas fa-ticket-alt"></i> Réservations</a>
            <a href="#"><i class="fas fa-history"></i> Historique</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-handshake"></i> Gestion des sponsors</h2>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <form method="GET" class="search-bar">
                    <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">
                    <input type="hidden" name="order" value="<?= $order ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <select class="sort-bar" onchange="window.location.href=this.value" style="padding:0.5rem; border-radius:25px; background:rgba(255,255,255,0.08); color:white; border:1px solid rgba(97,179,250,0.2);">
                    <option value="?sort=id&order=DESC&search=<?= urlencode($search) ?>">ID (plus récent)</option>
                    <option value="?sort=id&order=ASC&search=<?= urlencode($search) ?>">ID (plus ancien)</option>
                    <option value="?sort=nom_entreprise&order=ASC&search=<?= urlencode($search) ?>">Nom (A→Z)</option>
                    <option value="?sort=nom_entreprise&order=DESC&search=<?= urlencode($search) ?>">Nom (Z→A)</option>
                    <option value="?sort=montant_sponsoring&order=DESC&search=<?= urlencode($search) ?>">Montant (plus élevé)</option>
                    <option value="?sort=montant_sponsoring&order=ASC&search=<?= urlencode($search) ?>">Montant (moins élevé)</option>
                </select>
                <a href="list.php" class="btn-reset" style="background:rgba(108,117,125,0.3); padding:0.5rem 1rem; border-radius:25px; text-decoration:none; color:white;">Réinitialiser</a>
            </div>
        </div>
        <div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
            <a href="?export_excel=1&search=<?= urlencode($search) ?>" class="btn-export-excel"><i class="fas fa-file-excel"></i> Excel</a>
            <button onclick="exportToPDF()" class="btn-export-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
            <a href="form.php" class="btn-primary"><i class="fas fa-plus"></i> Ajouter</a>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
        <div class="alert-success" style="background:rgba(39,174,96,0.15); color:#27ae60; padding:1rem; border-radius:10px; margin-bottom:1rem;">
            <?php if($_GET['success'] == 'added') echo '✓ Sponsor ajouté avec succès';
                  elseif($_GET['success'] == 'updated') echo '✓ Sponsor modifié avec succès';
                  elseif($_GET['success'] == 'deleted') echo '✓ Sponsor supprimé avec succès'; ?>
        </div>
        <?php endif; ?>
        
        <?php if(empty($sponsors)): ?>
            <p style="text-align:center;padding:2rem;">Aucun sponsor trouvé</p>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><a href="?sort=id&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">ID <?= $sort=='id' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                        <th><a href="?sort=nom_entreprise&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Entreprise <?= $sort=='nom_entreprise' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                        <th><a href="?sort=montant_sponsoring&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Montant <?= $sort=='montant_sponsoring' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Événement</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($sponsors as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['nom_entreprise']) ?></td>
                        <td><?= number_format($s['montant_sponsoring'], 0, ',', ' ') ?> DT</td>
                        <td><?= htmlspecialchars($s['type_sponsor'] ?? '-') ?></td>
                        <td><?= $s['statut'] ?></td>
                        <td><?= htmlspecialchars($s['event_titre'] ?? 'Non assigné') ?></td>
                        <td class="actions">
    <a href="detail.php?id=<?= $s['id'] ?>" class="btn-details" title="Détails"><i class="fas fa-eye"></i></a>
    <a href="form.php?id=<?= $s['id'] ?>" class="btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
    <a href="list.php?delete=<?= $s['id'] ?>" class="btn-delete" title="Supprimer" onclick="return confirmDelete()"><i class="fas fa-trash"></i></a>
</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($totalPages > 1): ?>
        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>"><i class="fas fa-chevron-left"></i> Précédent</a>
            <?php endif; ?>
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <?php if($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if($page < $totalPages): ?>
                <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>">Suivant <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div id="pdf-content" style="display:none;">
    <h2 style="text-align:center;">Liste des sponsors</h2>
    <p>Date d'export : <?= date('d/m/Y H:i') ?></p>
    <table border="1"><thead><tr><th>ID</th><th>Entreprise</th><th>Montant</th><th>Type</th><th>Statut</th><th>Événement</th></tr></thead>
    <tbody><?php foreach($sponsors as $s): ?><tr><td><?= $s['id'] ?></td><td><?= $s['nom_entreprise'] ?></td><td><?= number_format($s['montant_sponsoring'], 0, ',', ' ') ?> DT</td><td><?= $s['type_sponsor'] ?? '-' ?></td><td><?= $s['statut'] ?></td><td><?= $s['event_titre'] ?? 'Non assigné' ?></td></tr><?php endforeach; ?></tbody>
    </table>
</div>

<footer><p>Eco Ride © 2025 - Administration</p></footer>

<script>
function confirmDelete() { return confirm('Supprimer ?'); }
function toggleMenu() { document.getElementById("dropdownMenu").classList.toggle("show"); }
window.onclick = function(e) { if (!e.target.matches('.dropdown-btn') && !e.target.closest('.dropdown-btn')) { var d = document.getElementById("dropdownMenu"); if (d && d.classList.contains('show')) d.classList.remove('show'); } }
function exportToPDF() { var e = document.getElementById('pdf-content'); e.style.display = 'block'; html2pdf().set({ margin: 10, filename: 'sponsors_<?= date('Y-m-d') ?>.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } }).from(e).save().then(() => e.style.display = 'none'); }
</script>
</body>
</html>