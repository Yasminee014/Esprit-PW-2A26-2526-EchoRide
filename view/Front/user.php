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
body.light-mode .navbar-modern { background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.12); }
body.light-mode .logo { color:#1976D2; }
body.light-mode .nav-links a { color:#444; }
body.light-mode .nav-links a:hover { color:#1976D2; }
body.light-mode .profile-dropdown-content { background:#fff; border-color:#e0e0e0; }
body.light-mode .profile-dropdown-content a { color:#333; }
body.light-mode .fcard,
body.light-mode .twrap { background:#fff; border-color:#e0e0e0; color:#333; }
body.light-mode .hero-section { background:linear-gradient(135deg,#1565C0,#0D47A1); }
body.light-mode .igrp input,
body.light-mode .igrp select { background:#f0f0f0; color:#333; border-color:#ccc; }

/* ══════════════════════════
   NAVBAR
══════════════════════════ */
.navbar-modern {
  background: linear-gradient(135deg, #1976D2 0%, #0F3B6E 100%);
  padding: 1.2rem 5%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1000;
  flex-wrap: wrap;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.logo { display: flex; flex-direction: column; text-decoration: none; }
.logo-text { font-size: 1.6rem; font-weight: 700; letter-spacing: 1px; color: white; line-height: 1.3; }
.logo-tagline { font-size: 0.65rem; color: rgba(255,255,255,0.7); letter-spacing: 0.5px; }

.menu-toggle {
  background: rgba(255,255,255,0.15); border: none; color: white;
  font-size: 1.2rem; padding: 0.5rem 1rem; border-radius: 25px;
  cursor: pointer; display: none; transition: all 0.3s;
}
.menu-toggle:hover { background: rgba(255,255,255,0.25); }


.nav-links {
  display: flex; gap: 0.8rem; list-style: none;
  margin: 0; padding: 0; align-items: center; flex-wrap: wrap;
}
.nav-links li:not(.profile-dropdown):not(.nav-admin):not(.theme-li):not(.nav-lostfound) a {
  text-decoration: none; padding: 0.5rem 1.2rem; border-radius: 30px;
  font-size: 0.9rem; font-weight: 500; transition: all 0.3s;
  display: inline-block; background: transparent; color: white; border: none;
}
.nav-links li:not(.profile-dropdown):not(.nav-admin):not(.theme-li):not(.nav-lostfound) a:hover {
  background: rgba(255,255,255,0.2); transform: translateY(-2px);
}
.nav-links li:not(.profile-dropdown):not(.nav-admin):not(.theme-li):not(.nav-lostfound) a.active {
  background: #0A1628; color: white; box-shadow: 0 2px 8px rgba(10,22,40,0.3);
}

/* Bouton Admin */
.nav-admin a {
  background: rgba(231,76,60,0.2); border: 1px solid rgba(231,76,60,0.4);
  color: #e74c3c !important; padding: 0.5rem 1.2rem; border-radius: 30px;
  text-decoration: none; display: inline-block;
  font-size: 0.9rem; font-weight: 500; transition: all 0.3s;
}
.nav-admin a:hover { background: rgba(231,76,60,0.35) !important; transform: translateY(-2px); }

/* Bouton Lost & Found */


/* Bouton thème */
.theme-btn {
  background: rgba(255,255,255,0.15); border: none; color: white;
  width: 38px; height: 38px; border-radius: 50%; cursor: pointer;
  font-size: 1.1rem; transition: all 0.3s;
  display: flex; align-items: center; justify-content: center;
}
.theme-btn:hover { background: rgba(255,255,255,0.3); transform: rotate(15deg); }

/* ── Profil dropdown ── */
.profile-dropdown { position: relative; }
.profile-btn {
  display: flex; align-items: center; gap: 10px;
  background: #2F6FA5; border: none; padding: 0.5rem 1.2rem;
  border-radius: 30px; cursor: pointer; transition: all 0.3s;
  color: #fff; font-size: 0.9rem; font-weight: 500; font-family: inherit;
}
.profile-btn:hover { background: #3C82C4; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(60,130,196,0.3); }
.profile-avatar { width: 28px; height: 28px; background: #5FA8E0; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.profile-avatar i { font-size: 0.8rem; color: #fff; }

.dropdown-menu {
  display: none; position: absolute; top: calc(100% + 10px); right: 0;
  width: 260px; background: #0F2A44; border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 1001;
  overflow: hidden; animation: ddFadeIn .2s ease;
}
.dropdown-menu.show { display: block; }
@keyframes ddFadeIn {
  from { opacity: 0; transform: translateY(-8px); }
  to   { opacity: 1; transform: translateY(0); }
}
.dropdown-header {
  display: flex; align-items: center; gap: 12px;
  padding: 1rem; background: #163A5C;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.dropdown-header .dh-avatar {
  width: 45px; height: 45px; background: #5FA8E0;
  border-radius: 50%; display: flex; align-items: center;
  justify-content: center; flex-shrink: 0;
}
.dropdown-header .dh-avatar i { font-size: 1.2rem; color: white; }
.dh-name { font-size: 0.95rem; font-weight: 600; color: #CFE6FF; }
.dh-role { font-size: 0.65rem; color: rgba(207,230,255,0.7); }

.dropdown-links { padding: 0.5rem 0; }
.dropdown-links a {
  display: flex; align-items: center; gap: 12px;
  padding: 0.7rem 1rem; margin: 0 0.5rem;
  border-radius: 10px; color: #CFE6FF;
  text-decoration: none; font-size: 0.85rem; transition: all 0.2s;
}
.dropdown-links a i { width: 22px; color: #5FA8E0; font-size: 1rem; }
.dropdown-links a:hover { background: rgba(255,255,255,0.05); }
.dropdown-links a.active { background: #1E4F7A; border-left: 3px solid #5FA8E0; }


.dropdown-divider { height: 1px; background: rgba(255,255,255,0.08); margin: 0.3rem 0; }
.dropdown-actions { padding-bottom: 0.5rem; }
.dropdown-actions button {
  display: flex; align-items: center; gap: 12px;
  padding: 0.7rem 1rem; margin: 0 0.5rem;
  border-radius: 10px; color: #FF5C5C;
  background: transparent; border: none; font-family: inherit;
  font-size: 0.85rem; cursor: pointer; width: calc(100% - 1rem); transition: all 0.2s;
}
.dropdown-actions button i { width: 22px; color: #FF5C5C; }
.dropdown-actions button:hover { background: rgba(255,92,92,0.15); }

body.light-mode .navbar-modern { background: linear-gradient(135deg, #1565C0, #0D47A1); }

@media(max-width:900px) {
  .menu-toggle { display: block; }
  .nav-links { display: none; width: 100%; flex-direction: column; margin-top: 1rem; }
  .nav-links.show { display: flex; }
  .navbar-modern { flex-wrap: wrap; padding: 1rem; }
  .dropdown-menu { position: static; width: 100%; margin-top: 8px; }
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
.section-indicator .si-label { font-size:1rem; font-weight:600; }
.section-indicator .si-sub { font-size:.75rem; color:var(--grey); margin-top:1px; }

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

/* ── PAGE TABS ── */
.page-tab-content { display:none; animation:fadeIn .3s ease; }
.page-tab-content.active { display:block; }
@keyframes fadeIn { from{opacity:0;}to{opacity:1;} }

/* ── LAYOUT ── */
.layout { display:grid; grid-template-columns:420px 1fr; gap:1.8rem; }

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

.dist-badge {
  background:rgba(39,174,96,.1); color:var(--green);
  border:1px solid rgba(39,174,96,.25); padding:.45rem .9rem;
  border-radius:9px; font-size:.8rem; margin-bottom:.9rem;
  display:none; align-items:center; gap:7px;
}

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

.badge { padding:.2rem .6rem; border-radius:20px; font-size:.72rem; font-weight:600; }
.badge-confirmed { background:rgba(39,174,96,.18); color:var(--green); }
.badge-pending   { background:rgba(241,196,15,.18); color:var(--yellow); }
.badge-cancelled { background:rgba(231,76,60,.18); color:var(--red); }

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
/* Bouton favori */
.abtn-fav  { background:rgba(231,76,60,.1); color:var(--grey); }
.abtn-fav:hover { background:rgba(231,76,60,.22); color:var(--red); }
.abtn-fav.is-fav { background:rgba(231,76,60,.2); color:var(--red); }

/* reservation inline panel */
.resa-row td { padding:0!important; }
.resa-box {
  background:rgba(25,118,210,.08); border-top:1px solid rgba(97,179,250,.2);
  border-bottom:1px solid rgba(97,179,250,.2); padding:1.4rem 1.2rem;
}
.resa-box h3 { font-size:.9rem; margin-bottom:1rem; display:flex; align-items:center; gap:7px; color:var(--blue-light); }
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
@keyframes tIn  { from{transform:translateY(14px);opacity:0;} to{transform:translateY(0);opacity:1;} }
@keyframes tOut { to{opacity:0;transform:translateY(8px);} }

/* Stats historique */
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

/* ══ PAGINATION ══ */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .4rem;
  padding: 1.5rem 0 .5rem;
  flex-wrap: wrap;
}
.pagination a, .pagination span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  min-width: 36px;
  height: 36px;
  padding: 0 .7rem;
  border-radius: 8px;
  font-size: .83rem;
  font-family: inherit;
  text-decoration: none;
  transition: all .2s;
  border: 1px solid rgba(97,179,250,.2);
  color: rgba(255,255,255,.7);
  background: rgba(255,255,255,.05);
}
.pagination a:hover {
  background: rgba(97,179,250,.15);
  border-color: rgba(97,179,250,.4);
  color: #fff;
}
.pagination span.active {
  background: linear-gradient(135deg, var(--blue), var(--blue-light));
  border-color: transparent;
  color: #fff;
  font-weight: 700;
  box-shadow: 0 2px 10px rgba(25,118,210,.4);
}
body.light-mode .pagination a,
body.light-mode .pagination span {
  background: #f0f4ff;
  border-color: #cdd9f5;
  color: #444;
}
body.light-mode .pagination a:hover { background: #dde8ff; }
body.light-mode .pagination span.active { background: linear-gradient(135deg,#1976D2,#61B3FA); color:#fff; }

footer {
  text-align:center; padding:1.4rem; color:var(--grey);
  font-size:.8rem; border-top:1px solid rgba(97,179,250,.12); margin-top:2rem;
}

@media(max-width:900px) {
  .layout { grid-template-columns:1fr; }
  .fcard { position:static; }
  .navbar-modern { padding:0 1rem; }
  .nav-links { display:none; }
  .container { padding:1rem; }
}
</style>
</head>
<body>

<!-- ══ NAVBAR MODERNE ══ -->
<nav class="navbar-modern">
  <a href="index.php" class="logo">
    <div class="logo-text">ECO RIDE</div>
    <div class="logo-tagline">Covoiturage Intelligent</div>
  </a>

  <button class="menu-toggle" onclick="document.getElementById('navLinks').classList.toggle('show')">
    <i class="fas fa-bars"></i>
  </button>

  <ul class="nav-links" id="navLinks">
    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
    <li><a href="View/FrontOffice/events.php"><i class="fa-solid fa-calendar"></i> Événements</a></li>
    <li><a href="View/FrontOffice/sponsors.php"><i class="fas fa-handshake"></i> Sponsors</a></li>
    <li><a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a></li>
    <li><a href="lost_and_found.php"><i class="fas fa-search-location"></i> objets perdus</a></li>


    <!-- ADMIN -->
    <li class="nav-admin">
      <a href="View/BackOffice/dashboard.php"><i class="fas fa-shield-alt"></i> Admin</a>
    </li>

    <!-- PROFIL -->
    <li class="profile-dropdown">
      <button class="profile-btn" onclick="toggleProfileDropdown(event)">
        <div class="profile-avatar"><i class="fas fa-user"></i></div>
        <span>Profil</span>
        <i class="fas fa-chevron-down" style="font-size:.7rem;margin-left:4px;"></i>
      </button>

      <div class="dropdown-menu" id="profileDropdown">
        <div class="dropdown-header">
          <div class="dh-avatar"><i class="fas fa-user"></i></div>
          <div>
            <div class="dh-name">Utilisateur</div>
            <div class="dh-role">Membre EcoRide</div>
          </div>
        </div>

        <div class="dropdown-links">
          <a href="#" id="pdmenu-mes-trajets" class="active"
             onclick="navToTab('mes-trajets');return false;">
            <i class="fas fa-map-marker-alt"></i> Mes trajets
          </a>
          <a href="#" id="pdmenu-tous-trajets"
             onclick="navToTab('tous-trajets');return false;">
            <i class="fas fa-route"></i> Tous les trajets
          </a>
          <a href="#" id="pdmenu-historique"
             onclick="navToTab('historique');return false;">
            <i class="fas fa-history"></i> Mon historique
          </a>
          <a href="#" id="pdmenu-mes_vehicules"
             onclick="navToTab('mes_vehicules');return false;">
            <i class="fas fa-key"></i> Mes véhicules
          </a>
          <a href="#" id="pdmenu-favoris"
             onclick="navToTab('favoris');return false;">
            <i class="fas fa-heart"></i> Mes favoris
          </a>
          <a href="#" id="pdmenu-lostfound" class="lf-link"
             onclick="navToTab('lostfound');return false;">
            <i class="fas fa-search-location"></i> Mes objets perdus
          </a>
          <a href="#" id="pdmenu-reclamations" class="rec-link"
             onclick="navToTab('reclamations');return false;">
            <i class="fas fa-flag"></i> Réclamations
          </a>
        </div>

        <div class="dropdown-divider"></div>

        <div class="dropdown-actions">
          <button onclick="deconnecter()">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </button>
        </div>
      </div>
    </li>

    <!-- THEME -->
    <li class="theme-li">
      <button id="themeToggle" class="theme-btn" title="Changer le thème">
        <i class="fas fa-moon"></i>
      </button>
    </li>
  </ul>
</nav>
<div class="container">

  <!-- HERO -->
  <div class="hero-section">
    <div class="hero-content">
      <h1>Gérez vos <span class="highlight">trajets</span></h1>
      <p>Publiez, recherchez et réservez en quelques secondes</p>
    </div>
    <div class="hero-icon"><i class="fas fa-route"></i></div>
  </div>

  <!-- ══════════════════════════════════════════
       TAB 1 — MES TRAJETS
  ══════════════════════════════════════════════ -->
  <div id="tab-mes-trajets" class="page-tab-content active">
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

      <!-- MY TRIPS TABLE -->
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
          <div id="pagination-container-mes-trajets"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
       TAB 2 — TOUS LES TRAJETS
  ══════════════════════════════════════════════ -->
  <div id="tab-tous-trajets" class="page-tab-content">
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
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="allTripsBody">
          <tr><td colspan="7"><div class="empty"><i class="fas fa-route"></i><p>Chargement des trajets...</p></div></td></tr>
        </tbody>
      </table>
      <div id="pagination-container"></div>
    </div>
    <div id="allResContainer"></div>
  </div>

  <!-- ══════════════════════════════════════════
       TAB 3 — MON HISTORIQUE
  ══════════════════════════════════════════════ -->
  <div id="tab-historique" class="page-tab-content">
    <div class="section-indicator">
      <div class="si-icon"><i class="fas fa-history"></i></div>
      <div>
        <div class="si-label">Mon historique</div>
        <div class="si-sub">Vos réservations passées</div>
      </div>
    </div>

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
            <th>#</th><th>Départ</th><th>Arrivée</th>
            <th>Prix (DT)</th><th>Distance</th><th>Arrêt réservé</th><th>Statut</th>
          </tr>
        </thead>
        <tbody id="histBody">
          <tr><td colspan="7"><div class="empty"><i class="fas fa-history"></i><p>Aucune réservation trouvée.</p></div></td></tr>
        </tbody>
      </table>
      <div id="pagination-container-historique"></div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════
       TAB 4 — MES FAVORIS
  ══════════════════════════════════════════════ -->
  <div id="tab-favoris" class="page-tab-content">
    <div class="section-indicator">
      <div class="si-icon"><i class="fas fa-heart" style="color:#e74c3c;"></i></div>
      <div>
        <div class="si-label">Mes favoris</div>
        <div class="si-sub">Vos trajets sauvegardés</div>
      </div>
    </div>
    <div class="twrap">
      <div class="table-top">
        <h3><i class="fas fa-heart" style="color:#e74c3c;"></i> Mes favoris</h3>
        <span class="count-badge" id="favCount">0 favori(s)</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>ID</th><th>Départ</th><th>Arrivée</th>
            <th>Prix (DT)</th><th>Distance</th><th>Sauvegardé le</th><th>Actions</th>
          </tr>
        </thead>
        <tbody id="favBody">
          <tr><td colspan="7"><div class="empty"><i class="fas fa-heart" style="color:rgba(231,76,60,.2);font-size:2.5rem;"></i><p>Aucun favori enregistré.</p><p style="font-size:.75rem;opacity:.6;">Cliquez sur ❤️ dans "Tous les trajets".</p></div></td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /.container -->

<footer>
  <p>Eco Ride © 2025 &nbsp;·&nbsp; Covoiturage intelligent · Sécurisé · Économique</p>
</footer>

<!-- ══ SCRIPTS ══ -->
<script>
/* ───────────────────────────────────────────
   NAVBAR — dropdown profil
─────────────────────────────────────────── */
function toggleProfileDropdown() {
  document.getElementById('profileDropdown').classList.toggle('show');
}
window.addEventListener('click', e => {
  if (!e.target.closest('.profile-dropdown')) {
    document.getElementById('profileDropdown').classList.remove('show');
  }
});

/* Déconnexion */
function deconnecter() {
  if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
    window.location.href = 'logout.php'; // adapter selon votre projet
  }
}

/* ───────────────────────────────────────────
   NAV TABS — navigation entre sections
─────────────────────────────────────────── */
function navToTab(tabName) {
  document.getElementById('profileDropdown').classList.remove('show');

  document.querySelectorAll('.page-tab-content').forEach(t => t.classList.remove('active'));
  const tab = document.getElementById('tab-' + tabName);
  if (tab) tab.classList.add('active');

  // Mettre à jour le lien actif dans le dropdown
  document.querySelectorAll('.dropdown-links a[id^="pdmenu-"]').forEach(a => a.classList.remove('active'));
  const menuEl = document.getElementById('pdmenu-' + tabName);
  if (menuEl) menuEl.classList.add('active');

  if (tabName === 'tous-trajets') { window.currentPage = 1; loadAllTrips(); }
  if (tabName === 'historique')   { window.currentPage = 1; loadHistorique(); }
  if (tabName === 'favoris')      renderFavorites();

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ───────────────────────────────────────────
   THEME TOGGLE
─────────────────────────────────────────── */
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

/* ───────────────────────────────────────────
   FAVORITES SYSTEM (localStorage)
─────────────────────────────────────────── */
const FAV_KEY = 'ecoride_favorites';

function getFavorites() {
  try { return JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); }
  catch { return []; }
}
function saveFavorites(favs) {
  localStorage.setItem(FAV_KEY, JSON.stringify(favs));
}
function isFavorite(id) {
  return getFavorites().some(f => String(f.id_T) === String(id));
}

function toggleFavorite(id) {
  const favs = getFavorites();
  const idx  = favs.findIndex(f => String(f.id_T) === String(id));
  const trip = (typeof allTrips !== 'undefined') ? allTrips.find(t => String(t.id_T) === String(id)) : null;

  if (idx >= 0) {
    favs.splice(idx, 1);
    saveFavorites(favs);
    showToast('Retiré des favoris', true);
  } else if (trip) {
    favs.push({ ...trip, savedAt: new Date().toISOString() });
    saveFavorites(favs);
    showToast('Ajouté aux favoris ❤️', true);
  } else {
    showToast('Trajet introuvable', false);
    return;
  }

  // Mettre à jour tous les boutons ❤ pour ce trajet
  updateFavBtns(id);

  // Rafraîchir le tab favoris si actif
  const favTab = document.getElementById('tab-favoris');
  if (favTab && favTab.classList.contains('active')) renderFavorites();
}

function updateFavBtns(id) {
  const fav = isFavorite(id);
  document.querySelectorAll('.abtn-fav[data-fav-id="' + id + '"]').forEach(btn => {
    btn.innerHTML = fav ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>';
    btn.classList.toggle('is-fav', fav);
    btn.title = fav ? 'Retirer des favoris' : 'Ajouter aux favoris';
  });
}

function favBtnHtml(id) {
  const fav = isFavorite(id);
  return `<button class="abtn abtn-fav${fav ? ' is-fav' : ''}" data-fav-id="${id}"
    title="${fav ? 'Retirer des favoris' : 'Ajouter aux favoris'}"
    onclick="toggleFavorite(${id})">
    <i class="${fav ? 'fas' : 'far'} fa-heart"></i>
  </button>`;
}

function renderFavorites() {
  const tbody   = document.getElementById('favBody');
  const countEl = document.getElementById('favCount');
  if (!tbody) return;

  const favs = getFavorites();
  if (countEl) countEl.textContent = favs.length + ' favori(s)';

  if (!favs.length) {
    tbody.innerHTML = `<tr><td colspan="7">
      <div class="empty">
        <i class="fas fa-heart" style="color:rgba(231,76,60,.2);font-size:2.5rem;display:block;margin-bottom:.8rem;opacity:1;"></i>
        <p>Aucun favori enregistré.</p>
        <p style="font-size:.75rem;opacity:.6;margin-top:.3rem;">Cliquez sur ❤️ dans "Tous les trajets".</p>
      </div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = favs.map(t => {
    const dist = parseFloat(t.distance_total || 0);
    const prix = parseFloat(t.prix_total || t.prix || 0);
    const date = t.savedAt ? new Date(t.savedAt).toLocaleDateString('fr-FR') : '—';
    return `<tr>
      <td><span class="chip">#${t.id_T}</span></td>
      <td>${escH(t.point_depart || '—')}</td>
      <td>${escH(t.point_arrive || '—')}</td>
      <td><strong>${prix.toFixed(2)} DT</strong></td>
      <td>${dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—'}</td>
      <td style="font-size:.75rem;color:var(--grey);">${date}</td>
      <td><div class="abtns">
        ${favBtnHtml(t.id_T)}
        <button class="abtn abtn-res" title="Réserver" onclick="window.app.reserverTrajetAllTrips && window.app.reserverTrajetAllTrips(${t.id_T})">
          <i class="fas fa-ticket-alt"></i>
        </button>
      </div></td>
    </tr>`;
  }).join('');
}

/* ───────────────────────────────────────────
   LOAD ALL TRIPS — avec bouton ❤
─────────────────────────────────────────── */
function loadAllTrips() {
  const tbody   = document.getElementById('allTripsBody');
  const countEl = document.getElementById('allTripsCount');
  const trips   = (typeof allTrips !== 'undefined') ? allTrips : [];

  if (!trips.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><i class="fas fa-route"></i><p>Aucun trajet disponible.</p></div></td></tr>';
    countEl.textContent = '0 trajet(s)';
    return;
  }

  // Apply search filter
  const search = (document.getElementById('allSearch').value || '').toLowerCase();
  let filteredTrips = trips.filter(t => 
    !search || 
    (t.point_depart || '').toLowerCase().includes(search) || 
    (t.point_arrive || '').toLowerCase().includes(search)
  );

  countEl.textContent = filteredTrips.length + ' trajet(s)';

  // Pagination
  const pageSize = (typeof window.pageSize !== 'undefined') ? window.pageSize : 10;
  const currentPage = (typeof window.currentPage !== 'undefined') ? window.currentPage : 1;
  const start = (currentPage - 1) * pageSize;
  const end = start + pageSize;
  const paginatedTrips = filteredTrips.slice(start, end);

  tbody.innerHTML = paginatedTrips.map(t => {
    const dist    = parseFloat(t.distance_total || 0);
    const prix    = parseFloat(t.prix_total || t.prix || 0);
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
        ${favBtnHtml(t.id_T)}
        <button class="abtn abtn-res" title="Réserver ce trajet" onclick="window.app.reserverTrajetAllTrips(${t.id_T})">
          <i class="fas fa-ticket-alt"></i>
        </button>
      </div></td>
    </tr>`;
  }).join('');

  // Update pagination
  if (typeof window.renderPagination === 'function') {
    window.totalPages = Math.ceil(filteredTrips.length / pageSize);
    window.renderPagination('pagination-container');
  }
}

function filterAllTrips() {
  loadAllTrips();
}

/* ───────────────────────────────────────────
   HISTORIQUE
─────────────────────────────────────────── */
function loadHistorique() {
  const tbody   = document.getElementById('histBody');
  const countEl = document.getElementById('histCount');
  const dests   = (typeof allDests !== 'undefined') ? allDests : [];
  const resas   = dests.filter(d => String(d.ordre) === '999');

  // Apply pagination
  const pageSize = (typeof window.pageSize !== 'undefined') ? window.pageSize : 10;
  const currentPage = (typeof window.currentPage !== 'undefined') ? window.currentPage : 1;
  const start = (currentPage - 1) * pageSize;
  const end = start + pageSize;
  const paginatedResas = resas.slice(start, end);

  countEl.textContent = resas.length + ' réservation(s)';
  document.getElementById('hist-total').textContent     = resas.length;
  document.getElementById('hist-confirmed').textContent = resas.filter(r => r.statut === 'confirmée' || r.statut === 'confirmed').length;
  document.getElementById('hist-cancelled').textContent = resas.filter(r => r.statut === 'annulée'   || r.statut === 'cancelled').length;
  document.getElementById('hist-pending').textContent   = resas.filter(r => !r.statut || r.statut === 'attente' || r.statut === 'pending').length;

  if (!paginatedResas.length) {
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
  tbody.innerHTML = paginatedResas.map((r, i) => {
    const trip = trips.find(t => t.id_T == r.trajet_id) || {};
    const globalIndex = start + i + 1; // Global index for numbering
    return `<tr>
      <td>${globalIndex}</td>
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

  // Update pagination
  if (typeof window.renderPagination === 'function') {
    window.totalPages = Math.ceil(resas.length / pageSize);
    window.renderPagination('pagination-container-historique');
  }
}

/* ───────────────────────────────────────────
   TOAST HELPER
─────────────────────────────────────────── */
function showToast(msg, success = true) {
  const t = document.createElement('div');
  t.className = 'toast ' + (success ? 'success' : 'error');
  t.innerHTML = `<i class="fas fa-${success ? 'check' : 'times'}"></i> ${msg}`;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3200);
}

/* ───────────────────────────────────────────
   ESCAPE HTML
─────────────────────────────────────────── */
function escH(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escJ(s) { return (s||'').replace(/'/g,"\\'"); }
</script>

<!-- FICHIER JS PRINCIPAL DU PROJET -->
<script src="../../assets/user.js"></script>
<script src="../../assets/map-integration.js"></script>

<script>
/* ══════════════════════════════════════════════════════
   POST-LOAD PATCHES — chargés APRÈS user.js
══════════════════════════════════════════════════════ */

/* ── 1. renderTrajets (Mes trajets) — sans bouton Réserver ── */
window.renderTrajets = function(data) {
  const tbody = document.getElementById('resultats');
  if (!data || data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty"><i class="fas fa-inbox"></i>Aucun trajet trouvé</div></td></tr>';
    return;
  }
  tbody.innerHTML = data.map(t => {
    const dist = parseFloat(t.distance_total || 0);
    return `<tr>
      <td><span class="chip">#${t.id_T}</span></td>
      <td>${escH(t.point_depart || '—')}</td>
      <td>${escH(t.point_arrive || '—')}</td>
      <td><strong>${parseFloat(t.prix_total || t.prix || 0).toFixed(2)} DT</strong></td>
      <td>${dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—'}</td>
      <td><div class="abtns">
        <button class="abtn abtn-edit" title="Modifier"
          onclick="window.app.modifierTrajet(${t.id_T},'${escJ(t.point_depart)}','${escJ(t.point_arrive)}',${parseFloat(t.prix_total||t.prix||0)})">
          <i class="fas fa-edit"></i>
        </button>
        <button class="abtn abtn-del" title="Supprimer"
          onclick="window.app.supprimerTrajet(${t.id_T})">
          <i class="fas fa-trash"></i>
        </button>
      </div></td>
    </tr>`;
  }).join('');
};

/* ── 2. applySort / filterTable / rechercherTrajet ── */
window.app.applySort = function() {
  const val    = document.getElementById('sortSelect').value || '';
  const search = (document.getElementById('tableSearch').value || '').toLowerCase();
  let data = allTrips.slice().filter(t =>
    !search ||
    (t.point_depart  || '').toLowerCase().includes(search) ||
    (t.point_arrive  || '').toLowerCase().includes(search)
  );
  const sf = {
    depart_asc:  (a,b) => (a.point_depart||'').localeCompare(b.point_depart||''),
    depart_desc: (a,b) => (b.point_depart||'').localeCompare(a.point_depart||''),
    arrivee_asc: (a,b) => (a.point_arrive||'').localeCompare(b.point_arrive||''),
    prix_asc:    (a,b) => parseFloat(a.prix_total||0) - parseFloat(b.prix_total||0),
    prix_desc:   (a,b) => parseFloat(b.prix_total||0) - parseFloat(a.prix_total||0),
    dist_asc:    (a,b) => parseFloat(a.distance_total||0) - parseFloat(b.distance_total||0),
    dist_desc:   (a,b) => parseFloat(b.distance_total||0) - parseFloat(a.distance_total||0)
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

/* ── 3. Réservation depuis "Tous les trajets" ── */
window.app.reserverTrajetAllTrips = function(id) {
  curResId     = id;
  selectedStop = null;

  const old = document.getElementById('resRowAll');
  if (old) old.remove();

  const trip = allTrips.find(t => t.id_T == id);
  if (!trip) return;

  const arrets    = allDests.filter(d => d.trajet_id == id && d.ordre != 999)
                             .sort((a,b) => parseInt(a.ordre) - parseInt(b.ordre));
  const prixFinal = parseFloat(trip.prix_total || trip.prix || 0);
  const distFinal = parseFloat(trip.distance_total || 0);

  let stopsHtml = '';
  arrets.forEach(arret => {
    const dist = parseFloat(arret.distance || 0);
    let prix   = parseFloat(arret.prix || 0);
    if (!prix && dist && distFinal && prixFinal)
      prix = Math.round((dist / distFinal) * prixFinal * 100) / 100;
    stopsHtml += `<div class="stop-card"
      data-nom="${escH(arret.nom || arret.descente || '')}"
      data-prix="${prix}" data-dist="${dist}"
      onclick="window.app.selectStop(this)">
      <div class="sc-check"><i class="fas fa-check"></i></div>
      <div class="sc-name">${escH(arret.nom || arret.descente || 'Arrêt')}</div>
      <div class="sc-dist"><i class="fas fa-road"></i>${dist > 0 ? dist + ' km' : 'Distance n/a'}</div>
      <div class="sc-prix">${prix > 0 ? prix.toFixed(2) + ' DT' : 'Prix libre'}</div>
    </div>`;
  });
  stopsHtml += `<div class="stop-card"
    data-nom="${escH(trip.point_arrive || '')}"
    data-prix="${prixFinal}" data-dist="${distFinal}"
    onclick="window.app.selectStop(this)">
    <div class="sc-final">Destination finale</div>
    <div class="sc-check"><i class="fas fa-check"></i></div>
    <div class="sc-name">${escH(trip.point_arrive || '—')}</div>
    <div class="sc-dist"><i class="fas fa-road"></i>${distFinal > 0 ? distFinal + ' km' : 'Distance n/a'}</div>
    <div class="sc-prix">${prixFinal.toFixed(2)} DT</div>
  </div>`;

  const tbody = document.getElementById('allTripsBody');
  let target  = null;
  tbody.querySelectorAll('tr').forEach(r => {
    const chip = r.querySelector('.chip');
    if (chip && chip.textContent === '#' + id) target = r;
  });

  const row  = document.createElement('tr');
  row.id     = 'resRowAll';
  row.className = 'resa-row';
  const cell = row.insertCell(0);
  cell.colSpan = 7;
  cell.innerHTML = `<div class="resa-box">
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
  el.closest('.stops-grid').querySelectorAll('.stop-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedStop = { nom: el.dataset.nom, prix: parseFloat(el.dataset.prix || 0), dist: parseFloat(el.dataset.dist || 0) };
  const nameEl  = document.getElementById('rsiNameAll');
  const priceEl = document.getElementById('rsiPriceAll');
  const rowEl   = document.getElementById('resaConfirmRowAll');
  if (nameEl)  nameEl.textContent  = selectedStop.nom;
  if (priceEl) priceEl.textContent = selectedStop.prix.toFixed(2) + ' DT';
  if (rowEl)   rowEl.style.display = 'flex';
};

window.app.confirmerReservationAll = function() {
  if (!selectedStop) { showToast('Choisissez un point de descente !', false); return; }
  if (!curResId)     { showToast('Aucun trajet sélectionné', false); return; }
  fetch(DEST_API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ trajet_id: curResId, descente: selectedStop.nom, distance: selectedStop.dist, prix: selectedStop.prix })
  })
  .then(r => r.json())
  .then(data => {
    showToast(data.message || 'Réservation confirmée pour ' + selectedStop.nom);
    window.app.annulerReservationAll();
  })
  .catch(e => showToast('Erreur : ' + e.message, false));
};

window.app.annulerReservationAll = function() {
  const r = document.getElementById('resRowAll');
  if (r) r.remove();
  curResId = null; selectedStop = null;
};

/* ── 4. Refresh au chargement ── */
setTimeout(() => {
  const active = document.querySelector('.page-tab-content.active');
  if (active && active.id === 'tab-tous-trajets') loadAllTrips();
  if (active && active.id === 'tab-historique')   loadHistorique();
  if (active && active.id === 'tab-favoris')      renderFavorites();
}, 1500);

/* Exposer toggleFavorite globalement */
window.toggleFavorite = toggleFavorite;
window.isFavorite     = isFavorite;
window.favBtnHtml     = favBtnHtml;
window.renderFavorites = renderFavorites;
</script>

</body>
</html>