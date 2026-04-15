<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0A2F44; --bg2: #0D1F3A; --panel: #0b2840; --card: #0c2236;
            --cyan: #00B4D8; --ocean: #0077B6;
            --cyan-dim: rgba(0,180,216,0.10); --cyan-glo: rgba(0,180,216,0.22);
            --border: rgba(0,180,216,0.16); --bh: rgba(0,180,216,0.42);
            --text: #D9EEF5; --muted: #4E7A90;
            --green: #00D98B; --red: #FF4D6A; --yellow: #FFD166; --orange: #FF9A3C;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html,body { height:100%; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(145deg, var(--bg) 0%, var(--bg2) 100%);
            color: var(--text); min-height:100vh;
        }
        body::before {
            content:''; position:fixed; top:0; left:0; right:0; bottom:0; pointer-events:none;
            background:
                radial-gradient(ellipse 60% 40% at 10% 0%, rgba(0,180,216,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 40% 60% at 90% 100%, rgba(0,119,182,0.08) 0%, transparent 60%);
        }
        /* SIDEBAR */
        .sidebar {
            width: 250px; background: rgba(11,40,64,0.95);
            backdrop-filter: blur(14px);
            position: fixed; height: 100vh;
            padding: 1.5rem 0.8rem;
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column; z-index: 50;
        }
        .logo {
            display:flex; align-items:center; gap:10px;
            margin-bottom:2rem; padding:.8rem 1rem;
            background: linear-gradient(135deg, rgba(0,119,182,0.15), var(--cyan-dim));
            border-radius:12px; border:1px solid var(--border);
        }
        .logo-icon {
            width:36px; height:36px;
            background: linear-gradient(135deg, var(--cyan), var(--ocean));
            border-radius:9px; display:flex; align-items:center;
            justify-content:center; font-size:16px; color:#fff; flex-shrink:0;
        }
        .logo h2 { font-family:'Syne',sans-serif; font-size:1.1rem; letter-spacing:1px; }
        .logo h2 span { color:var(--cyan); }
        .nav-label {
            font-size:9px; color:var(--muted); text-transform:uppercase;
            letter-spacing:2px; padding:.4rem .8rem; margin-bottom:.2rem;
        }
        .nav-item {
            display:flex; align-items:center; gap:10px;
            padding:.7rem .9rem; color:var(--muted); border-radius:10px;
            margin-bottom:.2rem; transition:all .2s; cursor:pointer; font-size:.88rem;
        }
        .nav-item i { width:18px; font-size:.85rem; }
        .nav-item:hover { background:var(--cyan-dim); color:var(--text); }
        .nav-item.active { background:var(--cyan-dim); color:var(--cyan); border:1px solid var(--border); }
        .nav-item.active i { color:var(--cyan); }
        .sidebar-foot { margin-top:auto; padding-top:1rem; border-top:1px solid var(--border); }
        .sidebar-foot a { text-decoration:none; display:flex; }
        /* MAIN */
        .main { margin-left:250px; padding:1.8rem 2.5rem; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
        .page-title {
            font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:700;
            display:flex; align-items:center; gap:10px;
        }
        .page-title i { color:var(--cyan); }
        .back-btn {
            background:var(--cyan-dim); color:var(--cyan);
            padding:.5rem 1.1rem; border-radius:20px; text-decoration:none;
            display:flex; align-items:center; gap:7px;
            font-size:.83rem; border:1px solid var(--border); transition:all .2s;
        }
        .back-btn:hover { background:var(--cyan-glo); transform:translateX(-2px); }
        /* STATS */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.8rem; }
        .stat-card {
            background:rgba(12,34,54,0.85); border-radius:14px;
            padding:1.3rem; border:1px solid var(--border);
            transition:all .25s; position:relative; overflow:hidden; backdrop-filter:blur(8px);
        }
        .stat-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background:linear-gradient(90deg,var(--cyan),var(--ocean));
        }
        .stat-card:hover { border-color:var(--bh); transform:translateY(-2px); }
        .stat-icon {
            width:38px; height:38px; border-radius:9px;
            background:linear-gradient(135deg,rgba(0,119,182,0.15),var(--cyan-dim));
            display:flex; align-items:center; justify-content:center;
            margin-bottom:.9rem; font-size:.95rem; color:var(--cyan);
        }
        .stat-num { font-family:'Syne',sans-serif; font-size:2rem; font-weight:700; color:var(--cyan); line-height:1; }
        .stat-lbl { color:var(--muted); font-size:.78rem; margin-top:.35rem; }
        /* CHARTS */
        .charts-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.8rem; }
        .chart-card {
            background:rgba(12,34,54,0.85); backdrop-filter:blur(8px);
            border-radius:14px; padding:1.4rem; border:1px solid var(--border);
        }
        .chart-card h3 {
            font-family:'Syne',sans-serif; font-size:.88rem;
            color:var(--cyan); margin-bottom:1rem; display:flex; align-items:center; gap:7px;
        }
        /* ACTIONS BAR */
        .act-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:.7rem; }
        .act-left { display:flex; gap:.6rem; flex-wrap:wrap; align-items:center; }
        .act-right { display:flex; gap:.6rem; }
        /* SEARCH */
        .sbox { position:relative; }
        .sbox i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); font-size:.75rem; }
        .sbox input {
            padding:.55rem .9rem .55rem 2.2rem; border-radius:20px;
            border:1px solid var(--border); background:rgba(12,34,54,0.85);
            color:var(--text); font-family:'DM Sans',sans-serif; font-size:.82rem;
            width:200px; transition:border-color .2s;
        }
        .sbox input:focus { outline:none; border-color:var(--cyan); }
        select.fsel {
            padding:.55rem .9rem; border-radius:20px;
            background:rgba(12,34,54,0.85); color:var(--text);
            border:1px solid var(--border);
            font-family:'DM Sans',sans-serif; font-size:.82rem; cursor:pointer;
        }
        select.fsel:focus { outline:none; border-color:var(--cyan); }
        select.fsel option { background:#0A2F44; }
        /* EXPORT */
        .btn-exp {
            display:flex; align-items:center; gap:5px;
            padding:.55rem .9rem; border-radius:9px; border:1px solid; cursor:pointer;
            font-family:'DM Sans',sans-serif; font-size:.8rem; transition:all .2s; font-weight:500;
        }
        .btn-pdf { background:rgba(255,77,106,0.1); color:var(--red); border-color:rgba(255,77,106,0.25); }
        .btn-pdf:hover { background:rgba(255,77,106,0.2); }
        .btn-xls { background:rgba(0,217,139,0.1); color:var(--green); border-color:rgba(0,217,139,0.25); }
        .btn-xls:hover { background:rgba(0,217,139,0.2); }
        /* TABLE */
        .twrap {
            background:rgba(12,34,54,0.85); backdrop-filter:blur(8px);
            border-radius:16px; border:1px solid var(--border); overflow:hidden;
        }
        .twrap table { width:100%; border-collapse:collapse; }
        .twrap thead th {
            background:rgba(0,180,216,0.07); color:var(--cyan); font-size:.78rem;
            padding:1rem 1.4rem; text-align:left; font-weight:600; letter-spacing:.4px; white-space:nowrap;
        }
        .th-s { cursor:pointer; user-select:none; }
        .th-s:hover { color:#fff; }
        .si { margin-left:4px; opacity:.35; font-size:.65rem; }
        .si.on { opacity:1; }
        .twrap tbody td {
            padding:1rem 1.4rem; font-size:.85rem;
            border-bottom:1px solid rgba(255,255,255,0.035); vertical-align:middle;
        }
        .twrap tbody tr:last-child td { border-bottom:none; }
        .twrap tbody tr:hover td { background:rgba(0,180,216,0.04); }
        /* CHIPS */
        .chip {
            display:inline-flex; align-items:center; background:var(--cyan-dim); color:var(--cyan);
            padding:.18rem .55rem; border-radius:5px; font-size:.74rem; font-weight:700; border:1px solid var(--border);
        }
        .chip-green { background:rgba(0,217,139,0.1); color:var(--green); border-color:rgba(0,217,139,0.2); }
        .dist-pill {
            display:inline-flex; align-items:center; gap:4px;
            background:rgba(0,180,216,0.06); color:var(--muted);
            padding:.15rem .55rem; border-radius:20px; font-size:.75rem;
        }
        .dist-pill i { color:var(--cyan); font-size:.65rem; }
        /* ACTION BTNS */
        .abtns { display:flex; gap:5px; }
        .abtn {
            width:30px; height:30px; border-radius:7px; border:none; cursor:pointer;
            display:flex; align-items:center; justify-content:center; font-size:.77rem; transition:all .2s;
        }
        .abtn-del { background:rgba(255,77,106,0.1); color:var(--red); }
        .abtn-del:hover { background:rgba(255,77,106,0.25); transform:scale(1.05); }
        /* EMPTY */
        .empty { text-align:center; padding:4rem 2rem; color:var(--muted); }
        .empty i { font-size:2.5rem; display:block; margin-bottom:.8rem; opacity:.2; }
        .empty p { font-size:.85rem; }
        /* PAGINATION */
        .pag {
            display:flex; justify-content:flex-end; align-items:center;
            gap:5px; padding:.9rem 1.4rem; border-top:1px solid var(--border);
        }
        .pag-btn {
            width:30px; height:30px; border-radius:7px;
            border:1px solid var(--border); background:transparent;
            color:var(--muted); cursor:pointer; font-size:.78rem;
            display:flex; align-items:center; justify-content:center; transition:all .2s;
        }
        .pag-btn:hover,.pag-btn.on { background:var(--cyan-dim); color:var(--cyan); border-color:var(--bh); }
        .pag-info { font-size:.78rem; color:var(--muted); margin-right:4px; }
        /* TOAST */
        .toast {
            position:fixed; bottom:24px; right:24px; padding:.8rem 1.2rem; border-radius:10px;
            z-index:2000; font-size:.85rem; display:flex; align-items:center; gap:8px;
            animation:tIn .3s ease, tOut .3s 2.8s ease forwards;
            border:1px solid; backdrop-filter:blur(10px);
        }
        .t-ok { background:rgba(0,217,139,0.15); color:var(--green); border-color:rgba(0,217,139,0.3); }
        .t-err { background:rgba(255,77,106,0.15); color:var(--red); border-color:rgba(255,77,106,0.3); }
        @keyframes tIn { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }
        @keyframes tOut { to{opacity:0;transform:translateY(8px);} }
        @media(max-width:900px) {
            .sidebar { display:none; }
            .main { margin-left:0; padding:1rem; }
            .stats-row { grid-template-columns:1fr 1fr; }
            .charts-row { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<div style="display:flex;min-height:100vh;">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-leaf"></i></div>
            <h2>ECO<span>RIDE</span></h2>
        </div>
        <div class="nav-label">Navigation</div>
        <nav>
            <div class="nav-item active" data-page="dashboard"><i class="fas fa-chart-line"></i> Dashboard</div>
            <div class="nav-item" data-page="trips"><i class="fas fa-route"></i> Trajets</div>
            <div class="nav-item" data-page="destinations"><i class="fas fa-map-pin"></i> Destinations</div>
        </nav>
        <div class="sidebar-foot">
            <a href="../Front/user.html" class="nav-item" style="color:var(--muted);">
                <i class="fas fa-arrow-left"></i> Retour accueil
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main" style="flex:1;">
        <div class="top-bar">
            <div class="page-title" id="pageTitle"><i class="fas fa-chart-line"></i> Tableau de Bord</div>
            <a href="../Front/user.html" class="back-btn"><i class="fas fa-arrow-left"></i> Retour</a>
        </div>

        <!-- DASHBOARD -->
        <div id="dashboardPage">
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-route"></i></div>
                    <div class="stat-num" id="totalTrips">0</div>
                    <div class="stat-lbl">Trajets enregistrés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-map-pin"></i></div>
                    <div class="stat-num" id="totalDest">0</div>
                    <div class="stat-lbl">Destinations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-num" id="avgPrice">0</div>
                    <div class="stat-lbl">Prix moyen (DT)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-road"></i></div>
                    <div class="stat-num" id="avgDist">0</div>
                    <div class="stat-lbl">Dist. moyenne (km)</div>
                </div>
            </div>
            <div class="charts-row">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Trajets par prix</h3>
                    <canvas id="tripsChart" height="140"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Destinations par trajet</h3>
                    <canvas id="destChart" height="140"></canvas>
                </div>
            </div>
        </div>

        <!-- TRAJETS PAGE -->
        <div id="tripsPage" style="display:none;">
            <div class="act-bar">
                <div class="act-left">
                    <div class="sbox"><i class="fas fa-search"></i><input type="text" id="tripSearch" placeholder="Rechercher trajet..."></div>
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
                <div class="act-right">
                    <button class="btn-exp btn-pdf" onclick="exportTripsPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="btn-exp btn-xls" onclick="exportTripsExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                </div>
            </div>
            <div class="twrap">
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

        <!-- DESTINATIONS PAGE -->
        <div id="destinationsPage" style="display:none;">
            <div class="act-bar">
                <div class="act-left">
                    <div class="sbox"><i class="fas fa-search"></i><input type="text" id="destSearch" placeholder="Rechercher destination..."></div>
                </div>
                <div class="act-right">
                    <button class="btn-exp btn-pdf" onclick="exportDestPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
                    <button class="btn-exp btn-xls" onclick="exportDestExcel()"><i class="fas fa-file-excel"></i> Excel</button>
                </div>
            </div>
            <div class="twrap">
                <table>
                    <thead>
                        <tr>
                            <th class="th-s" onclick="sortCol('dest','id_des')">ID Dest. <i class="fas fa-sort si" id="ds-id_des"></i></th>
                            <th class="th-s" onclick="sortCol('dest','trajet_id')">ID Trajet <i class="fas fa-sort si" id="ds-trajet_id"></i></th>
                            <th class="th-s" onclick="sortCol('dest','point_arrive')">Point d'arrivée <i class="fas fa-sort si" id="ds-point_arrive"></i></th>
                            <th class="th-s" onclick="sortCol('dest','nom')">Destination (nom) <i class="fas fa-sort si" id="ds-nom"></i></th>
                            <th class="th-s" onclick="sortCol('dest','distance')">Distance (km) <i class="fas fa-sort si" id="ds-distance"></i></th>
                            <th class="th-s" onclick="sortCol('dest','ordre')">Ordre <i class="fas fa-sort si" id="ds-ordre"></i></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="destBody"></tbody>
                </table>
                <div class="pag" id="destPag"></div>
            </div>
        </div>
    </div>
</div>

<!-- JS externe -->
<script src="..\..\assets\admin.js"></script>

</body>
</html>