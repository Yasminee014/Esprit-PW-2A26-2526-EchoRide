<?php
$initialPage = $_GET['page'] ?? 'trajets';
$allowedPages = ['dashboard', 'passagers', 'trajets', 'destinations', 'evenements', 'reclamations', 'lostfound'];
if (!in_array($initialPage, $allowedPages, true)) {
    $initialPage = 'trajets';
}
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
            width: 28px;
            height: 28px;
            background: #5FA8FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar i {
            font-size: 0.8rem;
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
                <li><button class="nav-btn <?= $initialPage === 'dashboard' ? 'active' : '' ?>" data-page="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</button></li>
                <li><button class="nav-btn" data-page="passagers"><i class="fas fa-users"></i> Passagers</button></li>
                <li><button class="nav-btn <?= $initialPage === 'trajets' ? 'active' : '' ?>" data-page="trajets"><i class="fas fa-route"></i> Trajets</button></li>
                <li><button class="nav-btn <?= $initialPage === 'destinations' ? 'active' : '' ?>" data-page="destinations"><i class="fas fa-map-pin"></i> Destinations</button></li>
                <li><button class="nav-btn" data-page="evenements"><i class="fas fa-calendar-alt"></i> Événements</button></li>
                <li><a class="nav-btn" href="/ecoride/View/backoffice/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
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

                <a href="profil.php" class="profile-btn">
                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
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
                <div class="section-divider"><span>Gestion des Passagers</span></div>
                <div class="empty"><i class="fas fa-users"></i><p>Module des passagers</p></div>
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
                <div class="section-divider"><span>Gestion des Événements</span></div>
                <div class="empty"><i class="fas fa-calendar-alt"></i><p>Module des événements</p></div>
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