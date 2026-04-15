// ====================================================
// BASE DE DONNÉES COMPLÈTE DES VILLES TUNISIENNES
// ====================================================
var CITIES = {
  // === Existing cities (your original data) ===
  'tunis':[36.8065,10.1815],'carthage':[36.8528,10.3233],
  'la marsa':[36.8775,10.3222],'sidi bou said':[36.8703,10.3411],
  'la goulette':[36.8183,10.3050],'ariana':[36.8665,10.1647],
  'ben arous':[36.7530,10.2190],'manouba':[36.8097,10.0997],
  'sfax':[34.7406,10.7603],'sfax ville':[34.7406,10.7603],
  'sousse':[35.8283,10.6400],'monastir':[35.7776,10.8262],
  'mahdia':[35.5047,11.0622],'nabeul':[36.4561,10.7376],
  'hammamet':[36.4000,10.6167],'bizerte':[37.2744,9.8739],
  'gabes':[33.8833,10.1000],'medenine':[33.3549,10.5055],
  'tataouine':[32.9210,10.4510],'kairouan':[35.6812,10.0969],
  'gafsa':[34.4311,8.7757],'tozeur':[33.9197,8.1337],
  'nefta':[33.8750,7.8781],'douz':[33.4581,9.0178],
  'kebili':[33.7044,8.9694],'jendouba':[36.5011,8.7803],
  'tabarka':[36.9544,8.7581],'ain draham':[36.7833,8.6833],
  'siliana':[36.0839,9.3714],'makthar':[35.8569,9.2044],
  'beja':[36.7333,9.1833],'nefza':[37.0211,9.0364],
  'kasserine':[35.1667,8.8167],'sbeitla':[35.2353,9.1056],
  'sidi bouzid':[35.0380,9.4841],'regueb':[34.7200,9.5800],
  'zaghouan':[36.4028,10.1422],'ennadhour':[36.3650,10.0800],
  'kef':[36.1822,8.7147],'tajerouine':[35.8952,8.5369],
  'enfidha':[36.1333,10.3833],'hergla':[36.0479,10.4800],
  'msaken':[35.7297,10.5783],'chott meriem':[35.9200,10.5600],
  'akouda':[35.8806,10.5819],'hammam sousse':[35.8617,10.5900],
  'port el kantaoui':[35.8917,10.5967],'skanes':[35.7667,10.7608],
  'ksar hellal':[35.6442,10.8889],'moknine':[35.6333,10.9000],
  'ksour essef':[35.4228,11.0150],'chebba':[35.2381,11.1119],
  'skhira':[34.2967,10.0686],'sfax nord':[34.8000,10.7500],
  'el hamma':[33.8889,9.7939],'mareth':[33.6250,10.3219],
  'zarzis':[33.5083,11.1119],'houmt souk':[33.8758,10.8575],
  'jerba':[33.8133,10.8550],'djerba':[33.8133,10.8550],
  'ajim':[33.7278,10.7528],'guellala':[33.7083,10.8500],
  'midoun':[33.7919,10.9867],'ben gardane':[33.1389,11.2222],
  'remada':[32.3153,10.3914],'dehiba':[32.0069,10.7119],
  'ras jebel':[37.2306,10.1178],'utique':[37.0569,10.0531],
  'menzel bourguiba':[37.1622,9.7900],'mateur':[37.0417,9.6639],
  'sejnane':[37.0583,9.2417],'ghardimaou':[36.4500,8.4333],
  'bou salem':[36.6167,8.9667],'fernana':[36.6500,8.7250],
  'haffouz':[35.6278,9.6694],'el ajem':[35.2919,10.5669],
  'oudhna':[36.5625,10.0953],'hammamet nord':[36.4200,10.5800],
  'bir mcherga':[36.3972,9.9444],'el fahs':[36.3619,9.9047],
  'pont du fahs':[36.3619,9.9047],'grombalia':[36.6167,10.5000],
  'soliman':[36.7000,10.4833],'bou argoub':[36.5417,10.5472],
  'menzel temime':[36.7833,10.9667],'korba':[36.5833,10.8667],
  'kelibia':[36.8500,11.1000],'haouaria':[37.0500,11.0167],
  'sidi ali ben aoun':[35.1906,9.5906],'bir el hafey':[34.7344,9.3194],
  'cebbala':[33.9333,9.5000],'el guettar':[34.3333,8.9667],
  'metlaoui':[34.3244,8.4044],'redeyef':[34.3722,8.1500],
  'moulares':[34.4833,8.2667],'tameghza':[34.2833,7.9500],
  'chebika':[34.2500,7.9167],'ksar ghilane':[32.9833,9.6500],
  'zaafrane':[33.3300,9.4017],'souk lahad':[33.4250,9.0333],
  'el faouar':[33.2292,9.0056],'blidet amor':[32.6250,9.3956],
  'el borma':[31.6944,9.2556],

  // === Newly added cities (your existing additions) ===
  'sakiet ezzit':[34.7817,10.7719],
  'sakiet eddaier':[34.7967,10.7806],
  'thyna':[34.7364,10.6931],
  'gremda':[34.7500,10.7833],
  'agareb':[34.7444,10.5106],
  'jebiniana':[35.0392,10.9083],
  'el hencha':[34.9500,10.5167],
  'menzel chaker':[34.9167,10.3500],
  'bir ali ben khelifa':[34.7333,10.1000],
  'mahrenes':[34.6167,10.5000],
  'kerkennah':[34.7000,11.1833],
  'beni mtir':[36.7342,8.7353],
  'oued melliz':[36.4931,8.5531],
  'tinja':[37.1604,9.7643],
  'hammam lif':[36.7389,10.3058],
  'ezzahra':[36.7444,10.3167],
  'la mohammedia':[36.6745,10.1563],
  'radez':[36.7667,10.2833],
  'megrine':[36.7667,10.2500],
  'fouchana':[36.7000,10.2500],
  'mornag':[36.6833,10.0333],
  'tebourba':[36.8333,10.1000],
  'douar hisher':[36.8000,10.0833],
  'oued ellil':[36.8167,10.0500],
  'mornaguia':[36.7500,10.0167],
  'borj el amri':[36.7167,9.9667],
  'el battan':[36.8000,9.4500],
  'goubellat':[36.5333,9.6667],
  'testour':[36.5500,9.4500],
  'thibar':[36.5333,8.8667],
  'nebeur':[36.5000,8.7833],
  'touiref':[36.2500,8.6000],
  'sakiet sidi youssef':[36.2167,8.3667],
  'kalaat senan':[35.7667,8.4000],
  'foussana':[35.3667,8.6333],
  'thala':[35.5667,8.6833],
  'jedelienne':[35.6167,9.7000],
  'oueslatia':[35.8500,9.6000],
  'el rouhia':[35.8333,9.4000],
  'gaafour':[36.3167,9.3333],
  'kesra':[35.8000,9.3667],
  'el krib':[36.3333,9.9000],
  'bir halima':[35.5500,9.7500],
  'menzel bouzaiane':[35.0167,9.4000],
  'meknassy':[34.6167,9.6000],
  'mazouna':[34.5500,9.8500],
  'souassed':[34.9000,9.5833],
  'el alem':[33.9667,9.9000],
  'chenini':[33.8667,10.2667],
  'matmata':[33.5444,9.9764],
  'ghomrassen':[33.0500,10.3333],
  'beni khedache':[33.2500,10.2000],

  // ========== AJOUTS MANQUANTS (Rades, Mourouj, Grand Tunis, centres-villes) ==========

  // === Rades et ses quartiers ===
  'rades':[36.7667,10.2833],
  'rades centre':[36.7700,10.2800],
  'rades ville':[36.7683,10.2817],
  'rades salines':[36.7800,10.2900],
  'rades meliane':[36.7750,10.2850],
  'rades plage':[36.7725,10.2883],
  'rades port':[36.7650,10.2850],
  'rades mourouj':[36.7600,10.2750],

  // === Mourouj (tous les numéros de 1 à 9) ===
  'mourouj':[36.7400,10.2300],
  'mourouj 1':[36.7350,10.2250],
  'mourouj 2':[36.7383,10.2300],
  'mourouj 3':[36.7417,10.2350],
  'mourouj 4':[36.7450,10.2400],
  'mourouj 5':[36.7483,10.2450],
  'mourouj 6':[36.7517,10.2500],


  // === Autres villes de Ben Arous ===
  'hammam lif centre':[36.7350,10.3000],
  'hammam lif cote bleue':[36.7400,10.3100],
  'ezzahra centre':[36.7450,10.3150],
  'megrine centre':[36.7683,10.2483],
  'mohamedia':[36.6745,10.1563],
  'mornag centre':[36.6850,10.0350],
  'fouchana centre':[36.6983,10.2483],
  'bir el bey':[36.7200,10.2200],
  'bir el bey centre':[36.7183,10.2183],
  'borj cedria':[36.7167,10.2800],
  'borj cedria plage':[36.7200,10.2900],
  'boumhel':[36.6900,10.2600],
  'boumhel el bassatine':[36.6950,10.2650],
  'chouchet':[36.7100,10.2300],
  'cite erriadh ben arous':[36.7300,10.2400],
  'cite ezzouhour ben arous':[36.7350,10.2450],
  'khelidia':[36.7000,10.2400],
  'mhamdia':[36.6700,10.2100],
  'naasan':[36.6900,10.2000],
  'riyadh ben arous':[36.7400,10.2550],
  'sidi amor':[36.7100,10.2600],
  'sidi frenj':[36.6950,10.2300],

  // === Grand Tunis - Quartiers ===
  'bardo':[36.8092,10.1347],
  'le bardo':[36.8092,10.1347],
  'bardo centre':[36.8117,10.1327],
  'el kram':[36.8267,10.3117],
  'el kram centre':[36.8283,10.3133],
  'el kram plage':[36.8300,10.3167],
  'gammarth':[36.9000,10.3167],
  'gammarth centre':[36.8983,10.3133],
  'gammarth plage':[36.9050,10.3200],
  'gammarth superieur':[36.8933,10.3083],
  'carthage byrsa':[36.8533,10.3250],
  'carthage president':[36.8500,10.3200],
  'carthage hannibal':[36.8600,10.3300],
  'carthage salambo':[36.8400,10.3150],
  'carthage amilcar':[36.8450,10.3183],
  'salambo':[36.8383,10.3133],
  'marsa plage':[36.8850,10.3300],
  'marsa erriadh':[36.8700,10.3250],
  'sidi bou said village':[36.8717,10.3400],

  // === Centre-ville Tunis ===
  'tunis centre ville':[36.7983,10.1800],
  'tunis centre':[36.7983,10.1800],
  'bab saadoun':[36.8083,10.1700],
  'bab souika':[36.8025,10.1733],
  'halfaouine':[36.8017,10.1683],
  'el omrane':[36.8117,10.1917],
  'el menzah':[36.8358,10.2083],
  'mutuelleville':[36.8250,10.1950],
  'belvedere':[36.8200,10.1883],
  'lafayette':[36.8125,10.1825],
  'cite olympique':[36.8300,10.2000],
  'ennasr':[36.8825,10.2567],
  'charguia':[36.8475,10.2683],
  'el khadhra':[36.8400,10.2200],

  // === Gouvernorat de l'Ariana ===
  'ariana centre':[36.8633,10.1683],
  'ariana superieure':[36.8733,10.1617],
  'erriadh ariana':[36.8500,10.1750],
  'kalza':[36.8400,10.1450],
  'khezama':[36.8900,10.1600],
  'manihla':[36.8300,10.1400],
  'sidi daoud':[36.8200,10.1350],
  'sidi thabet':[36.8000,10.1000],
  'soukra':[36.8900,10.1900],

  // === Gouvernorat de la Manouba ===
  'manouba centre':[36.8117,10.0977],
  'manouba universite':[36.8200,10.1050],
  'jedaida':[36.8300,10.0800],
  'den den':[36.7900,10.0900],
  'kef ennasr':[36.8150,10.0700],

  // === Villes supplémentaires (Cap Bon / Nabeul) ===
  'dar chaabane':[36.4667,10.7500],
  'dar chaabane el fehri':[36.4667,10.7500],
  'beni khiar':[36.4667,10.7833],
  'el maamoura':[36.4667,10.8000],
  'menzel bouzelfa':[36.6833,10.5833],
  'takelsa':[36.7833,10.6333],
  'tazarka':[36.6000,10.8000],

  // === Villes supplémentaires (Sousse) ===
  'kalâa kebira':[35.8500,10.5333],
  'kalâa seghira':[35.8167,10.5500],
  'sidi el heni':[35.8667,10.4667],
  'messaadine':[35.8000,10.5500],
  'zaouiet sousse':[35.7833,10.6167],

  // === Villes supplémentaires (Monastir) ===
  'sahline':[35.7500,10.7167],
  'ouerdanine':[35.7167,10.6833],
  'bembla':[35.6833,10.8000],
  'bembla mnara':[35.6833,10.8000],
  'jammel':[35.6333,10.7667],
  'sayada':[35.6667,10.9000],
  'lamta':[35.6833,10.8833],

  // === Villes supplémentaires (Mahdia) ===
  'rejiche':[35.4667,11.0333],
  'bou merdes':[35.3833,11.0333],
  'hiboun':[35.3833,11.0500],
  'el bradâa':[35.3833,10.9833],

  // === Mides (Sahara - manquant) ===
  'midès':[34.2167,7.8833]
};

const API_URL = "../../controller/TrajetController.php";
const DEST_API = "../../controller/DestinationController.php";

// Application state
let editId = null;
let curResId = null;
let arretCnt = 0;
let allTrips = [];
let allDests = [];
let sortKey = '';
let sortDir = 1;
let selectedStop = null;

// ====================================================
// FONCTIONS UTILES
// ====================================================

/* Calcul distance Haversine */
function calcDistance(c1, c2) {
  const a = CITIES[(c1 || '').toLowerCase().trim()];
  const b = CITIES[(c2 || '').toLowerCase().trim()];
  if (!a || !b) return null;
  const R = 6371;
  const dLat = (b[0] - a[0]) * Math.PI / 180;
  const dLon = (b[1] - a[1]) * Math.PI / 180;
  const x = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(a[0] * Math.PI/180) * Math.cos(b[0] * Math.PI/180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
  return Math.round(R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1-x)));
}

/* Calcul prix proportionnel */
function calcPrixArret(distArret, distTotal, prixTotal) {
  if (!distArret || !distTotal || !prixTotal) return null;
  return Math.round((distArret / distTotal) * prixTotal * 100) / 100;
}

/* TOAST */
function toast(msg, ok) {
  const t = document.createElement('div');
  t.className = 'toast ' + (ok !== false ? 't-ok' : 't-err');
  t.innerHTML = '<i class="fas ' + (ok !== false ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3200);
}

/* Escape pour JS */
function escJ(s) {
  return (s || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

/* Escape pour HTML */
function escH(s) {
  return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ====================================================
// VALIDATION DES SAISIES
// ====================================================

function validateVille(ville, nomChamp) {
  if (!ville || ville.trim() === '') {
    toast('Veuillez saisir la ' + nomChamp, false);
    return false;
  }
  const villeLower = ville.trim().toLowerCase();
  if (!CITIES[villeLower]) {
    toast('La ville "' + ville + '" n\'est pas reconnue dans notre base. Veuillez choisir une ville valide.', false);
    return false;
  }
  return true;
}

function validatePrix(prix, nomChamp) {
  if (!prix || prix.trim() === '') {
    toast('Veuillez saisir le ' + nomChamp, false);
    return false;
  }
  const prixNum = parseFloat(prix);
  if (isNaN(prixNum) || prixNum <= 0) {
    toast('Le ' + nomChamp + ' doit être un nombre positif', false);
    return false;
  }
  return true;
}

function validateArrets(arrets, depart, arrivee) {
  const nomsArrets = arrets.map(a => a.nom.toLowerCase().trim());
  const departLower = depart.toLowerCase().trim();
  const arriveeLower = arrivee.toLowerCase().trim();
  
  // Vérifier doublons entre arrêts
  const seen = new Set();
  for (const nom of nomsArrets) {
    if (seen.has(nom)) {
      toast('Les points d\'arrêt ne peuvent pas être en double', false);
      return false;
    }
    seen.add(nom);
  }
  
  // Vérifier conflits avec départ et arrivée
  for (const nom of nomsArrets) {
    if (nom === departLower) {
      toast('Un point d\'arrêt ne peut pas être identique au départ', false);
      return false;
    }
    if (nom === arriveeLower) {
      toast('Un point d\'arrêt ne peut pas être identique à la destination', false);
      return false;
    }
  }
  
  // Vérifier que chaque arrêt existe dans la base
  for (const arret of arrets) {
    if (!CITIES[arret.nom.toLowerCase().trim()]) {
      toast('La ville d\'arrêt "' + arret.nom + '" n\'est pas reconnue', false);
      return false;
    }
  }
  
  return true;
}

// ====================================================
// FONCTIONS DE GESTION DES ARRÊTS
// ====================================================

function ajouterArret() {
  arretCnt++;
  const id = arretCnt;
  const div = document.createElement('div');
  div.className = 'arret-item';
  div.id = 'arret-' + id;
  div.innerHTML = `
    <div class="arret-row">
      <div class="igrp">
        <i class="ic fas fa-map-pin"></i>
        <input type="text" id="an_${id}" list="cityList" placeholder="Nom de la ville" oninput="window.app.updateArretSingle(${id})">
      </div>
      <button type="button" class="btn-rm-arret" onclick="window.app.rmArret(${id})"><i class="fas fa-times"></i></button>
    </div>
    <div class="arret-info">
      <span><i class="fas fa-road"></i> <span id="ad_${id}">Distance auto</span></span>
      <span id="apb_${id}"></span>
    </div>
    <div class="arret-prix-row">
      <label style="font-size:.73rem;color:var(--muted);">Prix (DT) :</label>
      <div style="position:relative;flex:1;">
        <i style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.72rem;" class="fas fa-coins"></i>
        <input type="number" class="arret-prix-inp" id="ap_${id}" placeholder="0.00" min="0" step="0.5">
      </div>
    </div>
    <input type="hidden" id="av_${id}" value="">
  `;
  document.getElementById('arrets-list').appendChild(div);
}

function rmArret(id) {
  const el = document.getElementById('arret-' + id);
  if (el) el.remove();
}

function updateArretSingle(id) {
  const dep = document.getElementById('depart').value;
  const distTotal = parseFloat(document.getElementById('distance').value || 0);
  const prixTotal = parseFloat(document.getElementById('prix').value || 0);
  const nom = (document.getElementById('an_' + id) || {}).value || '';
  const dEl = document.getElementById('ad_' + id);
  const hEl = document.getElementById('av_' + id);
  const apEl = document.getElementById('ap_' + id);
  const apbEl = document.getElementById('apb_' + id);
  
  if (dep && nom) {
    const d = calcDistance(dep, nom);
    if (d) {
      if (dEl) dEl.textContent = d + ' km depuis départ';
      if (hEl) hEl.value = d;
      if (distTotal && prixTotal) {
        const pAuto = calcPrixArret(d, distTotal, prixTotal);
        if (apEl && !apEl._manual) apEl.value = pAuto;
        if (apbEl) apbEl.innerHTML = '<span class="prix-auto-badge"><i class="fas fa-magic" style="font-size:.62rem;margin-right:3px;"></i>' + pAuto + ' DT auto</span>';
      } else {
        if (apbEl) apbEl.innerHTML = '';
      }
      return;
    }
  }
  if (dEl) dEl.textContent = 'Distance auto';
  if (hEl) hEl.value = '';
  if (apbEl) apbEl.innerHTML = '';
}

function updateAllArretPrix() {
  document.querySelectorAll('.arret-item').forEach(item => {
    const id = item.id.split('-')[1];
    updateArretSingle(id);
  });
}

function getArrets() {
  const list = [];
  let ord = 1;
  document.querySelectorAll('.arret-item').forEach(item => {
    const id = item.id.split('-')[1];
    const nom = (document.getElementById('an_' + id) || {}).value || '';
    const dist = parseFloat((document.getElementById('av_' + id) || {}).value || 0);
    const prix = parseFloat((document.getElementById('ap_' + id) || {}).value || 0);
    if (nom.trim()) {
      list.push({
        nom: nom.trim(),
        ordre: ord++,
        distance: isNaN(dist) ? 0 : dist,
        prix: isNaN(prix) ? 0 : prix
      });
    }
  });
  return list;
}

// Marquer prix manuel
document.addEventListener('change', function(e) {
  if (e.target && e.target.id && e.target.id.startsWith('ap_')) {
    e.target._manual = true;
  }
});

// ====================================================
// GESTION DES TRAJETS
// ====================================================

function onRouteChange() {
  const dep = document.getElementById('depart').value;
  const arr = document.getElementById('arrivee').value;
  const badge = document.getElementById('distBadge');
  if (dep && arr) {
    const d = calcDistance(dep, arr);
    if (d) {
      document.getElementById('distance').value = d;
      document.getElementById('distText').textContent = 'Distance : ' + d + ' km';
      badge.style.display = 'flex';
      updateAllArretPrix();
      return;
    }
  }
  document.getElementById('distance').value = '';
  badge.style.display = 'none';
}

function ajouterTrajet() {
  const depart = (document.getElementById('depart').value || '').trim();
  const arrivee = (document.getElementById('arrivee').value || '').trim();
  const prix = (document.getElementById('prix').value || '').trim();
  const distance = document.getElementById('distance').value;
  
  // Validation des saisies
  if (!validateVille(depart, 'ville de départ')) return;
  if (!validateVille(arrivee, 'ville d\'arrivée')) return;
  if (!validatePrix(prix, 'prix total')) return;
  
  const prixNum = parseFloat(prix);
  
  if (depart.toLowerCase() === arrivee.toLowerCase()) {
    toast('Départ et arrivée doivent être différents !', false);
    return;
  }
  
  const arrets = getArrets();
  if (!validateArrets(arrets, depart, arrivee)) return;
  
  const payload = {
    depart: depart,
    arrivee: arrivee,
    prix_total: prixNum,
    distance_total: parseFloat(distance) || 0,
    arrets: arrets
  };
  
  const method = editId ? 'PUT' : 'POST';
  if (editId) payload.id = editId;
  
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
  
  fetch(API_URL, {
    method: method,
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(r => {
      const ct = r.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        return r.text().then(txt => { throw new Error(txt.substring(0, 120)); });
      }
      return r.json();
    })
    .then(data => {
      toast(data.message || (editId ? 'Trajet modifié' : 'Trajet publié'));
      chargerTrajets();
      resetForm();
    })
    .catch(e => toast(e.message, false))
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i>' + (editId ? ' Mettre à jour' : ' Publier le trajet');
    });
}

function modifierTrajet(id, dep, arr, prix) {
  editId = id;
  document.getElementById('depart').value = dep;
  document.getElementById('arrivee').value = arr;
  document.getElementById('prix').value = prix;
  onRouteChange();
  document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Mettre à jour';
  switchMode('add');
  document.getElementById('arrets-list').innerHTML = '';
  arretCnt = 0;

  // Charger les arrêts DIRECTEMENT depuis l'API (ne pas dépendre de allDests)
  fetch(DEST_API)
    .then(r => r.json())
    .then(dests => {
      allDests = dests; // mise à jour globale aussi
      const arrets = dests
        .filter(d => d.trajet_id == id && d.ordre != 999)
        .sort((a, b) => parseInt(a.ordre) - parseInt(b.ordre));

      arrets.forEach(arret => {
        ajouterArret();
        const aid = arretCnt;
        const nomEl  = document.getElementById('an_' + aid);
        const prixEl = document.getElementById('ap_' + aid);
        if (nomEl) nomEl.value = arret.nom || arret.descente || '';
        if (prixEl) { prixEl.value = arret.prix || ''; prixEl._manual = true; }
        updateArretSingle(aid);
      });
    })
    .catch(() => toast('Impossible de charger les arrêts existants', false));

  document.querySelector('.fcard').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
  editId = null;
  document.getElementById('depart').value = '';
  document.getElementById('arrivee').value = '';
  document.getElementById('prix').value = '';
  document.getElementById('distance').value = '';
  document.getElementById('distBadge').style.display = 'none';
  document.getElementById('arrets-list').innerHTML = '';
  arretCnt = 0;
  document.getElementById('submitBtn').innerHTML = '<i class="fas fa-paper-plane"></i> Publier le trajet';
}

// ====================================================
// RECHERCHE ET AFFICHAGE
// ====================================================

function rechercherTrajet() {
  const dep = (document.getElementById('searchDepart').value || '').toLowerCase();
  const arr = (document.getElementById('searchArrivee').value || '').toLowerCase();
  renderTrajets(allTrips.filter(t => 
    (!dep || (t.point_depart || '').toLowerCase().includes(dep)) &&
    (!arr || (t.point_arrive || '').toLowerCase().includes(arr))
  ));
}

function filterTable() {
  applySort();
}

function applySort() {
  const val = (document.getElementById('sortSelect').value || '');
  const search = (document.getElementById('tableSearch').value || '').toLowerCase();
  let data = allTrips.slice().filter(t => 
    !search || 
    (t.point_depart || '').toLowerCase().includes(search) || 
    (t.point_arrive || '').toLowerCase().includes(search)
  );
  
  const sortFunctions = {
    depart_asc: (a, b) => (a.point_depart || '').localeCompare(b.point_depart || ''),
    depart_desc: (a, b) => (b.point_depart || '').localeCompare(a.point_depart || ''),
    arrivee_asc: (a, b) => (a.point_arrive || '').localeCompare(b.point_arrive || ''),
    prix_asc: (a, b) => parseFloat(a.prix_total || 0) - parseFloat(b.prix_total || 0),
    prix_desc: (a, b) => parseFloat(b.prix_total || 0) - parseFloat(a.prix_total || 0),
    dist_asc: (a, b) => parseFloat(a.distance_total || 0) - parseFloat(b.distance_total || 0),
    dist_desc: (a, b) => parseFloat(b.distance_total || 0) - parseFloat(a.distance_total || 0)
  };
  
  if (sortFunctions[val]) data.sort(sortFunctions[val]);
  renderTrajets(data);
}

function sortBy(key) {
  if (sortKey === key) sortDir *= -1;
  else { sortKey = key; sortDir = 1; }
  
  document.querySelectorAll('.si').forEach(el => {
    el.className = 'fas fa-sort si';
  });
  
  const iconMap = {
    id: 'si-id',
    depart: 'si-depart',
    arrivee: 'si-arrivee',
    prix: 'si-prix',
    distance: 'si-distance'
  };
  
  const el = document.getElementById(iconMap[key]);
  if (el) el.className = 'fas fa-sort-' + (sortDir > 0 ? 'up' : 'down') + ' si on';
  
  const search = (document.getElementById('tableSearch').value || '').toLowerCase();
  let data = allTrips.slice().filter(t => 
    !search || 
    (t.point_depart || '').toLowerCase().includes(search) || 
    (t.point_arrive || '').toLowerCase().includes(search)
  );
  
  data.sort((a, b) => {
    let va, vb;
    if (key === 'id') { va = parseInt(a.id_T); vb = parseInt(b.id_T); }
    else if (key === 'depart') return sortDir * (a.point_depart || '').localeCompare(b.point_depart || '');
    else if (key === 'arrivee') return sortDir * (a.point_arrive || '').localeCompare(b.point_arrive || '');
    else if (key === 'prix') { va = parseFloat(a.prix_total || 0); vb = parseFloat(b.prix_total || 0); }
    else if (key === 'distance') { va = parseFloat(a.distance_total || 0); vb = parseFloat(b.distance_total || 0); }
    return sortDir * (va - vb);
  });
  
  renderTrajets(data);
}

function renderTrajets(data) {
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
          <button class="abtn abtn-edit" title="Modifier" onclick="window.app.modifierTrajet(${t.id_T},'${escJ(t.point_depart)}','${escJ(t.point_arrive)}',${parseFloat(t.prix_total || t.prix || 0)})"><i class="fas fa-edit"></i></button>
          <button class="abtn abtn-del" title="Supprimer" onclick="window.app.supprimerTrajet(${t.id_T})"><i class="fas fa-trash"></i></button>
          <button class="abtn abtn-res" title="Réserver" onclick="window.app.reserverTrajet(${t.id_T})"><i class="fas fa-ticket-alt"></i></button>
        </div></td>
      </tr>
    `;
  }).join('');
}

// ====================================================
// RÉSERVATION
// ====================================================

function reserverTrajet(id) {
  curResId = id;
  selectedStop = null;
  const old = document.getElementById('resRow');
  if (old) old.remove();
  
  const trip = allTrips.find(t => t.id_T == id);
  if (!trip) return;
  
  const arrets = allDests.filter(d => d.trajet_id == id && d.ordre != 999).sort((a, b) => parseInt(a.ordre) - parseInt(b.ordre));
  const prixFinal = parseFloat(trip.prix_total || trip.prix || 0);
  const distFinal = parseFloat(trip.distance_total || 0);
  
  let stopsHtml = '';
  
  arrets.forEach(arret => {
    const dist = parseFloat(arret.distance || 0);
    let prix = parseFloat(arret.prix || 0);
    if (!prix && dist && distFinal && prixFinal) {
      prix = Math.round((dist / distFinal) * prixFinal * 100) / 100;
    }
    stopsHtml += `
      <div class="stop-card" data-nom="${escH(arret.nom || arret.descente || '')}" data-prix="${prix}" data-dist="${dist}" onclick="window.app.selectStop(this)">
        <div class="sc-check"><i class="fas fa-check"></i></div>
        <div class="sc-name">${escH(arret.nom || arret.descente || 'Arrêt')}</div>
        <div class="sc-dist"><i class="fas fa-road"></i>${dist > 0 ? dist + ' km' : 'Distance n/a'}</div>
        <div class="sc-prix">${prix > 0 ? prix.toFixed(2) + ' DT' : 'Prix libre'}</div>
      </div>
    `;
  });
  
  stopsHtml += `
    <div class="stop-card" data-nom="${escH(trip.point_arrive || '')}" data-prix="${prixFinal}" data-dist="${distFinal}" onclick="window.app.selectStop(this)">
      <div class="sc-final">Destination finale</div>
      <div class="sc-check"><i class="fas fa-check"></i></div>
      <div class="sc-name">${escH(trip.point_arrive || '—')}</div>
      <div class="sc-dist"><i class="fas fa-road"></i>${distFinal > 0 ? distFinal + ' km' : 'Distance n/a'}</div>
      <div class="sc-prix">${prixFinal.toFixed(2)} DT</div>
    </div>
  `;
  
  const tbody = document.getElementById('resultats');
  const rows = tbody.querySelectorAll('tr');
  let target = null;
  rows.forEach(r => {
    if (r.querySelector('.chip') && r.querySelector('.chip').textContent === '#' + id) target = r;
  });
  
  const row = document.createElement('tr');
  row.id = 'resRow';
  row.className = 'resa-row';
  const cell = row.insertCell(0);
  cell.colSpan = 6;
  cell.innerHTML = `
    <div class="resa-box">
      <h3><i class="fas fa-ticket-alt"></i> Réservation — ${escH(trip.point_depart || '')}  <i class="fas fa-arrow-right" style="font-size:.75rem;opacity:.6;"></i>  ${escH(trip.point_arrive || '')}</h3>
      <p style="font-size:.78rem;color:var(--muted);margin-bottom:.9rem;">Choisissez votre point de descente :</p>
      <div class="stops-grid" id="stopsGrid">${stopsHtml}</div>
      <div class="resa-confirm-row" id="resaConfirmRow" style="display:none;">
        <div class="resa-selected-info">
          <span class="rsi-name" id="rsiName">—</span>
          <span class="rsi-price" id="rsiPrice">0.00 DT</span>
        </div>
        <button class="btn-confirm" onclick="window.app.confirmerReservation()"><i class="fas fa-check"></i> Confirmer</button>
        <button class="btn-cancel-r" onclick="window.app.annulerReservation()"><i class="fas fa-times"></i></button>
      </div>
      <div style="margin-top:.6rem;display:flex;justify-content:flex-end;">
        <button class="btn-cancel-r" onclick="window.app.annulerReservation()" style="font-size:.76rem;">Annuler</button>
      </div>
    </div>
  `;
  
  if (target) target.insertAdjacentElement('afterend', row);
  else tbody.appendChild(row);
}

function selectStop(el) {
  document.querySelectorAll('.stop-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  selectedStop = {
    nom: el.dataset.nom,
    prix: parseFloat(el.dataset.prix || 0),
    dist: parseFloat(el.dataset.dist || 0)
  };
  document.getElementById('rsiName').textContent = selectedStop.nom;
  document.getElementById('rsiPrice').textContent = selectedStop.prix.toFixed(2) + ' DT';
  document.getElementById('resaConfirmRow').style.display = 'flex';
}

function confirmerReservation() {
  if (!selectedStop) {
    toast('Choisissez un point de descente !', false);
    return;
  }
  if (!curResId) {
    toast('Aucun trajet sélectionné', false);
    return;
  }
  
  const payload = {
    trajet_id: curResId,
    descente: selectedStop.nom,
    distance: selectedStop.dist,
    prix:     selectedStop.prix 
  };
  
  fetch(DEST_API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
    .then(r => r.json())
    .then(data => {
      toast(data.message || 'Réservation confirmée pour ' + selectedStop.nom + ' — ' + selectedStop.prix.toFixed(2) + ' DT');
      annulerReservation();
    })
    .catch(e => toast('Erreur : ' + e.message, false));
}

function annulerReservation() {
  const r = document.getElementById('resRow');
  if (r) r.remove();
  curResId = null;
  selectedStop = null;
}

// ====================================================
// CHARGEMENT INITIAL
// ====================================================

function switchMode(m) {
  document.querySelectorAll('.tab-btn')[0].classList.toggle('active', m === 'add');
  document.querySelectorAll('.tab-btn')[1].classList.toggle('active', m === 'search');
  document.getElementById('addForm').style.display = m === 'add' ? 'block' : 'none';
  document.getElementById('searchForm').style.display = m === 'search' ? 'block' : 'none';
}

function chargerTrajets() {
  fetch(API_URL)
    .then(r => r.json())
    .then(data => { allTrips = data; applySort(); })
    .catch(e => toast('Erreur chargement : ' + e.message, false));
  
  fetch(DEST_API)
    .then(r => r.json())
    .then(data => { allDests = data; })
    .catch(() => {});
}

// Remplir datalist
(function() {
  const dl = document.getElementById('cityList');
  Object.keys(CITIES).forEach(c => {
    const o = document.createElement('option');
    o.value = c.charAt(0).toUpperCase() + c.slice(1);
    dl.appendChild(o);
  });
})();

// Initialisation
window.onload = () => {
  chargerTrajets();
};

function supprimerTrajet(id) {
  if (!confirm('Supprimer ce trajet et ses destinations ?')) return;
  fetch(API_URL + '?id=' + id, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' }
  })
    .then(r => r.json())
    .then(data => {
      toast(data.message || 'Trajet supprimé');
      chargerTrajets();
    })
    .catch(e => toast('Erreur : ' + e.message, false));
}

// Exposer les fonctions nécessaires globalement
window.app = {
  switchMode,
  onRouteChange,
  ajouterArret,
  rmArret,
  updateArretSingle,
  updateAllArretPrix,
  ajouterTrajet,
  modifierTrajet,
  supprimerTrajet, 
  rechercherTrajet,
  filterTable,
  applySort,
  sortBy,
  reserverTrajet,
  selectStop,
  confirmerReservation,
  annulerReservation
};