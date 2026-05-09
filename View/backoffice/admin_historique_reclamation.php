<?php
session_start();
$_SESSION['is_admin'] = true;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ecoride;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: admin_reclamations.php');
    exit;
}

// Reclamation principale
$stmt = $pdo->prepare("SELECT * FROM reclamations WHERE id = ?");
$stmt->execute([$id]);
$reclamation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reclamation) {
    header('Location: admin_reclamations.php');
    exit;
}

// Réponses admin
$rStmt = $pdo->prepare("SELECT * FROM reponse WHERE reclamation_id = ? ORDER BY date_reponse DESC");
$rStmt->execute([$id]);
$reponses = $rStmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques globales pour cette réclamation
$totalReponses = count($reponses);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historique réclamation #<?= $id ?> — EcoRide</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;transition:background 0.3s,color 0.3s;}
.wrap{display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{width:280px;background:linear-gradient(180deg,#2F76BC 0%,#1E5EA5 50%,#174C8A 100%);padding:1.5rem 0;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,.2);display:flex;flex-direction:column;}
.sidebar-header{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:1.5rem;text-align:center;}
.sidebar-header .logo{display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;}
.sidebar-header .logo-img{width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 4px 14px rgba(97,179,250,.5));margin-bottom:4px;}
.sidebar-header .logo-text{font-size:1.3rem;font-weight:700;color:#A9D6FF;letter-spacing:1px;}
.sidebar-header .logo-tagline{font-size:0.65rem;color:#BFD8F1;margin-top:2px;letter-spacing:2px;opacity:.85;}
.nav-section{color:#CFE6FF;font-size:0.7rem;text-transform:uppercase;letter-spacing:2px;padding:.75rem 1.5rem;margin-top:.5rem;opacity:.8;font-weight:600;}
.sidebar nav ul{list-style:none;}
.sidebar nav ul li{margin-bottom:.25rem;}
.sidebar nav ul li a{display:flex;align-items:center;gap:12px;padding:.7rem 1.5rem;color:#EAF4FF;text-decoration:none;transition:all .3s;font-size:.85rem;margin:0 .5rem;border-radius:10px;font-weight:500;}
.sidebar nav ul li a i{width:22px;color:#EAF4FF;font-size:1rem;}
.sidebar nav ul li a:hover{background:rgba(111,168,220,.3);color:#fff;transform:translateX(5px);}
.sidebar nav ul li a.active{background:linear-gradient(135deg,#6FA8DC,#8FC1F5);color:#fff;box-shadow:0 4px 12px rgba(111,168,220,.3);}
.sidebar-footer{margin-top:auto;padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.1);}
.sidebar-footer a{display:flex;align-items:center;gap:12px;color:#FFCDD2;text-decoration:none;font-size:.85rem;padding:.5rem 0;border-radius:10px;transition:all .3s;}
.sidebar-footer a:hover{color:#FF8A80;transform:translateX(5px);}

/* MAIN */
.main{flex:1;margin-left:280px;padding:1.6rem;position:relative;z-index:1;}

/* HEADER */
.admin-header{background:linear-gradient(90deg,#071C2F,#0A2A47,#0D355B);padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;border-radius:12px;border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap;gap:1rem;}
.admin-logo{display:flex;flex-direction:column;}
.admin-logo .logo-eco{font-size:1.3rem;font-weight:700;letter-spacing:1px;}
.admin-logo .logo-eco span:first-child{color:#4EA3FF;}
.admin-logo .logo-eco span:last-child{color:#6BB8FF;}
.admin-logo .logo-tagline{font-size:.65rem;color:#A8C1D9;margin-top:2px;}
.admin-nav{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;}
.admin-nav a{text-decoration:none;padding:.5rem 1.2rem;border-radius:30px;font-size:.9rem;font-weight:500;transition:all .3s;background:transparent;color:#CFE6FF;}
.admin-nav a:hover{background:rgba(255,255,255,.1);color:#fff;}
.admin-nav .profile-btn{background:#003050;color:#fff;display:flex;align-items:center;gap:8px;padding:.5rem 1.2rem;border-radius:30px;text-decoration:none;font-size:.9rem;font-weight:500;transition:all .3s;}
.admin-nav .profile-btn:hover{background:#002050;transform:translateY(-2px);}
.profile-avatar{width:28px;height:28px;background:#5FA8FF;border-radius:50%;display:flex;align-items:center;justify-content:center;}
.profile-avatar i{font-size:.8rem;color:#fff;}
.admin-nav .admin-btn{background:rgba(231,76,60,.2);border:1px solid rgba(231,76,60,.4);color:#e74c3c;padding:.5rem 1.2rem;border-radius:30px;text-decoration:none;font-size:.9rem;font-weight:500;transition:all .3s;}
.admin-nav .admin-btn:hover{background:rgba(231,76,60,.35);}
.theme-btn{background:rgba(255,255,255,.1);border:none;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:1.1rem;transition:all .3s;display:flex;align-items:center;justify-content:center;color:#fff;}
.theme-btn:hover{background:rgba(255,255,255,.2);transform:rotate(15deg);}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.6rem;}
.stat{background:rgba(255,255,255,.07);border:1px solid rgba(78,163,255,.16);border-radius:14px;padding:1.2rem;text-align:center;transition:transform .3s;}
.stat:hover{transform:translateY(-3px);border-color:#4EA3FF;}
.stat i{font-size:1.8rem;color:#4EA3FF;margin-bottom:.35rem;display:block;}
.stat .num{font-size:2rem;font-weight:700;background:linear-gradient(135deg,#4EA3FF,#fff);-webkit-background-clip:text;background-clip:text;color:transparent;}
.stat .lbl{color:var(--grey);font-size:.75rem;}

/* FICHE RECLAMATION */
.claim-card{background:rgba(255,255,255,.07);border:1px solid rgba(78,163,255,.16);border-radius:20px;padding:1.5rem;margin-bottom:2rem;}
.claim-card-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;}
.claim-card h2{color:#4EA3FF;font-size:1.15rem;margin-bottom:.4rem;}
.claim-card-meta{display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.5rem;}
.claim-card-meta span{background:rgba(78,163,255,.15);padding:.2rem .8rem;border-radius:20px;font-size:.75rem;display:flex;align-items:center;gap:5px;}
.claim-desc{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:1rem;font-size:.88rem;line-height:1.7;color:#CFE6FF;margin-top:1rem;}

/* BADGE STATUT */
.badge-statut{display:inline-flex;align-items:center;gap:5px;padding:.3rem .9rem;border-radius:20px;font-size:.78rem;font-weight:600;}
.badge-en_attente{background:rgba(241,196,15,.15);color:#f1c40f;border:1px solid rgba(241,196,15,.3);}
.badge-en_cours{background:rgba(52,152,219,.15);color:#3498db;border:1px solid rgba(52,152,219,.3);}
.badge-resolue{background:rgba(39,174,96,.15);color:#27ae60;border:1px solid rgba(39,174,96,.3);}
.badge-rejetee{background:rgba(231,76,60,.15);color:#e74c3c;border:1px solid rgba(231,76,60,.3);}
.badge-faible{background:rgba(39,174,96,.15);color:#27ae60;}
.badge-moyenne{background:rgba(241,196,15,.15);color:#f1c40f;}
.badge-elevee{background:rgba(231,76,60,.15);color:#e74c3c;}

/* SECTION TITLE */
.section-title{font-size:1rem;margin-bottom:1rem;color:#4EA3FF;display:flex;align-items:center;gap:8px;}

/* HISTORIQUE TABLE */
.histo-table{width:100%;border-collapse:collapse;font-size:.82rem;background:rgba(255,255,255,.03);border-radius:14px;overflow:hidden;}
.histo-table th{text-align:left;padding:.8rem 1rem;background:rgba(78,163,255,.12);color:#4EA3FF;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;}
.histo-table td{padding:.85rem 1rem;border-bottom:1px solid rgba(255,255,255,.05);vertical-align:middle;}
.histo-table tbody tr:last-child td{border-bottom:none;}
.histo-table tbody tr:hover td{background:rgba(97,179,250,.04);}

/* EMPTY */
.empty-state{text-align:center;padding:3rem;color:var(--grey);}
.empty-state i{font-size:3rem;opacity:.3;margin-bottom:1rem;display:block;}

/* BTN BACK */
.btn-back{background:rgba(78,163,255,.15);border:1px solid rgba(78,163,255,.3);padding:.5rem 1.1rem;border-radius:20px;color:#4EA3FF;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:.85rem;transition:all .2s;margin-bottom:1.5rem;}
.btn-back:hover{background:rgba(78,163,255,.25);}

/* RESPONSIVE */
@media(max-width:768px){.sidebar{display:none;}.main{margin-left:0;}}

body.light-mode .sidebar{background:linear-gradient(180deg,#2F76BC,#1E5EA5,#174C8A);}
body.light-mode .stat{background:#fff;}
body.light-mode .claim-card{background:#fff;color:#333;}
body.light-mode .histo-table{background:#fff;}
</style>
</head>
<body>
<div class="wrap">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/ecoride/View/backoffice/admin.php" class="logo">
            <img src="/ecoride/assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="/ecoride/View/backoffice/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=passagers"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="/ecoride/View/backoffice/admin_trajet.php?page=evenements"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="/ecoride/View/backoffice/admin_reclamations.php" class="active"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="/ecoride/View/backoffice/admin.php"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="/ecoride/View/backoffice/lostfound_admin.php"><i class="fas fa-search-location"></i> Objets perdus</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="/ecoride/View/frontoffice/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

<main class="main">

<!-- HEADER -->
<div class="admin-header">
    <div class="admin-logo">
        <div class="logo-eco"><span>ECO</span> <span>RIDE</span></div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>
        <a href="/ecoride/View/backoffice/profil.php" class="profile-btn">
            <div class="profile-avatar"><i class="fas fa-user"></i></div>
            <span>Profil</span>
        </a>
        <a href="/ecoride/View/backoffice/admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn"><i class="fas fa-moon"></i></button>
    </div>
</div>

<!-- BOUTON RETOUR -->
<a href="/ecoride/View/backoffice/admin_reclamations.php" class="btn-back">
    <i class="fas fa-arrow-left"></i> Retour aux réclamations
</a>

<!-- STATS -->
<div class="stats">
    <div class="stat">
        <i class="fas fa-hashtag"></i>
        <div class="num">#<?= $id ?></div>
        <div class="lbl">Réclamation</div>
    </div>
    <div class="stat">
        <i class="fas fa-user"></i>
        <div class="num"><?= intval($reclamation['utilisateur_id'] ?? 0) ?></div>
        <div class="lbl">Utilisateur ID</div>
    </div>
    <div class="stat">
        <i class="fas fa-reply"></i>
        <div class="num"><?= $totalReponses ?></div>
        <div class="lbl">Réponse(s)</div>
    </div>
    <div class="stat">
        <i class="fas fa-calendar-alt"></i>
        <div class="num"><?= date('d/m', strtotime($reclamation['date_creation'])) ?></div>
        <div class="lbl">Date création</div>
    </div>
</div>

<!-- FICHE RÉCLAMATION -->
<div class="claim-card">
    <div class="claim-card-header">
        <div>
            <h2><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($reclamation['titre']) ?></h2>
            <div class="claim-card-meta">
                <span><i class="fas fa-tag"></i> <?= htmlspecialchars(ucfirst($reclamation['categorie'] ?? '')) ?></span>
                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($reclamation['date_creation'])) ?></span>
                <span><i class="fas fa-user"></i> Utilisateur #<?= intval($reclamation['utilisateur_id'] ?? 0) ?></span>
                <?php
                    $priorite = $reclamation['priorite'] ?? 'faible';
                    $prioriteLabel = ['faible' => '🟢 Faible', 'moyenne' => '🟡 Moyenne', 'elevee' => '🔴 Élevée'][$priorite] ?? $priorite;
                ?>
                <span class="badge-statut badge-<?= $priorite ?>"><?= $prioriteLabel ?></span>
            </div>
        </div>
        <?php
            $statut = $reclamation['statut'] ?? 'en_attente';
            $statutLabel = ['en_attente' => '⏳ En attente', 'en_cours' => '🔄 En cours', 'resolue' => '✅ Résolue', 'rejetee' => '❌ Rejetée'][$statut] ?? $statut;
        ?>
        <span class="badge-statut badge-<?= $statut ?>"><?= $statutLabel ?></span>
    </div>
    <div class="claim-desc">
        <?= nl2br(htmlspecialchars($reclamation['description'] ?? '')) ?>
    </div>
</div>

<!-- HISTORIQUE DES RÉPONSES -->
<div class="section-title">
    <i class="fas fa-history"></i> Historique des réponses admin
</div>

<?php if (empty($reponses)): ?>
    <div class="empty-state">
        <i class="fas fa-comment-slash"></i>
        <p>Aucune réponse admin pour cette réclamation</p>
    </div>
<?php else: ?>
    <table class="histo-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Auteur</th>
                <th>Date réponse</th>
                <th>Contenu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reponses as $rep): ?>
            <tr>
                <td><code>#<?= intval($rep['id']) ?></code></td>
                <td><i class="fas fa-user-shield"></i> <?= htmlspecialchars($rep['auteur_admin'] ?? 'Admin') ?></td>
                <td><i class="fas fa-calendar-day"></i> <?= date('d/m/Y H:i', strtotime($rep['date_reponse'])) ?></td>
                <td><?= nl2br(htmlspecialchars($rep['contenu'] ?? '')) ?></td>
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
