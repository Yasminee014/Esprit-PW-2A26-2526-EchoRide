<?php
session_start();
$_SESSION['is_admin'] = true;

try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=ecoride;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Statistiques par statut
$stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM reclamations GROUP BY statut");
$statuts = $stmt->fetchAll();
$statutLabels = array_column($statuts, 'statut');
$statutData = array_column($statuts, 'count');

// Par priorité
$stmt = $pdo->query("SELECT priorite, COUNT(*) as count FROM reclamations GROUP BY priorite");
$priorites = $stmt->fetchAll();
$prioLabels = array_column($priorites, 'priorite');
$prioData = array_column($priorites, 'count');

// Par catégorie
$stmt = $pdo->query("SELECT categorie, COUNT(*) as count FROM reclamations GROUP BY categorie");
$categories = $stmt->fetchAll();
$catLabels = array_column($categories, 'categorie');
$catData = array_column($categories, 'count');

// Évolution 6 derniers mois
$stmt = $pdo->query("SELECT DATE_FORMAT(date_creation, '%Y-%m') as mois, COUNT(*) as count FROM reclamations WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(date_creation, '%Y-%m') ORDER BY mois ASC");
$evolution = $stmt->fetchAll();
$evoLabels = array_column($evolution, 'mois');
$evoData = array_column($evolution, 'count');

$total = array_sum($statutData);

// Charger la photo admin
if (empty($_SESSION['admin_photo']) && !empty($_SESSION['admin_id'])) {
    $stmtPhoto = $pdo->prepare("SELECT photo FROM admins WHERE id = :id");
    $stmtPhoto->execute([':id' => $_SESSION['admin_id']]);
    $adminRow = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
    if ($adminRow && !empty($adminRow['photo'])) {
        $_SESSION['admin_photo'] = $adminRow['photo'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistiques - Admin EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;--yellow:#f1c40f;--bg:#071b2c;}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(circle at 20% 15%, rgba(97,179,250,.16), transparent 36%),radial-gradient(circle at 78% 78%, rgba(25,118,210,.14), transparent 40%);opacity:1;z-index:-2;pointer-events:none;}
body::after{content:'';position:fixed;inset:0;background:linear-gradient(130deg,rgba(8,20,38,.88) 0%,rgba(12,31,58,.84) 45%,rgba(8,20,38,.9) 100%);z-index:-1;pointer-events:none;}
.wrap{display:flex;min-height:100vh;}
.sidebar{width:280px;background:linear-gradient(180deg,#2F76BC 0%,#1E5EA5 50%,#174C8A 100%);padding:1.5rem 0;position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;box-shadow:4px 0 20px rgba(0,0,0,.2);display:flex;flex-direction:column;}
.sidebar-header{padding:1.5rem;border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:1.5rem;text-align:center;}
.sidebar-header .logo{display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;}
.sidebar-header .logo-img{width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 4px 14px rgba(97,179,250,.5));margin-bottom:4px;}
.sidebar-header .logo-text{font-size:1.3rem;font-weight:700;color:#A9D6FF;letter-spacing:1px;}
.sidebar-header .logo-tagline{font-size:0.75rem;color:#BFD8F1;margin-top:2px;letter-spacing:1px;opacity:.85;}
.nav-section{color:#CFE6FF;font-size:0.7rem;text-transform:uppercase;letter-spacing:2px;padding:.75rem 1.5rem;margin-top:0.5rem;opacity:.8;font-weight:600;}
.sidebar nav ul{list-style:none;}
.sidebar nav ul li{margin-bottom:.35rem;}
.sidebar nav ul li a{display:flex;align-items:center;gap:12px;padding:.75rem 1.5rem;color:#EAF4FF;text-decoration:none;transition:all .3s;font-size:.9rem;margin:0 0.5rem;border-radius:10px;font-weight:500;}
.sidebar nav ul li a i{width:22px;color:#EAF4FF;font-size:1rem;}
.sidebar nav ul li a:hover{background:rgba(111,168,220,.3);color:#fff;transform:translateX(4px);}
.sidebar nav ul li a.active{background:linear-gradient(135deg,#6FA8DC,#8FC1F5);color:#FFFFFF;box-shadow:0 4px 12px rgba(111,168,220,.3);}
.sidebar-footer{margin-top:auto;padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,.1);}
.sidebar-footer a{display:flex;align-items:center;gap:12px;color:#FFCDD2;text-decoration:none;font-size:0.85rem;padding:0.5rem 0;border-radius:10px;transition:all .3s;}
.sidebar-footer a:hover{color:#FF8A80;transform:translateX(5px);}
.main{flex:1;margin-left:280px;padding:1.6rem;position:relative;z-index:1;}
.admin-header{background:linear-gradient(90deg,#071C2F,#0A2A47,#0D355B);padding:1rem 1.8rem;display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;border-radius:12px;border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap;gap:1rem;}
.admin-logo{display:flex;flex-direction:column;}
.admin-logo .logo-eco{font-size:1.5rem;font-weight:700;letter-spacing:1px;}
.admin-logo .logo-eco span:first-child{color:#4EA3FF;}
.admin-logo .logo-eco span:last-child{color:#6BB8FF;}
.admin-logo .logo-tagline{font-size:0.75rem;color:#A8C1D9;margin-top:4px;}
.admin-nav{display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;}
.admin-nav a{text-decoration:none;padding:0.5rem 1.2rem;border-radius:30px;font-size:0.9rem;font-weight:500;transition:all 0.3s;background:transparent;color:#CFE6FF;font-family:'Poppins',sans-serif;}
.admin-nav a:hover{background:rgba(255,255,255,0.1);color:#FFFFFF;}
.admin-nav .lang-form{display:flex;align-items:center;}
.admin-nav .profile-btn{background:#003050;color:#FFFFFF;display:flex;align-items:center;gap:10px;padding:0.5rem 1.2rem;border-radius:30px;text-decoration:none;}
.admin-nav .profile-btn:hover{background:#002050;transform:translateY(-2px);}
.profile-avatar{width:28px;height:28px;background:#5FA8FF;border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.profile-avatar i{font-size:0.8rem;color:#FFFFFF;}
.profile-avatar img{width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;}
.admin-nav .admin-btn{background:rgba(231,76,60,0.2);border:1px solid rgba(231,76,60,0.4);color:#e74c3c;}
.admin-nav .admin-btn:hover{background:rgba(231,76,60,0.35);}
.theme-btn{background:rgba(255,255,255,0.1);border:none;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:1.1rem;transition:all 0.3s;display:flex;align-items:center;justify-content:center;color:white;}
.content{padding:2rem;}
.hero-section{background:linear-gradient(135deg,#1976D2,#0F3B6E);border-radius:20px;padding:1.5rem 2rem;margin-bottom:2rem;display:flex;justify-content:space-between;}
.hero-content h1{font-size:1.5rem;}
.hero-content h1 .highlight{color:#61B3FA;}
.stats-overview{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;}
.stat-card{background:rgba(255,255,255,.08);border-radius:16px;padding:1rem;text-align:center;flex:1;min-width:120px;border:1px solid rgba(97,179,250,.15);}
.stat-card .number{font-size:2rem;font-weight:bold;color:#61B3FA;}
.charts-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:2rem;}
.chart-card{background:rgba(255,255,255,.07);border-radius:20px;padding:1.5rem;border:1px solid rgba(97,179,250,.15);}
.chart-card h3{text-align:center;margin-bottom:1rem;color:#61B3FA;}
canvas{max-height:280px;width:100%!important;}
.footer{text-align:center;margin-top:2rem;padding:1rem;color:#A7A9AC;border-top:1px solid rgba(97,179,250,.1);}
body.light-mode{background:#f5f5f5;color:#263238;}
body.light-mode .sidebar{background:#fff;border-right:1px solid #dfe6f2;}
body.light-mode .sidebar-header .logo-text{color:#1f2937;}
body.light-mode .sidebar nav ul li a{color:#1f2937;}
body.light-mode .sidebar nav ul li a.active{background:rgba(25,118,210,.08);color:#1976D2;}
body.light-mode .admin-header{background:#fff;border:1px solid #dbe4f0;color:#1f2937;}
body.light-mode .admin-nav a{color:#1f2937;}
body.light-mode .stat-card, body.light-mode .chart-card{background:#fff;border-color:#e0e0e0;color:#333;}
body.light-mode .footer{color:#666;}
</style>
</head>
<body>
<div class="wrap">
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
                <li><a href="/ecoride/View/backoffice/dashboard_event.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a href="/ecoride/View/backoffice/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
                <li><a href="/ecoride/View/backoffice/admin.php"><i class="fas fa-car"></i> Véhicules</a></li>
                <li><a href="/ecoride/View/backoffice/lostfound_admin.php"><i class="fas fa-search-location"></i> Objets perdus</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="/ecoride/View/frontoffice/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <main class="main">
        <div class="admin-header">
            <div class="admin-logo">
                <div class="logo-eco">
                    <span>ECO</span> <span>RIDE</span>
                </div>
                <div class="logo-tagline">Covoiturage Intelligent</div>
            </div>
            <div class="admin-nav">
                <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>
                <a href="/ecoride/Controller/AdminController.php?action=showProfile" class="profile-btn">
                    <div class="profile-avatar">
                        <?php if (!empty($_SESSION['admin_photo'])): ?>
                            <img src="/ecoride/uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="Photo admin" onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <i class="fas fa-user-shield" style="display:none"></i>
                        <?php else: ?>
                            <i class="fas fa-user-shield"></i>
                        <?php endif; ?>
                    </div>
                    <span>Profil</span>
                </a>
                <a href="/ecoride/View/backoffice/admin.php" class="admin-btn">Admin</a>
                <button class="theme-btn" onclick="toggleTheme()" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>

        <div class="content">
            <div class="hero-section">
                <div class="hero-content">
                    <h1>Statistiques des <span class="highlight">réclamations</span></h1>
                    <p>Visualisation interactive</p>
                </div>
                <div class="hero-icon"><i class="fas fa-chart-pie"></i></div>
            </div>

            <div class="stats-overview">
                <div class="stat-card"><div class="number"><?= $total ?></div><div class="label">Total réclamations</div></div>
                <?php foreach($statuts as $s): ?>
                <div class="stat-card"><div class="number"><?= $s['count'] ?></div><div class="label"><?= ucfirst(str_replace('_',' ',$s['statut'])) ?></div></div>
                <?php endforeach; ?>
            </div>

            <div class="charts-grid">
                <div class="chart-card"><h3><i class="fas fa-chart-pie"></i> Par statut</h3><canvas id="statutChart"></canvas></div>
                <div class="chart-card"><h3><i class="fas fa-chart-pie"></i> Par priorité</h3><canvas id="prioriteChart"></canvas></div>
                <div class="chart-card"><h3><i class="fas fa-chart-pie"></i> Par catégorie</h3><canvas id="categorieChart"></canvas></div>
                <div class="chart-card"><h3><i class="fas fa-chart-line"></i> Évolution (6 mois)</h3><canvas id="evolutionChart"></canvas></div>
            </div>

            <div class="footer">EcoRide - Tableau de bord statistique © <?= date('Y') ?></div>
        </div>
    </main>
</div>

<script>
const statutLabels = <?= json_encode($statutLabels) ?>;
const statutData = <?= json_encode($statutData) ?>;
const prioLabels = <?= json_encode($prioLabels) ?>;
const prioData = <?= json_encode($prioData) ?>;
const catLabels = <?= json_encode($catLabels) ?>;
const catData = <?= json_encode($catData) ?>;
const evoLabels = <?= json_encode($evoLabels) ?>;
const evoData = <?= json_encode($evoData) ?>;

function getColors(n) {
    const palette = ['#61B3FA','#f1c40f','#27ae60','#e74c3c','#9b59b6','#e67e22','#1abc9c'];
    return palette.slice(0,n);
}

new Chart(document.getElementById('statutChart'), {
    type:'pie',
    data:{ labels:statutLabels.map(l=>l.replace('_',' ')), datasets:[{ data:statutData, backgroundColor:getColors(statutLabels.length) }] },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ color:'#fff' } } } }
});
new Chart(document.getElementById('prioriteChart'), {
    type:'doughnut',
    data:{ labels:prioLabels, datasets:[{ data:prioData, backgroundColor:['#27ae60','#f1c40f','#e74c3c'] }] },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ color:'#fff' } } } }
});
new Chart(document.getElementById('categorieChart'), {
    type:'pie',
    data:{ labels:catLabels, datasets:[{ data:catData, backgroundColor:getColors(catLabels.length) }] },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ color:'#fff' } } } }
});
new Chart(document.getElementById('evolutionChart'), {
    type:'bar',
    data:{ labels:evoLabels, datasets:[{ label:'Nb réclamations', data:evoData, backgroundColor:'#61B3FA', borderRadius:8 }] },
    options:{ responsive:true, scales:{ y:{ beginAtZero:true, ticks:{ color:'#fff' } }, x:{ ticks:{ color:'#fff' } } }, plugins:{ legend:{ labels:{ color:'#fff' } } } }
});

function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    const btn = document.getElementById('themeToggle');
    if (btn) btn.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    location.reload();
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const btn = document.getElementById('themeToggle');
    if (btn) btn.innerHTML = '<i class="fas fa-sun"></i>';
}
</script>
</body>
</html>