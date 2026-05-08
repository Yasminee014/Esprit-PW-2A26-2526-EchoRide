<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';
require_once __DIR__ . '/../../Model/ReservationModel.php';

$db = Database::getInstance();
$vModel = new VehiculeModel();
$rModel = new ReservationModel();

// Force admin mode for testing (remove in production)
$_SESSION['is_admin'] = true;
$_SESSION['user_id'] = 1;

$vehiculeId = intval($_GET['id'] ?? 0);
$vehicule = $vModel->getById($vehiculeId);
$historiqueResa = $rModel->getByVehiculeId($vehiculeId);
$resaStats = $rModel->countByVehiculeId($vehiculeId);

if (!$vehicule) {
    header('Location: admin.php');
    exit;
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reservation_statut') {
        $resaId = intval($_POST['resa_id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        if (in_array($statut, ['confirmee', 'annulee', 'en_attente'])) {
            $stmt = $db->prepare("UPDATE reservations SET statut = ? WHERE id = ?");
            $stmt->execute([$statut, $resaId]);
            $_SESSION['success'] = 'Statut de réservation mis à jour';
        }
        header("Location: admin_historique_vehicule.php?id=" . $vehiculeId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historique du véhicule — EcoRide</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;transition:background 0.3s, color 0.3s;}
.wrap{display:flex;min-height:100vh;}

/* ========== SIDEBAR - MEME STYLE QUE ADMIN.PHP ========== */
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

/* Stats - MEME STYLE QUE ADMIN.PHP */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.6rem;}
.stat{background:rgba(255,255,255,.07);border:1px solid rgba(78,163,255,.16);border-radius:14px;padding:1.2rem;text-align:center;transition:transform 0.3s;}
.stat:hover{transform:translateY(-3px);border-color:#4EA3FF;}
.stat i{font-size:1.8rem;color:#4EA3FF;margin-bottom:.35rem;display:block;}
.stat .num{font-size:2rem;font-weight:700;background:linear-gradient(135deg,#4EA3FF,#fff);-webkit-background-clip:text;background-clip:text;color:transparent;}
.stat .lbl{color:var(--grey);font-size:.75rem;}

/* Carte véhicule */
.vehicle-card {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(78,163,255,.16);
    border-radius: 20px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
    align-items: center;
}

.vehicle-image {
    width: 100px;
    height: 80px;
    background: #4EA3FF;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vehicle-image i { font-size: 40px; color: white; }
.vehicle-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; }

.vehicle-info h2 { color: #4EA3FF; margin-bottom: 0.5rem; font-size: 1.3rem; }
.vehicle-details { display: flex; flex-wrap: wrap; gap: 0.8rem; margin-top: 0.5rem; }
.vehicle-details span { background: rgba(78,163,255,.15); padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.75rem; }

/* Tableau historique - MEME STYLE QUE ADMIN.PHP */
.histo-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}

.histo-table th {
    text-align: left;
    padding: 0.8rem;
    background: rgba(78,163,255,.1);
    color: #4EA3FF;
}

.histo-table td {
    padding: 0.8rem;
    border-bottom: 1px solid rgba(255,255,255,.05);
}

.statut-resa {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.7rem;
}
.statut-confirmee { background: rgba(39,174,96,.2); color: #27ae60; }
.statut-annulee { background: rgba(231,76,60,.2); color: #e74c3c; }
.statut-attente { background: rgba(241,196,15,.2); color: #f1c40f; }

.btn-back {
    background: rgba(78,163,255,.15);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    color: #4EA3FF;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}

.btn-back:hover { background: rgba(78,163,255,.25); }

select, button[type="submit"] {
    background: rgba(78,163,255,.15);
    border: 1px solid rgba(78,163,255,.3);
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    color: white;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.section-title {
    font-size: 1rem;
    margin-bottom: 1rem;
    color: #4EA3FF;
    display: flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; }
}

body.light-mode .sidebar {
    background: linear-gradient(180deg, #2F76BC, #1E5EA5, #174C8A);
}
body.light-mode .stat { background: white; }
body.light-mode .vehicle-card { background: white; }
</style>
</head>
<body>
<div class="wrap">

<!-- SIDEBAR - MEME QUE ADMIN.PHP -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php" class="logo">
            <img src="/ecoride/assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="passagers.php"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="evenements.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="/ecoride/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin.php" class="active"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="lost_found.php"><i class="fas fa-search-location"></i> Lost &amp; Found</a></li>
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
        <a href="profil.php" class="profile-btn"><i class="fas fa-user"></i> Profil</a>
        <a href="admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
    <?php unset($_SESSION['success']); endif; ?>

<!-- Statistiques -->
<div class="stats">
    <div class="stat"><i class="fas fa-calendar-alt"></i><div class="num"><?= $resaStats['total'] ?? 0 ?></div><div class="lbl">Total réservations</div></div>
    <div class="stat"><i class="fas fa-check-circle"></i><div class="num"><?= $resaStats['confirmee'] ?? 0 ?></div><div class="lbl">Confirmées</div></div>
    <div class="stat"><i class="fas fa-clock"></i><div class="num"><?= $resaStats['en_attente'] ?? 0 ?></div><div class="lbl">En attente</div></div>
    <div class="stat"><i class="fas fa-ban"></i><div class="num"><?= $resaStats['annulee'] ?? 0 ?></div><div class="lbl">Annulées</div></div>
</div>

<!-- Carte du véhicule -->
<div class="vehicle-card">
    <div class="vehicle-image">
        <?php if (!empty($vehicule['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/ecoride/assets/uploads/vehicules/' . $vehicule['photo'])): ?>
            <img src="/ecoride/assets/uploads/vehicules/<?= $vehicule['photo'] ?>" alt="Véhicule">
        <?php else: ?>
            <i class="fas fa-car"></i>
        <?php endif; ?>
    </div>
    <div class="vehicle-info">
        <h2><?= htmlspecialchars($vehicule['marque']) ?> <?= htmlspecialchars($vehicule['modele']) ?></h2>
        <div class="vehicle-details">
            <span><i class="fas fa-id-card"></i> <?= htmlspecialchars($vehicule['immatriculation']) ?></span>
            <span><i class="fas fa-user"></i> <?= htmlspecialchars(($vehicule['prenom'] ?? '') . ' ' . ($vehicule['nom'] ?? '')) ?></span>
            <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($vehicule['email'] ?? 'Non renseigné') ?></span>
            <span><i class="fas fa-users"></i> <?= $vehicule['capacite'] ?> places</span>
            <span><i class="fas fa-palette"></i> <?= htmlspecialchars($vehicule['couleur'] ?? 'Non spécifiée') ?></span>
            <span><?= $vehicule['climatisation'] ? '<i class="fas fa-snowflake"></i> Clim' : '<i class="fas fa-sun"></i> Sans clim' ?></span>
        </div>
    </div>
</div>

<!-- Historique des réservations -->
<div class="section-title">
    <i class="fas fa-history"></i> Historique des réservations
</div>

<?php if (empty($historiqueResa)): ?>
    <div style="text-align:center;padding:3rem;color:var(--grey);">
        <i class="fas fa-calendar-times" style="font-size:3rem;opacity:0.3;margin-bottom:1rem;display:block;"></i>
        <p>Aucune réservation pour ce véhicule</p>
    </div>
<?php else: ?>
    <table class="histo-table">
        <thead>
            <tr><th>ID</th><th>Passager</th><th>Email</th><th>Date réservation</th><th>Note</th><th>Statut</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php foreach ($historiqueResa as $resa): ?>
            <tr>
                <td>#<?= $resa['id'] ?></td>
                <td><i class="fas fa-user"></i> <?= htmlspecialchars(($resa['passager_prenom'] ?? '') . ' ' . ($resa['passager_nom'] ?? '')) ?></td>
                <td><i class="fas fa-envelope"></i> <?= htmlspecialchars($resa['passager_email'] ?? 'Non renseigné') ?></td>
                <td><i class="fas fa-calendar-day"></i> <?= date('d/m/Y H:i', strtotime($resa['date_reservation'])) ?></td>
                <td><?= htmlspecialchars($resa['note'] ?? 'Aucune') ?></td>
                <td>
                    <span class="statut-resa statut-<?= $resa['statut'] ?>">
                        <?= $resa['statut'] === 'confirmee' ? '✅ Confirmée' : ($resa['statut'] === 'annulee' ? '❌ Annulée' : '⏳ En attente') ?>
                    </span>
                </td>
                <td>
                    <?php if ($resa['statut'] === 'en_attente'): ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="reservation_statut">
                        <input type="hidden" name="resa_id" value="<?= $resa['id'] ?>">
                        <select name="statut" onchange="this.form.submit()">
                            <option value="en_attente" selected>⏳ En attente</option>
                            <option value="confirmee">✅ Confirmer</option>
                            <option value="annulee">❌ Annuler</option>
                        </select>
                    </form>
                    <?php else: ?>
                    <span style="opacity:0.5;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

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

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'opacity 0.5s';
        a.style.opacity = '0';
        setTimeout(() => a.remove(), 500);
    });
}, 4000);
</script>
</body>
</html>