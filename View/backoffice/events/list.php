<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/Event.php';
require_once __DIR__ . '/../../../Model/Sponsor.php';
require_once __DIR__ . '/../partials/partials.php';
use Model\Event;

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}

$eventModel = new Event();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;

$events = $eventModel->getAllAdmin($search, $sort, $order, $page, $limit);
$totalEvents = $eventModel->countAllAdmin($search);
$totalPages = ceil($totalEvents / $limit);

if(isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="evenements_'.date('Y-m-d').'.xls"');
    echo "<table border='1'> <tr><th>ID</th><th>Titre</th><th>Type</th><th>Ville</th><th>Date</th><th>Places</th><th>Statut</th></tr>";
    foreach($events as $e) echo "<tr><td>{$e['id']}</td><td>{$e['titre']}</td><td>{$e['type']}</td><td>{$e['ville']}</td><td>".date('d/m/Y H:i', strtotime($e['date_evenement']))."</td><td>{$e['nb_places']}</td><td>{$e['statut']}</td></tr>";
    echo "</table>";
    exit();
}

if(isset($_GET['delete'])) {
    $eventModel->delete($_GET['delete']);
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
<title>Eco Ride - Gestion Événements</title>
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
    
    .app-wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
    }
    
    .sidebar {
        width: 260px;
        background: linear-gradient(180deg, #1976D2 0%, #1565C0 40%, #0F3B6E 100%);
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        padding: 2rem 1rem;
        overflow-y: auto;
        z-index: 100;
        display: flex;
        flex-direction: column;
    }
    
    .main-content {
        margin-left: 260px;
        width: calc(100% - 260px);
        min-height: 100vh;
        padding: 1.5rem 2rem;
    }
    
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%);
        border-radius: 16px;
        padding: 0.75rem 1.5rem;
        border: 1px solid rgba(97,179,250,0.18);
        box-shadow: 0 4px 24px rgba(0,0,0,0.25);
        position: sticky;
        top: 0;
        z-index: 50;
    }
    
    .navbar-logo strong {
        font-size: 1rem;
        font-weight: 800;
        color: #61B3FA;
        letter-spacing: 0.05em;
    }
    
    .navbar-logo span {
        font-size: 0.62rem;
        color: rgba(255,255,255,0.75);
        letter-spacing: 0.08em;
    }
    
    .top-bar-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .btn-top, .btn-admin-profile, .btn-admin-plain, .btn-theme-toggle {
        background: transparent;
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 25px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        font-size: 0.85rem;
        border: 1px solid rgba(255,255,255,0.18);
        cursor: pointer;
    }
    
    .btn-top:hover, .btn-admin-profile:hover, .btn-admin-plain:hover {
        background: rgba(255,255,255,0.12);
    }
    
    .btn-admin-plain {
        border-color: rgba(231,76,60,0.45);
        color: #E74C3C;
    }
    
    .btn-theme-toggle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        justify-content: center;
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
    
    .badge-ouvert {
        background: rgba(0,200,100,0.2);
        color: #4cff9a;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    
    .badge-complet {
        background: rgba(255,165,0,0.2);
        color: #ffa500;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    
    .badge-annule {
        background: rgba(255,68,68,0.2);
        color: #ff6b6b;
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
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
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s;
        }
        .sidebar.open {
            transform: translateX(0);
        }
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 1rem;
        }
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
        <div class="top-bar">
            <div class="navbar-logo">
                <strong>ECO RIDE</strong>
                <span>Covoiturage Intelligent</span>
            </div>
            <div class="top-bar-right">
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#hero" class="btn-top">Voir site</a>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                    <div class="admin-avatar-btn">
                        <?php if (!empty($_SESSION['admin_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-user-shield"></i>
                        <?php endif; ?>
                    </div>
                    Profil
                </a>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="btn-admin-plain">Admin</a>
                <button class="btn-theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon themeIcon"></i>
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Gestion des événements</h2>
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
                        <option value="?sort=titre&order=ASC&search=<?= urlencode($search) ?>">Titre (A→Z)</option>
                        <option value="?sort=titre&order=DESC&search=<?= urlencode($search) ?>">Titre (Z→A)</option>
                        <option value="?sort=date_evenement&order=ASC&search=<?= urlencode($search) ?>">Date (plus proche)</option>
                        <option value="?sort=date_evenement&order=DESC&search=<?= urlencode($search) ?>">Date (plus lointain)</option>
                        <option value="?sort=nb_places&order=DESC&search=<?= urlencode($search) ?>">Places (plus → moins)</option>
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
                <?php if($_GET['success'] == 'added') echo '✓ Événement ajouté avec succès';
                      elseif($_GET['success'] == 'updated') echo '✓ Événement modifié avec succès';
                      elseif($_GET['success'] == 'deleted') echo '✓ Événement supprimé avec succès'; ?>
            </div>
            <?php endif; ?>
            
            <?php if(empty($events)): ?>
                <p style="text-align:center;padding:2rem;">Aucun événement trouvé</p>
            <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><a href="?sort=id&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">ID <?= $sort=='id' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th><a href="?sort=titre&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Titre <?= $sort=='titre' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th>Type</th>
                            <th>Ville</th>
                            <th><a href="?sort=date_evenement&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Date <?= $sort=='date_evenement' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th><a href="?sort=nb_places&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Places <?= $sort=='nb_places' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
                            <th>Statut</th>
                            <th class="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($events as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><?= htmlspecialchars($e['titre']) ?></td>
                            <td><?= htmlspecialchars($e['type']) ?></td>
                            <td><?= htmlspecialchars($e['ville']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($e['date_evenement'])) ?></td>
                            <td><?= $e['nb_places'] ?></td>
                            <td><span class="badge-<?= $e['statut'] == 'ouvert' ? 'ouvert' : ($e['statut'] == 'complet' ? 'complet' : 'annule') ?>"><?= $e['statut'] ?></span></td>
                            <td class="actions">
                                <a href="detail.php?id=<?= $e['id'] ?>" class="btn-details" title="Détails"><i class="fas fa-eye"></i></a>
                                <a href="form.php?id=<?= $e['id'] ?>" class="btn-edit" title="Modifier"><i class="fas fa-edit"></i></a>
                                <a href="list.php?delete=<?= $e['id'] ?>" class="btn-delete" title="Supprimer" onclick="return confirmDelete()"><i class="fas fa-trash"></i></a>
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
                Eco Ride by Echo Group © 2025 - Gestion des Événements
            </p>
        </footer>
    </div>
</div>

<div id="pdf-content" style="display:none;">
    <h2 style="text-align:center;">Liste des événements</h2>
    <p>Date d'export : <?= date('d/m/Y H:i') ?></p>
    <table border="1">
        <thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>Ville</th><th>Date</th><th>Places</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach($events as $e): ?>
        <tr><td><?= $e['id'] ?></td><td><?= $e['titre'] ?></td><td><?= $e['type'] ?></td><td><?= $e['ville'] ?></td><td><?= date('d/m/Y H:i', strtotime($e['date_evenement'])) ?></td><td><?= $e['nb_places'] ?></td><td><?= $e['statut'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete() { return confirm('Supprimer ?'); }
    function exportToPDF() { 
        var e = document.getElementById('pdf-content'); 
        e.style.display = 'block'; 
        html2pdf().set({ margin: 10, filename: 'evenements_<?= date('Y-m-d') ?>.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } }).from(e).save().then(() => e.style.display = 'none'); 
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