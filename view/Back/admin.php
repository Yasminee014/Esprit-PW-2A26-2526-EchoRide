<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Administration</title>
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
            --sidebar-w: 260px;
        }

        body {
            font-family:'Poppins','Segoe UI',sans-serif;
            background:linear-gradient(135deg,var(--dark) 0%,var(--dark2) 100%);
            color:#fff; min-height:100vh;
        }
        body.light-mode {
            background:#eef2f7; color:#1a2a3a;
        }
        body.light-mode .sidebar {
            background:linear-gradient(180deg,#1565C0 0%,#0D47A1 100%);
        }
        body.light-mode .admin-header {
            background:linear-gradient(90deg,#1565C0,#0D47A1,#0A2A47);
        }
        body.light-mode .stat {
            background:#fff; border-color:#dde4ef;
        }
        body.light-mode .tbl-wrap {
            background:#fff; border-color:#dde4ef;
        }
        body.light-mode thead {
            background:rgba(25,118,210,.12);
        }
        body.light-mode tbody td { color:#1a2a3a; }
        body.light-mode tbody tr:hover { background:rgba(25,118,210,.05); }
        body.light-mode .search-box input,
        body.light-mode select.fsel {
            background:#f0f4f8; color:#1a2a3a; border-color:#c0cfe0;
        }

        /* ══════════════════════════════════════
           LAYOUT
        ══════════════════════════════════════ */
        .wrap {
            display:flex;
            min-height:100vh;
        }

        /* ══════════════════════════════════════
           SIDEBAR — exactement comme la photo
        ══════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #1976D2 0%, #0D3B7A 60%, #0A2A5A 100%);
            padding: 0;
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 4px 0 24px rgba(0,0,0,.45);
            z-index: 200;
            display: flex;
            flex-direction: column;
        }

        /* Zone logo en haut de la sidebar */
        .sidebar-logo-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem 1.4rem;
            border-bottom: 1px solid rgba(255,255,255,.18);
            margin-bottom: .5rem;
        }
        .sidebar-leaf-icon {
            width: 68px; height: 68px;
            background: rgba(255,255,255,.12);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; color: #fff;
            margin-bottom: .8rem;
            border: 2px solid rgba(255,255,255,.25);
            transition: transform .3s, background .3s;
        }
        .sidebar-leaf-icon:hover {
            transform: scale(1.06);
            background: rgba(255,255,255,.2);
        }
        .sidebar-brand {
            font-size: 1.1rem; font-weight: 700;
            color: #fff; letter-spacing: .5px;
            margin-bottom: .1rem;
        }
        .sidebar-sub {
            font-size: .68rem; color: rgba(255,255,255,.65);
            letter-spacing: .5px;
        }

        /* Nav dans la sidebar */
        .nav-section {
            color: rgba(255,255,255,.5);
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: .8rem 1.2rem .3rem;
            font-weight: 600;
        }
        .sidebar nav ul { list-style: none; padding: 0 .6rem; }
        .sidebar nav ul li { margin-bottom: .15rem; }
        .sidebar nav ul li a,
        .sidebar nav ul li .nav-btn {
            display: flex; align-items: center; gap: 11px;
            padding: .72rem 1rem;
            color: rgba(255,255,255,.85);
            text-decoration: none;
            border-radius: 10px;
            transition: all .22s;
            font-size: .88rem;
            cursor: pointer;
            background: none; border: none;
            width: 100%; font-family: inherit;
        }
        .sidebar nav ul li a i,
        .sidebar nav ul li .nav-btn i {
            width: 18px; font-size: .9rem;
            color: rgba(255,255,255,.7);
            flex-shrink: 0;
        }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li .nav-btn:hover,
        .sidebar nav ul li a.active,
        .sidebar nav ul li .nav-btn.active {
            background: rgba(255,255,255,.18);
            border-left: 3px solid rgba(255,255,255,.9);
            color: #fff;
        }
        .sidebar nav ul li a:hover i,
        .sidebar nav ul li .nav-btn:hover i,
        .sidebar nav ul li a.active i,
        .sidebar nav ul li .nav-btn.active i { color: #fff; }

        .sidebar-sep {
            border: none;
            border-top: 1px solid rgba(255,255,255,.15);
            margin: .6rem .8rem;
        }

        /* ══════════════════════════════════════
           MAIN CONTENT (droite de la sidebar)
        ══════════════════════════════════════ */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-w);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ══════════════════════════════════════
           ADMIN HEADER — comme la photo
        ══════════════════════════════════════ */
        .admin-header {
            background: linear-gradient(90deg, #071C2F 0%, #0A2A47 50%, #0D355B 100%);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,.08);
            flex-wrap: wrap;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 16px rgba(0,0,0,.35);
        }

        /* Logo ECO RIDE dans le header */
        .header-logo {
            display: flex;
            flex-direction: column;
            text-decoration: none;
        }
        .header-logo .logo-eco {
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.1;
        }
        .header-logo .logo-eco .eco  { color: #4EA3FF; }
        .header-logo .logo-eco .ride { color: #6BB8FF; }
        .header-logo .logo-tagline {
            font-size: .63rem;
            color: #A8C1D9;
            letter-spacing: .5px;
            margin-top: 2px;
        }

        /* Nav links dans le header */
        .admin-nav {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .admin-nav .nav-link {
            text-decoration: none;
            padding: .5rem 1.2rem;
            border-radius: 30px;
            font-size: .9rem;
            font-weight: 500;
            transition: all .3s;
            background: transparent;
            color: rgba(255,255,255,.85);
        }
        .admin-nav .nav-link:hover {
            background: rgba(255,255,255,.12);
            color: #fff;
        }

        /* Bouton Profil */
        .profile-btn {
            display: flex; align-items: center; gap: 9px;
            background: #2F6FA5;
            border: none; padding: .48rem 1.1rem;
            border-radius: 30px; cursor: pointer;
            transition: all .3s; color: #fff;
            font-size: .88rem; font-weight: 500;
            text-decoration: none;
        }
        .profile-btn:hover {
            background: #3C82C4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(60,130,196,.35);
        }
        .profile-avatar {
            width: 27px; height: 27px;
            background: #5FA8E0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .profile-avatar i { font-size: .78rem; color: #fff; }

        /* Badge Admin rouge */
        .admin-badge {
            background: rgba(231,76,60,.2);
            border: 1px solid rgba(231,76,60,.45);
            color: #e74c3c;
            padding: .48rem 1.1rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: .88rem; font-weight: 500;
            transition: all .3s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .admin-badge:hover {
            background: rgba(231,76,60,.32);
            transform: translateY(-2px);
        }

        /* Bouton thème lune/soleil */
        .theme-btn {
            background: rgba(255,255,255,.12);
            border: none; width: 38px; height: 38px;
            border-radius: 50%; cursor: pointer;
            font-size: 1rem; transition: all .3s;
            display: flex; align-items: center; justify-content: center;
            color: white;
        }
        .theme-btn:hover {
            background: rgba(255,255,255,.25);
            transform: rotate(15deg);
        }

        /* ══════════════════════════════════════
           MAIN (contenu des pages)
        ══════════════════════════════════════ */
        .main {
            flex: 1;
            padding: 1.8rem;
        }

        /* Topbar interne */
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.6rem; padding-bottom: 1rem;
            border-bottom: 1px solid rgba(97,179,250,.2);
        }
        .topbar h1 { font-size: 1.4rem; display: flex; align-items: center; gap: 9px; }
        .topbar h1 i { color: var(--blue-light); }
        .pill {
            background: rgba(255,255,255,.08); border: 1px solid rgba(97,179,250,.3);
            color: #fff; padding: .38rem .9rem; border-radius: 20px; font-size: .78rem;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .pill-user {
            background: rgba(255,255,255,.08); border: 1px solid rgba(97,179,250,.3);
            color: #fff; padding: .38rem .9rem; border-radius: 20px; font-size: .78rem;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all .25s;
        }
        .pill-user:hover { background: rgba(25,118,210,.3); border-color:#61b3fa; color:#61b3fa; }

        /* ── STATS ── */
        .stats {
            display: grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr));
            gap: 1rem; margin-bottom: 1.4rem;
        }
        .stat {
            background: rgba(255,255,255,.07); border: 1px solid rgba(97,179,250,.16);
            border-radius: 14px; padding: 1.2rem; text-align: center; transition: all .3s;
        }
        .stat:hover { transform: translateY(-4px); border-color: var(--blue-light); box-shadow: 0 8px 22px rgba(25,118,210,.18); }
        .stat i { font-size: 1.8rem; color: var(--blue-light); margin-bottom: .35rem; display: block; }
        .stat .num {
            font-size: 2rem; font-weight: 700;
            background: linear-gradient(135deg,var(--blue-light),#fff);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .stat .lbl { color: var(--grey); font-size: .75rem; margin-top: .2rem; }

        /* ── TOOLBAR ── */
        .toolbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem; flex-wrap: wrap; gap: .7rem;
        }
        .toolbar-left { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
        .toolbar-right { display: flex; gap: .6rem; }

        .search-box { position: relative; }
        .search-box i { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--grey); font-size: .82rem; }
        .search-box input {
            background: rgba(255,255,255,.08); border: 1px solid rgba(97,179,250,.3);
            color: #fff; padding: .5rem .9rem .5rem 2.2rem; border-radius: 18px;
            font-size: .84rem; outline: none; transition: all .25s; font-family: inherit; width: 220px;
        }
        .search-box input::placeholder { color: var(--grey); }
        .search-box input:focus { border-color: var(--blue-light); background: rgba(97,179,250,.08); }

        select.fsel {
            background: rgba(255,255,255,.08); border: 1px solid rgba(97,179,250,.3);
            color: #fff; padding: .5rem .9rem; border-radius: 18px;
            font-size: .84rem; font-family: inherit; cursor: pointer; outline: none; transition: all .25s;
        }
        select.fsel option { background: #0D1F3A; }

        .btn {
            padding: .5rem 1.1rem; border-radius: 18px; font-size: .84rem;
            font-family: inherit; cursor: pointer; border: none;
            display: inline-flex; align-items: center; gap: 6px;
            transition: all .25s; font-weight: 500;
        }
        .btn-pdf { background: rgba(231,76,60,.14); color: var(--red); border: 1px solid rgba(231,76,60,.35); }
        .btn-pdf:hover { background: rgba(231,76,60,.28); transform: translateY(-1px); }
        .btn-xls { background: rgba(39,174,96,.14); color: var(--green); border: 1px solid rgba(39,174,96,.35); }
        .btn-xls:hover { background: rgba(39,174,96,.28); transform: translateY(-1px); }

        /* ── TABLE ── */
        .tbl-wrap {
            background: rgba(255,255,255,.04); border-radius: 14px;
            overflow: hidden; border: 1px solid rgba(97,179,250,.1);
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(25,118,210,.22); }
        thead th {
            padding: .85rem 1rem; text-align: left; font-size: .76rem;
            text-transform: uppercase; letter-spacing: .7px;
            color: var(--blue-light); font-weight: 600;
        }
        .th-s { cursor: pointer; user-select: none; }
        .th-s:hover { color: #fff; }
        .si { margin-left: 4px; opacity: .35; font-size: .65rem; }
        .si.on { opacity: 1; }
        tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .18s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(97,179,250,.05); }
        tbody td { padding: .8rem 1rem; font-size: .87rem; vertical-align: middle; }

        .chip {
            display: inline-flex; align-items: center;
            background: rgba(97,179,250,.12); color: var(--blue-light);
            padding: .18rem .65rem; border-radius: 11px; font-size: .73rem; font-weight: 600;
            border: 1px solid rgba(97,179,250,.25);
        }
        .dist-pill {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(255,255,255,.06); color: var(--grey);
            padding: .15rem .55rem; border-radius: 20px; font-size: .75rem;
        }
        .dist-pill i { color: var(--blue-light); font-size: .65rem; }

        .abtns { display: flex; gap: 5px; }
        .abtn {
            width: 30px; height: 30px; border: none; border-radius: 7px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: .82rem; transition: all .22s;
        }
        .abtn-del { background: rgba(231,76,60,.18); color: #e74c3c; }
        .abtn-del:hover { background: rgba(231,76,60,.35); transform: scale(1.1); }

        .empty { text-align: center; padding: 2.5rem; color: var(--grey); }
        .empty i { font-size: 2.2rem; color: rgba(97,179,250,.22); margin-bottom: .7rem; display: block; }

        /* ── PAGINATION ── */
        .pag {
            display: flex; justify-content: flex-end; align-items: center;
            gap: 5px; padding: .9rem 1rem; border-top: 1px solid rgba(97,179,250,.1);
            flex-wrap: wrap;
        }
        .pag-btn {
            width: 30px; height: 30px; border-radius: 7px;
            border: 1px solid rgba(97,179,250,.2); background: transparent;
            color: var(--grey); cursor: pointer; font-size: .78rem;
            display: flex; align-items: center; justify-content: center; transition: all .2s;
        }
        .pag-btn:hover, .pag-btn.on { background: rgba(25,118,210,.22); color: var(--blue-light); border-color: var(--blue-light); }
        .pag-info { font-size: .78rem; color: var(--grey); margin-right: 4px; }

        /* ── TOAST ── */
        .toast {
            position: fixed; bottom: 24px; right: 24px; padding: .8rem 1.2rem; border-radius: 10px;
            z-index: 2000; font-size: .85rem; display: flex; align-items: center; gap: 8px;
            animation: tIn .3s ease, tOut .3s 2.8s ease forwards;
            border: 1px solid; backdrop-filter: blur(10px);
        }
        .t-ok  { background: rgba(39,174,96,.15);  color: var(--green); border-color: rgba(39,174,96,.3); }
        .t-err { background: rgba(231,76,60,.15);  color: var(--red);   border-color: rgba(231,76,60,.3); }
        @keyframes tIn  { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }
        @keyframes tOut { to{opacity:0;transform:translateY(8px);} }

        .section-divider {
            display: flex; align-items: center; gap: .8rem;
            margin-bottom: 1.2rem; margin-top: .4rem;
        }
        .section-divider span {
            font-size: .72rem; text-transform: uppercase; letter-spacing: 1.5px;
            color: var(--blue-light); font-weight: 600; white-space: nowrap;
        }
        .section-divider::before, .section-divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(97,179,250,.18);
        }

        /* ── RESPONSIVE ── */
        @media(max-width:900px){
            .sidebar { display: none; }
            .main-wrapper { margin-left: 0; }
            .stats { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">

    <!-- ══════════════════════════════════════
         SIDEBAR — logo feuille + EcoRide + Administration
    ══════════════════════════════════════ -->
    <aside class="sidebar">

        <!-- Logo zone -->
        <div class="sidebar-logo-zone">
            <div class="sidebar-leaf-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <div class="sidebar-brand">EcoRide</div>
            <div class="sidebar-sub">Administration</div>
        </div>

        <nav>
            <div class="nav-section">Gestion</div>
            <ul>
                <li>
                    <button class="nav-btn active" data-page="trips">
                        <i class="fas fa-route"></i> Trajets
                    </button>
                </li>
                <li>
                    <button class="nav-btn" data-page="destinations">
                        <i class="fas fa-map-pin"></i> Destinations
                    </button>
                </li>
            </ul>

            <hr class="sidebar-sep">

            <ul>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <!-- ══════════════════════════════════════
         MAIN WRAPPER (header + contenu)
    ══════════════════════════════════════ -->
    <div class="main-wrapper">

        <!-- ADMIN HEADER -->
        <header class="admin-header">
            <!-- Logo ECO RIDE -->
            <a href="index.php" class="header-logo">
                <div class="logo-eco">
                    <span class="eco">ECO </span><span class="ride">RIDE</span>
                </div>
                <div class="logo-tagline">Covoiturage Intelligent</div>
            </a>

            <!-- Nav -->
            <nav class="admin-nav">
                <a href="../FrontOffice/index.php" class="nav-link">Accueil</a>
                <a href="../FrontOffice/events.php" class="nav-link">Événements</a>
                <a href="../FrontOffice/sponsors.php" class="nav-link">Sponsors</a>

                <!-- Profil -->
                <a href="mon_profil.php" class="profile-btn">
                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
                    <span>Profil</span>
                </a>

                <!-- Admin badge rouge -->
                <a href="dashboard.php" class="admin-badge">
                    <i class="fas fa-shield-alt"></i> Admin
                </a>

                <!-- Thème -->
                <button class="theme-btn" id="themeToggle" title="Changer le thème">
                    <i class="fas fa-moon"></i>
                </button>
            </nav>
        </header>

        <!-- CONTENU -->
        <main class="main">

            <!-- Topbar interne -->
            <div class="topbar">
                <h1 id="pageTitle"><i class="fas fa-route"></i> Gestion des Trajets</h1>
                <div style="display:flex;gap:.6rem;align-items:center;">
                    <a href="../FrontOffice/user.html" class="pill-user"><i class="fas fa-user"></i> Espace utilisateur</a>
                    <span class="pill"><i class="fas fa-shield-alt"></i> Admin</span>
                </div>
            </div>

            <!-- ══ TRAJETS PAGE ══ -->
            <div id="tripsPage">
                <div class="stats">
                    <div class="stat"><i class="fas fa-route"></i><div class="num" id="totalTrips">0</div><div class="lbl">Trajets enregistrés</div></div>
                    <div class="stat"><i class="fas fa-coins"></i><div class="num" id="avgPrice">0</div><div class="lbl">Prix moyen (DT)</div></div>
                    <div class="stat"><i class="fas fa-road"></i><div class="num" id="avgDist">0</div><div class="lbl">Dist. moyenne (km)</div></div>
                </div>
                <div class="section-divider"><span>Liste des Trajets</span></div>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="tripSearch" placeholder="Rechercher trajet...">
                        </div>
                        <select class="fsel" id="tripSortSel">
                            <option value="">Trier par...</option>
                            <option value="id_asc">ID ↑</option>
                            <option value="id_desc">ID ↓</option>
                            <option value="dep_asc">Départ A→Z</option>
                            <option value="arr_asc">Arrivée A→Z</option>
                            <option value="prix_asc">Prix ↑</option>
                            <option value="prix_desc">Prix ↓</option>
                            <option value="dist_asc">Distance ↑</option>
                            <option value="dist_desc">Distance ↓</option>
                        </select>
                    </div>
                    <div class="toolbar-right">
                        <button class="btn btn-pdf" onclick="exportTripsPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                        <button class="btn btn-xls" onclick="exportTripsExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead><tr>
                            <th class="th-s" onclick="sortCol('trips','id_T')">ID <i class="fas fa-sort si" id="ts-id_T"></i></th>
                            <th class="th-s" onclick="sortCol('trips','point_depart')">Départ <i class="fas fa-sort si" id="ts-point_depart"></i></th>
                            <th class="th-s" onclick="sortCol('trips','point_arrive')">Arrivée <i class="fas fa-sort si" id="ts-point_arrive"></i></th>
                            <th class="th-s" onclick="sortCol('trips','prix_total')">Prix (DT) <i class="fas fa-sort si" id="ts-prix_total"></i></th>
                            <th class="th-s" onclick="sortCol('trips','distance_total')">Distance <i class="fas fa-sort si" id="ts-distance_total"></i></th>
                            <th class="th-s" onclick="sortCol('trips','nb_arrets')">Arrêts <i class="fas fa-sort si" id="ts-nb_arrets"></i></th>
                            <th>Actions</th>
                        </tr></thead>
                        <tbody id="tripsBody"></tbody>
                    </table>
                    <div class="pag" id="tripsPag"></div>
                </div>
            </div>

            <!-- ══ DESTINATIONS PAGE ══ -->
            <div id="destinationsPage" style="display:none;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-map-pin"></i><div class="num" id="totalDest">0</div><div class="lbl">Destinations</div></div>
                    <div class="stat"><i class="fas fa-road"></i><div class="num" id="avgDestDist">0</div><div class="lbl">Dist. moyenne (km)</div></div>
                    <div class="stat"><i class="fas fa-list-ol"></i><div class="num" id="maxOrdre">0</div><div class="lbl">Ordre max</div></div>
                </div>
                <div class="section-divider"><span>Liste des Destinations</span></div>
                <div class="toolbar">
                    <div class="toolbar-left">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="destSearch" placeholder="Rechercher destination...">
                        </div>
                    </div>
                    <div class="toolbar-right">
                        <button class="btn btn-pdf" onclick="exportDestPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                        <button class="btn btn-xls" onclick="exportDestExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                    </div>
                </div>
                <div class="tbl-wrap">
                    <table>
                        <thead><tr>
                            <th class="th-s" onclick="sortCol('dest','id_des')">ID Dest. <i class="fas fa-sort si" id="ds-id_des"></i></th>
                            <th class="th-s" onclick="sortCol('dest','trajet_id')">ID Trajet <i class="fas fa-sort si" id="ds-trajet_id"></i></th>
                            <th class="th-s" onclick="sortCol('dest','point_arrive')">Point d'arrivée <i class="fas fa-sort si" id="ds-point_arrive"></i></th>
                            <th class="th-s" onclick="sortCol('dest','nom')">Destination (nom) <i class="fas fa-sort si" id="ds-nom"></i></th>
                            <th class="th-s" onclick="sortCol('dest','distance')">Distance (km) <i class="fas fa-sort si" id="ds-distance"></i></th>
                            <th class="th-s" onclick="sortCol('dest','ordre')">Ordre <i class="fas fa-sort si" id="ds-ordre"></i></th>
                            <th>Actions</th>
                        </tr></thead>
                        <tbody id="destBody"></tbody>
                    </table>
                    <div class="pag" id="destPag"></div>
                </div>
            </div>

            <!-- ══ DASHBOARD PAGE ══ -->
            <div id="dashboardPage" style="display:none;">
                <div class="stats">
                    <div class="stat"><i class="fas fa-route"></i><div class="num" id="totalTrips2">—</div><div class="lbl">Trajets</div></div>
                    <div class="stat"><i class="fas fa-map-pin"></i><div class="num" id="totalDest2">—</div><div class="lbl">Destinations</div></div>
                </div>
            </div>

        </main>
    </div><!-- /.main-wrapper -->
</div><!-- /.wrap -->

<script>
/* ── Thème lune / soleil ── */
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

/* ── Navigation sidebar ── */
document.querySelectorAll('.nav-btn[data-page]').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.nav-btn[data-page]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        document.getElementById('tripsPage').style.display        = 'none';
        document.getElementById('destinationsPage').style.display = 'none';
        document.getElementById('dashboardPage').style.display    = 'none';

        const titleEl = document.getElementById('pageTitle');
        const page = this.dataset.page;

        if (page === 'trips') {
            document.getElementById('tripsPage').style.display = 'block';
            titleEl.innerHTML = '<i class="fas fa-route"></i> Gestion des Trajets';
        } else if (page === 'destinations') {
            document.getElementById('destinationsPage').style.display = 'block';
            titleEl.innerHTML = '<i class="fas fa-map-pin"></i> Gestion des Destinations';
            updateDestStats();
        } else if (page === 'dashboard') {
            document.getElementById('dashboardPage').style.display = 'block';
            titleEl.innerHTML = '<i class="fas fa-tachometer-alt"></i> Tableau de bord';
            syncDashStats();
        }
    });
});

/* ── Stats dashboard ── */
function syncDashStats() {
    var t2 = document.getElementById('totalTrips2');
    var d2 = document.getElementById('totalDest2');
    if (t2) t2.textContent = document.getElementById('totalTrips').textContent;
    if (d2) d2.textContent = document.getElementById('totalDest').textContent;
}

/* ── Stats destinations ── */
function updateDestStats() {
    var rows = document.querySelectorAll('#destBody tr');
    if (!rows.length) return;
    var sumDist = 0, maxOrd = 0, count = 0;
    rows.forEach(function (row) {
        var cells = row.querySelectorAll('td');
        var dist  = parseFloat(cells[4] && cells[4].textContent.trim()) || 0;
        var ordre = parseInt(cells[5]   && cells[5].textContent.trim()) || 0;
        sumDist += dist;
        if (ordre > maxOrd) maxOrd = ordre;
        count++;
    });
    var el1 = document.getElementById('avgDestDist');
    var el2 = document.getElementById('maxOrdre');
    if (el1) el1.textContent = count ? (sumDist / count).toFixed(1) : 0;
    if (el2) el2.textContent = maxOrd;
}

/* ── Observers ── */
var destObserver = new MutationObserver(function () {
    if (document.getElementById('destinationsPage').style.display !== 'none') updateDestStats();
});
document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('destBody');
    if (tbody) destObserver.observe(tbody, { childList: true, subtree: true });
});

var statsObserver = new MutationObserver(syncDashStats);
['totalTrips', 'totalDest'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) statsObserver.observe(el, { childList: true, characterData: true, subtree: true });
});

/* ── Supprimer toasts erreurs chargement ── */
(function () {
    var orig = window.toast;
    window.toast = function (msg, ok) {
        if (!msg) return;
        if (msg.includes('chargement') || msg.includes('Erreur')) { console.log('[Toast ignoré]', msg); return; }
        if (orig) orig(msg, ok);
    };
})();
</script>

<!-- admin.js — chemin inchangé -->
<script src="../../assets/admin.js"></script>

</body>
</html>