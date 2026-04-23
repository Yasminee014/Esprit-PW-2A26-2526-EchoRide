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
        }
        body {
            font-family:'Poppins','Segoe UI',sans-serif;
            background:linear-gradient(135deg,var(--dark) 0%,var(--dark2) 100%);
            color:#fff; min-height:100vh;
        }
        .wrap { display:flex; min-height:100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            width:260px;
            background:linear-gradient(180deg,var(--blue) 0%,var(--dark3) 100%);
            padding:1.2rem 1rem;
            position:fixed; height:100vh; overflow-y:auto;
            box-shadow:4px 0 20px rgba(0,0,0,.4); z-index:50;
        }

        /* ── LOGO ZONE ── */
        .logo {
            display:flex; flex-direction:column; align-items:center;
            margin-bottom:1.2rem; padding-bottom:1rem;
            border-bottom:2px solid rgba(97,179,250,.35);
        }
        .logo-img-wrap {
            width:115px; height:115px; border-radius:50%;
            border:2.5px solid rgba(97,179,250,.45);
            overflow:hidden; margin-bottom:.65rem;
            background:linear-gradient(145deg, rgba(25,118,210,.25) 0%, rgba(15,59,110,.4) 100%);
            box-shadow:0 4px 28px rgba(0,0,0,.5), 0 0 0 5px rgba(97,179,250,.1);
            transition: box-shadow .3s, transform .3s;
            flex-shrink:0;
        }
        .logo-img-wrap:hover {
            box-shadow:0 6px 32px rgba(25,118,210,.5), 0 0 0 6px rgba(97,179,250,.2);
            transform: scale(1.04);
        }
        .logo-img-wrap img {
            width:100%; height:100%;
            object-fit:cover;
            object-position:center center;
            display:block;
        }
        .logo-text {
            font-size:.78rem; font-weight:700; letter-spacing:2.5px;
            text-transform:uppercase; color:rgba(255,255,255,.9);
            text-shadow:0 1px 4px rgba(0,0,0,.4);
        }
        .logo-sub {
            font-size:.62rem; color:var(--blue-light); letter-spacing:1px;
            margin-top:.1rem; opacity:.85;
        }

        .nav-section { color:var(--grey); font-size:.68rem; text-transform:uppercase; letter-spacing:1.5px; padding:.7rem 1rem .25rem; font-weight:600; }
        nav ul { list-style:none; }
        nav ul li { margin-bottom:.25rem; }
        nav ul li a,
        nav ul li .nav-btn,
        nav ul li .nav-item {
            display:flex; align-items:center; gap:11px;
            padding:.72rem 1rem; color:#fff; text-decoration:none;
            border-radius:10px; transition:all .25s; font-size:.88rem;
            cursor:pointer; background:none; border:none; width:100%; font-family:inherit;
        }
        nav ul li a i, nav ul li .nav-btn i, nav ul li .nav-item i { width:18px; color:var(--blue-light); font-size:.9rem; }
        nav ul li a:hover, nav ul li .nav-btn:hover, nav ul li .nav-item:hover,
        nav ul li a.active, nav ul li .nav-btn.active, nav ul li .nav-item.active {
            background:rgba(255,255,255,.15); border-left:3px solid var(--blue-light);
        }
        nav ul li a:hover i, nav ul li .nav-btn:hover i, nav ul li .nav-item:hover i,
        nav ul li a.active i, nav ul li .nav-btn.active i, nav ul li .nav-item.active i { color:#fff; }
        .sidebar-sep { border:none; border-top:1px solid rgba(97,179,250,.2); margin:.75rem 0; }

        /* ── MAIN ── */
        .main { flex:1; margin-left:260px; padding:1.6rem; }

        /* ── TOPBAR ── */
        .topbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:1.6rem; padding-bottom:1rem;
            border-bottom:1px solid rgba(97,179,250,.2);
        }
        .topbar h1 { font-size:1.5rem; display:flex; align-items:center; gap:9px; color:var(--white); }
        .topbar h1 i { color:var(--blue-light); }
        .pill {
            background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3);
            color:#fff; padding:.4rem .9rem; border-radius:20px; font-size:.8rem;
            display:inline-flex; align-items:center; gap:6px;
        }
        .pill-user {
            background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3);
            color:#fff; padding:.4rem .9rem; border-radius:20px; font-size:.8rem;
            display:inline-flex; align-items:center; gap:6px;
            text-decoration:none; transition:all .25s;
        }
        .pill-user:hover { background:rgba(25,118,210,.3); border-color:#61b3fa; color:#61b3fa; }

        /* ── STATS ── */
        .stats {
            display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
            gap:1rem; margin-bottom:1.4rem;
        }
        .stat {
            background:rgba(255,255,255,.07); border:1px solid rgba(97,179,250,.16);
            border-radius:14px; padding:1.2rem; text-align:center; transition:all .3s;
        }
        .stat:hover { transform:translateY(-4px); border-color:var(--blue-light); box-shadow:0 8px 22px rgba(25,118,210,.18); }
        .stat i { font-size:1.8rem; color:var(--blue-light); margin-bottom:.35rem; display:block; }
        .stat .num {
            font-size:2rem; font-weight:700;
            background:linear-gradient(135deg,var(--blue-light),#fff);
            -webkit-background-clip:text; background-clip:text; color:transparent;
        }
        .stat .lbl { color:var(--grey); font-size:.75rem; margin-top:.2rem; }

        /* ── TOOLBAR ── */
        .toolbar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:1rem; flex-wrap:wrap; gap:.7rem;
        }
        .toolbar-left { display:flex; gap:.6rem; flex-wrap:wrap; align-items:center; }
        .toolbar-right { display:flex; gap:.6rem; }

        /* ── SEARCH ── */
        .search-box { position:relative; }
        .search-box i { position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:var(--grey); font-size:.82rem; }
        .search-box input {
            background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3);
            color:#fff; padding:.5rem .9rem .5rem 2.2rem; border-radius:18px;
            font-size:.84rem; outline:none; transition:all .25s; font-family:inherit; width:220px;
        }
        .search-box input::placeholder { color:var(--grey); }
        .search-box input:focus { border-color:var(--blue-light); background:rgba(97,179,250,.08); }

        select.fsel {
            background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3);
            color:#fff; padding:.5rem .9rem; border-radius:18px;
            font-size:.84rem; font-family:inherit; cursor:pointer; outline:none; transition:all .25s;
        }
        select.fsel:focus { border-color:var(--blue-light); }
        select.fsel option { background:#0D1F3A; }

        .btn {
            padding:.5rem 1.1rem; border-radius:18px; font-size:.84rem;
            font-family:inherit; cursor:pointer; border:none;
            display:inline-flex; align-items:center; gap:6px;
            transition:all .25s; font-weight:500; text-decoration:none;
        }
        .btn-pdf { background:rgba(231,76,60,.14); color:var(--red); border:1px solid rgba(231,76,60,.35); }
        .btn-pdf:hover { background:rgba(231,76,60,.28); transform:translateY(-1px); }
        .btn-xls { background:rgba(39,174,96,.14); color:var(--green); border:1px solid rgba(39,174,96,.35); }
        .btn-xls:hover { background:rgba(39,174,96,.28); transform:translateY(-1px); }

        /* ── TABLE ── */
        .tbl-wrap {
            background:rgba(255,255,255,.04); border-radius:14px;
            overflow:hidden; border:1px solid rgba(97,179,250,.1);
        }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(25,118,210,.22); }
        thead th {
            padding:.85rem 1rem; text-align:left; font-size:.76rem;
            text-transform:uppercase; letter-spacing:.7px;
            color:var(--blue-light); font-weight:600;
        }
        .th-s { cursor:pointer; user-select:none; }
        .th-s:hover { color:#fff; }
        .si { margin-left:4px; opacity:.35; font-size:.65rem; }
        .si.on { opacity:1; }
        tbody tr { border-bottom:1px solid rgba(255,255,255,.04); transition:background .18s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:rgba(97,179,250,.05); }
        tbody td { padding:.8rem 1rem; font-size:.87rem; vertical-align:middle; }

        .chip {
            display:inline-flex; align-items:center;
            background:rgba(97,179,250,.12); color:var(--blue-light);
            padding:.18rem .65rem; border-radius:11px; font-size:.73rem; font-weight:600;
            border:1px solid rgba(97,179,250,.25);
        }
        .chip-green { background:rgba(39,174,96,.14); color:var(--green); border:1px solid rgba(39,174,96,.28); }
        .dist-pill {
            display:inline-flex; align-items:center; gap:4px;
            background:rgba(255,255,255,.06); color:var(--grey);
            padding:.15rem .55rem; border-radius:20px; font-size:.75rem;
        }
        .dist-pill i { color:var(--blue-light); font-size:.65rem; }

        .abtns { display:flex; gap:5px; }
        .abtn {
            width:30px; height:30px; border:none; border-radius:7px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            font-size:.82rem; transition:all .22s;
        }
        .abtn-del { background:rgba(231,76,60,.18); color:#e74c3c; }
        .abtn-del:hover { background:rgba(231,76,60,.35); transform:scale(1.1); }

        /* ── EMPTY ── */
        .empty { text-align:center; padding:2.5rem; color:var(--grey); }
        .empty i { font-size:2.2rem; color:rgba(97,179,250,.22); margin-bottom:.7rem; display:block; }

        /* ── PAGINATION ── */
        .pag {
            display:flex; justify-content:flex-end; align-items:center;
            gap:5px; padding:.9rem 1rem; border-top:1px solid rgba(97,179,250,.1);
            flex-wrap:wrap;
        }
        .pag-btn {
            width:30px; height:30px; border-radius:7px;
            border:1px solid rgba(97,179,250,.2); background:transparent;
            color:var(--grey); cursor:pointer; font-size:.78rem;
            display:flex; align-items:center; justify-content:center; transition:all .2s;
        }
        .pag-btn:hover, .pag-btn.on { background:rgba(25,118,210,.22); color:var(--blue-light); border-color:var(--blue-light); }
        .pag-info { font-size:.78rem; color:var(--grey); margin-right:4px; }

        /* ── TOAST ── */
        .toast {
            position:fixed; bottom:24px; right:24px; padding:.8rem 1.2rem; border-radius:10px;
            z-index:2000; font-size:.85rem; display:flex; align-items:center; gap:8px;
            animation:tIn .3s ease, tOut .3s 2.8s ease forwards;
            border:1px solid; backdrop-filter:blur(10px);
        }
        .t-ok  { background:rgba(39,174,96,.15);  color:var(--green); border-color:rgba(39,174,96,.3); }
        .t-err { background:rgba(231,76,60,.15);  color:var(--red);   border-color:rgba(231,76,60,.3); }
        @keyframes tIn  { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }
        @keyframes tOut { to{opacity:0;transform:translateY(8px);} }

        .section-divider {
            display:flex; align-items:center; gap:.8rem;
            margin-bottom:1.2rem; margin-top:.4rem;
        }
        .section-divider span {
            font-size:.72rem; text-transform:uppercase; letter-spacing:1.5px;
            color:var(--blue-light); font-weight:600; white-space:nowrap;
        }
        .section-divider::before, .section-divider::after {
            content:''; flex:1; height:1px; background:rgba(97,179,250,.18);
        }

        @media(max-width:900px){
            .sidebar { display:none; }
            .main { margin-left:0; }
            .stats { grid-template-columns:1fr 1fr; }

        }
    </style>
</head>
<body>
<div class="wrap">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-img-wrap">
                <img id="sidebarLogo" alt="Eco Ride" src="ecoride-logo.png">
            </div>
            <div class="logo-text">Eco Ride</div>
            <div class="logo-sub">Administration</div>
        </div>
        <nav>
            <div class="nav-section">Navigation</div>
            <ul>
                <li><button class="nav-btn nav-item active" data-page="trips"><i class="fas fa-route"></i> Trajets</button></li>
                <li><button class="nav-btn nav-item" data-page="destinations"><i class="fas fa-map-pin"></i> Destinations</button></li>
            </ul>
            <hr class="sidebar-sep">
            <ul>
                <li><a href="../Front/user.html"><i class="fas fa-arrow-left"></i> Retour accueil</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <!-- ══ MAIN ══ -->
    <main class="main">

        <!-- TOPBAR -->
        <div class="topbar">
            <h1 id="pageTitle"><i class="fas fa-route"></i> Gestion des Trajets</h1>
            <div style="display:flex;gap:.6rem;align-items:center;">
                <a href="../Front/user.html" class="pill-user"><i class="fas fa-user"></i> Espace utilisateur</a>
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
                    </tr>
                    </thead>
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
</div>

<script>
// ── Sync stats pour dashboard ──
function syncDashStats() {
    var t2 = document.getElementById('totalTrips2');
    var d2 = document.getElementById('totalDest2');
    if (t2) t2.textContent = document.getElementById('totalTrips').textContent;
    if (d2) d2.textContent = document.getElementById('totalDest').textContent;
}

// ── Calcul stats destinations depuis les données chargées par admin.js ──
function updateDestStats() {
    var rows = document.querySelectorAll('#destBody tr');
    if (!rows.length) return;
    var sumDist = 0, maxOrd = 0, count = 0;
    rows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        var dist  = parseFloat(cells[4] && cells[4].textContent.trim()) || 0;
        var ordre = parseInt(cells[5]  && cells[5].textContent.trim())  || 0;
        sumDist += dist;
        if (ordre > maxOrd) maxOrd = ordre;
        count++;
    });
    var el1 = document.getElementById('avgDestDist');
    var el2 = document.getElementById('maxOrdre');
    if (el1) el1.textContent = count ? (sumDist/count).toFixed(1) : 0;
    if (el2) el2.textContent = maxOrd;
}

// Observe les changements dans destBody pour recalculer
var destObserver = new MutationObserver(function() {
    if (document.getElementById('destinationsPage').style.display !== 'none') {
        updateDestStats();
    }
});
document.addEventListener('DOMContentLoaded', function() {
    var tbody = document.getElementById('destBody');
    if (tbody) destObserver.observe(tbody, { childList:true, subtree:true });
});

// Observer aussi totalTrips/totalDest pour le dashboard
var statsObserver = new MutationObserver(syncDashStats);
['totalTrips','totalDest'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) statsObserver.observe(el, { childList:true, characterData:true, subtree:true });
});
</script>

<!-- Ton vrai admin.js (chemin inchangé depuis ton projet) -->
<script src="../../assets/admin.js"></script>

<script>
// Désactiver complètement les toasts d'erreur
(function() {
    // Sauvegarder la fonction toast originale
    var originalToast = window.toast;
    
    // La remplacer par une version qui ignore les erreurs de chargement
    window.toast = function(msg, ok) {
        // Ignorer les messages d'erreur de chargement
        if (msg === 'Erreur chargement trajets' || 
            msg === 'Erreur chargement destinations' ||
            (ok === false && (msg.includes('chargement') || msg.includes('Erreur')))) {
            console.log('[Toast ignoré]', msg);
            return;
        }
        // Pour les autres messages, appeler la fonction originale
        if (originalToast) originalToast(msg, ok);
    };
})();
</script>

</body>
</html>