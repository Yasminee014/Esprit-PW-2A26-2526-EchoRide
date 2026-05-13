<?php
// ══════════════════════════════════════════════
//  ADMIN.PHP — Page admin unique EcoRide
//  Affichage des véhicules + historique par véhicule
// ══════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';
require_once __DIR__ . '/../../Model/ReservationModel.php';

$db           = Database::getInstance();
$vModel       = new VehiculeModel();
$rModel       = new ReservationModel();

// Force admin mode for testing (remove in production)
$_SESSION['is_admin'] = true;
$_SESSION['user_id']  = $_SESSION['user_id']  ?? 1;
$_SESSION['admin_id'] = $_SESSION['admin_id'] ?? 1;
$_SESSION['admin_nom']   = $_SESSION['admin_nom']   ?? 'Admin';
$_SESSION['admin_email'] = $_SESSION['admin_email'] ?? 'admin@ecoride.fr';

// Charger la photo admin depuis la BDD
if (empty($_SESSION['admin_photo'])) {
    $stmtPhoto = $db->prepare("SELECT photo FROM admins WHERE id = :id");
    $stmtPhoto->execute([':id' => $_SESSION['admin_id']]);
    $adminRow = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
    if ($adminRow && !empty($adminRow['photo'])) {
        $_SESSION['admin_photo'] = $adminRow['photo'];
    }
}

// ─── Recherche ────────────────────────────────
$search   = trim($_GET['search'] ?? '');
$vehicules = $search ? $vModel->search($search) : $vModel->getAll();
$vStats   = [
    'total'         => $vModel->countAll(),
    'disponibles'   => $vModel->countByStatut('disponible'),
    'maintenance'   => $vModel->countByStatut('en_maintenance'),
    'indisponibles' => $vModel->countByStatut('indisponible'),
];

// ══════════════════════════════════════════════
//  TRAITEMENT DES ACTIONS POST
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'vehicule_statut') {
        $id     = intval($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if (in_array($statut, ['disponible','indisponible','en_maintenance'])) {
            $vModel->updateStatut($id, $statut);
            $_SESSION['success'] = 'Statut mis à jour';
        }
        header('Location: admin.php'); exit;
    }

    if ($action === 'vehicule_delete') {
        $id = intval($_POST['id'] ?? 0);
        $vModel->delete($id)
            ? ($_SESSION['success'] = 'Véhicule supprimé.')
            : ($_SESSION['errors']  = ['Erreur lors de la suppression.']);
        header('Location: admin.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — EcoRide</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Bibliothèques pour générer un vrai PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;transition:background 0.3s, color 0.3s;}
.wrap{display:flex;min-height:100vh;}

/* ========== SIDEBAR - NOUVEAU DEGRADE ========== */
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #2F76BC 0%, #1E5EA5 50%, #174C8A 100%);
    padding: 1.5rem 0;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    box-shadow: 4px 0 20px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.15);
    margin-bottom: 1.5rem;
    text-align: center;
}

.sidebar-header .logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.sidebar-header .logo-img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    filter: drop-shadow(0 4px 14px rgba(97,179,250,0.5));
    margin-bottom: 4px;
}

.sidebar-header .logo-text {
    font-size: 1.3rem;
    font-weight: 700;
    color: #A9D6FF;
    letter-spacing: 1px;
    font-family: 'Poppins', sans-serif;
}

.sidebar-header .logo-tagline {
    font-size: 0.65rem;
    color: #BFD8F1;
    margin-top: 2px;
    letter-spacing: 2px;
    font-family: 'Poppins', sans-serif;
    opacity: 0.85;
}

.nav-section {
    color: #CFE6FF;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 0.75rem 1.5rem;
    margin-top: 0.5rem;
    opacity: 0.8;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
}

.sidebar nav ul {
    list-style: none;
}

.sidebar nav ul li {
    margin-bottom: 0.25rem;
}

.sidebar nav ul li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1.5rem;
    color: #EAF4FF;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.85rem;
    margin: 0 0.5rem;
    border-radius: 10px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.sidebar nav ul li a i {
    width: 22px;
    color: #EAF4FF;
    font-size: 1rem;
}

.sidebar nav ul li a:hover {
    background: rgba(111,168,220,0.3);
    color: white;
    transform: translateX(5px);
}

.sidebar nav ul li a.active {
    background: linear-gradient(135deg, #6FA8DC, #8FC1F5);
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(111,168,220,0.3);
}

.sidebar nav ul li a.active i {
    color: #FFFFFF;
}

.sidebar-footer {
    margin-top: auto;
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar-footer a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #FFCDD2;
    text-decoration: none;
    font-size: 0.85rem;
    padding: 0.5rem 0;
    border-radius: 10px;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
}

.sidebar-footer a i {
    width: 22px;
    color: #FFCDD2;
}

.sidebar-footer a:hover {
    color: #FF8A80;
    transform: translateX(5px);
}

/* Main content */
.main {
    flex: 1;
    margin-left: 280px;
    padding: 1.6rem;
    position: relative;
    z-index: 1;
}

/* ========== HEADER STYLE ========== */
.admin-header {
    background: linear-gradient(90deg, #071C2F, #0A2A47, #0D355B);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-radius: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-logo {
    display: flex;
    flex-direction: column;
}

.admin-logo .logo-eco {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 1px;
    font-family: 'Poppins', sans-serif;
}

.admin-logo .logo-eco span:first-child {
    color: #4EA3FF;
}

.admin-logo .logo-eco span:last-child {
    color: #6BB8FF;
}

.admin-logo .logo-tagline {
    font-size: 0.65rem;
    color: #A8C1D9;
    margin-top: 2px;
}

.admin-nav {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.admin-nav a {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    background: transparent;
    color: #CFE6FF;
    font-family: 'Poppins', sans-serif;
}

.admin-nav a:hover {
    background: rgba(255,255,255,0.1);
    color: #FFFFFF;
}

/* ========== BOUTON PROFIL ========== */
.admin-nav .profile-btn {
    background: #003050;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.5rem 1.2rem;
}

.admin-nav .profile-btn:hover {
    background: #002050;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,48,80,0.4);
}

.profile-avatar {
    width: 36px;
    height: 36px;
    background: #5FA8FF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.3);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-avatar i {
    font-size: 0.9rem;
    color: #FFFFFF;
}

/* ========== BOUTON ADMIN - STYLE ROUGE ========== */
.admin-nav .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
}

.admin-nav .admin-btn:hover {
    background: rgba(231,76,60,0.35);
}

.theme-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.theme-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(15deg);
}

.alert{padding:.8rem 1.2rem;border-radius:12px;margin-bottom:1.2rem;display:flex;align-items:center;gap:9px;font-size:.88rem;}
.alert-success{background:rgba(39,174,96,.14);border:1px solid rgba(39,174,96,.35);color:var(--green);}
.alert-error{background:rgba(231,76,60,.14);border:1px solid rgba(231,76,60,.35);color:var(--red);}

.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.6rem;}
.stat{background:rgba(255,255,255,.07);border:1px solid rgba(78,163,255,.16);border-radius:14px;padding:1.2rem;text-align:center;transition:transform 0.3s;}
.stat:hover{transform:translateY(-3px);border-color:#4EA3FF;}
.stat i{font-size:1.8rem;color:#4EA3FF;margin-bottom:.35rem;display:block;}
.stat .num{font-size:2rem;font-weight:700;background:linear-gradient(135deg,#4EA3FF,#fff);-webkit-background-clip:text;background-clip:text;color:transparent;}
.stat .lbl{color:var(--grey);font-size:.75rem;}

.toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.7rem;}
.search-box{position:relative;flex:1;max-width:300px;}
.search-box input{width:100%;background:rgba(255,255,255,.08);border:1px solid rgba(78,163,255,.3);color:#fff;padding:.5rem .9rem .5rem 2.2rem;border-radius:18px;font-size:.84rem;outline:none;font-family:'Poppins',sans-serif;}
.search-box input:focus{border-color:#4EA3FF;background:rgba(78,163,255,.1);}
.search-box input::placeholder{color:var(--grey);}
.search-box i{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--grey);}
.btn{padding:.5rem 1.1rem;border-radius:18px;font-size:.84rem;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.3s;font-family:'Poppins',sans-serif;}
.btn-outline{background:rgba(255,255,255,.08);border:1px solid rgba(78,163,255,.3);color:#fff;}
.btn-outline:hover{background:rgba(78,163,255,.25);border-color:#4EA3FF;}
.btn-pdf{background:rgba(231,76,60,.15);border:1px solid rgba(231,76,60,.4);color:#e74c3c;}
.btn-pdf:hover{background:rgba(231,76,60,.3);color:#e74c3c;}

.tbl-wrap{background:rgba(255,255,255,.04);border-radius:14px;overflow:hidden;border:1px solid rgba(97,179,250,.1);}
table{width:100%;border-collapse:collapse;}
thead{background:rgba(25,118,210,.22);}
thead th{padding:.85rem 1rem;text-align:left;font-size:.76rem;text-transform:uppercase;letter-spacing:.7px;color:var(--blue-light);font-weight:600;}
tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .18s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:rgba(97,179,250,.05);}
tbody td{padding:.8rem 1rem;font-size:.87rem;vertical-align:middle;}
.chip{display:inline-flex;align-items:center;background:rgba(97,179,250,.12);color:var(--blue-light);padding:.18rem .65rem;border-radius:11px;font-size:.73rem;font-weight:600;border:1px solid rgba(97,179,250,.25);}
.chip-green{background:rgba(39,174,96,.15);color:#27ae60;border-color:rgba(39,174,96,.3);}
.chip-red{background:rgba(231,76,60,.15);color:#e74c3c;border-color:rgba(231,76,60,.3);}
.chip-yellow{background:rgba(241,196,15,.15);color:#f1c40f;border-color:rgba(241,196,15,.3);}
.abtns{display:flex;gap:5px;}
.abtn{width:30px;height:30px;border:none;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.82rem;transition:all .22s;text-decoration:none;}
.abtn-view{background:rgba(78,163,255,.15);color:#4EA3FF;}
.abtn-view:hover{background:rgba(78,163,255,.3);transform:scale(1.1);}
.abtn-edit{background:rgba(78,163,255,.15);color:#4EA3FF;}
.abtn-edit:hover{background:rgba(78,163,255,.3);transform:scale(1.1);}
.abtn-hist{background:rgba(155,89,182,.15);color:#9b59b6;}
.abtn-hist:hover{background:rgba(155,89,182,.3);transform:scale(1.1);}
.abtn-del{background:rgba(231,76,60,.18);color:#e74c3c;}
.abtn-del:hover{background:rgba(231,76,60,.35);transform:scale(1.1);}
.veh-img{width:40px;height:32px;border-radius:6px;overflow:hidden;background:#4EA3FF;display:flex;align-items:center;justify-content:center;}
.veh-img img{width:100%;height:100%;object-fit:cover;}
.veh-img i{font-size:16px;color:#fff;}
.empty-state{text-align:center;padding:3rem;color:var(--grey);}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 280px;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(5px);
    z-index: 999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.show { display: flex; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.modal-content {
    background: linear-gradient(145deg, #0D1F3A, #0A1628);
    border-radius: 24px;
    width: 90%;
    max-width: 500px;
    border: 1px solid rgba(78,163,255,0.3);
    box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    max-height: 80vh;
    overflow-y: auto;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(78,163,255,.2);
    position: sticky;
    top: 0;
    background: #0D1F3A;
    border-radius: 24px 24px 0 0;
}
.modal-header h3 { color: #4EA3FF; display: flex; align-items: center; gap: 8px; }
.modal-close {
    background: rgba(255,255,255,.1);
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: #fff;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.2s;
}
.modal-close:hover { background: rgba(231,76,60,.4); transform: rotate(90deg); }
.modal-body { padding: 1.5rem; }
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(78,163,255,.2);
    display: flex;
    justify-content: flex-end;
    position: sticky;
    bottom: 0;
    background: #0D1F3A;
    border-radius: 0 0 24px 24px;
}
.modal-footer button {
    background: linear-gradient(135deg, #1976D2, #4EA3FF);
    border: none;
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.2s;
}
.modal-footer button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(78,163,255,0.4);
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.6rem 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.detail-row:last-child { border-bottom: none; }
.detail-label { color: var(--grey); font-size: 0.8rem; }
.detail-value { font-weight: 500; display: flex; align-items: center; gap: 8px; }
.color-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; }

body.light-mode .sidebar {
    background: linear-gradient(180deg, #2F76BC, #1E5EA5, #174C8A);
}
body.light-mode .stat { background: white; }

@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; }
}
</style>
</head>
<body>
<div class="wrap">

<!-- SIDEBAR - NOUVEAU DEGRADE -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php" class="logo">
            <img src="../../assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_trajet.php?page=passagers"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="dashboard_event.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="/ecoride/View/backoffice/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin.php" class="active"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="lostfound_admin.php"><i class="fas fa-search-location"></i> Objets perdus</a></li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

<main class="main">

<!-- HEADER -->
<div class="admin-header">
    <div class="admin-logo">
        <div class="logo-eco">
            <span>ECO</span> <span>RIDE</span>
        </div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>
        
        <!-- BOUTON PROFIL -->
        <a href="../../Controller/AdminController.php?action=showProfile" class="profile-btn">
            <div class="profile-avatar">
                <?php if (!empty($_SESSION['admin_photo'])): ?>
                    <img src="../../uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="Photo admin" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <i class="fas fa-user-shield" style="display:none"></i>
                <?php else: ?>
                    <i class="fas fa-user-shield"></i>
                <?php endif; ?>
            </div>
            <span>Profil</span>
        </a>
        
        <a href="admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php unset($_SESSION['success']); endif;
if (!empty($_SESSION['errors'])): ?>
  <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php foreach($_SESSION['errors'] as $e) echo htmlspecialchars($e) . ' '; unset($_SESSION['errors']); ?></div>
<?php endif; ?>

<div class="stats">
  <div class="stat"><i class="fas fa-car"></i><div class="num"><?= $vStats['total'] ?></div><div class="lbl">Total véhicules</div></div>
  <div class="stat"><i class="fas fa-check-circle"></i><div class="num"><?= $vStats['disponibles'] ?></div><div class="lbl">Disponibles</div></div>
  <div class="stat"><i class="fas fa-wrench"></i><div class="num"><?= $vStats['maintenance'] ?></div><div class="lbl">En maintenance</div></div>
  <div class="stat"><i class="fas fa-ban"></i><div class="num"><?= $vStats['indisponibles'] ?></div><div class="lbl">Indisponibles</div></div>
</div>

<div class="toolbar">
  <form method="GET" style="flex:1;">
    <div class="search-box"><i class="fas fa-search"></i><input type="text" name="search" placeholder="Taper ici pour rechercher..." value="<?= htmlspecialchars($search) ?>" id="searchInput"></div>
    <?php if ($search): ?><a href="admin.php" class="btn btn-outline"><i class="fas fa-times"></i> Effacer</a><?php endif; ?>
  </form>
  
  <button onclick="exportToPDF()" class="btn btn-pdf">
    <i class="fas fa-file-pdf"></i> Exporter PDF
  </button>
</div>

<?php if (empty($vehicules)): ?>
  <div class="empty-state">
    <i class="fas fa-car-side" style="font-size:3rem;opacity:0.3;margin-bottom:1rem;display:block;"></i>
    <p>Aucun véhicule trouvé</p>
  </div>
<?php else: ?>
<div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Photo</th>
        <th>Marque / Modèle</th>
        <th>Immatriculation</th>
        <th>Propriétaire</th>
        <th>Capacité</th>
        <th>Statut</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($vehicules as $v):
      $photoPath = '/ecoride/assets/uploads/vehicules/' . ($v['photo'] ?? '');
      $fullPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
      $statutClass = $v['statut'] === 'disponible' ? 'chip-green' : ($v['statut'] === 'en_maintenance' ? 'chip-yellow' : 'chip-red');
    ?>
      <tr>
        <td><span class="chip">#<?= $v['id'] ?></span></td>
        <td>
          <div class="veh-img">
            <?php if (!empty($v['photo']) && file_exists($fullPath)): ?>
              <img src="<?= $photoPath ?>" alt="">
            <?php else: ?>
              <i class="fas fa-car"></i>
            <?php endif; ?>
          </div>
        </td>
        <td><strong style="color:#4EA3FF;"><?= htmlspecialchars($v['marque']) ?></strong> <?= htmlspecialchars($v['modele']) ?></td>
        <td><?= htmlspecialchars($v['immatriculation']) ?></td>
        <td><?= htmlspecialchars(($v['prenom'] ?? '') . ' ' . ($v['nom'] ?? '')) ?></td>
        <td><?= $v['capacite'] ?> places</td>
        <td><span class="chip <?= $statutClass ?>"><?= ucfirst($v['statut']) ?></span></td>
        <td>
          <div class="abtns">
            <a href="admin_detail_vehicule.php?id=<?= $v['id'] ?>" class="abtn abtn-view" title="Détails"><i class="fas fa-eye"></i></a>
            <a href="admin_historique_vehicule.php?id=<?= $v['id'] ?>" class="abtn abtn-hist" title="Historique"><i class="fas fa-history"></i></a>
            <a href="admin_modifier_vehicule.php?id=<?= $v['id'] ?>" class="abtn abtn-edit" title="Modifier"><i class="fas fa-pen"></i></a>
            <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer ce véhicule ?')">
              <input type="hidden" name="action" value="vehicule_delete">
              <input type="hidden" name="id" value="<?= $v['id'] ?>">
              <button type="submit" class="abtn abtn-del" title="Supprimer"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>


<!-- MODAL -->
<div id="modalOverlay" class="modal-overlay">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fas fa-info-circle"></i> Détails du véhicule</h3>
      <button class="modal-close" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer">
      <button onclick="closeModal()">Fermer</button>
    </div>
  </div>
</div>

</main>
</div>

<script>
function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    
    const themeBtn = document.getElementById('themeBtn');
    const icon = themeBtn.querySelector('i');
    if (isLight) {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const themeBtn = document.getElementById('themeBtn');
    if (themeBtn) {
        const icon = themeBtn.querySelector('i');
        icon.className = 'fas fa-sun';
    }
}

function showDetails(vehicule) {
    const modalBody = document.getElementById('modalBody');
    const statutText = vehicule.statut === 'disponible' ? 'Disponible' : 
                      (vehicule.statut === 'en_maintenance' ? 'En maintenance' : 'Indisponible');
    
    let photoHtml = '';
    if (vehicule.photo) {
        photoHtml = `<div style="text-align:center;margin-bottom:1rem;">
                        <img src="/ecoride/assets/uploads/vehicules/${vehicule.photo}" 
                             style="width:120px;height:90px;object-fit:cover;border-radius:12px;border:2px solid #4EA3FF;">
                     </div>`;
    }
    
    modalBody.innerHTML = `
        ${photoHtml}
        <div class="detail-row"><span class="detail-label">ID</span><span class="detail-value">#${vehicule.id}</span></div>
        <div class="detail-row"><span class="detail-label">Conducteur</span><span class="detail-value"><i class="fas fa-user"></i> ${vehicule.conducteur}</span></div>
        <div class="detail-row"><span class="detail-label">Marque</span><span class="detail-value">${vehicule.marque}</span></div>
        <div class="detail-row"><span class="detail-label">Modèle</span><span class="detail-value">${vehicule.modele}</span></div>
        <div class="detail-row"><span class="detail-label">Immatriculation</span><span class="detail-value"><code>${vehicule.immatriculation}</code></span></div>
        <div class="detail-row"><span class="detail-label">Couleur</span><span class="detail-value"><span class="color-dot" style="background:${vehicule.colorHex};"></span> ${vehicule.couleur}</span></div>
        <div class="detail-row"><span class="detail-label">Capacité</span><span class="detail-value"><i class="fas fa-users"></i> ${vehicule.capacite} places</span></div>
        <div class="detail-row"><span class="detail-label">Climatisation</span><span class="detail-value">${vehicule.climatisation === 'Oui' ? '<i class="fas fa-snowflake" style="color:#4EA3FF;"></i> Oui' : '<i class="fas fa-sun" style="color:#f1c40f;"></i> Non'}</span></div>
        <div class="detail-row"><span class="detail-label">Statut</span><span class="detail-value"><span style="padding:4px 12px;border-radius:20px;background:rgba(78,163,255,.15);">${statutText}</span></span></div>
    `;
    
    document.getElementById('modalOverlay').classList.add('show');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('show');
}

async function exportToPDF() {
    try {
        const { jsPDF } = window.jspdf;
        const vehiculesData = <?php 
            $data = [];
            foreach ($vehicules as $v) {
                $resaCount = $rModel->countByVehiculeId($v['id']);
                $data[] = [
                    'id' => $v['id'],
                    'marque' => htmlspecialchars($v['marque']),
                    'modele' => htmlspecialchars($v['modele']),
                    'immatriculation' => htmlspecialchars($v['immatriculation']),
                    'conducteur' => htmlspecialchars(($v['prenom'] ?? '') . ' ' . ($v['nom'] ?? '')),
                    'capacite' => $v['capacite'],
                    'statut' => $v['statut'] === 'disponible' ? 'Disponible' : ($v['statut'] === 'en_maintenance' ? 'En maintenance' : 'Indisponible'),
                    'reservations' => $resaCount['total'] ?? 0
                ];
            }
            echo json_encode($data);
        ?>;
        const stats = {
            total: <?= $vStats['total'] ?>,
            disponibles: <?= $vStats['disponibles'] ?>,
            maintenance: <?= $vStats['maintenance'] ?>,
            indisponibles: <?= $vStats['indisponibles'] ?>
        };
        const container = document.createElement('div');
        container.style.padding = '20px';
        container.style.fontFamily = 'Poppins, sans-serif';
        container.style.backgroundColor = 'white';
        container.style.color = '#333';
        container.style.width = '900px';
        let tableRows = '';
        vehiculesData.forEach(v => {
            tableRows += `
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">${v.id}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><strong>${v.marque}</strong> ${v.modele}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${v.immatriculation}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${v.conducteur}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">${v.capacite}</td>
                    <td style="padding: 10px; border: 1px solid #ddd;">${v.statut}</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">${v.reservations}</td>
                </tr>
            `;
        });
        container.innerHTML = `
            <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4EA3FF; padding-bottom: 10px;">
                <h1 style="color: #4EA3FF; font-family: Poppins, sans-serif;">🚗 EcoRide - Liste des véhicules</h1>
                <p style="font-family: Poppins, sans-serif;">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
            </div>
            <div style="display: flex; justify-content: center; gap: 15px; margin: 20px 0; flex-wrap: wrap;">
                <div style="background: #e3f2fd; padding: 10px 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #4EA3FF;">${stats.total}</div>
                    <div>Total véhicules</div>
                </div>
                <div style="background: #e8f5e9; padding: 10px 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #27ae60;">${stats.disponibles}</div>
                    <div>Disponibles</div>
                </div>
                <div style="background: #fff3e0; padding: 10px 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #f1c40f;">${stats.maintenance}</div>
                    <div>En maintenance</div>
                </div>
                <div style="background: #ffebee; padding: 10px 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #e74c3c;">${stats.indisponibles}</div>
                    <div>Indisponibles</div>
                </div>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #4EA3FF; color: white;">
                        <th style="padding: 10px; text-align: left;">ID</th>
                        <th style="padding: 10px; text-align: left;">Marque/Modèle</th>
                        <th style="padding: 10px; text-align: left;">Immatriculation</th>
                        <th style="padding: 10px; text-align: left;">Conducteur</th>
                        <th style="padding: 10px; text-align: center;">Places</th>
                        <th style="padding: 10px; text-align: left;">Statut</th>
                        <th style="padding: 10px; text-align: center;">Réservations</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
            <div style="text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666;">
                EcoRide - Application de covoiturage
            </div>
        `;
        document.body.appendChild(container);
        const canvas = await html2canvas(container, { scale: 2, backgroundColor: '#ffffff', logging: false });
        document.body.removeChild(container);
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        const imgWidth = 280;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', 5, 10, imgWidth, imgHeight);
        const date = new Date();
        const fileName = `liste_vehicules_ecoride_${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}.pdf`;
        pdf.save(fileName);
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la génération du PDF.');
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

const si = document.getElementById('searchInput');
if(si) si.addEventListener('input', function(){ clearTimeout(this._t); this._t=setTimeout(()=>this.form.submit(),450); });

document.querySelectorAll('.alert').forEach(a => {
    setTimeout(()=>{ a.style.transition='opacity 0.5s'; a.style.opacity='0'; },4000);
    setTimeout(()=>a.remove(),4600);
});
</script>
</body>
</html>