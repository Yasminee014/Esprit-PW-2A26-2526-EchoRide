<?php require_once __DIR__ . '/includes/auth_guard.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recherche IA | EcoRide</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --bg:#0A1628;
  --bg2:#0d1f3a;
  --bg3:#0f2340;
  --card:#0d1f3a;
  --card2:#122a4a;
  --border:rgba(56,139,253,0.15);
  --border2:rgba(56,139,253,0.25);
  --blue:#3b82f6;
  --blue-bright:#60a5fa;
  --blue-dim:rgba(59,130,246,0.12);
  --green:#22c55e;
  --green-dim:rgba(34,197,94,0.12);
  --amber:#f59e0b;
  --amber-dim:rgba(245,158,11,0.12);
  --purple:#a855f7;
  --purple-dim:rgba(168,85,247,0.12);
  --red:#ef4444;
  --text:#e2e8f0;
  --text2:#cbd5e1;
  --muted:#64748b;
  --muted2:#475569;
}
body{
  font-family:'Outfit',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  overflow-x:hidden;
}

/* ─── TOP BAR ─── */
.topbar{
  padding:.75rem 1.5rem;
  display:flex;align-items:center;gap:1rem;
  border-bottom:1px solid var(--border);
  background:var(--bg2);
}
.topbar a{
  color:var(--muted);font-size:.85rem;text-decoration:none;
  display:flex;align-items:center;gap:6px;
  transition:color .2s;
}
.topbar a:hover{color:var(--blue-bright)}
.topbar .logo{
  font-size:1.1rem;font-weight:700;color:var(--text);
  background:linear-gradient(135deg,var(--blue-bright),var(--purple));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;
  margin-left:auto;
}

/* ─── SEARCH PANEL ─── */
.search-panel{
  background:var(--bg2);
  border-bottom:1px solid var(--border);
  padding:1.25rem 1.5rem 1rem;
}
.search-row{
  display:grid;
  grid-template-columns:1fr 44px 1fr 1fr auto;
  gap:.65rem;align-items:end;
  max-width:1100px;margin:0 auto;
}
@media(max-width:768px){.search-row{grid-template-columns:1fr;gap:.5rem}}

.field-wrap{display:flex;flex-direction:column;gap:5px}
.field-lbl{
  font-size:.68rem;font-weight:600;letter-spacing:.07em;
  text-transform:uppercase;color:var(--muted);
  display:flex;align-items:center;gap:5px;
}
.field-lbl i{font-size:.75rem}
.fi{
  width:100%;padding:.7rem 1rem;
  background:rgba(255,255,255,0.04);
  border:1px solid var(--border2);
  border-radius:10px;
  color:var(--text);font-family:inherit;font-size:.92rem;
  outline:none;transition:border-color .2s,box-shadow .2s;
}
.fi:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(59,130,246,0.15)}
.fi::placeholder{color:var(--muted)}

.swap-btn{
  width:44px;height:44px;border-radius:50%;
  background:rgba(59,130,246,0.08);
  border:1px solid var(--border2);
  color:var(--blue-bright);cursor:pointer;
  font-size:.9rem;display:flex;align-items:center;justify-content:center;
  transition:all .25s;align-self:flex-end;
}
.swap-btn:hover{background:rgba(59,130,246,0.2);transform:rotate(180deg)}

.opt-item{display:flex;flex-direction:column;gap:5px}
.places-fi{width:70px;text-align:center}

.range-wrap{display:flex;align-items:center;gap:8px;height:44px}
.range-wrap input[type=range]{accent-color:var(--blue);width:100px;cursor:pointer}
.range-val{
  background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.3);
  color:var(--blue-bright);font-weight:600;font-size:.78rem;
  padding:3px 10px;border-radius:99px;white-space:nowrap;min-width:48px;text-align:center;
}

.btn-search{
  height:44px;padding:0 1.4rem;
  background:linear-gradient(135deg,#1565C0,#1976D2);
  border:none;border-radius:10px;
  color:#fff;font-family:inherit;font-size:.92rem;font-weight:600;
  cursor:pointer;display:flex;align-items:center;gap:8px;white-space:nowrap;
  box-shadow:0 4px 20px rgba(25,118,210,0.4);
  transition:opacity .2s,transform .1s;align-self:flex-end;
}
.btn-search:hover{opacity:.9}
.btn-search:active{transform:scale(.97)}

/* ─── MAIN LAYOUT ─── */
.main{
  display:grid;
  grid-template-columns:1fr 300px;
  gap:1.5rem;
  max-width:1100px;margin:1.5rem auto;
  padding:0 1.5rem;
}
@media(max-width:900px){.main{grid-template-columns:1fr}}

/* ─── NO DIRECT FOUND BANNER ─── */
.no-direct-banner{
  display:flex;align-items:center;gap:1.25rem;
  background:var(--card2);
  border:1px solid var(--border);
  border-radius:14px;
  padding:1.25rem 1.5rem;
  margin-bottom:1.5rem;
  position:relative;overflow:hidden;
}
.no-direct-banner::before{
  content:'';
  position:absolute;right:0;top:0;bottom:0;width:160px;
  background:radial-gradient(ellipse at right,rgba(59,130,246,0.06),transparent 70%);
}
.banner-robot{
  font-size:2.6rem;flex-shrink:0;
  background:rgba(59,130,246,0.1);
  border:1px solid var(--border2);border-radius:50%;
  width:60px;height:60px;display:flex;align-items:center;justify-content:center;
}
.banner-text h3{font-size:1.05rem;font-weight:600;margin-bottom:.25rem}
.banner-text p{font-size:.82rem;color:var(--muted);line-height:1.5}
.banner-img{
  margin-left:auto;flex-shrink:0;
  width:90px;height:60px;
  background:linear-gradient(135deg,rgba(59,130,246,0.08),rgba(168,85,247,0.08));
  border-radius:10px;border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  font-size:1.8rem;
}

/* ─── FILTER TABS ─── */
.filter-row{display:flex;gap:.5rem;margin-bottom:1.25rem;flex-wrap:wrap}
.ftab{
  padding:.38rem .85rem;border-radius:99px;font-size:.8rem;font-weight:500;
  cursor:pointer;border:1px solid transparent;transition:all .2s;
  display:flex;align-items:center;gap:5px;
}
.ftab .cnt{
  font-size:.7rem;font-weight:700;padding:1px 6px;border-radius:99px;
}
.ftab.via{background:var(--green-dim);color:var(--green);border-color:rgba(34,197,94,0.25)}
.ftab.via .cnt{background:rgba(34,197,94,0.2);color:var(--green)}
.ftab.proche{background:var(--amber-dim);color:var(--amber);border-color:rgba(245,158,11,0.25)}
.ftab.proche .cnt{background:rgba(245,158,11,0.2);color:var(--amber)}
.ftab.combi{background:var(--purple-dim);color:var(--purple);border-color:rgba(168,85,247,0.25)}
.ftab.combi .cnt{background:rgba(168,85,247,0.2);color:var(--purple)}
.ftab:not(.all):not(.via):not(.proche):not(.combi){
  background:rgba(255,255,255,0.03);color:var(--muted);border-color:var(--border);
}
.ftab:not(.all):not(.via):not(.proche):not(.combi):hover{background:rgba(255,255,255,0.06);color:var(--text)}

/* ─── SECTION HEADER ─── */
.section-hdr{
  display:flex;align-items:center;gap:8px;
  margin-bottom:.9rem;
}
.section-hdr h4{font-size:.9rem;font-weight:600}
.section-badge{
  font-size:.7rem;font-weight:700;
  width:20px;height:20px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
}
.sec-green h4{color:var(--green)}
.sec-green .section-badge{background:var(--green);color:#000}
.sec-amber h4{color:var(--amber)}
.sec-amber .section-badge{background:var(--amber);color:#000}
.sec-purple h4{color:var(--purple)}
.sec-purple .section-badge{background:var(--purple);color:#fff}

.section-sep{margin-bottom:1.5rem}

/* ─── RESULT CARD ─── */
.rcard{
  background:var(--card2);
  border:1px solid var(--border);
  border-radius:14px;
  padding:1.2rem 1.4rem;
  margin-bottom:.85rem;
  display:grid;grid-template-columns:1fr auto;
  gap:1rem;align-items:center;
  position:relative;overflow:hidden;
  transition:border-color .2s,box-shadow .2s;
  animation:fadein .4s ease both;
}
.rcard:hover{border-color:rgba(59,130,246,0.35);box-shadow:0 0 24px rgba(59,130,246,0.07)}
.rcard::before{
  content:'';position:absolute;left:0;top:0;bottom:0;width:3px;
  background:var(--rc-accent,var(--blue));border-radius:3px 0 0 3px;
}
.rcard.green{--rc-accent:var(--green)}
.rcard.amber{--rc-accent:var(--amber)}
.rcard.purple{--rc-accent:var(--purple)}

/* Left */
.rcard-route{display:flex;align-items:center;gap:8px;margin-bottom:.5rem;flex-wrap:wrap}
.rcard-city{font-size:1.08rem;font-weight:600}
.rcard-arrow{color:var(--muted);font-size:.85rem}

.via-badge{
  display:inline-flex;align-items:center;gap:5px;
  background:var(--green-dim);border:1px solid rgba(34,197,94,0.25);
  color:var(--green);font-size:.72rem;font-weight:600;
  padding:3px 10px;border-radius:99px;
}
.via-badge.amber{background:var(--amber-dim);border-color:rgba(245,158,11,0.25);color:var(--amber)}

.rcard-meta{display:flex;gap:1rem;flex-wrap:wrap;font-size:.78rem;color:var(--muted);margin-top:.4rem}
.rcard-meta span{display:flex;align-items:center;gap:5px}
.rcard-meta i{font-size:.7rem}

/* Multi segment */
.segment-row{
  display:grid;grid-template-columns:1fr auto 1fr;
  gap:.5rem;align-items:center;
  background:var(--bg2);border:1px solid var(--border);
  border-radius:10px;padding:.75rem 1rem;margin-top:.6rem;
}
.seg-info{font-size:.78rem}
.seg-info strong{display:block;font-size:.85rem;margin-bottom:2px}
.seg-info span{color:var(--muted)}
.seg-conn{
  text-align:center;font-size:.68rem;color:var(--muted);
  display:flex;flex-direction:column;align-items:center;gap:3px;
}
.seg-conn i{font-size:.7rem;color:var(--blue-bright)}
.seg-total{
  display:flex;gap:1.5rem;flex-wrap:wrap;margin-top:.6rem;
  font-size:.78rem;color:var(--muted);
}
.seg-total .eco{color:var(--green);font-weight:600}

/* Right */
.rcard-right{display:flex;flex-direction:column;align-items:flex-end;gap:.6rem}

/* Score ring */
.sring{position:relative;width:68px;height:68px;flex-shrink:0}
.sring svg{transform:rotate(-90deg)}
.sring-num{
  position:absolute;inset:0;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  font-size:1rem;font-weight:700;line-height:1;
}
.sring-num small{font-size:.52rem;font-weight:400;color:var(--muted);line-height:1.5}
.sring-label{
  font-size:.65rem;font-weight:700;text-align:center;margin-top:-2px;
}

.rcard-price{font-size:1.2rem;font-weight:700;color:var(--blue-bright);text-align:right}
.rcard-price small{display:block;font-size:.68rem;color:var(--muted);font-weight:400}

.btn-voir{
  padding:.45rem .9rem;
  background:linear-gradient(135deg,#1565C0,#1976D2);
  border:none;border-radius:8px;
  color:#fff;font-family:inherit;font-size:.78rem;font-weight:600;
  cursor:pointer;white-space:nowrap;
  box-shadow:0 3px 12px rgba(25,118,210,0.35);
  transition:opacity .2s,transform .1s;text-decoration:none;
  display:inline-flex;align-items:center;gap:5px;
}
.btn-voir:hover{opacity:.9;transform:translateY(-1px)}

/* ─── SIDEBAR ─── */
.sidebar{}
.sidebar-card{
  background:var(--card2);border:1px solid var(--border);
  border-radius:14px;padding:1.2rem;margin-bottom:1rem;
}
.sidebar-card h5{
  font-size:.88rem;font-weight:600;
  display:flex;align-items:center;gap:8px;margin-bottom:1rem;
}
.sidebar-card h5 i{color:var(--purple)}

/* How it works */
.how-step{display:flex;gap:.85rem;margin-bottom:.85rem;align-items:flex-start}
.how-step:last-child{margin-bottom:0}
.step-num{
  width:24px;height:24px;border-radius:50%;
  font-size:.72rem;font-weight:700;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.step-num.s1{background:var(--blue-dim);color:var(--blue-bright);border:1px solid rgba(59,130,246,0.3)}
.step-num.s2{background:var(--green-dim);color:var(--green);border:1px solid rgba(34,197,94,0.3)}
.step-num.s3{background:var(--amber-dim);color:var(--amber);border:1px solid rgba(245,158,11,0.3)}
.step-num.s4{background:var(--purple-dim);color:var(--purple);border:1px solid rgba(168,85,247,0.3)}
.step-body{}
.step-title{font-size:.8rem;font-weight:600;margin-bottom:2px}
.step-title.s1{color:var(--blue-bright)}
.step-title.s2{color:var(--green)}
.step-title.s3{color:var(--amber)}
.step-title.s4{color:var(--purple)}
.step-desc{font-size:.73rem;color:var(--muted);line-height:1.45}

/* Tips */
.tip-item{
  display:flex;align-items:flex-start;gap:7px;
  font-size:.78rem;color:var(--muted);
  margin-bottom:.6rem;line-height:1.4;
}
.tip-item:last-child{margin-bottom:0}
.tip-item::before{content:'•';color:var(--blue-bright);flex-shrink:0;margin-top:1px}

/* Help */
.help-card{background:var(--card2);border:1px solid var(--border);border-radius:14px;padding:1.2rem}
.help-card h5{font-size:.88rem;font-weight:600;display:flex;align-items:center;gap:8px;margin-bottom:.5rem}
.help-card h5 i{color:var(--blue-bright)}
.help-card p{font-size:.78rem;color:var(--muted);margin-bottom:.85rem}
.btn-support{
  width:100%;padding:.6rem;
  background:rgba(59,130,246,0.1);
  border:1px solid rgba(59,130,246,0.25);
  border-radius:8px;color:var(--blue-bright);
  font-family:inherit;font-size:.82rem;font-weight:600;
  cursor:pointer;transition:background .2s;
}
.btn-support:hover{background:rgba(59,130,246,0.18)}

/* ─── STATES ─── */
.state-box{text-align:center;padding:3.5rem 1rem;color:var(--muted)}
.state-box .si{font-size:3rem;margin-bottom:1rem;opacity:.35}
.state-box h3{font-size:1rem;font-weight:500;margin-bottom:.4rem;color:var(--text)}
.state-box p{font-size:.83rem}

.loader{display:flex;justify-content:center;gap:7px;padding:3rem}
.loader span{width:9px;height:9px;border-radius:50%;background:var(--blue);animation:bloop .8s infinite alternate}
.loader span:nth-child(2){animation-delay:.15s}
.loader span:nth-child(3){animation-delay:.30s}
@keyframes bloop{from{opacity:.2;transform:scale(.7)}to{opacity:1;transform:scale(1)}}

@keyframes fadein{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
</style>
</head>
<body>
  <?php include_once __DIR__ . '/partials/navbar.php'; ?>
  <!-- Bouton Retour design -->
<div style="max-width: 1100px; margin: 1rem auto 0; padding: 0 1.5rem;">
    <a href="tous_les_trajets.php" style="
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(10px);
        padding: 8px 18px;
        border-radius: 40px;
        text-decoration: none;
        color: #61B3FA;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid rgba(97,179,250,0.3);
    " onmouseover="this.style.background='rgba(97,179,250,0.15)'; this.style.transform='translateX(-3px)'; this.style.borderColor='rgba(97,179,250,0.6)';"
       onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.transform='translateX(0)'; this.style.borderColor='rgba(97,179,250,0.3)';">
        <i class="fas fa-arrow-left" style="font-size: 0.75rem;"></i>
        <span>Retour</span>
    </a>
</div>



<!-- SEARCH PANEL -->
<div class="search-panel">
  <div class="search-row">

    <div class="field-wrap">
      <span class="field-lbl"><i class="fas fa-circle-dot" style="color:var(--blue-bright)"></i>Départ</span>
      <input id="inp-depart" class="fi" type="text" placeholder="Tunis, Ariana…" autocomplete="off" list="sug-dep">
      <datalist id="sug-dep"></datalist>
    </div>

    <button class="swap-btn" onclick="swapVilles()" title="Inverser">
      <i class="fas fa-arrow-right-arrow-left"></i>
    </button>

    <div class="field-wrap">
      <span class="field-lbl"><i class="fas fa-location-dot" style="color:var(--blue-bright)"></i>Arrivée</span>
      <input id="inp-arrivee" class="fi" type="text" placeholder="Sousse, Sfax…" autocomplete="off" list="sug-arr">
      <datalist id="sug-arr"></datalist>
    </div>

    <div class="field-wrap opt-item">
      <span class="field-lbl"><i class="fas fa-users" style="color:var(--blue-bright)"></i>Places</span>
      <input id="inp-places" type="number" class="fi places-fi" min="1" max="9" value="1">
    </div>

    <div class="field-wrap opt-item">
      <span class="field-lbl"><i class="fas fa-location-crosshairs" style="color:var(--amber)"></i>Tolérance</span>
      <div class="range-wrap">
        <input id="inp-rayon" type="range" min="10" max="200" value="60"
               oninput="document.getElementById('rval').textContent=this.value+'km'">
        <span class="range-val" id="rval">60km</span>
      </div>
    </div>

    <button class="btn-search" onclick="lancerRecherche()">
      <i class="fas fa-sparkles"></i> Recherche intelligente
    </button>

  </div>
</div>

<!-- MAIN -->
<div class="main">

  <!-- LEFT COLUMN: results -->
  <div>
    <div id="results-zone">
      <div class="state-box">
        <div class="si">🗺️</div>
        <h3>Prêt à matcher</h3>
        <p>Saisis un départ et une arrivée, l'IA s'occupe du reste.</p>
      </div>
    </div>
  </div>

  <!-- RIGHT COLUMN: sidebar -->
  <div class="sidebar">

    <div class="sidebar-card">
      <h5><i class="fas fa-circle-question"></i> Comment ça marche ?</h5>
      <div class="how-step">
        <div class="step-num s1">1</div>
        <div class="step-body">
          <div class="step-title s1">Recherche exacte</div>
          <div class="step-desc">On cherche d'abord des trajets directs.</div>
        </div>
      </div>
      <div class="how-step">
        <div class="step-num s2">2</div>
        <div class="step-body">
          <div class="step-title s2">Trajets qui passent par</div>
          <div class="step-desc">On trouve des trajets qui passent par votre destination.</div>
        </div>
      </div>
      <div class="how-step">
        <div class="step-num s3">3</div>
        <div class="step-body">
          <div class="step-title s3">Trajets proches</div>
          <div class="step-desc">On cherche des trajets à proximité que vous pouvez rejoindre.</div>
        </div>
      </div>
      <div class="how-step">
        <div class="step-num s4">4</div>
        <div class="step-body">
          <div class="step-title s4">Combinaisons intelligentes</div>
          <div class="step-desc">On combine plusieurs trajets pour créer un itinéraire complet.</div>
        </div>
      </div>
    </div>

    <div class="sidebar-card" id="tips-card">
      <h5><i class="fas fa-lightbulb" style="color:var(--amber)"></i> Conseils</h5>
      <div class="tip-item">Augmentez la tolérance pour plus de résultats</div>
      <div class="tip-item">Les trajets avec correspondance peuvent prendre plus de temps</div>
      <div class="tip-item">Vérifiez les horaires de correspondance</div>
    </div>

    <div class="help-card">
      <h5><i class="fas fa-headset"></i> Besoin d'aide ?</h5>
      <p>Notre équipe est là pour vous aider</p>
      <button class="btn-support">Contacter le support</button>
    </div>

  </div>
</div>

<script>
const API_URL = 'matching_api.php';
const ALL_TRIPS = 'tous_les_trajets.php';

let currentMode = 'trajet';

const VILLES = [
  'Tunis','Sfax','Sousse','Monastir','Nabeul','Hammamet','Bizerte',
  'Kairouan','Gabès','Gafsa','Tozeur','Medenine','Tataouine','Mahdia',
  'Jendouba','Tabarka','Kasserine','Sidi Bouzid','Zaghouan','Kebili','Kef',
  'Siliana','Beja','Enfidha','Grombalia','Soliman','Djerba','Zarzis',
  'Ariana','La Marsa','Carthage','Sidi Bou Saïd','Radès','El Fahs',
  'Msaken','Hammam Sousse','El Ajem','Manouba','Ben Gardane'
];

['sug-dep','sug-arr'].forEach(id => {
  const dl = document.getElementById(id);
  VILLES.forEach(v => {
    const o = document.createElement('option'); o.value = v; dl.appendChild(o);
  });
});

function swapVilles(){
  const d = document.getElementById('inp-depart');
  const a = document.getElementById('inp-arrivee');
  [d.value,a.value]=[a.value,d.value];
}

['inp-depart','inp-arrivee'].forEach(id=>{
  document.getElementById(id)?.addEventListener('keydown',e=>{if(e.key==='Enter')lancerRecherche()});
});

/* ─── SEARCH ─── */
async function lancerRecherche(){
  const dep = document.getElementById('inp-depart').value.trim();
  const arr = document.getElementById('inp-arrivee').value.trim();
  const ray = document.getElementById('inp-rayon').value;
  const pl  = document.getElementById('inp-places').value;
  if(!dep||!arr){shakeBorder();return}
  showLoader();
  try{
    const r = await fetch(`${API_URL}?depart=${encodeURIComponent(dep)}&arrivee=${encodeURIComponent(arr)}&rayon=${ray}&places=${pl}`);
    const d = await r.json();
    if(!d.success){showErr(d.message||'Erreur serveur');return}
    render(d);
  }catch(e){showErr('Impossible de joindre le serveur.')}
}

/* ─── RENDER ─── */
function render(data){
  const zone = document.getElementById('results-zone');
  const sug  = data.suggestions || {};
  const res  = data.resultats   || [];

  const passant = sug.trajets_passant  || [];
  const proche  = sug.trajets_proches  || [];
  const multi   = sug.multi_trajets    || [];
  const total   = res.length + passant.length + proche.length + multi.length;

  const hasNoDirectMsg = (!res.length || !data.has_exact) && (passant.length||proche.length||multi.length);

  let html = '';

  /* No direct banner */
  if(hasNoDirectMsg){
    html += `
    <div class="no-direct-banner">
      <div class="banner-robot">🤖</div>
      <div class="banner-text">
        <h3>Aucun trajet direct trouvé 😞</h3>
        <p>Pas de souci ! Notre IA a trouvé des solutions pour vous aider à atteindre votre destination.</p>
      </div>
      <div class="banner-img">🗺️</div>
    </div>`;
  }

  if(total === 0){
    zone.innerHTML = `
      <div class="state-box">
        <div class="si">🔍</div>
        <h3>Aucun résultat trouvé</h3>
        <p>Essayez d'augmenter la tolérance ou de modifier vos villes.</p>
      </div>`;
    return;
  }

  /* Filter tabs */
  html += `<div class="filter-row" id="ftabs">
    ${passant.length?`<span class="ftab via" onclick="filterTab('via',this)">Passe par votre destination <span class="cnt">${passant.length}</span></span>`:''}
    ${proche.length?`<span class="ftab proche" onclick="filterTab('proche',this)">Trajets proches <span class="cnt">${proche.length}</span></span>`:''}
    ${multi.length?`<span class="ftab combi" onclick="filterTab('combi',this)">Combinaisons <span class="cnt">${multi.length}</span></span>`:''}
  </div>`;

  /* Exact results */
  if(res.length){
    html += res.map(t=>exactCard(t)).join('');
  }

  /* Passe par destination */
  if(passant.length){
    html += `<div class="section-sep sec-green" data-sec="via">
      <div class="section-hdr">
        <i class="fas fa-route" style="color:var(--green);font-size:.8rem"></i>
        <h4>Passe par votre destination</h4>
        <span class="section-badge" style="background:var(--green);color:#000">${passant.length}</span>
      </div>
      ${passant.map(t=>passantCard(t)).join('')}
    </div>`;
  }

  /* Trajets proches */
  if(proche.length){
    html += `<div class="section-sep sec-amber" data-sec="proche">
      <div class="section-hdr">
        <i class="fas fa-location-crosshairs" style="color:var(--amber);font-size:.8rem"></i>
        <h4>Trajets proches de votre destination</h4>
        <span class="section-badge" style="background:var(--amber);color:#000">${proche.length}</span>
      </div>
      ${proche.map(t=>procheCard(t)).join('')}
    </div>`;
  }

  /* Combinaisons */
  if(multi.length){
    html += `<div class="section-sep sec-purple" data-sec="combi">
      <div class="section-hdr">
        <i class="fas fa-shuffle" style="color:var(--purple);font-size:.8rem"></i>
        <h4>Combinaisons intelligentes</h4>
        <span class="section-badge" style="background:var(--purple);color:#fff">${multi.length}</span>
      </div>
      ${multi.map(t=>multiCard(t)).join('')}
    </div>`;
  }

  zone.innerHTML = html;
}

/* ─── FILTER TABS ─── */
let activeFilter = null;

function filterTab(type, el){
  /* Reclic = reset, tout afficher */
  if(activeFilter === type){
    activeFilter = null;
    document.querySelectorAll('.ftab').forEach(t=>{
      t.style.outline=''; t.style.boxShadow=''; t.style.opacity='1';
    });
    document.querySelectorAll('.section-sep').forEach(s=>s.style.display='');
    document.querySelectorAll('#results-zone > .rcard').forEach(c=>c.style.display='');
    document.querySelectorAll('.no-direct-banner').forEach(e=>e.style.display='');
    return;
  }

  activeFilter = type;

  /* Style tabs */
  document.querySelectorAll('.ftab').forEach(t=>{
    t.style.outline='none'; t.style.boxShadow='none'; t.style.opacity='.35';
  });
  el.style.opacity='1';
  el.style.outline='2px solid currentColor';
  el.style.boxShadow='0 0 12px rgba(255,255,255,0.1)';

  /* Cacher la bannière "aucun trajet direct" quand on filtre */
  document.querySelectorAll('.no-direct-banner').forEach(e=>e.style.display='none');

  /* Cacher toutes les cartes exact (hors sections) */
  document.querySelectorAll('#results-zone > .rcard').forEach(c=>c.style.display='none');

  /* Afficher uniquement la section correspondante */
  document.querySelectorAll('.section-sep').forEach(s=>{
    s.style.display = s.dataset.sec===type ? '' : 'none';
  });
}

/* ─── CARD: exact match ─── */
function exactCard(t){
  const score = t.pertinence||0;
  const color = '#22c55e';
  const urlDirect  = t.vehicule_id
    ? `reserver_vehicule.php?vehicule_id=${t.vehicule_id}&trajet_id=${t.id_T}`
    : `reserver_vehicule.php?trajet_id=${t.id_T}`;
  const urlOptimal = `vehicule_optimal.php?trajet_id=${t.id_T}`;

  const vehicleBlock = t.marque ? `
    <div style="margin-top:.75rem;padding:.65rem .85rem;
      background:linear-gradient(135deg,rgba(34,197,94,0.07),rgba(59,130,246,0.05));
      border-radius:10px;border:1px solid rgba(34,197,94,0.2);position:relative;overflow:hidden;">
      <div style="position:absolute;right:-8px;top:-8px;font-size:2.2rem;opacity:.08;pointer-events:none">&#x1F697;</div>
      <div style="display:flex;align-items:center;gap:6px;margin-bottom:.3rem;">
        <span style="font-size:.6rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;
          background:var(--green);color:#000;padding:2px 7px;border-radius:99px;">&#x2605; IA</span>
        <span style="font-size:.8rem;font-weight:700;color:var(--green);">${t.marque} ${t.modele}</span>
      </div>
      ${t.vehicle_raisons && t.vehicle_raisons.length ? `
      <div style="display:flex;flex-wrap:wrap;gap:5px;margin-top:.2rem;">
        ${t.vehicle_raisons.slice(0,2).map(r=>`
          <span style="font-size:.67rem;padding:2px 8px;border-radius:99px;
            background:${r.type==='success'?'rgba(34,197,94,0.12)':r.type==='warning'?'rgba(245,158,11,0.12)':'rgba(255,255,255,0.05)'};
            color:${r.type==='success'?'var(--green)':r.type==='warning'?'var(--amber)':'var(--muted)'};">
            ${r.texte}
          </span>`).join('')}
      </div>` : ''}
    </div>` : '';

  return `
  <div class="rcard green" data-type="exact">
    <div>
      <div class="rcard-route">
        <span class="rcard-city">${t.point_depart}</span>
        <span class="rcard-arrow"><i class="fas fa-arrow-right"></i></span>
        <span class="rcard-city">${t.point_arrive}</span>
      </div>
      <div class="rcard-meta">
        ${t.heure_depart?`<span><i class="fas fa-clock"></i>${t.heure_depart}</span>`:''}
        ${t.capacite?`<span><i class="fas fa-users"></i>${t.capacite} places disponibles</span>`:''}
        ${t.distance_total?`<span><i class="fas fa-road"></i>${t.distance_total} km</span>`:''}
      </div>
      ${vehicleBlock}
    </div>
    <div class="rcard-right">
      ${scoreRing(score,color,'Bon match')}
      <div class="rcard-price">${t.prix_total?parseFloat(t.prix_total).toFixed(2)+' TND':'—'}<small>par personne</small></div>
      <a href="${urlOptimal}" style="
        display:inline-flex;align-items:center;gap:7px;
        padding:.58rem 1.1rem;
        background:linear-gradient(135deg,#16a34a,#22c55e);
        border-radius:10px;color:#fff;text-decoration:none;
        font-size:.8rem;font-weight:700;white-space:nowrap;
        box-shadow:0 4px 18px rgba(34,197,94,0.38);
        transition:opacity .2s,transform .15s;"
        onmouseover="this.style.opacity='.88';this.style.transform='translateY(-1px)'"
        onmouseout="this.style.opacity='1';this.style.transform='none'">
        <i class="fas fa-crown" style="font-size:.75rem;color:#fde68a"></i>
        Meilleur v&#xe9;hicule IA
      </a>
      <a href="${urlDirect}" style="
        font-size:.7rem;color:var(--muted);text-decoration:none;
        display:flex;align-items:center;gap:4px;transition:color .2s;"
        onmouseover="this.style.color='var(--blue-bright)'"
        onmouseout="this.style.color='var(--muted)'">
        <i class="fas fa-arrow-right" style="font-size:.6rem"></i> R&#xe9;server sans s&#xe9;lection
      </a>
    </div>
  </div>`;
}

/* ─── CARD: passe par destination ─── */
function passantCard(t){
  const score = t.pertinence || 57;
  const color = '#22c55e';
  const url   = t.vehicule_id
    ? `reserver_vehicule.php?vehicule_id=${t.vehicule_id}&trajet_id=${t.id_T}`
    : `reserver_vehicule.php?trajet_id=${t.id_T}`;
  return `
  <div class="rcard green" data-type="via">
    <div>
      <div class="rcard-route">
        <span class="rcard-city">${t.point_depart}</span>
        <span class="rcard-arrow"><i class="fas fa-arrow-right"></i></span>
        <span class="rcard-city">${t.point_arrive}</span>
        <span class="via-badge"><i class="fas fa-check-circle"></i>Passe par ${t.point_match}</span>
      </div>
      <div class="rcard-meta">
        ${t.heure_depart?`<span><i class="fas fa-clock"></i>Départ ${t.heure_depart}</span>`:''}
        ${t.heure_arrivee?`<span><i class="fas fa-flag-checkered"></i>Arrivée ${t.heure_arrivee}</span>`:''}
        ${t.capacite?`<span><i class="fas fa-users"></i>${t.capacite} places disponibles</span>`:''}
        ${t.distance_total?`<span><i class="fas fa-road"></i>${t.distance_total} km</span>`:''}
      </div>
    </div>
    <div class="rcard-right">
      ${scoreRing(score,color,'Bon match')}
      <div class="rcard-price">${t.prix_total?parseFloat(t.prix_total).toFixed(2)+' TND':'—'}<small>par personne</small></div>
      ${btnVehiculeIA(t.id_T, url)}
    </div>
  </div>`;
}

/* ─── CARD: proche ─── */
function procheCard(t){
  const score = t.pertinence || 48;
  const color = '#f59e0b';
  const dist  = t.distance ? Math.round(t.distance) : '?';
  const url   = t.vehicule_id
    ? `reserver_vehicule.php?vehicule_id=${t.vehicule_id}&trajet_id=${t.id_T}`
    : `reserver_vehicule.php?trajet_id=${t.id_T}`;
  return `
  <div class="rcard amber" data-type="proche">
    <div>
      <div class="rcard-route">
        <span class="rcard-city">${t.point_depart}</span>
        <span class="rcard-arrow"><i class="fas fa-arrow-right"></i></span>
        <span class="rcard-city">${t.point_arrive}</span>
        <span class="via-badge amber"><i class="fas fa-location-crosshairs"></i>à ${dist} km de votre dest.</span>
      </div>
      <div class="rcard-meta">
        ${t.heure_depart?`<span><i class="fas fa-clock"></i>Départ ${t.heure_depart}</span>`:''}
        ${t.heure_arrivee?`<span><i class="fas fa-flag-checkered"></i>Arrivée ${t.heure_arrivee}</span>`:''}
        ${t.capacite?`<span><i class="fas fa-users"></i>${t.capacite} places disponibles</span>`:''}
        ${t.distance_total?`<span><i class="fas fa-road"></i>${t.distance_total} km</span>`:''}
      </div>
    </div>
    <div class="rcard-right">
      ${scoreRing(score,color,'Match moyen')}
      <div class="rcard-price">${t.prix_total?parseFloat(t.prix_total).toFixed(2)+' TND':'—'}<small>par personne</small></div>
      ${btnVehiculeIA(t.id_T, url)}
    </div>
  </div>`;
}

/* ─── CARD: multi / combinaison ─── */
function multiCard(t){
  const score = t.pertinence || 69;
  const color = '#a855f7';
  const legs  = Array.isArray(t.legs) && t.legs.length >= 2 ? t.legs : null;
  const connPt= t.connection_point || '?';
  const url = t.vehicule_id
    ? `reserver_vehicule.php?vehicule_id=${t.vehicule_id}&trajet_id=${t.id_T}`
    : `reserver_vehicule.php?trajet_id=${t.id_T}`;

  const segHtml = legs ? `
    <div class="segment-row">
      <div class="seg-info">
        <strong>${legs[0].point_depart} → ${legs[0].point_arrive}</strong>
        <span><i class="fas fa-clock" style="font-size:.65rem"></i> ${legs[0].heure_depart||'—'} &nbsp; <i class="fas fa-users" style="font-size:.65rem"></i> ${legs[0].capacite||'?'} places</span>
      </div>
      <div class="seg-conn">
        <i class="fas fa-arrow-right-arrow-left"></i>
        <span>Correspondance</span>
        <span style="color:var(--blue-bright);font-weight:600">⏱ 20 min</span>
        <i class="fas fa-arrow-right-arrow-left"></i>
      </div>
      <div class="seg-info">
        <strong>${legs[1].point_depart} → ${legs[1].point_arrive}</strong>
        <span><i class="fas fa-clock" style="font-size:.65rem"></i> ${legs[1].heure_depart||'—'} &nbsp; <i class="fas fa-users" style="font-size:.65rem"></i> ${legs[1].capacite||'?'} places</span>
      </div>
    </div>
    <div class="seg-total">
      <span><i class="fas fa-clock" style="font-size:.68rem;color:var(--muted)"></i> Durée totale : <strong>${t.duree_totale||'—'}</strong></span>
      <span><i class="fas fa-road" style="font-size:.68rem;color:var(--muted)"></i> Distance totale : <strong>${t.distance_total||'—'} km</strong></span>
      ${t.economie?`<span class="eco"><i class="fas fa-leaf" style="font-size:.68rem"></i> Économie : ${t.economie}%</span>`:''}
    </div>` : `
    <div style="font-size:.8rem;color:var(--muted);margin-top:.5rem">
      Via ${connPt} (solution multi-trajets)
    </div>`;

  return `
  <div class="rcard purple" data-type="combi">
    <div style="width:100%">
      <div class="rcard-route" style="margin-bottom:.4rem">
        <span class="rcard-city">${t.point_depart||'—'}</span>
        <span class="rcard-arrow"><i class="fas fa-arrow-right"></i></span>
        <span class="rcard-city">${t.point_arrive||connPt}</span>
      </div>
      ${segHtml}
    </div>
    <div class="rcard-right">
      ${scoreRing(score,color,'Excellent match')}
      <div class="rcard-price">${t.prix_total?parseFloat(t.prix_total).toFixed(2)+' TND':'—'}<small>prix total</small></div>
      ${btnVehiculeIA(t.id_T, url)}
    </div>
  </div>`;
}

/* ─── BOUTONS VÉHICULE IA (commun) ─── */
function btnVehiculeIA(trajetId, urlDirect){
  return `
    <a href="vehicule_optimal.php?trajet_id=${trajetId}" style="
      display:inline-flex;align-items:center;gap:7px;
      padding:.58rem 1.1rem;
      background:linear-gradient(135deg,#16a34a,#22c55e);
      border-radius:10px;color:#fff;text-decoration:none;
      font-size:.8rem;font-weight:700;white-space:nowrap;
      box-shadow:0 4px 18px rgba(34,197,94,0.38);
      transition:opacity .2s,transform .15s;"
      onmouseover="this.style.opacity='.88';this.style.transform='translateY(-1px)'"
      onmouseout="this.style.opacity='1';this.style.transform='none'">
      <i class="fas fa-crown" style="font-size:.75rem;color:#fde68a"></i>
      Meilleur v&#xe9;hicule IA
    </a>
    <a href="${urlDirect}" style="
      font-size:.7rem;color:var(--muted);text-decoration:none;
      display:flex;align-items:center;gap:4px;transition:color .2s;"
      onmouseover="this.style.color='var(--blue-bright)'"
      onmouseout="this.style.color='var(--muted)'">
      <i class="fas fa-arrow-right" style="font-size:.6rem"></i> R&#xe9;server sans s&#xe9;lection
    </a>`;
}

/* ─── SCORE RING SVG ─── */
function scoreRing(pct, color, label){
  const R = 26, C = 2*Math.PI*R;
  const dash = C*(pct/100);
  return `
  <div class="sring">
    <svg width="68" height="68" viewBox="0 0 68 68">
      <circle cx="34" cy="34" r="${R}" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="5"/>
      <circle cx="34" cy="34" r="${R}" fill="none" stroke="${color}"
        stroke-width="5" stroke-linecap="round"
        stroke-dasharray="${dash.toFixed(1)} ${C.toFixed(1)}"/>
    </svg>
    <div class="sring-num">${pct}<small>%</small></div>
  </div>
  <div class="sring-label" style="color:${color}">${label}</div>`;
}

/* ─── STATES ─── */
function showLoader(){
  document.getElementById('results-zone').innerHTML=`
    <div class="loader"><span></span><span></span><span></span></div>
    <div style="text-align:center;color:var(--muted);font-size:.82rem;margin-top:-.5rem">Analyse IA en cours…</div>`;
}
function showErr(msg){
  document.getElementById('results-zone').innerHTML=`
    <div class="state-box"><div class="si">⚠️</div><h3>Erreur</h3><p>${msg}</p></div>`;
}
function shakeBorder(){
  const s = document.querySelector('.search-panel');
  s.style.outline='2px solid var(--red)';
  setTimeout(()=>s.style.outline='none',800);
}
</script>
</body>
</html>