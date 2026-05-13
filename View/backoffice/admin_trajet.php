<?php
$initialPage = $_GET['page'] ?? 'trajets';
$allowedPages = ['dashboard', 'passagers', 'trajets', 'destinations', 'evenements', 'reclamations', 'lostfound'];
if (!in_array($initialPage, $allowedPages, true)) {
    $initialPage = 'trajets';
}

require_once __DIR__ . '/../../Config/Database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../Controller/AdminController.php?action=showLogin');
    exit();
}

$pdo = Database::getInstance();

// Charger la photo admin depuis la BDD
if (empty($_SESSION['admin_photo'])) {
    $stmtPhoto = $pdo->prepare("SELECT photo FROM admins WHERE id = :id");
    $stmtPhoto->execute([':id' => $_SESSION['admin_id']]);
    $adminRow = $stmtPhoto->fetch(PDO::FETCH_ASSOC);
    if ($adminRow && !empty($adminRow['photo'])) {
        $_SESSION['admin_photo'] = $adminRow['photo'];
    }
}

// Passagers
$passagers = $pdo->query("SELECT id, prenom, nom, email, telephone, role, statut, created_at FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Événements
$evenements = $pdo->query("SELECT id, titre, type, ville, date_evenement, nb_places, statut FROM evenements ORDER BY date_evenement DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --blue:#1976D2; --blue-light:#61B3FA;
            --dark:#0A1628; --dark2:#0D1F3A; --dark3:#0F3B6E;
            --white:#F4F5F7; --grey:#A7A9AC;
            --green:#27ae60; --red:#e74c3c; --yellow:#f1c40f; --orange:#e67e22;
            --muted:#6b8fa8;
            --sidebar-w: 280px;
        }

        body {
            font-family:'Poppins',sans-serif;
            background:linear-gradient(135deg,var(--dark),var(--dark2));
            color:#fff;
            min-height:100vh;
            transition:background 0.3s, color 0.3s;
        }
        body.light-mode {
            background:#eef2f7;
            color:#1a2a3a;
        }
        body.light-mode .sidebar {
            background: linear-gradient(180deg, #2F76BC 0%, #1E5EA5 50%, #174C8A 100%);
        }
        body.light-mode .stat {
            background:#fff;
            border-color:#dde4ef;
        }
        body.light-mode .tbl-wrap {
            background:#fff;
            border-color:#dde4ef;
        }
        body.light-mode thead {
            background:rgba(25,118,210,.12);
        }
        body.light-mode tbody td {
            color:#1a2a3a;
        }
        body.light-mode .search-box input,
        body.light-mode select.fsel {
            background:#f0f4f8;
            color:#1a2a3a;
            border-color:#c0cfe0;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #2F76BC 0%, #1E5EA5 50%, #174C8A 100%);
            padding: 1.5rem 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.2);
            z-index: 200;
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
        }

        .sidebar-header .logo-tagline {
            font-size: 0.65rem;
            color: #BFD8F1;
            margin-top: 2px;
            letter-spacing: 2px;
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
            font-weight: 600;
        }

        .sidebar nav ul {
            list-style: none;
        }

        .sidebar nav ul li {
            margin-bottom: 0.25rem;
        }

        .sidebar nav ul li .nav-btn {
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
            font-weight: 500;
            cursor: pointer;
            background: none;
            border: none;
            width: calc(100% - 1rem);
            font-family: inherit;
        }

        .sidebar nav ul li .nav-btn i {
            width: 22px;
            color: #EAF4FF;
            font-size: 1rem;
        }

        .sidebar nav ul li .nav-btn:hover {
            background: rgba(111,168,220,0.3);
            color: white;
            transform: translateX(5px);
        }

        .sidebar nav ul li .nav-btn.active {
            background: linear-gradient(135deg, #6FA8DC, #8FC1F5);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(111,168,220,0.3);
        }

        .sidebar nav ul li .nav-btn.active i {
            color: #FFFFFF;
        }

        .sidebar nav ul li a.nav-btn {
            display: flex;
            width: calc(100% - 1rem);
            text-decoration: none;
            color: #EAF4FF;
            box-sizing: border-box;
        }

        .sidebar-sep {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 0.5rem 1rem;
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
        }

        .sidebar-footer a i {
            width: 22px;
            color: #FFCDD2;
        }

        .sidebar-footer a:hover {
            color: #FF8A80;
            transform: translateX(5px);
        }

        /* ========== MAIN CONTENT ========== */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ========== HEADER STYLE (comme header-nav.php) ========== */
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
        }

        .admin-nav a:hover {
            background: rgba(255,255,255,0.1);
            color: #FFFFFF;
        }

        /* ========== BOUTON PROFIL (comme header-nav.php) ========== */
        .admin-nav .profile-btn {
            background: #003050;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.5rem 1.2rem;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s;
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

        /* ========== BOUTON ADMIN - STYLE ROUGE (comme header-nav.php) ========== */
        .admin-nav .admin-btn {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .admin-nav .admin-btn:hover {
            background: rgba(231,76,60,0.35);
            transform: translateY(-2px);
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

        /* ========== MAIN CONTENT ========== */
        .main {
            flex: 1;
            margin-left: var(--sidebar-w);
            padding: 1.6rem;
            position: relative;
            z-index: 1;
        }

        /* ========== STATS ========== */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.6rem;
        }

        .stat {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(78,163,255,0.16);
            border-radius: 14px;
            padding: 1.2rem;
            text-align: center;
            transition: transform 0.3s;
        }

        .stat:hover {
            transform: translateY(-3px);
            border-color: #4EA3FF;
        }

        .stat i {
            font-size: 1.8rem;
            color: #4EA3FF;
            margin-bottom: 0.35rem;
            display: block;
        }

        .stat .num {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4EA3FF, #fff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .stat .lbl {
            color: var(--grey);
            font-size: 0.75rem;
        }

        /* ========== TOOLBAR ========== */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .toolbar-left {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .toolbar-right {
            display: flex;
            gap: 0.6rem;
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--grey);
            font-size: 0.82rem;
        }

        .search-box input {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(78,163,255,0.3);
            color: #fff;
            padding: 0.5rem 0.9rem 0.5rem 2.2rem;
            border-radius: 18px;
            font-size: 0.84rem;
            outline: none;
            font-family: inherit;
            width: 220px;
        }

        .search-box input:focus {
            border-color: #4EA3FF;
            background: rgba(78,163,255,0.1);
        }

        .search-box input::placeholder {
            color: var(--grey);
        }

        select.fsel {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(78,163,255,0.3);
            color: #fff;
            padding: 0.5rem 0.9rem;
            border-radius: 18px;
            font-size: 0.84rem;
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }

        select.fsel option {
            background: #0D1F3A;
        }

        .btn {
            padding: 0.5rem 1.1rem;
            border-radius: 18px;
            font-size: 0.84rem;
            font-family: inherit;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-pdf {
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
        }

        .btn-pdf:hover {
            background: rgba(231,76,60,0.3);
        }

        .btn-xls {
            background: rgba(39,174,96,0.14);
            color: var(--green);
            border: 1px solid rgba(39,174,96,0.35);
        }

        .btn-xls:hover {
            background: rgba(39,174,96,0.28);
        }

        /* ========== TABLE ========== */
        .tbl-wrap {
            background: rgba(255,255,255,0.04);
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(97,179,250,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(25,118,210,0.22);
        }

        thead th {
            padding: 0.85rem 1rem;
            text-align: left;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: var(--blue-light);
            font-weight: 600;
        }

        .th-s {
            cursor: pointer;
            user-select: none;
        }

        .th-s:hover {
            color: #fff;
        }

        .si {
            margin-left: 4px;
            opacity: 0.35;
            font-size: 0.65rem;
        }

        .si.on {
            opacity: 1;
        }

        tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.18s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(97,179,250,0.05);
        }

        tbody td {
            padding: 0.8rem 1rem;
            font-size: 0.87rem;
            vertical-align: middle;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            background: rgba(97,179,250,0.12);
            color: var(--blue-light);
            padding: 0.18rem 0.65rem;
            border-radius: 11px;
            font-size: 0.73rem;
            font-weight: 600;
            border: 1px solid rgba(97,179,250,0.25);
        }

        .badge-pass {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.75rem;
            border-radius: 20px;
            font-size: 0.73rem;
            font-weight: 600;
        }
        .badge-pass.actif  { background: rgba(39,174,96,0.15);  color: #27ae60; border: 1px solid rgba(39,174,96,0.3); }
        .badge-pass.inactif{ background: rgba(231,76,60,0.12);  color: #e74c3c; border: 1px solid rgba(231,76,60,0.3); }

        .act-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            font-size: 0.78rem;
            text-decoration: none;
            transition: opacity .18s;
        }
        .act-btn:hover { opacity: .75; }
        .act-view  { background: rgba(52,152,219,0.15); color: #3498db; }
        .act-edit  { background: rgba(52,152,219,0.15); color: #3498db; }
        .act-ban   { background: rgba(231,76,60,0.12);  color: #e74c3c; }
        .act-unban { background: rgba(39,174,96,0.15);  color: #27ae60; }

        .dist-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,0.06);
            color: var(--grey);
            padding: 0.15rem 0.55rem;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        .dist-pill i {
            color: var(--blue-light);
            font-size: 0.65rem;
        }

        .abtns {
            display: flex;
            gap: 5px;
        }

        .abtn {
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            transition: all 0.22s;
        }

        .abtn-del {
            background: rgba(231,76,60,0.18);
            color: #e74c3c;
        }

        .abtn-del:hover {
            background: rgba(231,76,60,0.35);
            transform: scale(1.1);
        }

        .empty {
            text-align: center;
            padding: 2.5rem;
            color: var(--grey);
        }

        .empty i {
            font-size: 2.2rem;
            color: rgba(97,179,250,0.22);
            margin-bottom: 0.7rem;
            display: block;
        }

        /* ========== PAGINATION ========== */
        .pag {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 5px;
            padding: 0.9rem 1rem;
            border-top: 1px solid rgba(97,179,250,0.1);
            flex-wrap: wrap;
        }

        .pag-btn {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: 1px solid rgba(97,179,250,0.2);
            background: transparent;
            color: var(--grey);
            cursor: pointer;
            font-size: 0.78rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .pag-btn:hover,
        .pag-btn.on {
            background: rgba(25,118,210,0.22);
            color: var(--blue-light);
            border-color: var(--blue-light);
        }

        .pag-info {
            font-size: 0.78rem;
            color: var(--grey);
            margin-right: 4px;
        }

        .section-divider {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
            margin-top: 0.4rem;
        }

        .section-divider span {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--blue-light);
            font-weight: 600;
            white-space: nowrap;
        }

        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(97,179,250,0.18);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }
            .main-wrapper {
                margin-left: 0;
            }
            .stats {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
<div class="wrap">

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="admin.php" class="logo">
                <img src="../../assets/images/photo.png" alt="EcoRide Logo" class="logo-img" onerror="this.style.display='none'">
                <div class="logo-text">EcoRide</div>
                <div class="logo-tagline">ADMINISTRATION</div>
            </a>
        </div>

        <div class="nav-section">GESTION</div>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php" class="nav-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="admin_trajet.php?page=passagers" class="nav-btn <?= $initialPage === 'passagers' ? 'active' : '' ?>" data-page="passagers"><i class="fas fa-users"></i> Passagers</a></li>
                <li><a href="admin_trajet.php?page=trajets" class="nav-btn <?= $initialPage === 'trajets' ? 'active' : '' ?>" data-page="trajets"><i class="fas fa-route"></i> Trajets</a></li>
                <li><a href="admin_trajet.php?page=destinations" class="nav-btn <?= $initialPage === 'destinations' ? 'active' : '' ?>" data-page="destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
                <li><a href="dashboard_event.php" class="nav-btn"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a class="nav-btn" href="admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
                <li><a class="nav-btn" href="admin.php"><i class="fas fa-car"></i> Véhicules</a></li>
                <li><a class="nav-btn" href="lostfound_admin.php"><i class="fas fa-search-location"></i> Objets perdus</a></li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <!-- ========== MAIN WRAPPER ========== -->
    <div class="main-wrapper">

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main">

        <!-- ========== HEADER ========== -->
        <div class="admin-header">
            <div class="admin-logo">
                <div class="logo-eco">
                    <span>ECO</span> <span>RIDE</span>
                </div>
                <div class="logo-tagline">Covoiturage Intelligent</div>
            </div>
            <div class="admin-nav">
                <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>

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

                <button class="theme-btn" id="themeToggle" title="Changer le thème">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>

            <!-- ========== PAGE DASHBOARD ========== -->
            <div id="page-dashboard" class="page-content" style="display:<?= $initialPage === 'dashboard' ? 'block' : 'none' ?>;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-route"></i><div class="num" id="totalTripsDash">0</div><div class="lbl">Trajets</div></div>
                    <div class="stat"><i class="fas fa-map-pin"></i><div class="num" id="totalDestDash">0</div><div class="lbl">Destinations</div></div>
                    <div class="stat"><i class="fas fa-car"></i><div class="num">0</div><div class="lbl">Véhicules</div></div>
                    <div class="stat"><i class="fas fa-users"></i><div class="num">0</div><div class="lbl">Passagers</div></div>
                </div>
                <div class="section-divider"><span>Tableau de bord</span></div>
                <div class="empty"><i class="fas fa-chart-line"></i><p>Dashboard en construction</p></div>
            </div>

            <!-- ========== PAGE PASSAGERS ========== -->
            <div id="page-passagers" class="page-content" style="display:<?= $initialPage === 'passagers' ? 'block' : 'none' ?>;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-users"></i><div class="num"><?= count($passagers) ?></div><div class="lbl">Total utilisateurs</div></div>
                    <div class="stat"><i class="fas fa-user-check"></i><div class="num"><?= count(array_filter($passagers, fn($u) => $u['statut'] === 'actif')) ?></div><div class="lbl">Actifs</div></div>
                    <div class="stat"><i class="fas fa-user-times"></i><div class="num"><?= count(array_filter($passagers, fn($u) => $u['statut'] === 'inactif')) ?></div><div class="lbl">Inactifs</div></div>
                    <div class="stat"><i class="fas fa-car"></i><div class="num"><?= count(array_filter($passagers, fn($u) => $u['role'] === 'conducteur')) ?></div><div class="lbl">Conducteurs</div></div>
                </div>
                <div class="section-divider"><span>Liste des Utilisateurs</span></div>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="passSearch" oninput="filterPassagers(this.value)" placeholder="Rechercher utilisateur..."></div>
                    </div>
                    <div class="toolbar-right">
                        <button class="btn btn-pdf" onclick="exportPassagersPDF()"><i class="fas fa-file-pdf"></i> Exporter PDF</button>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Statut</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="passagersBody">
                        <?php foreach ($passagers as $u): ?>
                            <?php if (($u['role'] ?? '') !== 'passager') continue; ?>
                            <tr>
                                <td><?= (int)$u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
                                <td>
                                    <?php if ($u['statut'] === 'actif'): ?>
                                        <span class="badge-pass actif">Actif</span>
                                    <?php else: ?>
                                        <span class="badge-pass inactif">Banni</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $u['created_at'] ? htmlspecialchars(date('d/m/Y', strtotime($u['created_at']))) : '—' ?></td>
                                <td>
                                    <div style="display:flex;gap:6px;align-items:center">
                                        <a href="../../Controller/AdminController.php?action=showPassagerDetailsPage&id=<?= (int)$u['id'] ?>" class="act-btn act-view" title="Voir les détails"><i class="fas fa-eye"></i></a>
                                        <a href="../../Controller/AdminController.php?action=showEditPassager&id=<?= (int)$u['id'] ?>" class="act-btn act-edit" title="Modifier"><i class="fas fa-pen"></i></a>
                                        <?php if ($u['statut'] === 'actif'): ?>
                                        <a href="../../Controller/AdminController.php?action=banPassager&id=<?= (int)$u['id'] ?>" class="act-btn act-ban" title="Bannir" onclick="return confirm('Bannir <?= addslashes(htmlspecialchars($u['prenom'] . ' ' . $u['nom'])) ?> ?')"><i class="fas fa-ban"></i></a>
                                        <?php else: ?>
                                        <a href="../../Controller/AdminController.php?action=unbanPassager&id=<?= (int)$u['id'] ?>" class="act-btn act-unban" title="Réactiver"><i class="fas fa-check-circle"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($passagers)): ?>
                            <tr class="empty-row"><td colspan="7" class="empty"><i class="fas fa-users"></i> Aucun passager</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="pag" id="passagersPag"></div>
                </div>
            </div>

            <!-- ========== PAGE TRAJETS ========== -->
            <div id="page-trajets" class="page-content" style="display:<?= $initialPage === 'trajets' ? 'block' : 'none' ?>;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-route"></i><div class="num" id="totalTrips">0</div><div class="lbl">Trajets enregistrés</div></div>
                    <div class="stat"><i class="fas fa-coins"></i><div class="num" id="avgPrice">0</div><div class="lbl">Prix moyen (DT)</div></div>
                    <div class="stat"><i class="fas fa-road"></i><div class="num" id="avgDist">0</div><div class="lbl">Dist. moyenne (km)</div></div>
                </div>
                <div class="section-divider"><span>Liste des Trajets</span></div>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="tripSearch" placeholder="Rechercher trajet..."></div>
                        <select class="fsel" id="tripSortSel">
                            <option value="">Trier par...</option>
                            <option value="id_asc">ID ↑</option><option value="id_desc">ID ↓</option>
                            <option value="dep_asc">Départ A→Z</option><option value="arr_asc">Arrivée A→Z</option>
                            <option value="prix_asc">Prix ↑</option><option value="prix_desc">Prix ↓</option>
                            <option value="dist_asc">Distance ↑</option><option value="dist_desc">Distance ↓</option>
                        </select>
                    </div>
                    <div class="toolbar-right">
                        <button class="btn btn-pdf" onclick="exportTripsPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                        <button class="btn btn-xls" onclick="exportTripsExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="th-s" onclick="sortCol('trips','id_T')">ID <i class="fas fa-sort si" id="ts-id_T"></i></th>
                                <th class="th-s" onclick="sortCol('trips','point_depart')">Départ <i class="fas fa-sort si" id="ts-point_depart"></i></th>
                                <th class="th-s" onclick="sortCol('trips','point_arrive')">Arrivée <i class="fas fa-sort si" id="ts-point_arrive"></i></th>
                                <th class="th-s" onclick="sortCol('trips','prix_total')">Prix (DT) <i class="fas fa-sort si" id="ts-prix_total"></i></th>
                                <th class="th-s" onclick="sortCol('trips','distance_total')">Distance <i class="fas fa-sort si" id="ts-distance_total"></i></th>
                                <th class="th-s" onclick="sortCol('trips','nb_arrets')">Arrêts <i class="fas fa-sort si" id="ts-nb_arrets"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tripsBody"></tbody>
                    </table>
                    <div class="pag" id="tripsPag"></div>
                </div>
            </div>

            <!-- ========== PAGE DESTINATIONS ========== -->
            <div id="page-destinations" class="page-content" style="display:<?= $initialPage === 'destinations' ? 'block' : 'none' ?>;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-map-pin"></i><div class="num" id="totalDest">0</div><div class="lbl">Destinations</div></div>
                    <div class="stat"><i class="fas fa-road"></i><div class="num" id="avgDestDist">0</div><div class="lbl">Dist. moyenne (km)</div></div>
                    <div class="stat"><i class="fas fa-list-ol"></i><div class="num" id="maxOrdre">0</div><div class="lbl">Ordre max</div></div>
                </div>
                <div class="section-divider"><span>Liste des Destinations</span></div>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="destSearch" placeholder="Rechercher destination..."></div>
                    </div>
                    <div class="toolbar-right">
                        <button class="btn btn-pdf" onclick="exportDestPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                        <button class="btn btn-xls" onclick="exportDestExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="th-s" onclick="sortCol('dest','id_des')">ID Dest. <i class="fas fa-sort si" id="ds-id_des"></i></th>
                                <th class="th-s" onclick="sortCol('dest','trajet_id')">ID Trajet <i class="fas fa-sort si" id="ds-trajet_id"></i></th>
                                <th class="th-s" onclick="sortCol('dest','point_arrive')">Point d'arrivée <i class="fas fa-sort si" id="ds-point_arrive"></i></th>
                                <th class="th-s" onclick="sortCol('dest','nom')">Destination <i class="fas fa-sort si" id="ds-nom"></i></th>
                                <th class="th-s" onclick="sortCol('dest','distance')">Distance <i class="fas fa-sort si" id="ds-distance"></i></th>
                                <th class="th-s" onclick="sortCol('dest','ordre')">Ordre <i class="fas fa-sort si" id="ds-ordre"></i></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="destBody"></tbody>
                    </table>
                    <div class="pag" id="destPag"></div>
                </div>
            </div>

            <!-- ========== PAGE ÉVÉNEMENTS ========== -->
            <div id="page-evenements" class="page-content" style="display:<?= $initialPage === 'evenements' ? 'block' : 'none' ?>;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-calendar-alt"></i><div class="num"><?= count($evenements) ?></div><div class="lbl">Total événements</div></div>
                    <div class="stat"><i class="fas fa-calendar-check"></i><div class="num"><?= count(array_filter($evenements, fn($e) => $e['statut'] === 'actif')) ?></div><div class="lbl">Actifs</div></div>
                    <div class="stat"><i class="fas fa-calendar-times"></i><div class="num"><?= count(array_filter($evenements, fn($e) => $e['statut'] === 'annulé')) ?></div><div class="lbl">Annulés</div></div>
                    <div class="stat"><i class="fas fa-users"></i><div class="num"><?= array_sum(array_column($evenements, 'nb_places')) ?></div><div class="lbl">Places totales</div></div>
                </div>
                <div class="section-divider"><span>Liste des Événements</span></div>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box"><i class="fas fa-search"></i><input type="text" id="eventSearch" oninput="filterEvenements(this.value)" placeholder="Rechercher événement..."></div>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Ville</th>
                                <th>Date</th>
                                <th>Places</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody id="evenementsBody">
                        <?php foreach ($evenements as $e): ?>
                            <tr>
                                <td><?= $e['id'] ?></td>
                                <td><?= htmlspecialchars($e['titre']) ?></td>
                                <td><span class="chip"><?= htmlspecialchars($e['type'] ?? '—') ?></span></td>
                                <td><?= htmlspecialchars($e['ville'] ?? '—') ?></td>
                                <td><?= $e['date_evenement'] ? date('d/m/Y', strtotime($e['date_evenement'])) : '—' ?></td>
                                <td><?= (int)$e['nb_places'] ?></td>
                                <td>
                                    <?php if ($e['statut'] === 'actif'): ?>
                                        <span style="color:#27ae60;font-weight:600"><i class="fas fa-circle" style="font-size:.55rem"></i> Actif</span>
                                    <?php elseif ($e['statut'] === 'annulé'): ?>
                                        <span style="color:#e74c3c;font-weight:600"><i class="fas fa-circle" style="font-size:.55rem"></i> Annulé</span>
                                    <?php else: ?>
                                        <span style="color:#f39c12;font-weight:600"><i class="fas fa-circle" style="font-size:.55rem"></i> <?= htmlspecialchars($e['statut']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($evenements)): ?>
                            <tr><td colspan="7" class="empty"><i class="fas fa-calendar-alt"></i> Aucun événement</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========== PAGE RÉCLAMATIONS ========== -->
            <div id="page-reclamations" class="page-content" style="display:<?= $initialPage === 'reclamations' ? 'block' : 'none' ?>;">
                <div class="section-divider"><span>Gestion des Réclamations</span></div>
                <div class="empty"><i class="fas fa-exclamation-triangle"></i><p>Module des réclamations</p></div>
            </div>

            <!-- ========== PAGE VÉHICULES ========== -->
            <div id="page-vehicules" class="page-content" style="display:none;">
                <div class="section-divider"><span>Gestion des Véhicules</span></div>
                <div class="empty"><i class="fas fa-car"></i><p>Module des véhicules</p></div>
            </div>

            <!-- ========== PAGE LOST & FOUND ========== -->
            <div id="page-lostfound" class="page-content" style="display:<?= $initialPage === 'lostfound' ? 'block' : 'none' ?>;">
                <div class="section-divider"><span>Lost & Found</span></div>
                <div class="empty"><i class="fas fa-search-location"></i><p>Module des objets perdus</p></div>
            </div>

        </main>
    </div>
</div>

<script>
/* ========== PAGINATION ET FILTRES PASSAGERS ========== */
let pPage = 1;
const pLimit = 5;
let pQuery = '';

function renderPassagersPag() {
    const rows = Array.from(document.querySelectorAll('#passagersBody tr:not(.empty-row)'));
    if (rows.length === 0) return;
    
    const filteredRows = rows.filter(row => row.textContent.toLowerCase().includes(pQuery));
    rows.forEach(row => row.style.display = 'none');
    
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / pLimit));
    if (pPage > totalPages) pPage = 1;
    
    const start = (pPage - 1) * pLimit;
    const end = start + pLimit;
    filteredRows.slice(start, end).forEach(row => row.style.display = '');
    
    const pagEl = document.getElementById('passagersPag');
    if (!pagEl) return;
    
    if (total <= pLimit) {
        pagEl.innerHTML = '';
        return;
    }
    
    let html = '<span class="pag-info">Page ' + pPage + ' / ' + totalPages + '</span>';
    if (pPage > 1) html += '<button class="pag-btn" onclick="goToPassagersPage(' + (pPage - 1) + ')"><i class="fas fa-chevron-left"></i></button>';
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || Math.abs(i - pPage) <= 1) {
            html += '<button class="pag-btn' + (i === pPage ? ' on' : '') + '" onclick="goToPassagersPage(' + i + ')">' + i + '</button>';
        } else if (Math.abs(i - pPage) === 2) {
            html += '<span class="pag-info">…</span>';
        }
    }
    if (pPage < totalPages) html += '<button class="pag-btn" onclick="goToPassagersPage(' + (pPage + 1) + ')"><i class="fas fa-chevron-right"></i></button>';
    pagEl.innerHTML = html;
}

function filterPassagers(q) {
    pQuery = q.toLowerCase();
    pPage = 1;
    renderPassagersPag();
}

function goToPassagersPage(page) {
    pPage = page;
    renderPassagersPag();
}

document.addEventListener('DOMContentLoaded', function() {
    renderPassagersPag();
});

/* ========== EXPORT PDF PASSAGERS ========== */
function exportPassagersPDF() {
    var { jsPDF } = window.jspdf;
    if (!jsPDF) {
        alert("La bibliothèque jsPDF n'est pas chargée.");
        return;
    }
    var doc = new jsPDF('landscape');
    doc.setFont('helvetica', 'bold'); doc.setFontSize(15);
    doc.text('EcoRide — Liste des Passagers', 14, 16);
    doc.setFontSize(9); doc.setFont('helvetica', 'normal');
    doc.text(new Date().toLocaleDateString('fr-FR'), 14, 23);
    
    // Récupérer les lignes actuelles (en excluant la ligne vide)
    var rows = Array.from(document.querySelectorAll('#passagersBody tr:not(.empty-row)'));
    var bodyData = rows.map(function(row) {
        var cells = row.querySelectorAll('td');
        if (cells.length >= 6) {
            return [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim()
            ];
        }
        return [];
    }).filter(row => row.length > 0);

    doc.autoTable({
        startY: 28,
        head: [['ID', 'Nom complet', 'Email', 'Téléphone', 'Statut', "Date d'inscription"]],
        body: bodyData,
        styles: { fontSize: 9 },
        headStyles: { fillColor: [0, 119, 182] },
        alternateRowStyles: { fillColor: [245, 245, 245] }
    });
    doc.save('ecoride_passagers.pdf');
    if (typeof window.toast === 'function') {
        window.toast('PDF exporté', true);
    } else {
        alert("PDF exporté avec succès !");
    }
}

/* ========== FILTRES ÉVÉNEMENTS ========== */
function filterEvenements(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#evenementsBody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

/* ========== THÈME ========== */
const themeToggle = document.getElementById('themeToggle');
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
}
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    themeToggle.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
});

/* ========== NAVIGATION SIDEBAR ========== */
document.querySelectorAll('.nav-btn[data-page]').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.nav-btn[data-page]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const page = this.dataset.page;
        document.querySelectorAll('.page-content').forEach(p => p.style.display = 'none');
        const target = document.getElementById('page-' + page);
        if (target) target.style.display = 'block';
    });
});

/* ========== STATS DESTINATIONS ========== */
function updateDestStats() {
    var rows = document.querySelectorAll('#destBody tr');
    if (!rows.length) return;
    var sumDist = 0, maxOrd = 0, count = 0;
    rows.forEach(function (row) {
        var cells = row.querySelectorAll('td');
        var dist = parseFloat(cells[4] && cells[4].textContent.trim()) || 0;
        var ordre = parseInt(cells[5] && cells[5].textContent.trim()) || 0;
        sumDist += dist;
        if (ordre > maxOrd) maxOrd = ordre;
        count++;
    });
    var el1 = document.getElementById('avgDestDist');
    var el2 = document.getElementById('maxOrdre');
    if (el1) el1.textContent = count ? (sumDist / count).toFixed(1) : 0;
    if (el2) el2.textContent = maxOrd;
}

/* ========== OBSERVERS ========== */
var destObserver = new MutationObserver(function () {
    const destPage = document.getElementById('page-destinations');
    if (destPage && destPage.style.display !== 'none') updateDestStats();
});
document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('destBody');
    if (tbody) destObserver.observe(tbody, { childList: true, subtree: true });
});

/* ========== FILTRE DES TOASTS ERREURS ========== */
(function () {
    var orig = window.toast;
    window.toast = function (msg, ok) {
        if (!msg) return;
        if (msg.includes('chargement') || msg.includes('Erreur')) {
            console.log('[Toast ignoré]', msg);
            return;
        }
        if (orig) orig(msg, ok);
    };
})();
</script>

<script src="../../assets/js/admin.js"></script>

</body>
</html>