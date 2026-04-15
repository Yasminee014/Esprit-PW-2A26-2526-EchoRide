<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride — Trajets</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#0A2F44; --bg2:#0D1F3A; --panel:#0b2840; --card:#0c2236;
  --cyan:#00B4D8; --ocean:#0077B6;
  --cyan-dim:rgba(0,180,216,0.10); --cyan-glo:rgba(0,180,216,0.22);
  --border:rgba(0,180,216,0.16); --bh:rgba(0,180,216,0.42);
  --text:#D9EEF5; --muted:#4E7A90;
  --green:#00D98B; --red:#FF4D6A; --yellow:#FFD166; --orange:#FF9A3C;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'DM Sans',sans-serif;
  background:linear-gradient(145deg,var(--bg) 0%,var(--bg2) 100%);
  min-height:100vh;color:var(--text);
}
body::before{
  content:'';position:fixed;top:0;left:0;right:0;bottom:0;pointer-events:none;
  background:
    radial-gradient(ellipse 60% 40% at 10% 0%,rgba(0,180,216,0.07) 0%,transparent 60%),
    radial-gradient(ellipse 40% 60% at 90% 100%,rgba(0,119,182,0.08) 0%,transparent 60%);
}
/* NAVBAR */
.navbar{
  background:rgba(11,40,64,0.92);backdrop-filter:blur(14px);
  padding:.9rem 2.5rem;display:flex;justify-content:space-between;align-items:center;
  border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;
}
.nav-logo{display:flex;align-items:center;gap:10px;}
.logo-ring{
  width:38px;height:38px;
  background:linear-gradient(135deg,var(--cyan),var(--ocean));
  border-radius:10px;display:flex;align-items:center;justify-content:center;
  font-size:16px;color:#fff;
}
.nav-logo h2{font-family:'Syne',sans-serif;font-size:1.05rem;letter-spacing:2px;}
.nav-logo h2 span{color:var(--cyan);}
.nav-links{display:flex;gap:.3rem;}
.nav-links a{
  color:var(--muted);text-decoration:none;
  padding:.4rem .95rem;border-radius:20px;font-size:.85rem;
  transition:all .2s;display:flex;align-items:center;gap:6px;
}
.nav-links a:hover{color:var(--cyan);background:var(--cyan-dim);}
.nav-admin{
  background:var(--cyan-dim);color:var(--cyan);
  padding:.4rem 1.1rem;border-radius:20px;text-decoration:none;
  font-size:.83rem;border:1px solid var(--border);
  transition:all .2s;display:flex;align-items:center;gap:6px;
}
.nav-admin:hover{background:var(--cyan-glo);}
/* HERO */
.hero{text-align:center;padding:3rem 2rem 1.8rem;}
.hero-tag{
  display:inline-flex;align-items:center;gap:7px;
  background:var(--cyan-dim);border:1px solid var(--border);
  color:var(--cyan);padding:.3rem .9rem;border-radius:20px;
  font-size:.75rem;letter-spacing:.5px;margin-bottom:1.2rem;
}
.hero h1{font-family:'Syne',sans-serif;font-size:2.4rem;font-weight:800;line-height:1.1;margin-bottom:.6rem;}
.hero h1 em{
  font-style:normal;
  background:linear-gradient(90deg,var(--cyan),var(--ocean));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero p{color:var(--muted);font-size:.93rem;}
/* LAYOUT */
.wrap{
  display:grid;grid-template-columns:440px 1fr;gap:1.8rem;
  padding:0 2.5rem 3rem;max-width:1380px;margin:0 auto;
}
/* FORM CARD */
.fcard{
  background:rgba(12,34,54,0.85);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:20px;overflow:hidden;
  height:fit-content;position:sticky;top:74px;
}
.fcard-head{
  padding:1.1rem 1.5rem;border-bottom:1px solid var(--border);
  display:flex;align-items:center;gap:10px;background:rgba(0,180,216,0.05);
}
.fcard-head i{color:var(--cyan);}
.fcard-head h2{font-family:'Syne',sans-serif;font-size:.95rem;font-weight:600;}
.fcard-body{padding:1.4rem;}
.tab-row{display:flex;gap:.5rem;margin-bottom:1.4rem;}
.tab-btn{
  flex:1;padding:.55rem;border:1px solid var(--border);border-radius:10px;
  background:transparent;color:var(--muted);cursor:pointer;
  font-family:'DM Sans',sans-serif;font-size:.83rem;transition:all .2s;
  display:flex;align-items:center;justify-content:center;gap:6px;
}
.tab-btn.active{background:var(--cyan-dim);color:var(--cyan);border-color:var(--bh);}
.tab-btn:hover:not(.active){background:rgba(0,180,216,0.05);color:var(--text);}
/* INPUTS */
.lbl{display:block;font-size:.75rem;color:var(--muted);margin-bottom:.3rem;padding-left:2px;}
.igrp{position:relative;margin-bottom:.9rem;}
.igrp .ic{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.8rem;pointer-events:none;}
.igrp input,.igrp select{
  width:100%;padding:.68rem 1rem .68rem 2.3rem;
  border-radius:10px;border:1px solid var(--border);
  background:rgba(10,47,68,0.7);color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:.86rem;
  transition:border-color .2s,box-shadow .2s;
}
.igrp input:focus,.igrp select:focus{outline:none;border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,180,216,.1);}
.igrp input::placeholder{color:var(--muted);}
.igrp select option{background:#0A2F44;color:var(--text);}
/* DIST BADGE */
.dist-badge{
  background:rgba(0,217,139,0.08);color:var(--green);
  border:1px solid rgba(0,217,139,0.2);padding:.45rem .9rem;
  border-radius:9px;font-size:.8rem;margin-bottom:.9rem;
  display:none;align-items:center;gap:7px;
}
/* ARRETS */
.arrets-hdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:.7rem;}
.arrets-ttl{font-size:.78rem;color:var(--muted);display:flex;align-items:center;gap:6px;}
.arrets-ttl i{color:var(--cyan);}
.btn-add-arret{
  background:var(--cyan-dim);color:var(--cyan);border:1px solid var(--border);
  border-radius:8px;padding:.3rem .75rem;font-size:.76rem;
  cursor:pointer;display:flex;align-items:center;gap:4px;
  font-family:'DM Sans',sans-serif;transition:all .2s;
}
.btn-add-arret:hover{background:var(--cyan-glo);}
.arret-item{
  background:rgba(0,180,216,0.04);border:1px solid var(--border);
  border-radius:10px;padding:.75rem;margin-bottom:.5rem;
}
.arret-row{display:flex;gap:.5rem;align-items:flex-end;}
.arret-row .igrp{flex:1;margin-bottom:0;}
.arret-info{font-size:.73rem;color:var(--muted);margin-top:.4rem;display:flex;gap:1rem;flex-wrap:wrap;}
.arret-info span{display:flex;align-items:center;gap:4px;}
.arret-info span i{color:var(--cyan);font-size:.68rem;}
.arret-prix-row{display:flex;gap:.5rem;margin-top:.45rem;align-items:center;}
.arret-prix-row label{font-size:.73rem;color:var(--muted);white-space:nowrap;}
.arret-prix-inp{
  flex:1;padding:.38rem .6rem .38rem 1.8rem;border-radius:8px;
  border:1px solid var(--border);background:rgba(10,47,68,0.6);
  color:var(--text);font-family:'DM Sans',sans-serif;font-size:.8rem;
}
.arret-prix-inp:focus{outline:none;border-color:var(--cyan);}
.prix-auto-badge{
  font-size:.7rem;color:var(--green);background:rgba(0,217,139,0.08);
  border:1px solid rgba(0,217,139,0.2);border-radius:6px;
  padding:.18rem .5rem;white-space:nowrap;
}
.btn-rm-arret{
  background:rgba(255,77,106,0.1);color:var(--red);
  border:none;border-radius:7px;width:29px;height:29px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;font-size:.72rem;
  transition:all .2s;flex-shrink:0;margin-top:17px;
}
.btn-rm-arret:hover{background:rgba(255,77,106,0.25);}
/* BTN PRIMARY */
.btn-primary{
  width:100%;padding:.78rem;
  background:linear-gradient(135deg,var(--cyan),var(--ocean));
  border:none;border-radius:12px;color:#fff;
  font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;
  cursor:pointer;transition:all .25s;
  display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-primary:hover{opacity:.9;transform:translateY(-1px);}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none;}
/* TABLE SECTION */
.tsec{min-height:400px;}
.thdr{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.7rem;}
.thdr h2{font-family:'Syne',sans-serif;font-size:.97rem;display:flex;align-items:center;gap:8px;}
.thdr h2 i{color:var(--cyan);}
.tcontrols{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;}
.sbox{position:relative;}
.sbox i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.75rem;}
.sbox input{
  padding:.48rem .9rem .48rem 2.1rem;border-radius:20px;
  border:1px solid var(--border);background:var(--card);color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:.8rem;width:175px;transition:border-color .2s;
}
.sbox input:focus{outline:none;border-color:var(--cyan);}
.ssel{
  padding:.48rem .8rem;border-radius:20px;border:1px solid var(--border);
  background:var(--card);color:var(--text);font-family:'DM Sans',sans-serif;
  font-size:.8rem;cursor:pointer;
}
.ssel:focus{outline:none;border-color:var(--cyan);}
.ssel option{background:#0A2F44;}
/* TABLE */
.twrap{
  background:rgba(12,34,54,0.85);backdrop-filter:blur(8px);
  border-radius:18px;border:1px solid var(--border);overflow:hidden;
}
.twrap table{width:100%;border-collapse:collapse;}
.twrap thead th{
  background:rgba(0,180,216,0.07);color:var(--cyan);
  font-size:.76rem;padding:.9rem 1rem;text-align:left;
  font-weight:600;letter-spacing:.4px;white-space:nowrap;
}
.th-s{cursor:pointer;user-select:none;}
.th-s:hover{color:#fff;}
.si{margin-left:3px;opacity:.35;font-size:.63rem;}
.si.on{opacity:1;}
.twrap tbody td{padding:.82rem 1rem;font-size:.84rem;border-bottom:1px solid rgba(255,255,255,.035);}
.twrap tbody tr:last-child td{border-bottom:none;}
.twrap tbody tr:hover td{background:rgba(0,180,216,0.04);}
/* CHIPS */
.chip{
  display:inline-flex;align-items:center;background:var(--cyan-dim);color:var(--cyan);
  padding:.14rem .5rem;border-radius:5px;font-size:.73rem;font-weight:700;border:1px solid var(--border);
}
.dist-pill{
  display:inline-flex;align-items:center;gap:4px;
  background:rgba(0,180,216,0.06);color:var(--muted);
  padding:.14rem .52rem;border-radius:20px;font-size:.74rem;
}
.dist-pill i{color:var(--cyan);font-size:.63rem;}
/* ACTION BTNS */
.abtns{display:flex;gap:4px;}
.abtn{
  width:29px;height:29px;border-radius:7px;border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;font-size:.75rem;transition:all .2s;
}
.abtn-edit{background:rgba(255,209,102,0.1);color:var(--yellow);}
.abtn-edit:hover{background:rgba(255,209,102,0.25);}
.abtn-del{background:rgba(255,77,106,0.1);color:var(--red);}
.abtn-del:hover{background:rgba(255,77,106,0.25);}
.abtn-res{background:rgba(0,217,139,0.1);color:var(--green);}
.abtn-res:hover{background:rgba(0,217,139,0.25);}
/* RESERVATION PANEL */
.resa-row td{padding:0!important;}
.resa-box{
  background:rgba(0,119,182,0.08);border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);padding:1.4rem 1.2rem;
}
.resa-box h3{font-size:.9rem;margin-bottom:1rem;display:flex;align-items:center;gap:7px;color:var(--cyan);}
.resa-route{
  display:flex;align-items:center;gap:8px;margin-bottom:1.1rem;
  font-size:.82rem;color:var(--muted);
}
.resa-route strong{color:var(--text);}
.resa-route i{color:var(--cyan);font-size:.75rem;}
/* STOP CARDS */
.stops-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.7rem;margin-bottom:1rem;}
.stop-card{
  background:rgba(0,180,216,0.06);border:1px solid var(--border);
  border-radius:12px;padding:.85rem;cursor:pointer;transition:all .22s;position:relative;
}
.stop-card:hover{border-color:var(--bh);background:rgba(0,180,216,0.12);}
.stop-card.selected{border-color:var(--cyan);background:rgba(0,180,216,0.18);}
.stop-card .sc-name{font-weight:600;font-size:.88rem;color:var(--text);margin-bottom:.25rem;}
.stop-card .sc-dist{font-size:.75rem;color:var(--muted);display:flex;align-items:center;gap:4px;margin-bottom:.4rem;}
.stop-card .sc-dist i{color:var(--cyan);font-size:.65rem;}
.stop-card .sc-prix{
  font-family:'Syne',sans-serif;font-size:1.05rem;font-weight:700;color:var(--cyan);
}
.stop-card .sc-final{
  position:absolute;top:7px;right:8px;
  font-size:.64rem;background:rgba(0,180,216,0.15);color:var(--cyan);
  border:1px solid var(--border);border-radius:4px;padding:.1rem .4rem;
}
.stop-card .sc-check{
  position:absolute;top:7px;right:8px;
  width:18px;height:18px;border-radius:50%;
  background:var(--cyan);display:none;align-items:center;justify-content:center;
}
.stop-card.selected .sc-check{display:flex;}
.stop-card.selected .sc-final{display:none;}
.sc-check i{font-size:.62rem;color:#fff;}
/* RESA CONFIRM */
.resa-confirm-row{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;}
.resa-selected-info{
  flex:1;background:rgba(0,180,216,0.06);border:1px solid var(--border);
  border-radius:10px;padding:.6rem .9rem;font-size:.82rem;
  display:flex;justify-content:space-between;align-items:center;
}
.resa-selected-info .rsi-name{color:var(--text);font-weight:500;}
.resa-selected-info .rsi-price{color:var(--cyan);font-family:'Syne',sans-serif;font-weight:700;}
.btn-confirm{
  background:linear-gradient(135deg,var(--cyan),var(--ocean));
  color:#fff;border:none;border-radius:9px;padding:.62rem 1.2rem;
  cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:600;font-size:.83rem;
  white-space:nowrap;transition:opacity .2s;
}
.btn-confirm:hover{opacity:.85;}
.btn-cancel-r{
  background:rgba(255,255,255,0.05);color:var(--muted);
  border:1px solid var(--border);border-radius:9px;padding:.62rem .9rem;
  cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.83rem;transition:all .2s;
}
.btn-cancel-r:hover{color:var(--red);border-color:rgba(255,77,106,.3);}
/* EMPTY */
.empty{text-align:center;padding:3.5rem;color:var(--muted);}
.empty i{font-size:2.2rem;display:block;margin-bottom:.7rem;opacity:.2;}
/* TOAST */
.toast{
  position:fixed;bottom:24px;right:24px;padding:.78rem 1.2rem;
  border-radius:10px;z-index:2000;font-size:.84rem;
  display:flex;align-items:center;gap:8px;
  animation:tIn .3s ease,tOut .3s 2.8s ease forwards;
  border:1px solid;backdrop-filter:blur(8px);
}
.t-ok{background:rgba(0,217,139,0.12);color:var(--green);border-color:rgba(0,217,139,.25);}
.t-err{background:rgba(255,77,106,0.12);color:var(--red);border-color:rgba(255,77,106,.25);}
@keyframes tIn{from{transform:translateY(14px);opacity:0;}to{transform:translateY(0);opacity:1;}}
@keyframes tOut{to{opacity:0;transform:translateY(8px);}}
footer{
  text-align:center;padding:1.4rem;color:var(--muted);
  font-size:.8rem;border-top:1px solid var(--border);margin-top:2rem;
}
@media(max-width:900px){
  .wrap{grid-template-columns:1fr;}
  .fcard{position:static;}
  .navbar{padding:.8rem 1rem;}
}
</style>
</head>
<body>
<div class="navbar">
  <div class="nav-logo">
    <div class="logo-ring"><i class="fas fa-leaf"></i></div>
    <h2>ECO<span>RIDE</span></h2>
  </div>
  <div class="nav-links">
    <a href="#"><i class="fas fa-home"></i> Accueil</a>
    <a href="#"><i class="fas fa-car-side"></i> Covoiturage</a>
    <a href="#"><i class="fas fa-envelope"></i> Contact</a>
  </div>
  <a href="../Back/admin.html" class="nav-admin">
    <i class="fas fa-shield-alt"></i> Administration
  </a>
</div>

<div class="hero">
  <div class="hero-tag"><i class="fas fa-leaf"></i> Plateforme de covoiturage</div>
  <h1>Gérez vos <em>trajets</em></h1>
  <p>Publiez, recherchez et réservez en quelques secondes</p>
</div>

<div class="wrap">
  <!-- FORM PANEL -->
  <div class="fcard">
    <div class="fcard-head">
      <i class="fas fa-car"></i>
      <h2>Gestion des trajets</h2>
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

  <!-- TABLE PANEL -->
  <div class="tsec">
    <div class="thdr">
      <h2><i class="fas fa-list"></i> Liste des trajets</h2>
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
</div>

<footer>
  <p>Eco Ride © 2025 &nbsp;·&nbsp; Covoiturage intelligent · Sécurisé · Économique</p>
</footer>

<script src="..\..\assets\user.js"></script>
</body>
</html>