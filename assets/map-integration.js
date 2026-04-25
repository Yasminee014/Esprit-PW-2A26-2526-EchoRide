/* ====================================================
   ECORIDE — MAP INTEGRATION (Leaflet + OpenStreetMap)
   À insérer dans user.js OU dans un fichier séparé
   chargé après user.js
   ==================================================== */

/* ── CONFIG ── */
const MAP_TILE = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const MAP_ATTR = '© <a href="https://openstreetmap.org">OpenStreetMap</a>';
const NOMINATIM = 'https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1';
const NOMINATIM_SEARCH = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=tn';

/* ── STATE MAP ── */
let _mapModal   = null; // instance Leaflet pour le formulaire
let _mapView    = null; // instance Leaflet pour la visu
let _mapState   = { mode: null }; // mode actif: 'depart'|'arrivee'|'arret_N'
let _formMarkers= {};  // { depart: L.marker, arrivee: L.marker, arrets: [] }
let _polylineForm = null;
let _viewModal  = null;
let _viewMap    = null;

/* ── COULEURS MARKERS ── */
const MARKER_ICONS = {
  depart  : { color: '#27ae60', icon: '🟢', label: 'Départ' },
  arrivee : { color: '#e74c3c', icon: '🔴', label: 'Arrivée' },
  arret   : { color: '#f1c40f', icon: '🟡', label: 'Arrêt' },
  resa    : { color: '#9b59b6', icon: '🟣', label: 'Réservation' }
};

function makeIcon(type) {
  const c = MARKER_ICONS[type] || MARKER_ICONS.arret;
  return L.divIcon({
    className: '',
    html: `<div style="
      width:32px;height:32px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);
      background:${c.color};border:3px solid #fff;
      box-shadow:0 2px 8px rgba(0,0,0,.4);
    "></div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -36]
  });
}

/* ====================================================
   MODAL MAP — FORMULAIRE (sélection des points)
   ==================================================== */

function createMapModal() {
  if (document.getElementById('ecoMapModal')) return;
  const m = document.createElement('div');
  m.id = 'ecoMapModal';
  m.innerHTML = `
    <div id="ecoMapOverlay" onclick="closeMapModal()"></div>
    <div id="ecoMapPanel">
      <div id="ecoMapHeader">
        <span id="ecoMapTitle"><i class="fas fa-map-marked-alt"></i> Choisir sur la carte</span>
        <button onclick="closeMapModal()" id="ecoMapClose"><i class="fas fa-times"></i></button>
      </div>
      <div id="ecoMapToolbar">
        <button class="maptb" id="tb-depart"  onclick="setMapMode('depart')" title="Cliquer sur la carte pour choisir le départ">
          <i class="fas fa-location-dot" style="color:#27ae60;"></i> Départ
        </button>
        <button class="maptb" id="tb-arrivee" onclick="setMapMode('arrivee')" title="Cliquer sur la carte pour choisir la destination">
          <i class="fas fa-flag-checkered" style="color:#e74c3c;"></i> Destination
        </button>
        <button class="maptb" id="tb-arret"   onclick="setMapMode('arret')" title="Cliquer sur la carte pour ajouter un arrêt">
          <i class="fas fa-map-pin" style="color:#f1c40f;"></i> + Arrêt
        </button>
        <button class="maptb maptb-danger" id="tb-clear" onclick="clearMapPoints()">
          <i class="fas fa-trash"></i> Effacer tout
        </button>
      </div>
      <div id="ecoMapHint">
        <i class="fas fa-info-circle"></i>
        Sélectionnez un mode puis cliquez sur la carte
      </div>
      <div id="ecoMapContainer"></div>
      <div id="ecoMapPointsList"></div>
      <div id="ecoMapFooter">
        <button id="ecoMapApply" onclick="applyMapToForm()">
          <i class="fas fa-check"></i> Appliquer au formulaire
        </button>
        <button id="ecoMapCancel" onclick="closeMapModal()">Annuler</button>
      </div>
    </div>
  `;
  document.body.appendChild(m);
  injectMapStyles();
}

function injectMapStyles() {
  if (document.getElementById('ecoMapStyles')) return;
  const s = document.createElement('style');
  s.id = 'ecoMapStyles';
  s.textContent = `
    #ecoMapModal {
  position:fixed;
  top:64px;          /* hauteur exacte de ta navbar */
  left:0; right:0; bottom:0;
  z-index:99;        /* sous la navbar (z-index:100) */
  display:none; align-items:center; justify-content:center;
}
    #ecoMapModal.open { display:flex; }
    #ecoMapOverlay {
  position:absolute; inset:0;
  background:rgba(5,15,30,.7); backdrop-filter:blur(4px);
  top:0;   /* relatif à #ecoMapModal qui commence déjà à 64px */
}
    #ecoMapPanel {
      position:relative;z-index:1;
      width:min(96vw,900px);max-height:calc(92vh - 64px);
      background:linear-gradient(145deg,#0D1F3A,#0A1628);
      border:1px solid rgba(97,179,250,.3);
      border-radius:18px;overflow:hidden;
      display:flex;flex-direction:column;
      box-shadow:0 24px 60px rgba(0,0,0,.7);
      animation:mapIn .28s ease;
    }
    @keyframes mapIn { from{opacity:0;transform:scale(.94)} to{opacity:1;transform:scale(1)} }
    #ecoMapHeader {
      display:flex;align-items:center;justify-content:space-between;
      padding:.9rem 1.3rem;
      background:rgba(25,118,210,.18);
      border-bottom:1px solid rgba(97,179,250,.2);
    }
    #ecoMapTitle { font-size:.95rem;font-weight:600;color:#fff;display:flex;align-items:center;gap:8px; }
    #ecoMapClose {
      width:32px;height:32px;border-radius:50%;
      border:1px solid rgba(255,255,255,.2);
      background:rgba(255,255,255,.06);color:#fff;cursor:pointer;
      display:flex;align-items:center;justify-content:center;font-size:.8rem;
      transition:all .2s;
    }
    #ecoMapClose:hover { background:rgba(231,76,60,.3);border-color:#e74c3c; }
    #ecoMapToolbar {
      display:flex;gap:.5rem;padding:.7rem 1rem;
      background:rgba(0,0,0,.2);border-bottom:1px solid rgba(97,179,250,.1);
      flex-wrap:wrap;
    }
    .maptb {
      padding:.4rem .9rem;border-radius:20px;border:1px solid rgba(97,179,250,.3);
      background:rgba(255,255,255,.05);color:rgba(255,255,255,.8);
      font-size:.8rem;cursor:pointer;font-family:inherit;
      display:flex;align-items:center;gap:6px;transition:all .2s;
    }
    .maptb:hover { background:rgba(97,179,250,.15);color:#fff; }
    .maptb.active { background:rgba(25,118,210,.35);color:#fff;border-color:var(--blue-light,#61B3FA); }
    .maptb-danger { border-color:rgba(231,76,60,.3);color:rgba(231,76,60,.8); }
    .maptb-danger:hover { background:rgba(231,76,60,.18);color:#e74c3c; }
    #ecoMapHint {
      padding:.45rem 1rem;font-size:.76rem;color:rgba(255,255,255,.5);
      display:flex;align-items:center;gap:6px;
      background:rgba(97,179,250,.04);border-bottom:1px solid rgba(97,179,250,.08);
    }
    #ecoMapHint i { color:#61B3FA; }
    #ecoMapContainer { flex:1;min-height:340px;max-height:55vh; }
    .leaflet-container { background:#0A1628!important; }
    #ecoMapPointsList {
      padding:.6rem 1rem;max-height:160px;overflow-y:auto;
      border-top:1px solid rgba(97,179,250,.12);
      display:flex;flex-direction:column;gap:.4rem;
    }
    .map-point-item {
      display:flex;align-items:center;gap:.5rem;
      background:rgba(255,255,255,.04);border:1px solid rgba(97,179,250,.15);
      border-radius:9px;padding:.4rem .7rem;font-size:.78rem;
    }
    .map-point-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0; }
    .map-point-name { flex:1;color:#fff; }
    .map-point-price { color:#61B3FA;font-weight:600;white-space:nowrap; }
    .map-point-del {
      background:rgba(231,76,60,.12);color:#e74c3c;
      border:none;border-radius:5px;width:22px;height:22px;
      cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.65rem;
      transition:all .2s;
    }
    .map-point-del:hover { background:rgba(231,76,60,.3); }
    #ecoMapFooter {
      display:flex;gap:.6rem;justify-content:flex-end;padding:.8rem 1.2rem;
      border-top:1px solid rgba(97,179,250,.15);background:rgba(0,0,0,.2);
    }
    #ecoMapApply {
      padding:.6rem 1.4rem;
      background:linear-gradient(135deg,#1976D2,#61B3FA);
      border:none;border-radius:10px;color:#fff;font-family:inherit;
      font-weight:700;font-size:.85rem;cursor:pointer;
      display:flex;align-items:center;gap:7px;transition:opacity .2s;
    }
    #ecoMapApply:hover { opacity:.88; }
    #ecoMapCancel {
      padding:.6rem 1.1rem;background:rgba(255,255,255,.06);
      border:1px solid rgba(97,179,250,.2);border-radius:10px;color:var(--grey,#A7A9AC);
      font-family:inherit;font-size:.85rem;cursor:pointer;transition:all .2s;
    }
    #ecoMapCancel:hover { background:rgba(255,255,255,.1);color:#fff; }

    /* Prix modal */
    #pricePickModal {
    position:fixed;
  top:64px; left:0; right:0; bottom:0;
  z-index:98;    
      position:fixed;inset:0;z-index:9999;display:none;
      align-items:center;justify-content:center;
    }
    #pricePickModal.open { display:flex; }
    #pricePickOverlay { position:absolute;inset:0;background:rgba(0,0,0,.5); }
    #pricePickBox {
      position:relative;z-index:1;
      background:linear-gradient(145deg,#0D1F3A,#0A1628);
      border:1px solid rgba(97,179,250,.35);border-radius:14px;
      padding:1.5rem;min-width:280px;
      box-shadow:0 12px 40px rgba(0,0,0,.6);
      animation:mapIn .2s ease;
    }
    #pricePickBox h3 { font-size:.9rem;margin-bottom:.8rem;color:#fff;display:flex;align-items:center;gap:8px; }
    #pricePickBox input {
      width:100%;padding:.6rem .9rem .6rem 2rem;
      border-radius:9px;border:1px solid rgba(97,179,250,.3);
      background:rgba(255,255,255,.07);color:#fff;
      font-family:inherit;font-size:.88rem;margin-bottom:.8rem;
    }
    #pricePickBox input:focus { outline:none;border-color:#61B3FA; }
    .pp-icon { position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#A7A9AC;font-size:.8rem; }
    #pricePickBox .pp-row { display:flex;gap:.5rem; }
    #pricePickOk, #pricePickSkip {
      flex:1;padding:.55rem;border-radius:9px;cursor:pointer;
      font-family:inherit;font-size:.83rem;transition:all .2s;border:none;
    }
    #pricePickOk { background:linear-gradient(135deg,#1976D2,#61B3FA);color:#fff;font-weight:700; }
    #pricePickOk:hover { opacity:.88; }
    #pricePickSkip { background:rgba(255,255,255,.06);border:1px solid rgba(97,179,250,.2);color:#A7A9AC; }
    #pricePickSkip:hover { color:#fff; }

    /* Bouton map dans formulaire */
    .btn-open-map {
      display:flex;align-items:center;gap:6px;
      padding:.45rem .9rem;border-radius:20px;
      border:1px dashed rgba(97,179,250,.4);
      background:rgba(97,179,250,.07);color:#61B3FA;
      font-size:.8rem;cursor:pointer;font-family:inherit;
      transition:all .2s;white-space:nowrap;
    }
    .btn-open-map:hover { background:rgba(97,179,250,.18);border-color:#61B3FA; }

    /* Modal visu */
    #ecoViewModal {
  position:fixed;
  top:64px; left:0; right:0; bottom:0;
  z-index:99;
  display:none; align-items:center; justify-content:center;
}
    #ecoViewModal.open { display:flex; }
    #ecoViewOverlay { position:absolute;inset:0;background:rgba(5,15,30,.75);backdrop-filter:blur(4px); }
    #ecoViewPanel {
      position:relative;z-index:1;
      width:min(96vw,820px);max-height:90vh;
      background:linear-gradient(145deg,#0D1F3A,#0A1628);
      border:1px solid rgba(97,179,250,.3);border-radius:18px;overflow:hidden;
      display:flex;flex-direction:column;
      box-shadow:0 24px 60px rgba(0,0,0,.7);
      animation:mapIn .28s ease;
      max-height:calc(90vh - 64px);
    }
    #ecoViewHeader {
      display:flex;align-items:center;justify-content:space-between;
      padding:.9rem 1.3rem;background:rgba(25,118,210,.18);
      border-bottom:1px solid rgba(97,179,250,.2);
    }
    #ecoViewTitle { font-size:.95rem;font-weight:600;color:#fff;display:flex;align-items:center;gap:8px; }
    #ecoViewClose {
      width:32px;height:32px;border-radius:50%;border:1px solid rgba(255,255,255,.2);
      background:rgba(255,255,255,.06);color:#fff;cursor:pointer;
      display:flex;align-items:center;justify-content:center;font-size:.8rem;
      transition:all .2s;
    }
    #ecoViewClose:hover { background:rgba(231,76,60,.3);border-color:#e74c3c; }
    #ecoViewLegend {
      display:flex;gap:.8rem;flex-wrap:wrap;
      padding:.55rem 1rem;background:rgba(0,0,0,.2);
      border-bottom:1px solid rgba(97,179,250,.1);
    }
    .vleg {
      display:flex;align-items:center;gap:5px;
      font-size:.75rem;color:rgba(255,255,255,.7);
    }
    .vleg-dot { width:10px;height:10px;border-radius:50%; }
    #ecoViewContainer { flex:1;min-height:380px;max-height:65vh; }
    #ecoViewFooter {
      padding:.7rem 1.2rem;text-align:right;
      border-top:1px solid rgba(97,179,250,.15);background:rgba(0,0,0,.2);
    }
    #ecoViewCloseBtn {
      padding:.55rem 1.3rem;background:rgba(255,255,255,.06);
      border:1px solid rgba(97,179,250,.2);border-radius:10px;
      color:var(--grey,#A7A9AC);font-family:inherit;font-size:.83rem;cursor:pointer;
      transition:all .2s;
    }
    #ecoViewCloseBtn:hover { color:#fff;background:rgba(255,255,255,.1); }

    /* body light mode overrides */
    body.light-mode #ecoMapPanel,
    body.light-mode #ecoViewPanel,
    body.light-mode #pricePickBox {
      background:linear-gradient(145deg,#fff,#f0f4f8);
      border-color:#d0d9ee;
    }
    body.light-mode #ecoMapHeader,
    body.light-mode #ecoViewHeader { background:rgba(25,118,210,.1); }
    body.light-mode .map-point-name,
    body.light-mode #ecoMapTitle,
    body.light-mode #ecoViewTitle,
    body.light-mode #pricePickBox h3 { color:#0A1628; }
    body.light-mode .maptb { background:rgba(0,0,0,.04);color:#444; }
    body.light-mode .maptb.active { background:rgba(25,118,210,.2);color:#1976D2; }
  `;
  document.head.appendChild(s);
}

/* ── Leaflet chargement dynamique ── */
function loadLeaflet(cb) {
  if (window.L) { cb(); return; }
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
  document.head.appendChild(link);
  const sc = document.createElement('script');
  sc.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
  sc.onload = cb;
  document.head.appendChild(sc);
}

/* ====================================================
   OUVRIR LA MAP FORMULAIRE
   ==================================================== */
function openFormMap() {
  createMapModal();
  document.getElementById('ecoMapModal').classList.add('open');
  document.documentElement.style.overflow = 'hidden';
document.body.style.paddingRight = '0px';

  loadLeaflet(() => {
    const container = document.getElementById('ecoMapContainer');
    if (_mapModal) {
      _mapModal.invalidateSize();
      return;
    }
    _mapModal = L.map(container, {
      center: [33.8869, 9.5375], zoom: 6,
      zoomControl: true
    });
    L.tileLayer(MAP_TILE, { attribution: MAP_ATTR, maxZoom: 18 }).addTo(_mapModal);

    // Clic sur la carte
    _mapModal.on('click', function(e) {
      handleMapClick(e.latlng);
    });

    // Reconstruire markers si déjà en mémoire
    rebuildFormMarkers();
  });

  updateMapPointsList();
}

function closeMapModal() {
  document.getElementById('ecoMapModal').classList.remove('open');
  document.documentElement.style.overflow = '';
document.body.style.paddingRight = '';
  setMapMode(null);
}

/* ── Modes ── */
function setMapMode(mode) {
  _mapState.mode = mode;
  document.querySelectorAll('.maptb').forEach(b => b.classList.remove('active'));
  if (mode) {
    const btn = document.getElementById('tb-' + (mode.startsWith('arret') ? 'arret' : mode));
    if (btn) btn.classList.add('active');
    const hints = {
      depart  : '🟢 Cliquez sur la carte pour définir le <strong>départ</strong>',
      arrivee : '🔴 Cliquez sur la carte pour définir la <strong>destination finale</strong>',
      arret   : '🟡 Cliquez sur la carte pour ajouter un <strong>point d\'arrêt</strong>'
    };
    document.getElementById('ecoMapHint').innerHTML =
      '<i class="fas fa-crosshairs" style="color:#61B3FA;"></i> ' + (hints[mode] || 'Cliquez sur la carte');
  } else {
    document.getElementById('ecoMapHint').innerHTML =
      '<i class="fas fa-info-circle" style="color:#61B3FA;"></i> Sélectionnez un mode puis cliquez sur la carte';
  }
}

/* ── Clic sur carte ── */
async function handleMapClick(latlng) {
  if (!_mapState.mode) {
    showToast('Sélectionnez d\'abord un mode (Départ / Destination / Arrêt)', false);
    return;
  }

  document.getElementById('ecoMapHint').innerHTML =
    '<i class="fas fa-spinner fa-spin" style="color:#61B3FA;"></i> Géocodage en cours...';

  let nom = null;
  try {
    const url = `${NOMINATIM}&lat=${latlng.lat}&lon=${latlng.lng}&zoom=14`;
    const res  = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
    const data = await res.json();
    const a = data.address || {};
    nom = a.city || a.town || a.village || a.hamlet || a.suburb ||
          a.municipality || a.county || data.display_name?.split(',')[0] || null;
  } catch(e) {}

  const displayNom = nom || `Point (${latlng.lat.toFixed(4)}, ${latlng.lng.toFixed(4)})`;

  const mode = _mapState.mode;

  if (mode === 'depart' || mode === 'arrivee') {
    // Pas de saisie prix pour départ; pour arrivée → saisie prix
    if (mode === 'arrivee') {
      askPrice(displayNom, (prix) => {
        _formMarkers[mode] = { latlng, nom: displayNom, prix };
        placeMarker(mode, latlng, displayNom);
        updateMapPointsList();
        setMapMode(null);
      });
    } else {
      _formMarkers[mode] = { latlng, nom: displayNom, prix: null };
      placeMarker(mode, latlng, displayNom);
      updateMapPointsList();
      setMapMode(null);
    }
  } else if (mode === 'arret') {
    askPrice(displayNom, (prix) => {
      if (!_formMarkers.arrets) _formMarkers.arrets = [];
      const idx = _formMarkers.arrets.length;
      _formMarkers.arrets.push({ latlng, nom: displayNom, prix });
      placeMarkerArret(idx, latlng, displayNom, prix);
      updateMapPointsList();
    });
  }

  setMapMode(mode === 'arret' ? 'arret' : null);
}

/* ── Placer marker ── */
function placeMarker(type, latlng, nom) {
  if (!_mapModal) return;
  if (_formMarkers['_L_' + type]) _mapModal.removeLayer(_formMarkers['_L_' + type]);
  const m = L.marker(latlng, { icon: makeIcon(type) })
    .addTo(_mapModal)
    .bindPopup(`<strong>${MARKER_ICONS[type].label}</strong><br>${nom}`);
  _formMarkers['_L_' + type] = m;
  drawPolylineForm();
}

function placeMarkerArret(idx, latlng, nom, prix) {
  if (!_mapModal) return;
  const m = L.marker(latlng, { icon: makeIcon('arret') })
    .addTo(_mapModal)
    .bindPopup(`<strong>Arrêt ${idx+1}</strong><br>${nom}<br>${prix > 0 ? prix + ' DT' : ''}`);
  if (!_formMarkers._L_arrets) _formMarkers._L_arrets = [];
  _formMarkers._L_arrets.push(m);
  drawPolylineForm();
}

function drawPolylineForm() {
  if (!_mapModal) return;
  if (_polylineForm) _mapModal.removeLayer(_polylineForm);
  const pts = [];
  if (_formMarkers.depart)  pts.push(_formMarkers.depart.latlng);
  if (_formMarkers.arrets)  _formMarkers.arrets.forEach(a => pts.push(a.latlng));
  if (_formMarkers.arrivee) pts.push(_formMarkers.arrivee.latlng);
  if (pts.length >= 2) {
    _polylineForm = L.polyline(pts, {
      color: '#61B3FA', weight: 3, dashArray: '8,6', opacity: .8
    }).addTo(_mapModal);
    _mapModal.fitBounds(_polylineForm.getBounds(), { padding: [30, 30] });
  }
}

function rebuildFormMarkers() {
  if (!_mapModal) return;
  ['depart', 'arrivee'].forEach(t => {
    if (_formMarkers[t]) placeMarker(t, _formMarkers[t].latlng, _formMarkers[t].nom);
  });
  (_formMarkers.arrets || []).forEach((a, i) => placeMarkerArret(i, a.latlng, a.nom, a.prix));
}

/* ── Liste des points ── */
function updateMapPointsList() {
  const el = document.getElementById('ecoMapPointsList');
  if (!el) return;
  let html = '';

  if (_formMarkers.depart) {
    html += pointItem('depart', '🟢', _formMarkers.depart.nom, null, "delFormPoint('depart')");
  }
  (_formMarkers.arrets || []).forEach((a, i) => {
    html += pointItem('arret', '🟡', a.nom, a.prix, `delFormArret(${i})`);
  });
  if (_formMarkers.arrivee) {
    html += pointItem('arrivee', '🔴', _formMarkers.arrivee.nom, _formMarkers.arrivee.prix, "delFormPoint('arrivee')");
  }
  el.innerHTML = html || '<div style="color:rgba(255,255,255,.35);font-size:.76rem;text-align:center;padding:.4rem;">Aucun point sélectionné</div>';
}

function pointItem(type, dot, nom, prix, delFn) {
  const colors = { depart: '#27ae60', arret: '#f1c40f', arrivee: '#e74c3c' };
  return `<div class="map-point-item">
    <div class="map-point-dot" style="background:${colors[type]||'#888'};"></div>
    <span class="map-point-name">${escH(nom)}</span>
    ${prix !== null && prix > 0 ? `<span class="map-point-price">${prix} DT</span>` : ''}
    <button class="map-point-del" onclick="${delFn}"><i class="fas fa-times"></i></button>
  </div>`;
}

function delFormPoint(type) {
  if (_formMarkers['_L_' + type]) _mapModal.removeLayer(_formMarkers['_L_' + type]);
  delete _formMarkers[type];
  delete _formMarkers['_L_' + type];
  drawPolylineForm();
  updateMapPointsList();
}

function delFormArret(idx) {
  if (_formMarkers._L_arrets && _formMarkers._L_arrets[idx]) {
    _mapModal.removeLayer(_formMarkers._L_arrets[idx]);
    _formMarkers._L_arrets.splice(idx, 1);
  }
  if (_formMarkers.arrets) _formMarkers.arrets.splice(idx, 1);
  drawPolylineForm();
  updateMapPointsList();
}

function clearMapPoints() {
  ['depart', 'arrivee'].forEach(t => {
    if (_formMarkers['_L_' + t]) _mapModal.removeLayer(_formMarkers['_L_' + t]);
  });
  (_formMarkers._L_arrets || []).forEach(m => _mapModal.removeLayer(m));
  if (_polylineForm) _mapModal.removeLayer(_polylineForm);
  _formMarkers = {};
  _polylineForm = null;
  updateMapPointsList();
}

/* ── Saisie prix ── */
function askPrice(nomPoint, callback) {
  let pp = document.getElementById('pricePickModal');
  if (!pp) {
    pp = document.createElement('div');
    pp.id = 'pricePickModal';
    pp.innerHTML = `
      <div id="pricePickOverlay"></div>
      <div id="pricePickBox">
        <h3><i class="fas fa-coins" style="color:#f1c40f;"></i> Prix pour <span id="ppName"></span></h3>
        <div style="position:relative;">
          <i class="pp-icon fas fa-coins"></i>
          <input type="number" id="ppInput" placeholder="Ex: 15" min="0" step="0.5">
        </div>
        <div class="pp-row">
          <button id="pricePickOk"><i class="fas fa-check"></i> Confirmer</button>
          <button id="pricePickSkip">Sans prix</button>
        </div>
      </div>`;
    document.body.appendChild(pp);
  }
  document.getElementById('ppName').textContent = nomPoint;
  document.getElementById('ppInput').value = '';
  pp.classList.add('open');

  const cleanup = () => pp.classList.remove('open');
  document.getElementById('pricePickOk').onclick = () => {
    const v = parseFloat(document.getElementById('ppInput').value || 0);
    cleanup(); callback(isNaN(v) ? 0 : v);
  };
  document.getElementById('pricePickSkip').onclick = () => { cleanup(); callback(0); };
  document.getElementById('ppInput').onkeydown = (e) => {
    if (e.key === 'Enter') document.getElementById('pricePickOk').click();
  };
  setTimeout(() => document.getElementById('ppInput').focus(), 100);
}

/* ── Appliquer la map au formulaire ── */
function applyMapToForm() {
  const dep = _formMarkers.depart;
  const arr = _formMarkers.arrivee;

  if (dep) {
    const el = document.getElementById('depart');
    if (el) { el.value = dep.nom; }
  }
  if (arr) {
    const el = document.getElementById('arrivee');
    if (el) el.value = arr.nom;
    if (arr.prix > 0) {
      const px = document.getElementById('prix');
      if (px) px.value = arr.prix;
    }
  }

  // Effacer arrêts existants
  const arretsList = document.getElementById('arrets-list');
  if (arretsList) arretsList.innerHTML = '';
  if (typeof arretCnt !== 'undefined') window.arretCnt = 0;

  // Ajouter arrêts depuis la map
  const arrets = _formMarkers.arrets || [];
  arrets.forEach(a => {
    if (typeof window.app !== 'undefined' && window.app.ajouterArret) {
      window.app.ajouterArret();
    } else if (typeof ajouterArret === 'function') {
      ajouterArret();
    }
    // On récupère l'ID du dernier arrêt ajouté
    const items = document.querySelectorAll('.arret-item');
    if (items.length > 0) {
      const last = items[items.length - 1];
      const id = last.id.split('-')[1];
      const nomEl = document.getElementById('an_' + id);
      const pxEl  = document.getElementById('ap_' + id);
      if (nomEl) nomEl.value = a.nom;
      if (pxEl && a.prix > 0) { pxEl.value = a.prix; pxEl._manual = true; }
    }
  });

  // Calcul distance si possible
  if (typeof window.app !== 'undefined' && window.app.onRouteChange) {
    window.app.onRouteChange();
  } else if (typeof onRouteChange === 'function') {
    onRouteChange();
  }

  closeMapModal();
  showToast('Points appliqués au formulaire ✓');
}

/* ====================================================
   MODAL VISU — AFFICHAGE TRAJET (Tous les trajets)
   ==================================================== */
function openTripViewMap(tripId) {
  createViewModal();
  const modal = document.getElementById('ecoViewModal');
  modal.classList.add('open');
 document.documentElement.style.overflow = 'hidden';
document.body.style.paddingRight = '0px';

  const trip    = (typeof allTrips !== 'undefined') ? allTrips.find(t => t.id_T == tripId) : null;
  const allD    = (typeof allDests !== 'undefined') ? allDests : [];

  let title = 'Trajet #' + tripId;
  if (trip) title = `${trip.point_depart || '?'} → ${trip.point_arrive || '?'}`;
  document.getElementById('ecoViewTitle').innerHTML =
    `<i class="fas fa-route"></i> ${escH(title)}`;

  loadLeaflet(() => {
    const container = document.getElementById('ecoViewContainer');
    if (_viewMap) { _viewMap.remove(); _viewMap = null; }
    _viewMap = L.map(container, { center: [33.8869, 9.5375], zoom: 6 });
    L.tileLayer(MAP_TILE, { attribution: MAP_ATTR, maxZoom: 18 }).addTo(_viewMap);

    const pts = [];
    const markers = [];

    // Récupérer données du trajet
    if (trip) {
      // Départ
      const depCoords = getCoords(trip.point_depart);
      if (depCoords) {
        pts.push(depCoords);
        markers.push({ latlng: depCoords, type: 'depart', nom: trip.point_depart, info: 'Départ' });
      }

      // Arrêts (ordre != 999)
      const arrets = allD
        .filter(d => d.trajet_id == tripId && String(d.ordre) !== '999')
        .sort((a, b) => parseInt(a.ordre) - parseInt(b.ordre));

      arrets.forEach(a => {
        const nom = a.nom || a.descente || '';
        const coords = getCoords(nom);
        if (coords) {
          pts.push(coords);
          const px = parseFloat(a.prix || 0);
          markers.push({
            latlng: coords, type: 'arret', nom,
            info: `Arrêt · ${px > 0 ? px.toFixed(2) + ' DT' : 'Prix libre'}`
          });
        }
      });

      // Destination finale
      const arrCoords = getCoords(trip.point_arrive);
      if (arrCoords) {
        const px = parseFloat(trip.prix_total || 0);
        pts.push(arrCoords);
        markers.push({
          latlng: arrCoords, type: 'arrivee', nom: trip.point_arrive,
          info: `Destination finale · ${px.toFixed(2)} DT`
        });
      }

      // Réservations (ordre == 999)
      const resas = allD.filter(d => d.trajet_id == tripId && String(d.ordre) === '999');
      resas.forEach(r => {
        const nom = r.nom || r.descente || '';
        const coords = getCoords(nom);
        if (coords) {
          pts.push(coords);
          markers.push({
            latlng: coords, type: 'resa', nom,
            info: `Réservé · ${parseFloat(r.prix||0).toFixed(2)} DT`
          });
        }
      });
    }

    // Afficher markers
    markers.forEach(mk => {
      L.marker(mk.latlng, { icon: makeIcon(mk.type) })
        .addTo(_viewMap)
        .bindPopup(`<strong>${escH(mk.nom)}</strong><br><small>${mk.info}</small>`);
    });

    // Polyline (sans réservations)
    const routePts = pts.slice(0, pts.length);
    if (routePts.length >= 2) {
      L.polyline(routePts, {
        color: '#61B3FA', weight: 4, dashArray: '10,7', opacity: .85
      }).addTo(_viewMap);
      _viewMap.fitBounds(L.latLngBounds(routePts), { padding: [30, 30] });
    } else if (routePts.length === 1) {
      _viewMap.setView(routePts[0], 10);
    }

    if (markers.length === 0) {
      showToast('Coordonnées des villes non disponibles', false);
    }
  });
}

function createViewModal() {
  if (document.getElementById('ecoViewModal')) return;
  const m = document.createElement('div');
  m.id = 'ecoViewModal';
  m.innerHTML = `
    <div id="ecoViewOverlay" onclick="closeViewMap()"></div>
    <div id="ecoViewPanel">
      <div id="ecoViewHeader">
        <span id="ecoViewTitle"><i class="fas fa-route"></i> Carte du trajet</span>
        <button id="ecoViewClose" onclick="closeViewMap()"><i class="fas fa-times"></i></button>
      </div>
      <div id="ecoViewLegend">
        <span class="vleg"><span class="vleg-dot" style="background:#27ae60;"></span> Départ</span>
        <span class="vleg"><span class="vleg-dot" style="background:#f1c40f;"></span> Arrêts</span>
        <span class="vleg"><span class="vleg-dot" style="background:#e74c3c;"></span> Destination</span>
        <span class="vleg"><span class="vleg-dot" style="background:#9b59b6;"></span> Réservations</span>
      </div>
      <div id="ecoViewContainer"></div>
      <div id="ecoViewFooter">
        <button id="ecoViewCloseBtn" onclick="closeViewMap()"><i class="fas fa-times"></i> Fermer</button>
      </div>
    </div>`;
  document.body.appendChild(m);
}

function closeViewMap() {
  const m = document.getElementById('ecoViewModal');
  if (m) m.classList.remove('open');
  document.documentElement.style.overflow = '';
document.body.style.paddingRight = '';
}

/* ── Coords depuis nom de ville ── */
function getCoords(nom) {
  if (!nom) return null;
  const key = nom.toLowerCase().trim();
  if (typeof CITIES !== 'undefined' && CITIES[key]) {
    return L.latLng(CITIES[key][0], CITIES[key][1]);
  }
  // Recherche partielle
  if (typeof CITIES !== 'undefined') {
    const found = Object.keys(CITIES).find(k => key.includes(k) || k.includes(key));
    if (found) return L.latLng(CITIES[found][0], CITIES[found][1]);
  }
  return null;
}

/* ====================================================
   INJECTION DU BOUTON MAP DANS LE FORMULAIRE
   ==================================================== */
function injectMapBtnInForm() {
  if (document.getElementById('mapFormBtn')) return;

  const btn = `<button type="button" id="mapFormBtn" class="btn-open-map" onclick="openFormMap()" style="margin-left:auto;">
      <i class="fas fa-map-marked-alt"></i>&nbsp; Carte
    </button>`;

  /* ── Position 1 : dans le header de la fcard (toujours visible) ── */
  const fcardHead = document.querySelector('.fcard-head');
  if (fcardHead) {
    fcardHead.style.justifyContent = 'space-between';
    fcardHead.insertAdjacentHTML('beforeend', btn);
    return;
  }

  /* ── Position 2 : après .tab-row (dans fcard-body) ── */
  const tabRow = document.querySelector('.tab-row');
  if (tabRow) {
    const wrapper = document.createElement('div');
    wrapper.id = 'mapFormBtnWrapper';
    wrapper.style.cssText = 'display:flex;justify-content:flex-end;margin-bottom:.9rem;';
    wrapper.innerHTML = btn;
    tabRow.insertAdjacentElement('afterend', wrapper);
    return;
  }

  /* ── Position 3 : avant le premier label dans #addForm ── */
  const addForm = document.getElementById('addForm');
  if (!addForm) { setTimeout(injectMapBtnInForm, 300); return; }

  const wrapper2 = document.createElement('div');
  wrapper2.id = 'mapFormBtnWrapper';
  wrapper2.style.cssText = 'display:flex;justify-content:flex-end;margin-bottom:.9rem;';
  wrapper2.innerHTML = btn;

  const firstLabel = addForm.querySelector('label.lbl') || addForm.querySelector('.igrp');
  if (firstLabel) firstLabel.parentNode.insertBefore(wrapper2, firstLabel);
  else addForm.insertBefore(wrapper2, addForm.firstChild);
}

/* ====================================================
   PATCH renderTrajets pour ajouter bouton 🗺 dans
   "Tous les trajets"
   ==================================================== */
function patchAllTripsWithMap() {
  const origLoad = window.loadAllTrips;
  window.loadAllTrips = function() {
    if (origLoad) origLoad();
    setTimeout(injectViewMapBtnsInAllTrips, 200);
  };
}

function injectViewMapBtnsInAllTrips() {
  // Appelé aussi lors du re-rendu
}

/* Patch pour le rendu de la table allTrips — injecter bouton map */
function patchAllTripsRender() {
  // On surcharge loadAllTrips
  const _orig = window.loadAllTrips;
  window.loadAllTrips = function() {
    const tbody   = document.getElementById('allTripsBody');
    const countEl = document.getElementById('allTripsCount');
    const trips   = (typeof allTrips !== 'undefined') ? allTrips : [];

    if (!trips.length) {
      tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><i class="fas fa-route"></i><p>Aucun trajet disponible.</p></div></td></tr>';
      if (countEl) countEl.textContent = '0 trajet(s)';
      return;
    }
    if (countEl) countEl.textContent = trips.length + ' trajet(s)';

    const allD = (typeof allDests !== 'undefined') ? allDests : [];

    tbody.innerHTML = trips.map(t => {
      const dist     = parseFloat(t.distance_total || 0);
      const prix     = parseFloat(t.prix_total || t.prix || 0);
      const nbArrets = allD.filter(d => d.trajet_id == t.id_T && d.ordre != 999).length;
      return `<tr>
        <td><span class="chip">#${t.id_T}</span></td>
        <td>${escH(t.point_depart || '—')}</td>
        <td>${escH(t.point_arrive || '—')}</td>
        <td><strong>${prix.toFixed(2)} DT</strong></td>
        <td>${dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—'}</td>
        <td>${nbArrets}</td>
        <td><div class="abtns">
          ${window.favBtnHtml ? window.favBtnHtml(t.id_T) : ''}
          <button class="abtn" title="Voir sur la carte"
            style="background:rgba(97,179,250,.12);color:#61B3FA;"
            onclick="openTripViewMap(${t.id_T})">
            <i class="fas fa-map-marked-alt"></i>
          </button>
          <button class="abtn abtn-res" title="Réserver ce trajet"
            onclick="window.app.reserverTrajetAllTrips && window.app.reserverTrajetAllTrips(${t.id_T})">
            <i class="fas fa-ticket-alt"></i>
          </button>
        </div></td>
      </tr>`;
    }).join('');
  };
}

/* ====================================================
   INITIALISATION
   ==================================================== */
function exposeGlobals() {
  window.openFormMap     = openFormMap;
  window.closeMapModal   = closeMapModal;
  window.setMapMode      = setMapMode;
  window.clearMapPoints  = clearMapPoints;
  window.delFormPoint    = delFormPoint;
  window.delFormArret    = delFormArret;
  window.applyMapToForm  = applyMapToForm;
  window.openTripViewMap = openTripViewMap;
  window.closeViewMap    = closeViewMap;
}

function initMapIntegration() {
  injectMapStyles();
  exposeGlobals();
  patchAllTripsRender();

  /* Injection du bouton formulaire :
     on tente immédiatement, puis on réessaie via MutationObserver
     si le DOM n'est pas encore prêt (ex: contenu chargé en AJAX) */
  injectMapBtnInForm();

  /* MutationObserver : si #addForm n'était pas là, on attend qu'il apparaisse */
  if (!document.getElementById('mapFormBtn')) {
    const obs = new MutationObserver(() => {
      if (document.getElementById('addForm') && !document.getElementById('mapFormBtn')) {
        injectMapBtnInForm();
      }
      if (document.getElementById('mapFormBtn')) obs.disconnect();
    });
    obs.observe(document.body, { childList: true, subtree: true });
    /* Arrêt de sécurité après 10s */
    setTimeout(() => obs.disconnect(), 10000);
  }
}

/* Lancement dès que possible */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMapIntegration);
} else {
  /* DOM déjà prêt — lancer après user.js (qui peut aussi initialiser au DOMContentLoaded) */
  setTimeout(initMapIntegration, 100);
}