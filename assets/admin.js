/* =====================================================
   ECO RIDE — admin.js
   Séparation JS / HTML + contrôles de saisie renforcés
   ===================================================== */

const T_API = "../../controller/TrajetController.php";
const D_API = "../../controller/DestinationController.php";

let trips = [], dests = [];
let tripsChart, destChart;
let tSortKey = 'id_T', tSortDir = 1;
let dSortKey = 'id_des', dSortDir = 1;
let tPage = 1, dPage = 1;
const PG = 12;

/* =====================================================
   UTILITAIRES
   ===================================================== */

/** Échappe HTML */
function escH(s) {
  return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

/** Affiche un toast */
function toast(msg, ok) {
  var t = document.createElement('div');
  t.className = 'toast ' + (ok !== false ? 't-ok' : 't-err');
  t.innerHTML = '<i class="fas ' + (ok !== false ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + msg;
  document.body.appendChild(t);
  setTimeout(function () { t.remove(); }, 3200);
}

/* =====================================================
   VALIDATION / CONTRÔLES DE SAISIE
   ===================================================== */

/**
 * Règles de validation pour les champs de recherche admin.
 * Les champs sont facultatifs, mais s'ils sont renseignés
 * ils doivent respecter les contraintes.
 */
var SEARCH_RULES = {
  minLen: 1,
  maxLen: 80,
  /** Autorise lettres accentuées, chiffres, espaces, tirets, # */
  pattern: /^[a-zA-ZÀ-ÖØ-öø-ÿ0-9\s'\-#]+$/,
  messages: {
    minLen:  'Minimum 1 caractère.',
    maxLen:  'Maximum 80 caractères.',
    pattern: 'Caractères invalides dans la recherche.',
  }
};

/**
 * Valide une valeur de champ de recherche (facultatif).
 * @returns {string|null}
 */
function validateSearch(val) {
  val = (val || '').trim();
  if (!val) return null; // champ vide = OK (pas obligatoire)
  if (val.length < SEARCH_RULES.minLen) return SEARCH_RULES.messages.minLen;
  if (val.length > SEARCH_RULES.maxLen) return SEARCH_RULES.messages.maxLen;
  if (!SEARCH_RULES.pattern.test(val))  return SEARCH_RULES.messages.pattern;
  return null;
}

/**
 * Affiche ou efface un message d'erreur sous un input.
 */
function setSearchError(inputEl, message) {
  if (!inputEl) return;
  var errId    = inputEl.id + '-err';
  var existing = document.getElementById(errId);
  if (message) {
    inputEl.style.borderColor = 'var(--red)';
    if (!existing) {
      var span = document.createElement('span');
      span.id = errId;
      span.style.cssText = 'display:block;font-size:.7rem;color:var(--red);margin-top:.2rem;padding-left:2px;';
      span.textContent = message;
      inputEl.insertAdjacentElement('afterend', span);
    } else {
      existing.textContent = message;
    }
  } else {
    inputEl.style.borderColor = '';
    if (existing) existing.remove();
  }
}

/** Attache la validation en temps réel sur un input de recherche */
function bindSearchValidation(inputId, onValidCallback) {
  var el = document.getElementById(inputId);
  if (!el) return;
  el.addEventListener('input', function () {
    var err = validateSearch(el.value);
    setSearchError(el, err);
    if (!err && typeof onValidCallback === 'function') onValidCallback();
  });
}

/* =====================================================
   NAVIGATION
   ===================================================== */
document.querySelectorAll('.nav-item[data-page]').forEach(function (el) {
  el.addEventListener('click', function () {
    document.querySelectorAll('.nav-item').forEach(function (n) { n.classList.remove('active'); });
    this.classList.add('active');
    var page = this.dataset.page;
    document.getElementById('dashboardPage').style.display     = page === 'dashboard'    ? 'block' : 'none';
    document.getElementById('tripsPage').style.display         = page === 'trips'        ? 'block' : 'none';
    document.getElementById('destinationsPage').style.display  = page === 'destinations' ? 'block' : 'none';
    var cfg = {
      dashboard:    { title: 'Tableau de Bord',          icon: 'fa-chart-line' },
      trips:        { title: 'Gestion des Trajets',       icon: 'fa-route'      },
      destinations: { title: 'Gestion des Destinations',  icon: 'fa-map-pin'    }
    };
    document.getElementById('pageTitle').innerHTML =
      '<i class="fas ' + cfg[page].icon + '"></i> ' + cfg[page].title;
    if (page === 'trips')        renderTrips();
    if (page === 'destinations') renderDest();
  });
});

/* =====================================================
   CHARGEMENT
   ===================================================== */
function loadData() {
  fetch(T_API)
    .then(function (r) { return r.json(); })
    .then(function (d) { trips = d; updateStats(); renderTrips(); updateCharts(); })
    .catch(function () { console.warn('Erreur chargement trajets'); });

  fetch(D_API)
    .then(function (r) { return r.json(); })
    .then(function (d) { dests = d; updateStats(); renderDest(); updateCharts(); })
    .catch(function () { console.warn('Erreur chargement destinations'); });
}

/* =====================================================
   STATISTIQUES
   ===================================================== */
function updateStats() {
  document.getElementById('totalTrips').textContent = trips.length;
  document.getElementById('totalDest').textContent  = dests.length;
  var avg = trips.length
    ? (trips.reduce(function (s, t) { return s + parseFloat(t.prix_total || t.prix || 0); }, 0) / trips.length).toFixed(1)
    : 0;
  document.getElementById('avgPrice').textContent = avg;
  var avgD = trips.length
    ? Math.round(trips.reduce(function (s, t) { return s + parseFloat(t.distance_total || 0); }, 0) / trips.length)
    : 0;
  document.getElementById('avgDist').textContent = avgD;
}

/* =====================================================
   GRAPHIQUES
   ===================================================== */
function updateCharts() {
  if (tripsChart) tripsChart.destroy();
  if (destChart)  destChart.destroy();

  var sorted = trips.slice()
    .sort(function (a, b) { return parseFloat(b.prix_total || 0) - parseFloat(a.prix_total || 0); })
    .slice(0, 6);

  var ctx1 = document.getElementById('tripsChart').getContext('2d');
  tripsChart = new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: sorted.map(function (t) {
        return (t.point_depart || '?').substring(0, 7) + '→' + (t.point_arrive || '?').substring(0, 7);
      }),
      datasets: [{
        label: 'Prix (DT)',
        data: sorted.map(function (t) { return parseFloat(t.prix_total || t.prix || 0); }),
        backgroundColor: 'rgba(0,180,216,0.5)',
        borderColor: '#00B4D8',
        borderWidth: 1,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { labels: { color: '#4E7A90', font: { family: 'DM Sans' } } } },
      scales: {
        x: { ticks: { color: '#4E7A90', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
        y: { ticks: { color: '#4E7A90' },                     grid: { color: 'rgba(255,255,255,0.04)' } }
      }
    }
  });

  var tripIds = [...new Set(dests.map(function (d) { return d.trajet_id; }))].slice(0, 6);
  var ctx2 = document.getElementById('destChart').getContext('2d');
  destChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: tripIds.map(function (id) { return 'Trajet #' + id; }),
      datasets: [{
        data: tripIds.map(function (id) {
          return dests.filter(function (d) { return d.trajet_id == id; }).length;
        }),
        backgroundColor: ['#00B4D8', '#0077B6', '#FF9A3C', '#FF4D6A', '#FFD166', '#00D98B'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      cutout: '60%',
      plugins: { legend: { labels: { color: '#4E7A90', font: { family: 'DM Sans', size: 11 }, padding: 12 } } }
    }
  });
}

/* =====================================================
   TRI COLONNES
   ===================================================== */
function sortCol(table, key) {
  if (table === 'trips') {
    if (tSortKey === key) tSortDir *= -1; else { tSortKey = key; tSortDir = 1; }
    document.querySelectorAll('[id^="ts-"]').forEach(function (el) { el.className = 'fas fa-sort si'; });
    var el = document.getElementById('ts-' + key);
    if (el) el.className = 'fas fa-sort-' + (tSortDir > 0 ? 'up' : 'down') + ' si on';
    tPage = 1; renderTrips();
  } else {
    if (dSortKey === key) dSortDir *= -1; else { dSortKey = key; dSortDir = 1; }
    document.querySelectorAll('[id^="ds-"]').forEach(function (el) { el.className = 'fas fa-sort si'; });
    var el2 = document.getElementById('ds-' + key);
    if (el2) el2.className = 'fas fa-sort-' + (dSortDir > 0 ? 'up' : 'down') + ' si on';
    dPage = 1; renderDest();
  }
}

/* =====================================================
   RENDU TRAJETS
   ===================================================== */
function renderTrips() {
  var search  = (document.getElementById('tripSearch').value || '').toLowerCase();
  var sortSel = document.getElementById('tripSortSel').value;

  var filtered = trips.filter(function (t) {
    return !search ||
      (t.point_depart || '').toLowerCase().includes(search) ||
      (t.point_arrive || '').toLowerCase().includes(search);
  });

  if (sortSel) {
    var sortFns = {
      id_asc:    function (a, b) { return parseInt(a.id_T) - parseInt(b.id_T); },
      id_desc:   function (a, b) { return parseInt(b.id_T) - parseInt(a.id_T); },
      dep_asc:   function (a, b) { return (a.point_depart || '').localeCompare(b.point_depart || ''); },
      arr_asc:   function (a, b) { return (a.point_arrive || '').localeCompare(b.point_arrive || ''); },
      prix_asc:  function (a, b) { return parseFloat(a.prix_total || 0) - parseFloat(b.prix_total || 0); },
      prix_desc: function (a, b) { return parseFloat(b.prix_total || 0) - parseFloat(a.prix_total || 0); },
      dist_asc:  function (a, b) { return parseFloat(a.distance_total || 0) - parseFloat(b.distance_total || 0); },
      dist_desc: function (a, b) { return parseFloat(b.distance_total || 0) - parseFloat(a.distance_total || 0); }
    };
    if (sortFns[sortSel]) filtered.sort(sortFns[sortSel]);
  } else {
    filtered.sort(function (a, b) {
      var va = a[tSortKey], vb = b[tSortKey];
      if (!isNaN(parseFloat(va))) { va = parseFloat(va); vb = parseFloat(vb); }
      if (va < vb) return -tSortDir;
      if (va > vb) return  tSortDir;
      return 0;
    });
  }

  var total = filtered.length, totalPages = Math.max(1, Math.ceil(total / PG));
  if (tPage > totalPages) tPage = 1;
  var slice = filtered.slice((tPage - 1) * PG, tPage * PG);
  var tbody = document.getElementById('tripsBody');

  if (slice.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="7"><div class="empty"><i class="fas fa-inbox"></i><p>Aucun trajet trouvé</p></div></tr></tr>';
  } else {
    tbody.innerHTML = slice.map(function (t) {
      var dist  = parseFloat(t.distance_total || 0);
      var nbArr = parseInt(t.nb_arrets || 0);
      return '<tr>' +
        '<td><span class="chip">#' + t.id_T + '</span></td>' +
        '<td><strong>' + escH(t.point_depart || '—') + '</strong></td>' +
        '<td>' + escH(t.point_arrive || '—') + '</td>' +
        '<td>' + parseFloat(t.prix_total || t.prix || 0).toFixed(2) + ' DT</td>' +
        '<td>' + (dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—') + '</td>' +
        '<td>' + (nbArr > 0 ? nbArr + ' arrêt' + (nbArr > 1 ? 's' : '') : '—') + '</td>' +
        '<td><div class="abtns">' +
          '<button class="abtn abtn-del" onclick="deleteTrip(' + t.id_T + ')" title="Supprimer"><i class="fas fa-trash"></i></button>' +
        '</div></td>' +
      '</tr>';
    }).join('');
  }
  renderPag('tripsPag', tPage, totalPages, function (p) { tPage = p; renderTrips(); });
}

/* =====================================================
   RENDU DESTINATIONS
   ===================================================== */
function renderDest() {
  var search = (document.getElementById('destSearch').value || '').toLowerCase();

  var filtered = dests.filter(function (d) {
    return !search ||
      (d.nom || '').toLowerCase().includes(search) ||
      String(d.trajet_id || '').includes(search) ||
      String(d.id_des || '').includes(search) ||
      (d.point_arrive || '').toLowerCase().includes(search);
  });

  filtered.sort(function (a, b) {
    var va = a[dSortKey], vb = b[dSortKey];
    if (!isNaN(parseFloat(va))) { va = parseFloat(va); vb = parseFloat(vb); }
    if (va < vb) return -dSortDir;
    if (va > vb) return  dSortDir;
    return 0;
  });

  var total = filtered.length, totalPages = Math.max(1, Math.ceil(total / PG));
  if (dPage > totalPages) dPage = 1;
  var slice = filtered.slice((dPage - 1) * PG, dPage * PG);
  var tbody = document.getElementById('destBody');

  if (slice.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="7"><div class="empty"><i class="fas fa-map-pin"></i><p>Aucune destination trouvée</p></div></td></tr>';
  } else {
    tbody.innerHTML = slice.map(function (d) {
      var trip  = trips.find(function (t) { return t.id_T == d.trajet_id; });
      var ptArr = trip ? (trip.point_arrive || '—') : (d.point_arrive || '—');
      var dist  = parseFloat(d.distance || 0);
      return '<tr>' +
        '<td><span class="chip">#' + d.id_des + '</span></td>' +
        '<td><span class="chip chip-green">#' + (d.trajet_id || '—') + '</span></td>' +
        '<td>' + escH(ptArr) + '</td>' +
        '<td><strong>' + escH(d.nom || d.descente || '—') + '</strong></td>' +
        '<td>' + (dist > 0 ? '<span class="dist-pill"><i class="fas fa-road"></i>' + dist + ' km</span>' : '—') + '</td>' +
        '<td>' + (d.ordre == 999 ? '<span style="color:var(--muted);font-size:.78rem;">réservation</span>' : (d.ordre || '—')) + '</td>' +
        '<td><div class="abtns">' +
          '<button class="abtn abtn-del" onclick="deleteDest(' + d.id_des + ')" title="Supprimer"><i class="fas fa-trash"></i></button>' +
        '</div></td>' +
      '</tr>';
    }).join('');
  }
  renderPag('destPag', dPage, totalPages, function (p) { dPage = p; renderDest(); });
}

/* =====================================================
   PAGINATION
   ===================================================== */
function renderPag(id, cur, total, cb) {
  var el = document.getElementById(id);
  if (!el || total <= 1) { if (el) el.innerHTML = ''; return; }
  var html = '<span class="pag-info">Page ' + cur + ' / ' + total + '</span>';
  if (cur > 1)
    html += '<button class="pag-btn" onclick="(' + cb.toString() + ')(' + (cur - 1) + ')"><i class="fas fa-chevron-left"></i></button>';
  for (var i = 1; i <= total; i++) {
    if (i === 1 || i === total || Math.abs(i - cur) <= 1)
      html += '<button class="pag-btn' + (i === cur ? ' on' : '') + '" onclick="(' + cb.toString() + ')(' + i + ')">' + i + '</button>';
    else if (Math.abs(i - cur) === 2)
      html += '<span class="pag-info">…</span>';
  }
  if (cur < total)
    html += '<button class="pag-btn" onclick="(' + cb.toString() + ')(' + (cur + 1) + ')"><i class="fas fa-chevron-right"></i></button>';
  el.innerHTML = html;
}

/* =====================================================
   SUPPRESSION
   ===================================================== */
function deleteTrip(id) {
  if (!confirm('Supprimer ce trajet et ses destinations associées ?')) return;
  fetch(T_API + '?id=' + id, { method: 'DELETE', headers: { 'Content-Type': 'application/json' } })
    .then(function (r) { return r.json(); })
    .then(function (d) { toast(d.message || 'Trajet supprimé'); loadData(); })
    .catch(function () {
      trips = trips.filter(function (t) { return t.id_T != id; });
      renderTrips(); updateStats();
      toast('Trajet supprimé');
    });
}

function deleteDest(id) {
  if (!confirm('Supprimer cette destination ?')) return;
  fetch(D_API, { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id }) })
    .then(function (r) { return r.json(); })
    .then(function () { toast('Destination supprimée'); loadData(); })
    .catch(function () {
      dests = dests.filter(function (d) { return d.id_des != id; });
      renderDest(); updateStats();
      toast('Destination supprimée');
    });
}

/* =====================================================
   EXPORT PDF
   ===================================================== */
function exportTripsPDF() {
  var { jsPDF } = window.jspdf;
  var doc = new jsPDF('landscape');
  doc.setFont('helvetica', 'bold'); doc.setFontSize(15);
  doc.text('EcoRide — Liste des Trajets', 14, 16);
  doc.setFontSize(9); doc.setFont('helvetica', 'normal');
  doc.text(new Date().toLocaleDateString('fr-FR'), 14, 23);
  doc.autoTable({
    startY: 28,
    head: [['ID', 'Départ', 'Arrivée', 'Prix (DT)', 'Distance (km)', 'Arrêts']],
    body: trips.map(function (t) {
      return [t.id_T, t.point_depart || '—', t.point_arrive || '—',
              parseFloat(t.prix_total || t.prix || 0).toFixed(2),
              parseFloat(t.distance_total || 0) || '—', t.nb_arrets || 0];
    }),
    styles: { fontSize: 9 },
    headStyles: { fillColor: [0, 119, 182] },
    alternateRowStyles: { fillColor: [13, 31, 58] }
  });
  doc.save('ecoride_trajets.pdf');
  toast('PDF exporté');
}

function exportDestPDF() {
  var { jsPDF } = window.jspdf;
  var doc = new jsPDF('landscape');
  doc.setFont('helvetica', 'bold'); doc.setFontSize(15);
  doc.text('EcoRide — Liste des Destinations', 14, 16);
  doc.setFontSize(9); doc.setFont('helvetica', 'normal');
  doc.text(new Date().toLocaleDateString('fr-FR'), 14, 23);
  doc.autoTable({
    startY: 28,
    head: [['ID Dest.', 'ID Trajet', 'Point Arrivée', 'Destination (nom)', 'Distance (km)', 'Ordre']],
    body: dests.map(function (d) {
      var trip = trips.find(function (t) { return t.id_T == d.trajet_id; });
      return [d.id_des, d.trajet_id || '—',
              trip ? (trip.point_arrive || '—') : (d.point_arrive || '—'),
              d.nom || d.descente || '—',
              parseFloat(d.distance || 0) || '—',
              d.ordre == 999 ? 'réservation' : (d.ordre || '—')];
    }),
    styles: { fontSize: 9 },
    headStyles: { fillColor: [0, 119, 182] }
  });
  doc.save('ecoride_destinations.pdf');
  toast('PDF exporté');
}

/* =====================================================
   EXPORT EXCEL
   ===================================================== */
function exportTripsExcel() {
  var ws = XLSX.utils.json_to_sheet(trips.map(function (t) {
    return {
      'ID': t.id_T,
      'Départ': t.point_depart || '—',
      'Arrivée': t.point_arrive || '—',
      'Prix (DT)': parseFloat(t.prix_total || t.prix || 0).toFixed(2),
      'Distance (km)': parseFloat(t.distance_total || 0) || '—',
      'Nb arrêts': t.nb_arrets || 0
    };
  }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Trajets');
  XLSX.writeFile(wb, 'ecoride_trajets.xlsx');
  toast('Excel exporté');
}

function exportDestExcel() {
  var ws = XLSX.utils.json_to_sheet(dests.map(function (d) {
    var trip = trips.find(function (t) { return t.id_T == d.trajet_id; });
    return {
      'ID Dest.': d.id_des,
      'ID Trajet': d.trajet_id || '—',
      'Point Arrivée': trip ? (trip.point_arrive || '—') : (d.point_arrive || '—'),
      'Destination': d.nom || d.descente || '—',
      'Distance (km)': parseFloat(d.distance || 0) || '—',
      'Ordre': d.ordre == 999 ? 'réservation' : (d.ordre || '—')
    };
  }));
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Destinations');
  XLSX.writeFile(wb, 'ecoride_destinations.xlsx');
  toast('Excel exporté');
}

/* =====================================================
   INITIALISATION
   ===================================================== */
window.onload = function () {
  /* Validation en temps réel sur les champs de recherche */
  bindSearchValidation('tripSearch', function () { tPage = 1; renderTrips(); });
  bindSearchValidation('destSearch', function () { dPage = 1; renderDest(); });

  /* Listeners normaux (déclenchent le rendu après validation) */
  document.getElementById('tripSearch').addEventListener('input', function () {
    if (!validateSearch(this.value)) { tPage = 1; renderTrips(); }
  });
  document.getElementById('tripSortSel').addEventListener('change', function () { tPage = 1; renderTrips(); });
  document.getElementById('destSearch').addEventListener('input', function () {
    if (!validateSearch(this.value)) { dPage = 1; renderDest(); }
  });

  loadData();
};