<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Sponsor.php';
require_once __DIR__ . '/../partials/partials.php';

use Model\Sponsor;

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}

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
    echo "<table border='1'> <tr><th>ID</th><th>Entreprise</th><th>Montant</th><th>Type</th><th>Statut</th><th>Événement</th></tr>";
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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<?php render_nav_css(); ?>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
        min-height: 100vh;
        color: #F4F5F7;
    }

    .card {
        background: rgba(13, 31, 45, 0.9);
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid rgba(25,118,210,0.3);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .card-header h2 {
        color: #61B3FA;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* ✅ Bouton Dashboard Événements */
    .btn-dashboard-event {
        background: linear-gradient(135deg, #8E44AD, #9B59B6);
        color: white;
        padding: 0.5rem 1.2rem;
        border-radius: 20px;
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-dashboard-event:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #9B59B6, #8E44AD);
    }
    
    .search-bar {
        display: flex;
        gap: 0.5rem;
    }
    
    .search-bar input {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        border: 1px solid rgba(25,118,210,0.3);
        background: rgba(10,22,40,0.8);
        color: white;
        font-size: 0.85rem;
    }
    
    .search-bar input:focus {
        outline: none;
        border-color: #1976D2;
    }
    
    .search-bar button {
        background: rgba(25,118,210,0.2);
        border: none;
        color: #1976D2;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        cursor: pointer;
    }
    
    .sort-bar {
        padding: 0.5rem;
        border-radius: 25px;
        background: rgba(10,22,40,0.8);
        color: white;
        border: 1px solid rgba(97,179,250,0.2);
    }
    
    .btn-reset {
        background: rgba(108,117,125,0.3);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        text-decoration: none;
        color: white;
        font-size: 0.85rem;
    }
    
    .btn-export-excel, .btn-export-pdf, .btn-primary {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        text-decoration: none;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-export-excel {
        background: rgba(39,174,96,0.2);
        color: #27ae60;
        border: 1px solid rgba(39,174,96,0.3);
    }
    
    .btn-export-pdf {
        background: rgba(231,76,60,0.2);
        color: #e74c3c;
        border: 1px solid rgba(231,76,60,0.3);
        cursor: pointer;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #1976D2, #1565C0);
        color: white;
    }
    
    .table-wrapper {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    
    thead th {
        background: rgba(25,118,210,0.15);
        color: #1976D2;
        padding: 0.9rem 1rem;
        text-align: left;
        font-weight: 600;
    }
    
    thead th a {
        color: #1976D2;
        text-decoration: none;
    }
    
    tbody tr {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.2s;
    }
    
    tbody tr:hover {
        background: rgba(25,118,210,0.05);
    }
    
    td {
        padding: 0.8rem 1rem;
        color: #A7A9AC;
        vertical-align: middle;
    }
    
    .actions {
        white-space: nowrap;
    }
    
    .btn-details, .btn-edit, .btn-delete {
        display: inline-block;
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.8rem;
        margin: 0 0.2rem;
    }
    
    .btn-details {
        background: rgba(52,152,219,0.15);
        color: #3498db;
    }
    
    .btn-edit {
        background: rgba(25,118,210,0.15);
        color: #1976D2;
    }
    
    .btn-delete {
        background: rgba(231,76,60,0.15);
        color: #e74c3c;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    
    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        background: rgba(13,31,58,0.9);
        border: 1px solid rgba(25,118,210,0.3);
        color: #A7A9AC;
    }
    
    .pagination a:hover {
        background: rgba(25,118,210,0.2);
        color: #1976D2;
    }
    
    .pagination .active {
        background: rgba(25,118,210,0.3);
        color: #1976D2;
        border-color: #1976D2;
    }
    
    .alert-success {
        background: rgba(39,174,96,0.15);
        color: #27ae60;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px solid rgba(39,174,96,0.3);
    }
    
    footer {
        text-align: center;
        padding: 2rem;
        border-top: 1px solid rgba(25,118,210, 0.2);
        color: #A7A9AC;
        margin-top: 2rem;
    }
    
    body.light-mode {
        background: linear-gradient(135deg, #EDF2F7 0%, #DBEAFE 100%) !important;
        color: #1A2844 !important;
    }
    
    body.light-mode .card {
        background: rgba(255,255,255,0.95);
    }
    
    body.light-mode td {
        color: #1A2844 !important;
    }
    
    body.light-mode .search-bar input,
    body.light-mode .sort-bar {
        background: white;
        color: #1A2844;
    }
    
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
</head>
<body>

<div class="app-wrapper">
    <?php sidebar_spa('evenements'); ?>

    <div class="main-content">
        <div class="page-content">
        <?php navbar_dashboard(); ?>

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
                    <select class="sort-bar" onchange="window.location.href=this.value">
                        <option value="?sort=id&order=DESC&search=<?= urlencode($search) ?>">ID (plus récent)</option>
                        <option value="?sort=id&order=ASC&search=<?= urlencode($search) ?>">ID (plus ancien)</option>
                        <option value="?sort=nom_entreprise&order=ASC&search=<?= urlencode($search) ?>">Nom (A→Z)</option>
                        <option value="?sort=nom_entreprise&order=DESC&search=<?= urlencode($search) ?>">Nom (Z→A)</option>
                        <option value="?sort=montant_sponsoring&order=DESC&search=<?= urlencode($search) ?>">Montant (plus élevé)</option>
                        <option value="?sort=montant_sponsoring&order=ASC&search=<?= urlencode($search) ?>">Montant (moins élevé)</option>
                    </select>
                    <a href="list.php" class="btn-reset">Réinitialiser</a>
                </div>
            </div>
            
            <!-- ✅ Barre d'actions avec Dashboard Événements (sans rien modifier dans la navbar) -->
            <div style="display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap;">
                <a href="../dashboard_event.php" class="btn-dashboard-event">
                    <i class="fas fa-chart-line"></i> Dashboard Événements
                </a>
                <a href="?export_excel=1&search=<?= urlencode($search) ?>" class="btn-export-excel">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <button onclick="exportToPDF()" class="btn-export-pdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <a href="form.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
            </div>
            
            <?php if(isset($_GET['success'])): ?>
            <div class="alert-success">
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
        
        <footer>
            <p>
                <svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle">
                    <path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/>
                </svg> 
                Eco Ride by Echo Group © 2025 - Gestion des Sponsors
            </p>
        </footer>
        </div>
    </div>
</div>

<div id="pdf-content" style="display:none;">
    <h2 style="text-align:center;">Liste des sponsors</h2>
    <p>Date d'export : <?= date('d/m/Y H:i') ?></p>
    <table border="1">
        <thead><tr><th>ID</th><th>Entreprise</th><th>Montant</th><th>Type</th><th>Statut</th><th>Événement</th></tr></thead>
        <tbody>
        <?php foreach($sponsors as $s): ?>
        <tr><td><?= $s['id'] ?></td><td><?= $s['nom_entreprise'] ?></td><td><?= number_format($s['montant_sponsoring'], 0, ',', ' ') ?> DT</td>
            <td><?= $s['type_sponsor'] ?? '-' ?></td>
            <td><?= $s['statut'] ?></td>
            <td><?= $s['event_titre'] ?? 'Non assigné' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete() { return confirm('Supprimer ?'); }
    function exportToPDF() { 
        var e = document.getElementById('pdf-content'); 
        e.style.display = 'block'; 
        html2pdf().set({ margin: 10, filename: 'sponsors_<?= date('Y-m-d') ?>.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } }).from(e).save().then(() => e.style.display = 'none'); 
    }
    
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
<?php require_once __DIR__ . '/../ai_helper_widget.php'; ?>
</body>
</html>