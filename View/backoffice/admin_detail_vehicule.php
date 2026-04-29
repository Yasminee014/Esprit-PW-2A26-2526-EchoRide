<?php
// ══════════════════════════════════════════════
//  ADMIN_DETAIL_VEHICULE.PHP — Page détail véhicule
//  Réutilise le header et la sidebar d'admin.php
// ══════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';
require_once __DIR__ . '/../../Model/ReservationModel.php';

$db     = Database::getInstance();
$vModel = new VehiculeModel();
$rModel = new ReservationModel();

// Force admin mode for testing (remove in production)
$_SESSION['is_admin'] = true;
$_SESSION['user_id']  = 1;

// ─── Récupération du véhicule ──────────────────
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin.php');
    exit;
}

$vehicule = $vModel->getById($id);
if (!$vehicule) {
    header('Location: admin.php');
    exit;
}

$reservations = $rModel->getByVehiculeId($id);
$resaStats    = $rModel->countByVehiculeId($id);

// Couleur hex
$couleur  = $vehicule['couleur'] ?? 'Non spécifiée';
$colorMap = [
    'rouge' => '#e74c3c', 'red'   => '#e74c3c',
    'bleu'  => '#1976D2', 'blue'  => '#1976D2',
    'vert'  => '#27ae60', 'green' => '#27ae60',
    'noir'  => '#2c3e50', 'black' => '#2c3e50',
    'blanc' => '#ecf0f1', 'white' => '#ecf0f1',
    'gris'  => '#7f8c8d', 'grey'  => '#7f8c8d',
    'jaune' => '#f1c40f', 'yellow'=> '#f1c40f',
];
$colorHex = $colorMap[strtolower(trim($couleur))] ?? '#4EA3FF';
$photoPath = '/ecoride/assets/uploads/vehicules/' . ($vehicule['photo'] ?? '');
$fullPath  = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Détail Véhicule — EcoRide Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;transition:background 0.3s,color 0.3s;}
.wrap{display:flex;min-height:100vh;}

/* ══ SIDEBAR (identique à admin.php) ══ */
.sidebar{width:280px;background:linear-gradient(180deg,#2F76BC 0%,#1E5EA5 50%,#174C8A 100%);padding:1.5rem 0;position:fixed;height:100vh;overflow-y:auto;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,0.2);}
.sidebar-header{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:1.5rem;text-align:center;}
.sidebar-header .logo{display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;}
.sidebar-header .logo-img{width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 4px 14px rgba(97,179,250,.5));margin-bottom:4px;}
.sidebar-header .logo-text{font-size:1.3rem;font-weight:700;color:#A9D6FF;letter-spacing:1px;}
.sidebar-header .logo-tagline{font-size:.65rem;color:#BFD8F1;margin-top:2px;letter-spacing:2px;opacity:.85;}
.nav-section{color:#CFE6FF;font-size:.7rem;text-transform:uppercase;letter-spacing:2px;padding:.75rem 1.5rem;margin-top:.5rem;opacity:.8;font-weight:600;}
.sidebar nav ul{list-style:none;}
.sidebar nav ul li{margin-bottom:.25rem;}
.sidebar nav ul li a{display:flex;align-items:center;gap:12px;padding:.7rem 1.5rem;color:#EAF4FF;text-decoration:none;transition:all .3s;font-size:.85rem;margin:0 .5rem;border-radius:10px;font-weight:500;}
.sidebar nav ul li a i{width:22px;color:#EAF4FF;font-size:1rem;}
.sidebar nav ul li a:hover{background:rgba(111,168,220,.3);color:#fff;transform:translateX(5px);}
.sidebar nav ul li a.active{background:linear-gradient(135deg,#6FA8DC,#8FC1F5);color:#fff;box-shadow:0 4px 12px rgba(111,168,220,.3);}
.sidebar-footer{position:absolute;bottom:0;left:0;right:0;padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.1);}
.sidebar-footer a{display:flex;align-items:center;gap:12px;color:#FFCDD2;text-decoration:none;font-size:.85rem;padding:.5rem 0;border-radius:10px;transition:all .3s;}
.sidebar-footer a i{width:22px;color:#FFCDD2;}
.sidebar-footer a:hover{color:#FF8A80;transform:translateX(5px);}

/* ══ MAIN ══ */
.main{flex:1;margin-left:280px;padding:1.6rem;position:relative;z-index:1;}

/* ══ HEADER (identique à admin.php) ══ */
.admin-header{background:linear-gradient(90deg,#071C2F,#0A2A47,#0D355B);padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;border-radius:12px;border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap;gap:1rem;}
.admin-logo .logo-eco{font-size:1.5rem;font-weight:700;letter-spacing:1px;}
.admin-logo .logo-eco span:first-child{color:#4EA3FF;}
.admin-logo .logo-eco span:last-child{color:#6BB8FF;}
.admin-logo .logo-tagline{font-size:.65rem;color:#A8C1D9;margin-top:2px;}
.admin-nav{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;}
.admin-nav a{text-decoration:none;padding:.5rem 1.2rem;border-radius:30px;font-size:.9rem;font-weight:500;transition:all .3s;background:transparent;color:#CFE6FF;}
.admin-nav a:hover{background:rgba(255,255,255,.1);color:#fff;}
.admin-nav .profile-btn{background:#4A90E2;color:#fff;display:flex;align-items:center;gap:10px;padding:.5rem 1.2rem;}
.admin-nav .profile-btn:hover{background:#2563EB;transform:translateY(-2px);box-shadow:0 4px 12px rgba(37,99,235,.3);}
.profile-avatar{width:28px;height:28px;background:#5FA8FF;border-radius:50%;display:flex;align-items:center;justify-content:center;}
.profile-avatar i{font-size:.8rem;color:#fff;}
.admin-nav .admin-btn{background:rgba(231,76,60,.2);border:1px solid rgba(231,76,60,.4);color:#e74c3c;}
.admin-nav .admin-btn:hover{background:rgba(231,76,60,.35);}
.theme-btn{background:rgba(255,255,255,.1);border:none;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:1.1rem;transition:all .3s;display:flex;align-items:center;justify-content:center;color:#fff;}
.theme-btn:hover{background:rgba(255,255,255,.2);transform:rotate(15deg);}

/* ══ PANEL DÉTAIL ══ */
.detail-panel{background:rgba(255,255,255,.04);border:1px solid rgba(78,163,255,.2);border-radius:20px;overflow:hidden;max-width:860px;margin:0 auto;}

.panel-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.4rem;border-bottom:1px solid rgba(78,163,255,.2);background:rgba(78,163,255,.1);}
.panel-header h2{color:#4EA3FF;font-size:1rem;font-weight:700;display:flex;align-items:center;gap:10px;margin:0;}
.btn-back{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.2);color:#fff;padding:.35rem .9rem;border-radius:20px;font-size:.82rem;font-weight:600;text-decoration:none;transition:background .2s;}
.btn-back:hover{background:rgba(255,255,255,.14);}

.panel-body{padding:1.4rem;}

.section{margin-bottom:1.5rem;border-bottom:1px solid rgba(255,255,255,.08);padding-bottom:1.2rem;}
.section:last-child{border-bottom:none;margin-bottom:0;}
.section h3{color:#4EA3FF;font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px;margin-bottom:.9rem;}

/* Photo véhicule */
.vehicle-photo{width:100%;max-width:280px;height:180px;object-fit:cover;border-radius:14px;border:2px solid rgba(78,163,255,.4);margin-bottom:1rem;display:block;}
.vehicle-photo-placeholder{width:280px;height:180px;background:rgba(78,163,255,.1);border:2px dashed rgba(78,163,255,.3);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:1rem;}
.vehicle-photo-placeholder i{font-size:3rem;color:rgba(78,163,255,.4);}

/* Infos */
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem;}
.info-item{background:rgba(10,47,68,.5);padding:.6rem .8rem;border-radius:10px;font-size:.85rem;}
.info-item strong{color:#4EA3FF;display:block;font-size:.7rem;margin-bottom:2px;text-transform:uppercase;letter-spacing:.05em;}

/* Badge couleur */
.color-dot{display:inline-block;width:14px;height:14px;border-radius:50%;vertical-align:middle;margin-right:6px;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:20px;font-size:.72rem;font-weight:600;}
.badge-disponible{background:rgba(39,174,96,.2);color:#27ae60;}
.badge-maintenance{background:rgba(241,196,15,.2);color:#f1c40f;}
.badge-indisponible{background:rgba(231,76,60,.2);color:#e74c3c;}

/* Stats */
.stats-row{display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:.5rem;}
.stat-box{background:rgba(78,163,255,.08);border:1px solid rgba(78,163,255,.15);border-radius:12px;padding:.7rem 1rem;text-align:center;min-width:90px;}
.stat-box .num{font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#4EA3FF,#fff);-webkit-background-clip:text;background-clip:text;color:transparent;}
.stat-box .lbl{font-size:.65rem;color:var(--grey);}

/* Table réservations */
.histo-table{width:100%;border-collapse:collapse;font-size:.8rem;margin-top:.5rem;}
.histo-table th{text-align:left;padding:.5rem .8rem;background:rgba(78,163,255,.1);color:#4EA3FF;font-weight:600;}
.histo-table td{padding:.5rem .8rem;border-bottom:1px solid rgba(255,255,255,.05);}
.histo-table tr:last-child td{border-bottom:none;}
.histo-table tr:hover td{background:rgba(78,163,255,.05);}
.empty-msg{text-align:center;padding:1.5rem;color:var(--grey);font-size:.85rem;}

.statut-resa{display:inline-block;padding:.2rem .5rem;border-radius:12px;font-size:.7rem;}
.statut-confirmee{background:rgba(39,174,96,.2);color:#27ae60;}
.statut-annulee{background:rgba(231,76,60,.2);color:#e74c3c;}
.statut-en_attente{background:rgba(241,196,15,.2);color:#f1c40f;}

/* Light mode */
body.light-mode{background:linear-gradient(135deg,#EDF2F7,#DBEAFE)!important;color:#1A2844!important;}
body.light-mode .detail-panel{background:rgba(255,255,255,.95)!important;}
body.light-mode .info-item{background:rgba(0,0,0,.04)!important;}
body.light-mode .sidebar{background:linear-gradient(180deg,#2F76BC,#1E5EA5,#174C8A)!important;}

@media(max-width:768px){
    .sidebar{display:none;}
    .main{margin-left:0;}
}
</style>
</head>
<body>
<div class="wrap">

<!-- ══ SIDEBAR (identique à admin.php) ══ -->
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
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="passagers.php"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="trajets.php"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="evenements.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin.php" class="active"><i class="fas fa-car"></i> Véhicules</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

<main class="main">

<!-- ══ HEADER (identique à admin.php) ══ -->
<div class="admin-header">
    <div class="admin-logo">
        <div class="logo-eco"><span>ECO</span> <span>RIDE</span></div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="../frontoffice/vehicules_disponibles.php">Accueil</a>
        <a href="../frontoffice/evenements.php">Événements</a>
        <a href="../frontoffice/sponsors.php">Sponsors</a>
        <a href="../frontoffice/vehicules_disponibles.php">Covoiturage</a>
        <a href="profil.php" class="profile-btn">
            <div class="profile-avatar"><i class="fas fa-user"></i></div>
            <span>Profil</span>
        </a>
        <a href="admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>

<!-- ══ PANEL DÉTAIL ══ -->
<div class="detail-panel">

    <div class="panel-header">
        <h2><i class="fas fa-car"></i> Détails du véhicule #<?= $vehicule['id'] ?></h2>
        <a href="admin.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>

    <div class="panel-body">

        <!-- PHOTO + INFOS GÉNÉRALES -->
        <div class="section">
            <h3><i class="fas fa-info-circle"></i> Informations générales</h3>

            <?php if (!empty($vehicule['photo']) && file_exists($fullPath)): ?>
                <img src="<?= $photoPath ?>" alt="Photo du véhicule" class="vehicle-photo">
            <?php else: ?>
                <div class="vehicle-photo-placeholder"><i class="fas fa-car"></i></div>
            <?php endif; ?>

            <div class="info-grid">
                <div class="info-item">
                    <strong>ID</strong>#<?= $vehicule['id'] ?>
                </div>
                <div class="info-item">
                    <strong>Marque / Modèle</strong>
                    <?= htmlspecialchars($vehicule['marque']) ?> <?= htmlspecialchars($vehicule['modele']) ?>
                </div>
                <div class="info-item">
                    <strong>Immatriculation</strong>
                    <code style="background:rgba(78,163,255,.12);padding:2px 8px;border-radius:6px;">
                        <?= htmlspecialchars($vehicule['immatriculation']) ?>
                    </code>
                </div>
                <div class="info-item">
                    <strong>Couleur</strong>
                    <span class="color-dot" style="background:<?= $colorHex ?>;"></span>
                    <?= htmlspecialchars($couleur) ?>
                </div>
                <div class="info-item">
                    <strong>Capacité</strong>
                    <i class="fas fa-users" style="color:#4EA3FF;font-size:.8rem;"></i>
                    <?= $vehicule['capacite'] ?> places
                </div>
                <div class="info-item">
                    <strong>Climatisation</strong>
                    <?php if ($vehicule['climatisation']): ?>
                        <i class="fas fa-snowflake" style="color:#4EA3FF;"></i> Oui
                    <?php else: ?>
                        <i class="fas fa-times" style="color:#e74c3c;"></i> Non
                    <?php endif; ?>
                </div>
                <div class="info-item">
                    <strong>Statut</strong>
                    <?php
                        $s = $vehicule['statut'];
                        $badgeClass = $s === 'disponible' ? 'badge-disponible' : ($s === 'en_maintenance' ? 'badge-maintenance' : 'badge-indisponible');
                        $statutLabel = $s === 'disponible' ? 'Disponible' : ($s === 'en_maintenance' ? 'En maintenance' : 'Indisponible');
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                </div>
                <div class="info-item">
                    <strong>Conducteur</strong>
                    <i class="fas fa-user" style="color:#4EA3FF;font-size:.8rem;"></i>
                    <?= htmlspecialchars(($vehicule['prenom'] ?? '') . ' ' . ($vehicule['nom'] ?? '')) ?>
                </div>
            </div>
        </div>

        <!-- STATISTIQUES RÉSERVATIONS -->
        <div class="section">
            <h3><i class="fas fa-chart-bar"></i> Statistiques des réservations</h3>
            <div class="stats-row">
                <div class="stat-box">
                    <div class="num"><?= $resaStats['total'] ?? 0 ?></div>
                    <div class="lbl">Total</div>
                </div>
                <div class="stat-box" style="border-color:rgba(39,174,96,.3);">
                    <div class="num" style="background:linear-gradient(135deg,#27ae60,#2ecc71);-webkit-background-clip:text;background-clip:text;color:transparent;">
                        <?= $resaStats['confirmee'] ?? 0 ?>
                    </div>
                    <div class="lbl">Confirmées</div>
                </div>
                <div class="stat-box" style="border-color:rgba(241,196,15,.3);">
                    <div class="num" style="background:linear-gradient(135deg,#f1c40f,#f39c12);-webkit-background-clip:text;background-clip:text;color:transparent;">
                        <?= $resaStats['en_attente'] ?? 0 ?>
                    </div>
                    <div class="lbl">En attente</div>
                </div>
                <div class="stat-box" style="border-color:rgba(231,76,60,.3);">
                    <div class="num" style="background:linear-gradient(135deg,#e74c3c,#c0392b);-webkit-background-clip:text;background-clip:text;color:transparent;">
                        <?= $resaStats['annulee'] ?? 0 ?>
                    </div>
                    <div class="lbl">Annulées</div>
                </div>
            </div>
        </div>



    </div><!-- /panel-body -->
</div><!-- /detail-panel -->

</main>
</div>

<script>
function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    const icon = document.getElementById('themeBtn').querySelector('i');
    icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
}
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const btn = document.getElementById('themeBtn');
    if (btn) btn.querySelector('i').className = 'fas fa-sun';
}
</script>
</body>
</html>