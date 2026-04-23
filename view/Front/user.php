<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride — Trajets</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root {
  --blue:#1976D2; --blue-light:#61B3FA;
  --dark:#0A1628; --dark2:#0D1F3A; --dark3:#0F3B6E;
  --white:#F4F5F7; --grey:#A7A9AC;
  --green:#27ae60; --red:#e74c3c; --yellow:#f1c40f; --orange:#e67e22;
}
body {
  font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
  background:#0A1628; color:#fff; min-height:100vh;
  transition:background .3s,color .3s;
}
body.light-mode { background:#f5f5f5; color:#333; }
body.light-mode .navbar { background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.1); }
body.light-mode .navbar .logo,
body.light-mode .navbar .dropdown-btn,
body.light-mode .navbar .user-info { color:#1976D2; }
body.light-mode .dropdown-content { background:#fff; border:1px solid #e0e0e0; }
body.light-mode .dropdown-content a { color:#333; }
body.light-mode .fcard,
body.light-mode .twrap,
body.light-mode .table-wrap { background:#fff; border-color:#e0e0e0; color:#333; }
body.light-mode .hero-section { background:linear-gradient(135deg,#1565C0,#0D47A1); }
body.light-mode .igrp input,
body.light-mode .igrp select { background:#f0f0f0; color:#333; border-color:#ccc; }

/* ── NAVBAR ── */
.navbar {
  background:linear-gradient(90deg,#1976D2,#0F3B6E);
  padding:.8rem 2rem;
  display:flex; justify-content:space-between; align-items:center;
  position:sticky; top:0; z-index:100;
}
.nav-left { display:flex; align-items:center; gap:2rem; }

/* ── LOGO avec image ── */
.logo {
  display:flex; align-items:center; gap:10px;
  font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none;
}
.logo-img-wrap {
  width:38px; height:38px; border-radius:10px;
  background:rgba(255,255,255,.15); border:1px solid rgba(97,179,250,.4);
  display:flex; align-items:center; justify-content:center;
  overflow:hidden; flex-shrink:0;
}
.logo-img-wrap img {
  width:100%; height:100%; object-fit:cover;
}
.logo-icon-fallback {
  font-size:1.1rem; color:var(--blue-light);
}
.logo-text { display:flex; flex-direction:column; line-height:1.1; }
.logo-text .brand { font-size:1.15rem; font-weight:800; letter-spacing:.5px; }
.logo-text .tagline { font-size:.58rem; color:rgba(255,255,255,.6); font-weight:400; letter-spacing:.8px; text-transform:uppercase; }

.dropdown { position:relative; display:inline-block; }
.dropdown-btn {
  background:rgba(255,255,255,.1); color:#fff;
  padding:.6rem 1.2rem; border:1px solid rgba(97,179,250,.4);
  border-radius:30px; font-size:.9rem; cursor:pointer;
  display:flex; align-items:center; gap:8px; font-family:inherit;
}
.dropdown-btn:hover { background:rgba(255,255,255,.2); }
.dropdown-content {
  display:none; position:absolute; top:110%; left:0;
  min-width:220px; background:linear-gradient(145deg,#0D1F3A,#122A4A);
  border:1px solid rgba(97,179,250,.3); border-radius:12px;
  box-shadow:0 8px 30px rgba(0,0,0,.4); z-index:200; overflow:hidden;
}
.dropdown-content.show { display:block; animation:fadeInDown .25s ease; }
@keyframes fadeInDown { from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);} }
.dropdown-content a {
  display:flex; align-items:center; gap:12px;
  padding:.8rem 1.2rem; color:#fff; text-decoration:none;
  font-size:.85rem; transition:all .2s;
}
.dropdown-content a i { width:20px; color:var(--blue-light); }
.dropdown-content a:hover { background:rgba(97,179,250,.15); padding-left:1.5rem; }
.dropdown-content a.active { background:rgba(25,118,210,.3); border-left:3px solid var(--blue-light); }
.dropdown-divider { height:1px; background:rgba(97,179,250,.2); margin:.3rem 0; }
.nav-right { display:flex; align-items:center; gap:1rem; }
.user-info {
  display:flex; align-items:center; gap:8px;
  background:rgba(255,255,255,.1); padding:.4rem 1rem;
  border-radius:30px; font-size:.85rem;
}
.theme-btn {
  background:rgba(255,255,255,.1); border:none; color:#fff;
  padding:.4rem .8rem; border-radius:30px; cursor:pointer;
}

/* ── Section title indicator ── */
.section-indicator {
  display:flex; align-items:center; gap:10px;
  margin-bottom:1.5rem; padding:.5rem 0;
}
.section-indicator .si-icon {
  width:32px; height:32px; border-radius:8px;
  background:rgba(97,179,250,.15); border:1px solid rgba(97,179,250,.3);
  display:flex; align-items:center; justify-content:center;
  color:var(--blue-light); font-size:.85rem;
}
.section-indicator .si-label {
  font-size:1rem; font-weight:600; color:#fff;
}
.section-indicator .si-sub {
  font-size:.75rem; color:var(--grey); margin-top:1px;
}

/* ── CONTAINER ── */
.container { max-width:1380px; margin:0 auto; padding:2rem; }

/* ── HERO ── */
.hero-section {
  background:linear-gradient(135deg,#1976D2,#0F3B6E);
  border-radius:20px; padding:2rem 2.5rem;
  margin-bottom:2rem;
  display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem;
}
.hero-content h1 { font-size:1.9rem; margin-bottom:.4rem; }
.hero-content h1 .highlight { color:var(--blue-light); }
.hero-content p { color:rgba(255,255,255,.8); font-size:.88rem; }
.hero-icon { font-size:4rem; opacity:.4; animation:float 3s ease-in-out infinite; }
@keyframes float { 0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);} }

/* ── PAGE TABS (hidden — navigation via menu only) ── */
.page-tab-content { display:none; animation:fadeIn .3s ease; }
.page-tab-content.active { display:block; }
@keyframes fadeIn { from{opacity:0;}to{opacity:1;} }

/* ── LAYOUT (form + table side-by-side) ── */
.layout {
  display:grid; grid-template-columns:420px 1fr; gap:1.8rem;
}

/* ── FORM CARD ── */
.fcard {
  background:rgba(255,255,255,.07); border:1px solid rgba(97,179,250,.15);
  border-radius:16px; overflow:hidden; height:fit-content;
  position:sticky; top:80px;
}
.fcard-head {
  padding:1rem 1.4rem; border-bottom:1px solid rgba(97,179,250,.15);
  display:flex; align-items:center; gap:10px;
  background:rgba(25,118,210,.12);
}
.fcard-head i { color:var(--blue-light); }
.fcard-head h2 { font-size:.95rem; font-weight:600; }
.fcard-body { padding:1.4rem; }

/* Mode tabs inside form */
.tab-row { display:flex; gap:.5rem; margin-bottom:1.3rem; }
.tab-btn {
  flex:1; padding:.55rem; border:1px solid rgba(97,179,250,.25);
  border-radius:10px; background:transparent; color:var(--grey);
  cursor:pointer; font-family:inherit; font-size:.83rem;
  transition:all .2s; display:flex; align-items:center; justify-content:center; gap:6px;
}
.tab-btn.active { background:rgba(25,118,210,.22); color:var(--blue-light); border-color:var(--blue-light); }
.tab-btn:hover:not(.active) { background:rgba(255,255,255,.05); color:#fff; }

/* ── INPUTS ── */
.lbl { display:block; font-size:.75rem; color:var(--grey); margin-bottom:.3rem; padding-left:2px; }
.igrp { position:relative; margin-bottom:.9rem; }
.igrp .ic { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--grey); font-size:.8rem; pointer-events:none; }
.igrp input, .igrp select {
  width:100%; padding:.65rem 1rem .65rem 2.3rem;
  border-radius:10px; border:1px solid rgba(97,179,250,.25);
  background:rgba(255,255,255,.07); color:#fff;
  font-family:inherit; font-size:.85rem;
  transition:border-color .2s, box-shadow .2s;
}
.igrp input:focus, .igrp select:focus {
  outline:none; border-color:var(--blue-light); box-shadow:0 0 0 3px rgba(97,179,250,.12);
}
.igrp input::placeholder { color:var(--grey); }
.igrp select option { background:#0D1F3A; }

/* dist badge */
.dist-badge {
  background:rgba(39,174,96,.1); color:var(--green);
  border:1px solid rgba(39,174,96,.25); padding:.45rem .9rem;
  border-radius:9px; font-size:.8rem; margin-bottom:.9rem;
  display:none; align-items:center; gap:7px;
}

/* arrets */
.arrets-hdr { display:flex; justify-content:space-between; align-items:center; margin-bottom:.7rem; }
.arrets-ttl { font-size:.78rem; color:var(--grey); display:flex; align-items:center; gap:6px; }
.arrets-ttl i { color:var(--blue-light); }
.btn-add-arret {
  background:rgba(25,118,210,.15); color:var(--blue-light);
  border:1px solid rgba(97,179,250,.3); border-radius:8px;
  padding:.3rem .75rem; font-size:.76rem; cursor:pointer;
  display:flex; align-items:center; gap:4px; font-family:inherit; transition:all .2s;
}
.btn-add-arret:hover { background:rgba(25,118,210,.3); }
.arret-item {
  background:rgba(255,255,255,.04); border:1px solid rgba(97,179,250,.15);
  border-radius:10px; padding:.75rem; margin-bottom:.5rem;
}
.arret-row { display:flex; gap:.5rem; align-items:flex-end; }
.arret-row .igrp { flex:1; margin-bottom:0; }
.arret-info { font-size:.73rem; color:var(--grey); margin-top:.4rem; display:flex; gap:1rem; flex-wrap:wrap; }
.arret-info span { display:flex; align-items:center; gap:4px; }
.arret-info span i { color:var(--blue-light); font-size:.68rem; }
.arret-prix-row { display:flex; gap:.5rem; margin-top:.45rem; align-items:center; }
.arret-prix-row label { font-size:.73rem; color:var(--grey); white-space:nowrap; }
.arret-prix-inp {
  flex:1; padding:.38rem .6rem .38rem 1.8rem; border-radius:8px;
  border:1px solid rgba(97,179,250,.2); background:rgba(255,255,255,.06);
  color:#fff; font-family:inherit; font-size:.8rem;
}
.arret-prix-inp:focus { outline:none; border-color:var(--blue-light); }
.prix-auto-badge {
  font-size:.7rem; color:var(--green); background:rgba(39,174,96,.1);
  border:1px solid rgba(39,174,96,.2); border-radius:6px; padding:.18rem .5rem; white-space:nowrap;
}
.btn-rm-arret {
  background:rgba(231,76,60,.12); color:var(--red); border:none;
  border-radius:7px; width:29px; height:29px; cursor:pointer;
  display:flex; align-items:center; justify-content:center; font-size:.72rem;
  transition:all .2s; flex-shrink:0; margin-top:17px;
}
.btn-rm-arret:hover { background:rgba(231,76,60,.28); }

/* primary btn */
.btn-primary {
  width:100%; padding:.75rem;
  background:linear-gradient(135deg,var(--blue),var(--blue-light));
  border:none; border-radius:12px; color:#fff;
  font-family:inherit; font-weight:700; font-size:.88rem;
  cursor:pointer; transition:all .25s;
  display:flex; align-items:center; justify-content:center; gap:8px;
}
.btn-primary:hover { opacity:.9; transform:translateY(-1px); }
.btn-primary:disabled { opacity:.5; cursor:not-allowed; transform:none; }

/* ── TABLE SECTION ── */
.tsec { min-height:400px; }
.thdr {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:1rem; flex-wrap:wrap; gap:.7rem;
}
.thdr h3 { font-size:.97rem; display:flex; align-items:center; gap:8px; }
.thdr h3 i { color:var(--blue-light); }
.tcontrols { display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; }

.sbox { position:relative; }
.sbox i { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--grey); font-size:.75rem; }
.sbox input {
  padding:.48rem .9rem .48rem 2.1rem; border-radius:20px;
  border:1px solid rgba(97,179,250,.25); background:rgba(255,255,255,.07);
  color:#fff; font-family:inherit; font-size:.8rem; width:175px; transition:border-color .2s;
}
.sbox input:focus { outline:none; border-color:var(--blue-light); }
.ssel {
  padding:.48rem .8rem; border-radius:20px;
  border:1px solid rgba(97,179,250,.25); background:rgba(255,255,255,.07);
  color:#fff; font-family:inherit; font-size:.8rem; cursor:pointer;
}
.ssel:focus { outline:none; border-color:var(--blue-light); }
.ssel option { background:#0D1F3A; }

/* ── TABLE ── */
.twrap {
  background:rgba(255,255,255,.05); border-radius:16px;
  overflow:hidden; border:1px solid rgba(97,179,250,.15);
}
.table-top {
  padding:1rem 1.2rem; display:flex; justify-content:space-between; align-items:center;
  border-bottom:1px solid rgba(97,179,250,.15);
}
.table-top h3 { font-size:.9rem; display:flex; align-items:center; gap:7px; color:var(--blue-light); }
.count-badge {
  background:rgba(97,179,250,.15); padding:.25rem .8rem;
  border-radius:20px; font-size:.78rem; color:var(--blue-light);
}
.twrap table { width:100%; border-collapse:collapse; }
.twrap thead { background:rgba(25,118,210,.18); }
.twrap thead th {
  color:var(--blue-light); font-size:.76rem; padding:.85rem 1rem;
  text-align:left; font-weight:600; letter-spacing:.4px; white-space:nowrap;
}
.th-s { cursor:pointer; user-select:none; }
.th-s:hover { color:#fff; }
.si { margin-left:3px; opacity:.35; font-size:.63rem; }
.si.on { opacity:1; }
.twrap tbody td {
  padding:.8rem 1rem; font-size:.85rem;
  border-bottom:1px solid rgba(255,255,255,.04); vertical-align:middle;
}
.twrap tbody tr:last-child td { border-bottom:none; }
.twrap tbody tr:hover td { background:rgba(97,179,250,.04); }

/* chips */
.chip {
  display:inline-flex; align-items:center;
  background:rgba(97,179,250,.12); color:var(--blue-light);
  padding:.15rem .55rem; border-radius:5px; font-size:.73rem; font-weight:700;
  border:1px solid rgba(97,179,250,.25);
}
.dist-pill {
  display:inline-flex; align-items:center; gap:4px;
  background:rgba(255,255,255,.06); color:var(--grey);
  padding:.14rem .52rem; border-radius:20px; font-size:.74rem;
}
.dist-pill i { color:var(--blue-light); font-size:.63rem; }

/* badges */
.badge { padding:.2rem .6rem; border-radius:20px; font-size:.72rem; font-weight:600; }
.badge-confirmed { background:rgba(39,174,96,.18); color:var(--green); }
.badge-pending   { background:rgba(241,196,15,.18); color:var(--yellow); }
.badge-cancelled { background:rgba(231,76,60,.18); color:var(--red); }

/* action btns */
.abtns { display:flex; gap:4px; }
.abtn {
  width:30px; height:30px; border-radius:7px; border:none; cursor:pointer;
  display:flex; align-items:center; justify-content:center; font-size:.77rem; transition:all .2s;
}
.abtn-edit { background:rgba(241,196,15,.12); color:var(--yellow); }
.abtn-edit:hover { background:rgba(241,196,15,.28); }
.abtn-del  { background:rgba(231,76,60,.12);  color:var(--red); }
.abtn-del:hover  { background:rgba(231,76,60,.28); }
.abtn-res  { background:rgba(39,174,96,.12);  color:var(--green); }
.abtn-res:hover  { background:rgba(39,174,96,.28); }

/* reservation inline panel */
.resa-row td { padding:0!important; }
.resa-box {
  background:rgba(25,118,210,.08); border-top:1px solid rgba(97,179,250,.2);
  border-bottom:1px solid rgba(97,179,250,.2); padding:1.4rem 1.2rem;
}
.resa-box h3 { font-size:.9rem; margin-bottom:1rem; display:flex; align-items:center; gap:7px; color:var(--blue-light); }
.resa-route { display:flex; align-items:center; gap:8px; margin-bottom:1.1rem; font-size:.82rem; color:var(--grey); }
.resa-route strong { color:#fff; }
.resa-route i { color:var(--blue-light); font-size:.75rem; }
.stops-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:.7rem; margin-bottom:1rem; }
.stop-card {
  background:rgba(97,179,250,.06); border:1px solid rgba(97,179,250,.2);
  border-radius:12px; padding:.85rem; cursor:pointer; transition:all .22s; position:relative;
}
.stop-card:hover { border-color:rgba(97,179,250,.5); background:rgba(97,179,250,.12); }
.stop-card.selected { border-color:var(--blue-light); background:rgba(97,179,250,.18); }
.stop-card .sc-name { font-weight:600; font-size:.88rem; margin-bottom:.25rem; }
.stop-card .sc-dist { font-size:.75rem; color:var(--grey); display:flex; align-items:center; gap:4px; margin-bottom:.4rem; }
.stop-card .sc-dist i { color:var(--blue-light); font-size:.65rem; }
.stop-card .sc-prix { font-size:1.05rem; font-weight:700; color:var(--blue-light); }
.stop-card .sc-final {
  position:absolute; top:7px; right:8px; font-size:.64rem;
  background:rgba(97,179,250,.12); color:var(--blue-light);
  border:1px solid rgba(97,179,250,.25); border-radius:4px; padding:.1rem .4rem;
}
.stop-card .sc-check {
  position:absolute; top:7px; right:8px; width:18px; height:18px;
  border-radius:50%; background:var(--blue-light); display:none;
  align-items:center; justify-content:center;
}
.stop-card.selected .sc-check { display:flex; }
.stop-card.selected .sc-final { display:none; }
.sc-check i { font-size:.62rem; color:#fff; }
.resa-confirm-row { display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; }
.resa-selected-info {
  flex:1; background:rgba(97,179,250,.06); border:1px solid rgba(97,179,250,.2);
  border-radius:10px; padding:.6rem .9rem; font-size:.82rem;
  display:flex; justify-content:space-between; align-items:center;
}
.resa-selected-info .rsi-name { color:#fff; font-weight:500; }
.resa-selected-info .rsi-price { color:var(--blue-light); font-weight:700; }
.btn-confirm {
  background:linear-gradient(135deg,var(--blue),var(--blue-light));
  color:#fff; border:none; border-radius:9px; padding:.62rem 1.2rem;
  cursor:pointer; font-family:inherit; font-weight:600; font-size:.83rem;
  white-space:nowrap; transition:opacity .2s;
}
.btn-confirm:hover { opacity:.85; }
.btn-cancel-r {
  background:rgba(255,255,255,.05); color:var(--grey);
  border:1px solid rgba(97,179,250,.2); border-radius:9px; padding:.62rem .9rem;
  cursor:pointer; font-family:inherit; font-size:.83rem; transition:all .2s;
}
.btn-cancel-r:hover { color:var(--red); border-color:rgba(231,76,60,.3); }

/* empty */
.empty { text-align:center; padding:3rem; color:var(--grey); }
.empty i { font-size:2.5rem; display:block; margin-bottom:.8rem; opacity:.2; }

/* toast */
.toast {
  position:fixed; bottom:24px; right:24px; padding:.78rem 1.2rem;
  border-radius:10px; z-index:2000; font-size:.84rem;
  display:flex; align-items:center; gap:8px;
  animation:tIn .3s ease, tOut .3s 2.8s ease forwards;
}
.toast.success { background:#27ae60; color:#fff; }
.toast.error   { background:#e74c3c; color:#fff; }
.toast.info    { background:#1976D2; color:#fff; }
.t-ok  { background:rgba(39,174,96,.15);  color:var(--green); border:1px solid rgba(39,174,96,.3); }
.t-err { background:rgba(231,76,60,.15);  color:var(--red);   border:1px solid rgba(231,76,60,.3); }
@keyframes tIn  { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }
@keyframes tOut { to{opacity:0;transform:translateY(8px);} }

/* historique stats */
.stats-row {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr));
  gap:1rem; margin-bottom:1.5rem;
}
.stat-card {
  background:rgba(255,255,255,.07); border-radius:14px; padding:1rem;
  display:flex; align-items:center; gap:1rem;
  border:1px solid rgba(97,179,250,.15); transition:transform .3s;
}
.stat-card:hover { transform:translateY(-3px); border-color:var(--blue-light); }
.stat-card .icon {
  width:42px; height:42px; border-radius:12px;
  display:flex; align-items:center; justify-content:center; font-size:1.2rem;
}
.stat-card .icon.blue  { background:rgba(97,179,250,.15); color:var(--blue-light); }
.stat-card .icon.green { background:rgba(39,174,96,.15);  color:var(--green); }
.stat-card .icon.red   { background:rgba(231,76,60,.15);  color:var(--red); }
.stat-card .icon.gold  { background:rgba(241,196,15,.15); color:var(--yellow); }
.stat-card .num { font-size:1.5rem; font-weight:700; }
.stat-card .lbl { font-size:.7rem; color:var(--grey); }

footer {
  text-align:center; padding:1.4rem; color:var(--grey);
  font-size:.8rem; border-top:1px solid rgba(97,179,250,.12); margin-top:2rem;
}
@media(max-width:900px) {
  .layout { grid-template-columns:1fr; }
  .fcard { position:static; }
  .navbar { padding:.8rem 1rem; }
  .container { padding:1rem; }
}
</style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav class="navbar">
  <div class="nav-left">
    <!-- LOGO avec icône + nom + tagline -->
    <a href="#" class="logo">
      <div class="logo-img-wrap">
        <!-- Si vous avez un fichier logo, remplacez le src. Sinon l'icône s'affiche -->
        <img src="ecoride-logo.png" alt="EcoRide">
      </div>
      <div class="logo-text">
        <span class="brand">EcoRide</span>
        <span class="tagline">Covoiturage vert</span>
      </div>
    </a>

    <!-- MENU déroulant — seul moyen de naviguer entre sections -->
    <div class="dropdown">
      <button class="dropdown-btn" onclick="toggleDropdown()">
        <i class="fas fa-bars"></i><span>Menu</span>
      </button>
      <div class="dropdown-content" id="dropdownMenu">
        <a href="#" id="menu-mes-trajets" class="active" onclick="navToTab('mes-trajets');return false;">
          <i class="fas fa-car-side"></i> Mes trajets
        </a>
        <a href="#" id="menu-tous-trajets" onclick="navToTab('tous-trajets');return false;">
          <i class="fas fa-globe"></i> Tous les trajets
        </a>
        <a href="#" id="menu-historique" onclick="navToTab('historique');return false;">
          <i class="fas fa-history"></i> Mon historique
        </a>
        <div class="dropdown-divider"></div>
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
        <div class="dropdown-divider"></div>
        <a href="../Back/admin.html"><i class="fas fa-shield-alt"></i> Administration</a>
      </div>
    </div>
  </div>

  <div class="nav-right">
    <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i></button>
    <div class="user-info"><i class="fas fa-user-circle"></i><span>Utilisateur</span></div>
  </div>
</nav>

<div class="container">

  <!-- ── HERO ── -->
  <div class="hero-section">
    <div class="hero-content">
      <h1>Gérez vos <span class="highlight">trajets</span></h1>
      <p>Publiez, recherchez et réservez en quelques secondes</p>
    </div>
    <div class="hero-icon"><i class="fas fa-route"></i></div>
  </div>

  <!-- ══════════════════════════════════════════
       TAB 1 — MES TRAJETS (form + my trips table)
       Pas de boutons tabs visibles — navigation via menu uniquement
  ══════════════════════════════════════════════ -->
  <div id="tab-mes-trajets" class="page-tab-content active">

    <!-- Indicateur de section -->
    <div class="section-indicator">
      <div class="si-icon"><i class="fas fa-car-side"></i></div>
      <div>
        <div class="si-label">Mes trajets</div>
        <div class="si-sub">Gérez vos trajets publiés</div>
      </div>
    </div>

    <div class="layout">

      <!-- FORM PANEL -->
      <div class="fcard">
        <div class="fcard-head">
          <i class="fas fa-car"></i>
          <h2 id="formTitle">Gestion des trajets</h2>
        </div>
        <div class="fcard-body">
          <div class="tab-row">
            <button class="tab-btn active" onclick="window.app.switchMode('add')"><i class="fas fa-plus"></i> Ajouter</button>
            <button class="tab-btn" onclick="window.app.switchMode('search')"><i class="fas fa-search"></i> Rechercher</button>
          </div>

          <!-- ADD FORM -->
          <div id="addForm">
            <label class="lbl">Ville de départ</label>
            <div class="igrp">
              <i class="ic fas fa-location-dot"></i>
              <input type="text" id="depart" list="cityList" placeholder="Ex: Tunis" oninput="window.app.onRouteChange()">
            </div>
            <datalist id="cityList"></datalist>

            <label class="lbl">Destination finale</label>
            <div class="igrp">
              <i class="ic fas fa-flag-checkered"></i>
              <input type="text" id="arrivee" list="cityList" placeholder="Ex: Sfax" oninput="window.app.onRouteChange()">
            </div>

            <label class="lbl">Prix total destination finale (DT)</label>
            <div class="igrp">
              <i class="ic fas fa-coins"></i>
              <input type="number" id="prix" placeholder="Ex: 25" min="0" step="0.5" oninput="window.app.updateAllArretPrix()">
            </div>

            <div class="dist-badge" id="distBadge">
              <i class="fas fa-road"></i>
              <span id="distText">Distance calculée</span>
            </div>
            <input type="hidden" id="distance">

            <!-- ARRETS -->
            <div style="margin-bottom:.9rem;">
              <div class="arrets-hdr">
                <div class="arrets-ttl"><i class="fas fa-map-pin"></i> Points d'arrêt intermédiaires</div>
                <button type="button" class="btn-add-arret" onclick="window.app.ajouterArret()"><i class="fas fa-plus"></i> Ajouter</button>
              </div>
              <div id="arrets-list"></div>
            </div>

            <button class="btn-primary" id="submitBtn" onclick="window.app.ajouterTrajet()">
              <i class="fas fa-paper-plane"></i> Publier le trajet
            </button>
          </div>

          <!-- SEARCH FORM -->
          <div id="searchForm" style="display:none;">
            <label class="lbl">Départ</label>
            <div class="igrp">
              <i class="ic fas fa-location-dot"></i>
              <input type="text" id="searchDepart" list="cityList" placeholder="Ville de départ">
            </div>
            <label class="lbl">Destination</label>
            <div class="igrp">
              <i class="ic fas fa-map-marker-alt"></i>
              <input type="text" id="searchArrivee" list="cityList" placeholder="Ville d'arrivée">
            </div>
            <button class="btn-primary" onclick="window.app.rechercherTrajet()">
              <i class="fas fa-search"></i> Rechercher
            </button>
          </div>
        </div>
      </div>

      <!-- MY TRIPS TABLE (sans bouton réserver) -->
      <div class="tsec">
        <div class="thdr">
          <h3><i class="fas fa-list"></i> Mes trajets publiés</h3>
          <div class="tcontrols">
            <div class="sbox">
              <i class="fas fa-search"></i>
              <input type="text" id="tableSearch" placeholder="Filtrer..." oninput="window.app.filterTable()">
            </div>
            <select class="ssel" id="sortSelect" onchange="window.app.applySort()">
              <option value="">Trier par...</option>
              <option value="depart_asc">Départ A→Z</option>
              <option value="depart_desc">Départ Z→A</option>
              <option value="arrivee_asc">Arrivée A→Z</option>
              <option value="prix_asc">Prix ↑</option>
              <option value="prix_desc">Prix ↓</option>
              <option value="dist_asc">Distance ↑</option>
              <option value="dist_desc">Distance ↓</option>
            </select>
          </div>
        </div>
        <div class="twrap">
          <table>
            <thead>
              <tr>
                <th class="th-s" onclick="window.app.sortBy('id')">ID <i class="fas fa-sort si" id="si-id"></i></th>
                <th class="th-s" onclick="window.app.sortBy('depart')">Départ <i class="fas fa-sort si" id="si-depart"></i></th>
                <th class="th-s" onclick="window.app.sortBy('arrivee')">Arrivée <i class="fas fa-sort si" id="si-arrivee"></i></th>
                <th class="th-s" onclick="window.app.sortBy('prix')">Prix <i class="fas fa-sort si" id="si-prix"></i></th>
                <th class="th-s" onclick="window.app.sortBy('distance')">Distance <i class="fas fa-sort si" id="si-distance"></i></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="resultats"></tbody>
          </table>
        </div>
      </div>

    </div><!-- /.layout -->
  </div><!-- /#tab-mes-trajets -->


  <!-- ══════════════════════════════════════════
       TAB 2 — TOUS LES TRAJETS (réservation ici uniquement)
  ══════════════════════════════════════════════ -->
  <div id="tab-tous-trajets" class="page-tab-content">

    <!-- Indicateur de section -->
    <div class="section-indicator">
      <div class="si-icon"><i class="fas fa-globe"></i></div>
      <div>
        <div class="si-label">Tous les trajets</div>
        <div class="si-sub">Recherchez et réservez un trajet</div>
      </div>
    </div>

    <div class="thdr" style="margin-bottom:1rem;">
      <h3><i class="fas fa-globe"></i> Tous les trajets disponibles</h3>
      <div class="tcontrols">
        <div class="sbox">
          <i class="fas fa-search"></i>
          <input type="text" id="allSearch" placeholder="Filtrer..." oninput="filterAllTrips()">
        </div>
      </div>
    </div>
    <div class="twrap">
      <div class="table-top">
        <h3><i class="fas fa-route"></i> Trajets des utilisateurs</h3>
        <span class="count-badge" id="allTripsCount">0 trajet(s)</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Départ</th>
            <th>Arrivée</th>
            <th>Prix (DT)</th>
            <th>Distance</th>
            <th>Arrêts</th>
            <th>Action</th>
          </tr>
        </thead>
        <!-- Le bouton réserver <i class="fas fa-ticket-alt"> s'affiche uniquement ici -->
        <tbody id="allTripsBody">
          <tr><td colspan="7"><div class="empty"><i class="fas fa-route"></i><p>Chargement des trajets...</p></div></td></tr>
        </tbody>
      </table>
    </div>

    <!-- Panneau de réservation inline (s'insère dans ce tab) -->
    <div id="allResContainer"></div>

  </div><!-- /#tab-tous-trajets -->


  <!-- ══════════════════════════════════════════
       TAB 3 — MON HISTORIQUE
  ══════════════════════════════════════════════ -->
  <div id="tab-historique" class="page-tab-content">

    <!-- Indicateur de section -->
    <div class="section-indicator">
      <div class="si-icon"><i class="fas fa-history"></i></div>
      <div>
        <div class="si-label">Mon historique</div>
        <div class="si-sub">Vos réservations passées</div>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card"><div class="icon blue"><i class="fas fa-route"></i></div><div><div class="num" id="hist-total">—</div><div class="lbl">Réservation(s)</div></div></div>
      <div class="stat-card"><div class="icon green"><i class="fas fa-check-circle"></i></div><div><div class="num" id="hist-confirmed">—</div><div class="lbl">Confirmée(s)</div></div></div>
      <div class="stat-card"><div class="icon red"><i class="fas fa-times-circle"></i></div><div><div class="num" id="hist-cancelled">—</div><div class="lbl">Annulée(s)</div></div></div>
      <div class="stat-card"><div class="icon gold"><i class="fas fa-hourglass-half"></i></div><div><div class="num" id="hist-pending">—</div><div class="lbl">En attente</div></div></div>
    </div>

    <div class="twrap">
      <div class="table-top">
        <h3><i class="fas fa-history"></i> Mes réservations</h3>
        <span class="count-badge" id="histCount">0 réservation(s)</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Départ</th>
            <th>Arrivée</th>
            <th>Prix (DT)</th>
            <th>Distance</th>
            <th>Arrêt réservé</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody id="histBody">
          <tr><td colspan="7"><div class="empty"><i class="fas fa-history"></i><p>Aucune réservation trouvée.</p></div></td></tr>
        </tbody>
      </table>
    </div>
  </div><!-- /#tab-historique -->

</div><!-- /.container -->

<footer>
  <p>Eco Ride © 2025 &nbsp;·&nbsp; Covoiturage intelligent · Sécurisé · Économique</p>
</footer>

<!-- ══ SCRIPTS ══ -->
<script>
/* ── Dropdown ── */
function toggleDropdown() { document.getElementById('dropdownMenu').classList.toggle('show'); }
function closeDropdown()  { document.getElementById('dropdownMenu').classList.remove('show'); }
window.addEventListener('click', e => {
  if (!e.target.closest('.dropdown')) closeDropdown();
});

/* ── Navigate to tab from dropdown menu (seul point d'entrée) ── */
function navToTab(tabName) {
  closeDropdown();

  // Masquer tous les tabs
  document.querySelectorAll('.page-tab-content').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + tabName).classList.add('active');

  // Mettre à jour l'état actif dans le menu
  document.querySelectorAll('.dropdown-content a[id^="menu-"]').forEach(a => a.classList.remove('active'));
  const menuEl = document.getElementById('menu-' + tabName);
  if (menuEl) menuEl.classList.add('active');

  // Charger le contenu si nécessaire
  if (tabName === 'tous-trajets') loadAllTrips();
  if (tabName === 'historique')   loadHistorique();

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ── Theme toggle ── */
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

/* ── Load all trips ── */
function loadAllTrips() {
  const tbody = document.getElementById('allTripsBody');
  const countEl = document.getElementById('allTripsCount');

  const trips = (typeof allTrips !== 'undefined') ? allTrips : [];

  if (!trips.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><i class="fas fa-route"></i><p>Aucun trajet disponible.</p></div></td></tr>';
    countEl.textContent = '0 trajet(s)';
    return;
  }

  countEl.textContent = trips.length + ' trajet(s)';
  tbody.innerHTML = trips.map(t => {
    const dist = parseFloat(t.distance_total || 0);
    const prix = parseFloat(t.prix_total || t.prix || 0);
    const nbArrets = (typeof allDests !== 'undefined')
      ? allDests.filter(d => d.trajet_id == t.id_T && d.ordre != 999).length
      : (t.nb_arrets || 0);
    return `<tr>
      <td><span class="chip">#${t.id_T}</span></td>
      <td>${escH(t.point_depart || '—')}</td>
      <td>${escH(t.point_arrive || '—')}</td>
      <td><strong>${prix.toFixed(2)} DT</strong></td>
      <td>${dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—'}</td>
      <td>${nbArrets}</td>
      <td><div class="abtns">
        <button class="abtn abtn-res" title="Réserver ce trajet" onclick="window.app.reserverTrajetAllTrips(${t.id_T})">
          <i class="fas fa-ticket-alt"></i>
        </button>
      </div></td>
    </tr>`;
  }).join('');
}

function filterAllTrips() {
  const q = document.getElementById('allSearch').value.toLowerCase();
  document.querySelectorAll('#allTripsBody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}


/* ── Load historique ── */
function loadHistorique() {
  const tbody   = document.getElementById('histBody');
  const countEl = document.getElementById('histCount');

  const dests = (typeof allDests !== 'undefined') ? allDests : [];
  const resas = dests.filter(d => String(d.ordre) === '999');

  countEl.textContent = resas.length + ' réservation(s)';
  document.getElementById('hist-total').textContent     = resas.length;
  document.getElementById('hist-confirmed').textContent = resas.filter(r => r.statut === 'confirmée' || r.statut === 'confirmed').length;
  document.getElementById('hist-cancelled').textContent = resas.filter(r => r.statut === 'annulée'   || r.statut === 'cancelled').length;
  document.getElementById('hist-pending').textContent   = resas.filter(r => !r.statut || r.statut === 'attente' || r.statut === 'pending').length;

  if (!resas.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><i class="fas fa-history"></i><p>Aucune réservation enregistrée.</p></div></td></tr>';
    return;
  }

  const trips = (typeof allTrips !== 'undefined') ? allTrips : [];

  const badgeClass = s => {
    if (!s || s === 'attente' || s === 'pending') return 'badge-pending';
    if (s === 'confirmée' || s === 'confirmed')   return 'badge-confirmed';
    return 'badge-cancelled';
  };
  const badgeLabel = s => {
    if (!s || s === 'attente' || s === 'pending') return '⏳ En attente';
    if (s === 'confirmée' || s === 'confirmed')   return '✅ Confirmée';
    return '❌ Annulée';
  };

  tbody.innerHTML = resas.map((r, i) => {
    const trip = trips.find(t => t.id_T == r.trajet_id) || {};
    return `<tr>
      <td>${i + 1}</td>
      <td>${escH(trip.point_depart || '—')}</td>
      <td>${escH(trip.point_arrive || '—')}</td>
      <td>${parseFloat(trip.prix_total || 0).toFixed(2)} DT</td>
      <td>${parseFloat(trip.distance_total || 0) > 0 ? parseFloat(trip.distance_total).toFixed(0) + ' km' : '—'}</td>
      <td><strong>${escH(r.nom || r.descente || r.point_arrive || '—')}</strong>
          ${parseFloat(r.prix || 0) > 0 ? '<br><small style="color:var(--blue-light);">' + parseFloat(r.prix).toFixed(2) + ' DT</small>' : ''}
      </td>
      <td><span class="badge ${badgeClass(r.statut)}">${badgeLabel(r.statut)}</span></td>
    </tr>`;
  }).join('');
}

/* escH helper */
function escH(s) { return (s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>

<!-- Original app logic -->
<script src="..\..\assets\user.js"></script>

<script>
/* ══════════════════════════════════════════════════════════════════
   POST-LOAD PATCHES — chargé APRÈS user.js
   Règle absolue : le bouton Réserver n'existe QUE dans "Tous les trajets".
   Dans "Mes trajets" (#resultats) : uniquement Modifier + Supprimer.
   Le panneau de réservation (resRow) s'ouvre uniquement dans allTripsBody.
══════════════════════════════════════════════════════════════════ */

/* ── 1. Réécrire renderTrajets (user.js) pour supprimer le bouton réserver ──
   On remplace la fonction à la source : le bouton .abtn-res
   n'est JAMAIS généré dans #resultats, quelle que soit la situation.       */
window.renderTrajets = function(data) {
  const tbody = document.getElementById('resultats');
  if (!data || data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty"><i class="fas fa-inbox"></i>Aucun trajet trouvé</div></td></tr>';
    return;
  }
  tbody.innerHTML = data.map(t => {
    const dist = parseFloat(t.distance_total || 0);
    return `
      <tr>
        <td><span class="chip">#${t.id_T}</span></td>
        <td>${escH(t.point_depart || '—')}</td>
        <td>${escH(t.point_arrive || '—')}</td>
        <td><strong>${parseFloat(t.prix_total || t.prix || 0).toFixed(2)} DT</strong></td>
        <td>${dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—'}</td>
        <td><div class="abtns">
          <button class="abtn abtn-edit" title="Modifier"
            onclick="window.app.modifierTrajet(${t.id_T},'${escJ(t.point_depart)}','${escJ(t.point_arrive)}',${parseFloat(t.prix_total || t.prix || 0)})">
            <i class="fas fa-edit"></i>
          </button>
          <button class="abtn abtn-del" title="Supprimer"
            onclick="window.app.supprimerTrajet(${t.id_T})">
            <i class="fas fa-trash"></i>
          </button>
          <!-- Pas de bouton réserver ici — uniquement dans "Tous les trajets" -->
        </div></td>
      </tr>`;
  }).join('');
};

/* Synchroniser window.app.applySort / sortBy / rechercherTrajet
   pour qu'ils appellent notre nouvelle renderTrajets               */
const _origApplySort = window.app.applySort;
window.app.applySort = function() {
  /* Copie de la logique de user.js mais appelle notre renderTrajets */
  const val    = (document.getElementById('sortSelect').value || '');
  const search = (document.getElementById('tableSearch').value || '').toLowerCase();
  let data = allTrips.slice().filter(t =>
    !search ||
    (t.point_depart  || '').toLowerCase().includes(search) ||
    (t.point_arrive  || '').toLowerCase().includes(search)
  );
  const sf = {
    depart_asc:  (a,b)=>(a.point_depart||'').localeCompare(b.point_depart||''),
    depart_desc: (a,b)=>(b.point_depart||'').localeCompare(a.point_depart||''),
    arrivee_asc: (a,b)=>(a.point_arrive||'').localeCompare(b.point_arrive||''),
    prix_asc:    (a,b)=>parseFloat(a.prix_total||0)-parseFloat(b.prix_total||0),
    prix_desc:   (a,b)=>parseFloat(b.prix_total||0)-parseFloat(a.prix_total||0),
    dist_asc:    (a,b)=>parseFloat(a.distance_total||0)-parseFloat(b.distance_total||0),
    dist_desc:   (a,b)=>parseFloat(b.distance_total||0)-parseFloat(a.distance_total||0)
  };
  if (sf[val]) data.sort(sf[val]);
  window.renderTrajets(data);
};

window.app.filterTable = window.app.applySort;

window.app.rechercherTrajet = function() {
  const dep = (document.getElementById('searchDepart').value  || '').toLowerCase();
  const arr = (document.getElementById('searchArrivee').value || '').toLowerCase();
  window.renderTrajets(allTrips.filter(t =>
    (!dep || (t.point_depart  || '').toLowerCase().includes(dep)) &&
    (!arr || (t.point_arrive  || '').toLowerCase().includes(arr))
  ));
};

/* ── 2. reserverTrajet dans "Tous les trajets" : injecter le resRow
        dans allTripsBody (et non dans #resultats)                  ──── */
const _origReserverTrajet = window.app.reserverTrajet;

window.app.reserverTrajetAllTrips = function(id) {
  /* Identique à la logique de user.js mais cible allTripsBody */
  curResId = id;
  selectedStop = null;

  /* Fermer un panneau déjà ouvert */
  const old = document.getElementById('resRowAll');
  if (old) old.remove();

  const trip = allTrips.find(t => t.id_T == id);
  if (!trip) return;

  const arrets   = allDests.filter(d => d.trajet_id == id && d.ordre != 999)
                           .sort((a,b) => parseInt(a.ordre) - parseInt(b.ordre));
  const prixFinal = parseFloat(trip.prix_total || trip.prix || 0);
  const distFinal = parseFloat(trip.distance_total || 0);

  let stopsHtml = '';
  arrets.forEach(arret => {
    const dist = parseFloat(arret.distance || 0);
    let prix = parseFloat(arret.prix || 0);
    if (!prix && dist && distFinal && prixFinal)
      prix = Math.round((dist / distFinal) * prixFinal * 100) / 100;
    stopsHtml += `
      <div class="stop-card"
           data-nom="${escH(arret.nom || arret.descente || '')}"
           data-prix="${prix}" data-dist="${dist}"
           onclick="window.app.selectStop(this)">
        <div class="sc-check"><i class="fas fa-check"></i></div>
        <div class="sc-name">${escH(arret.nom || arret.descente || 'Arrêt')}</div>
        <div class="sc-dist"><i class="fas fa-road"></i>${dist > 0 ? dist + ' km' : 'Distance n/a'}</div>
        <div class="sc-prix">${prix > 0 ? prix.toFixed(2) + ' DT' : 'Prix libre'}</div>
      </div>`;
  });
  stopsHtml += `
    <div class="stop-card"
         data-nom="${escH(trip.point_arrive || '')}"
         data-prix="${prixFinal}" data-dist="${distFinal}"
         onclick="window.app.selectStop(this)">
      <div class="sc-final">Destination finale</div>
      <div class="sc-check"><i class="fas fa-check"></i></div>
      <div class="sc-name">${escH(trip.point_arrive || '—')}</div>
      <div class="sc-dist"><i class="fas fa-road"></i>${distFinal > 0 ? distFinal + ' km' : 'Distance n/a'}</div>
      <div class="sc-prix">${prixFinal.toFixed(2)} DT</div>
    </div>`;

  /* Trouver la ligne cible dans allTripsBody */
  const tbody = document.getElementById('allTripsBody');
  let target = null;
  tbody.querySelectorAll('tr').forEach(r => {
    const chip = r.querySelector('.chip');
    if (chip && chip.textContent === '#' + id) target = r;
  });

  const row  = document.createElement('tr');
  row.id     = 'resRowAll';
  row.className = 'resa-row';
  const cell = row.insertCell(0);
  cell.colSpan = 7;
  cell.innerHTML = `
    <div class="resa-box">
      <h3><i class="fas fa-ticket-alt"></i> Réservation —
        ${escH(trip.point_depart || '')}
        <i class="fas fa-arrow-right" style="font-size:.75rem;opacity:.6;"></i>
        ${escH(trip.point_arrive || '')}
      </h3>
      <p style="font-size:.78rem;color:var(--grey);margin-bottom:.9rem;">Choisissez votre point de descente :</p>
      <div class="stops-grid" id="stopsGridAll">${stopsHtml}</div>
      <div class="resa-confirm-row" id="resaConfirmRowAll" style="display:none;">
        <div class="resa-selected-info">
          <span class="rsi-name" id="rsiNameAll">—</span>
          <span class="rsi-price" id="rsiPriceAll">0.00 DT</span>
        </div>
        <button class="btn-confirm" onclick="window.app.confirmerReservationAll()">
          <i class="fas fa-check"></i> Confirmer
        </button>
        <button class="btn-cancel-r" onclick="window.app.annulerReservationAll()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div style="margin-top:.6rem;display:flex;justify-content:flex-end;">
        <button class="btn-cancel-r" onclick="window.app.annulerReservationAll()" style="font-size:.76rem;">Annuler</button>
      </div>
    </div>`;

  if (target) target.insertAdjacentElement('afterend', row);
  else tbody.appendChild(row);
};

window.app.selectStop = function(el) {
  /* Fonctionne pour les deux grilles (resRow et resRowAll) */
  el.closest('.stops-grid').querySelectorAll('.stop-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedStop = {
    nom:  el.dataset.nom,
    prix: parseFloat(el.dataset.prix || 0),
    dist: parseFloat(el.dataset.dist || 0)
  };
  /* Mettre à jour les infos du panneau actif */
  const nameEl  = document.getElementById('rsiNameAll')  || document.getElementById('rsiName');
  const priceEl = document.getElementById('rsiPriceAll') || document.getElementById('rsiPrice');
  const rowEl   = document.getElementById('resaConfirmRowAll') || document.getElementById('resaConfirmRow');
  if (nameEl)  nameEl.textContent  = selectedStop.nom;
  if (priceEl) priceEl.textContent = selectedStop.prix.toFixed(2) + ' DT';
  if (rowEl)   rowEl.style.display = 'flex';
};

window.app.confirmerReservationAll = function() {
  if (!selectedStop) { toast('Choisissez un point de descente !', false); return; }
  if (!curResId)     { toast('Aucun trajet sélectionné', false); return; }
  fetch(DEST_API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      trajet_id: curResId,
      descente:  selectedStop.nom,
      distance:  selectedStop.dist,
      prix:      selectedStop.prix
    })
  })
    .then(r => r.json())
    .then(data => {
      toast(data.message || 'Réservation confirmée pour ' + selectedStop.nom + ' — ' + selectedStop.prix.toFixed(2) + ' DT');
      window.app.annulerReservationAll();
    })
    .catch(e => toast('Erreur : ' + e.message, false));
};

window.app.annulerReservationAll = function() {
  const r = document.getElementById('resRowAll');
  if (r) r.remove();
  curResId = null;
  selectedStop = null;
};

/* ── 3. Refresh des tabs au chargement des données ── */
setTimeout(() => {
  const activeTab = document.querySelector('.page-tab-content.active');
  if (activeTab && activeTab.id === 'tab-tous-trajets') loadAllTrips();
  if (activeTab && activeTab.id === 'tab-historique')   loadHistorique();
}, 1500);
</script>

</body>
</html>