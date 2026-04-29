// Config globale
const LOSTFOUND_ADMIN_CONFIG = window.LOSTFOUND_ADMIN_CONFIG || { initialObjets: [], initialSignalements: [] };
const trajetMap = {
  201: 'Paris -> Lyon',
  202: 'Lille -> Bruxelles',
  203: 'Marseille -> Nice',
  204: 'Bordeaux -> Toulouse',
  205: 'Nantes -> Rennes'
};

let objets = [];
let signalements = [];
let currentDetailId = null;

// Éléments du DOM
const els = {
  tbody: document.getElementById('declarationsTbody'),
  searchInput: document.getElementById('searchInput'),
  filterStatut: document.getElementById('filterStatut'),
  filterCategorie: document.getElementById('filterCategorie'),
  filterDeclarant: document.getElementById('filterDeclarant'),
  countBadge: document.getElementById('countBadge'),
  statTotal: document.getElementById('statTotal'),
  statPerdu: document.getElementById('statPerdu'),
  statRetrouve: document.getElementById('statRetrouve'),
  statRestitue: document.getElementById('statRestitue'),
  detailModal: document.getElementById('detailModal'),
  detailForm: document.getElementById('detailForm'),
  commentsList: document.getElementById('commentsList'),
  iaSuggestion: document.getElementById('iaSuggestion'),
  themeBtn: document.getElementById('themeBtn')
};

// Vérification que les éléments critiques existent
if (!els.tbody || !els.searchInput) {
  console.error('Éléments DOM critiques manquants. Assurez-vous que le HTML contient #declarationsTbody et #searchInput');
}

// ========== UTILITAIRES ==========
function formatDate(dateStr) {
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr || '-';
  return d.toLocaleDateString('fr-FR');
}

function formatDateTime(dateStr) {
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr || '-';
  return d.toLocaleString('fr-FR');
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text == null ? '' : String(text);
  return div.innerHTML;
}

function excerpt(text, max = 68) {
  if (!text) return '';
  return text.length > max ? text.slice(0, max - 1) + '…' : text;
}

function categorieLabel(cat) {
  const labels = {
    electronique: 'Electronique',
    vetement: 'Vetement',
    document: 'Document',
    bagage: 'Bagage',
    autre: 'Autre'
  };
  return labels[cat] || cat;
}

function statutBadge(statut) {
  if (statut === 'perdu') return '<span class="badge b-perdu">Perdu</span>';
  if (statut === 'retrouve') return '<span class="badge b-retrouve">Retrouve</span>';
  return '<span class="badge b-restitue">Restitue</span>';
}

function getDeclarantLabel(o) {
  if (o.passager_id) {
    const passagersMap = {
      1: 'Sophie Martin', 2: 'Youssef Belaid', 3: 'Camille Bernard',
      4: 'Antoine Girard', 5: 'Lea Martin'
    };
    return passagersMap[o.passager_id] || ('Passager #' + o.passager_id);
  }
  return 'Anonyme - ' + (o.anonyme_nom || 'Externe');
}

function getDeclarantType(o) {
  return o.passager_id ? 'inscrit' : 'anonyme';
}

function getTrajetLabel(id) {
  return 'Trajet #' + id + (trajetMap[id] ? ' · ' + trajetMap[id] : '');
}

function nextId(items) {
  return items.length ? Math.max(...items.map(i => i.id)) + 1 : 1;
}

// ========== ÉTAT ET DONNÉES ==========
function loadData() {
  objets = Array.isArray(LOSTFOUND_ADMIN_CONFIG.initialObjets) 
    ? JSON.parse(JSON.stringify(LOSTFOUND_ADMIN_CONFIG.initialObjets))
    : [];
  signalements = Array.isArray(LOSTFOUND_ADMIN_CONFIG.initialSignalements)
    ? JSON.parse(JSON.stringify(LOSTFOUND_ADMIN_CONFIG.initialSignalements))
    : [];
}

function getFilteredObjets() {
  const q = (els.searchInput.value || '').trim().toLowerCase();
  const statut = els.filterStatut.value;
  const categorie = els.filterCategorie.value;
  const declarant = els.filterDeclarant.value;

  return objets.filter(o => {
    const statusOk = statut === 'tous' || o.statut === statut;
    const catOk = categorie === 'toutes' || o.categorie === categorie;
    const decOk = declarant === 'tous' || getDeclarantType(o) === declarant;
    const haystack = (o.description + ' ' + getDeclarantLabel(o)).toLowerCase();
    const searchOk = !q || haystack.includes(q);
    return statusOk && catOk && decOk && searchOk;
  });
}

// ========== RENDU ==========
function renderStats() {
  els.statTotal.textContent = objets.length;
  els.statPerdu.textContent = objets.filter(o => o.statut === 'perdu').length;
  els.statRetrouve.textContent = objets.filter(o => o.statut === 'retrouve').length;
  els.statRestitue.textContent = objets.filter(o => o.statut === 'restitue').length;
}

function renderTable() {
  const rows = getFilteredObjets().sort((a, b) => b.id - a.id);
  if (!els.tbody) {
    console.error('tbody element not found');
    return;
  }
  
  if (!rows.length) {
    els.tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><i class="fas fa-inbox"></i>Aucune declaration ne correspond aux filtres.</div></td></tr>';
    if (els.countBadge) els.countBadge.textContent = '0 publication';
    return;
  }

  if (els.countBadge) {
    els.countBadge.textContent = rows.length + (rows.length > 1 ? ' publications' : ' publication');
  }

  els.tbody.innerHTML = rows.map(o => {
    const id = o.id || '';
    const declarant = getDeclarantLabel(o);
    const trajet = getTrajetLabel(o.trajet_id);
    const categorie = categorieLabel(o.categorie);
    const description = excerpt(o.description);
    const statut = o.statut || 'perdu';
    
    return '<tr>' +
      '<td><code>#' + id + '</code></td>' +
      '<td>' + escapeHtml(declarant) + '</td>' +
      '<td>' + escapeHtml(trajet) + '</td>' +
      '<td><span class="badge b-cat">' + escapeHtml(categorie) + '</span></td>' +
      '<td>' + escapeHtml(description) + '</td>' +
      '<td>' + statutBadge(statut) + '</td>' +
      '<td><div class="acts">' +
      '<button class="ic ic-view" title="Details" data-action="details" data-id="' + id + '"><i class="fas fa-eye"></i></button>' +
      '<button class="ic ic-del" title="Supprimer" data-action="delete" data-id="' + id + '"><i class="fas fa-trash"></i></button>' +
      '</div></td>' +
      '</tr>';
  }).join('');
}

function renderAll() {
  renderStats();
  renderTable();
}

// ========== MODALES ==========
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

function openDetails(id) {
  const obj = objets.find(o => o.id === id);
  if (!obj) return;
  currentDetailId = id;

  document.getElementById('detailId').value = obj.id;
  document.getElementById('detailTrajet').value = obj.trajet_id;
  document.getElementById('detailCategorie').value = obj.categorie;
  document.getElementById('detailDescription').value = obj.description;
  document.getElementById('detailStatut').value = obj.statut;

  renderComments(id);
  renderIaSuggestion(id);
  openModal('detailModal');
}

function renderComments(objetId) {
  const rows = signalements
    .filter(s => s.objet_id === objetId)
    .sort((a, b) => new Date(b.date_signalement) - new Date(a.date_signalement));

  if (!rows.length) {
    els.commentsList.innerHTML = '<div class="comment-item"><div class="comment-meta">Aucun commentaire</div><div class="comment-msg">Ajoutez un commentaire conducteur pour enrichir le suivi.</div></div>';
    return;
  }

  els.commentsList.innerHTML = rows.map(s => {
    return '<div class="comment-item">' +
      '<div class="comment-meta">' + escapeHtml(formatDateTime(s.date_signalement)) + ' · Conducteur #' + escapeHtml(String(s.conducteur_id)) + '</div>' +
      '<div class="comment-msg">' + escapeHtml(s.message) + '</div>' +
      '</div>';
  }).join('');
}

function renderIaSuggestion(objetId) {
  const currentObj = objets.find(o => o.id === objetId);
  if (!currentObj) {
    els.iaSuggestion.textContent = 'Aucune suggestion disponible.';
    return;
  }

  const comments = signalements
    .filter(s => s.objet_id === objetId)
    .sort((a, b) => new Date(b.date_signalement) - new Date(a.date_signalement));

  if (!comments.length) {
    els.iaSuggestion.textContent = 'Aucun commentaire pour le moment.';
    return;
  }

  const last = comments[0];
  const words = last.message.toLowerCase().split(/\s+/).filter(w => w.length > 2);

  let best = null;
  let score = 0;

  objets.forEach(o => {
    if (o.id === objetId) return;
    const hay = (o.description + ' ' + o.categorie).toLowerCase();
    let currentScore = 0;
    words.forEach(w => {
      if (hay.includes(w)) currentScore += 1;
    });
    if (currentObj.categorie === o.categorie) currentScore += 1;
    if (currentScore > score) {
      score = currentScore;
      best = o;
    }
  });

  if (!best || score < 1) {
    els.iaSuggestion.textContent = '🔍 IA : Aucun rapprochement clair detecte pour le dernier commentaire.';
    return;
  }

  const txt = '🔍 IA : Cet objet ressemble a la declaration #' + best.id +
    ' (' + excerpt(best.description, 35) + ') faite par ' +
    getDeclarantLabel(best) + ' le ' + formatDate(best.date_perte) + '.';
  els.iaSuggestion.textContent = txt;
}

function deleteDeclaration(id) {
  const ok = confirm('Supprimer cette declaration et tous ses commentaires associes ?');
  if (!ok) return;
  objets = objets.filter(o => o.id !== id);
  signalements = signalements.filter(s => s.objet_id !== id);
  renderAll();
  if (currentDetailId === id) closeModal('detailModal');
}

// ========== ACTIONS ==========
function addCommentFromModal() {
  if (currentDetailId == null) return;

  const conducteurId = Number(document.getElementById('commentConducteurId').value);
  const message = (document.getElementById('commentMessage').value || '').trim();
  if (!conducteurId || !message) {
    alert('Renseignez conducteur_id et message.');
    return;
  }

  signalements.push({
    id: nextId(signalements),
    message,
    date_signalement: new Date().toISOString(),
    conducteur_id: conducteurId,
    objet_id: currentDetailId
  });

  document.getElementById('commentMessage').value = '';
  document.getElementById('commentConducteurId').value = '';
  renderComments(currentDetailId);
  renderIaSuggestion(currentDetailId);
}

function markRestitue() {
  if (currentDetailId == null) return;

  const obj = objets.find(o => o.id === currentDetailId);
  if (!obj) return;
  obj.statut = 'restitue';
  renderAll();
  openDetails(currentDetailId);
}

// ========== EXPORT PDF ==========
async function exportToPDF() {
  try {
    const { jsPDF } = window.jspdf;
    const rows = getFilteredObjets();

    const vehiculesData = rows.map(o => ({
      id: o.id,
      declarant: getDeclarantLabel(o),
      trajet: getTrajetLabel(o.trajet_id),
      categorie: categorieLabel(o.categorie),
      description: excerpt(o.description, 40),
      statut: o.statut
    }));

    const container = document.createElement('div');
    container.style.padding = '20px';
    container.style.fontFamily = 'Poppins, sans-serif';
    container.style.backgroundColor = 'white';
    container.style.color = '#333';
    container.style.width = '900px';

    let tableRows = '';
    vehiculesData.forEach(v => {
      tableRows += `
        <tr>
          <td style="padding: 10px; border: 1px solid #ddd;">#${v.id}</td>
          <td style="padding: 10px; border: 1px solid #ddd;">${escapeHtml(v.declarant)}</td>
          <td style="padding: 10px; border: 1px solid #ddd;">${escapeHtml(v.categorie)}</td>
          <td style="padding: 10px; border: 1px solid #ddd;">${escapeHtml(v.description)}</td>
          <td style="padding: 10px; border: 1px solid #ddd;"><strong>${escapeHtml(v.statut)}</strong></td>
        </tr>
      `;
    });

    container.innerHTML = `
      <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4EA3FF; padding-bottom: 10px;">
        <h1 style="color: #4EA3FF; font-family: Poppins, sans-serif;">📦 EcoRide - Objets Perdus</h1>
        <p style="font-family: Poppins, sans-serif;">Généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</p>
      </div>
      <div style="display: flex; justify-content: center; gap: 15px; margin: 20px 0; flex-wrap: wrap;">
        <div style="background: #e3f2fd; padding: 10px 20px; border-radius: 8px; text-align: center;">
          <strong style="font-size: 18px; color: #4EA3FF;">${objets.length}</strong><br>
          <small>Total déclarations</small>
        </div>
        <div style="background: #fff3e0; padding: 10px 20px; border-radius: 8px; text-align: center;">
          <strong style="font-size: 18px; color: #f1c40f;">${objets.filter(o => o.statut === 'perdu').length}</strong><br>
          <small>Perdus</small>
        </div>
        <div style="background: #e8f5e9; padding: 10px 20px; border-radius: 8px; text-align: center;">
          <strong style="font-size: 18px; color: #27ae60;">${objets.filter(o => o.statut === 'retrouve').length}</strong><br>
          <small>Retrouvés</small>
        </div>
        <div style="background: #f3e5f5; padding: 10px 20px; border-radius: 8px; text-align: center;">
          <strong style="font-size: 18px; color: #9c27b0;">${objets.filter(o => o.statut === 'restitue').length}</strong><br>
          <small>Restitués</small>
        </div>
      </div>
      <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
          <tr style="background: #4EA3FF; color: white;">
            <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">ID</th>
            <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Déclarant</th>
            <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Catégorie</th>
            <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Description</th>
            <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Statut</th>
          </tr>
        </thead>
        <tbody>
          ${tableRows}
        </tbody>
      </table>
      <div style="text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666;">
        EcoRide - Application de covoiturage - Gestion des objets perdus
      </div>
    `;

    document.body.appendChild(container);
    const canvas = await html2canvas(container, { scale: 2, backgroundColor: '#ffffff', logging: false });
    document.body.removeChild(container);

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const imgWidth = 280;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    pdf.addImage(imgData, 'PNG', 5, 10, imgWidth, imgHeight);

    const date = new Date();
    const fileName = `objets_perdus_${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}.pdf`;
    pdf.save(fileName);
  } catch (error) {
    console.error('Erreur:', error);
    alert('Erreur lors de la génération du PDF.');
  }
}

// ========== THÈME ==========
function toggleTheme() {
  document.body.classList.toggle('light-mode');
  const isLight = document.body.classList.contains('light-mode');
  localStorage.setItem('lostfound_admin_theme', isLight ? 'light' : 'dark');

  if (els.themeBtn) {
    const icon = els.themeBtn.querySelector('i');
    if (icon) {
      icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    }
  }
}

// Exposer toggleTheme globalement pour onclick dans HTML
window.toggleTheme = toggleTheme;

function initTheme() {
  if (!localStorage.getItem('lostfound_admin_theme')) {
    localStorage.setItem('lostfound_admin_theme', 'dark');
  }
  if (localStorage.getItem('lostfound_admin_theme') === 'light') {
    document.body.classList.add('light-mode');
    if (els.themeBtn) {
      const icon = els.themeBtn.querySelector('i');
      if (icon) icon.className = 'fas fa-sun';
    }
  } else if (els.themeBtn) {
    const icon = els.themeBtn.querySelector('i');
    if (icon) icon.className = 'fas fa-moon';
  }

  if (els.themeBtn) {
    els.themeBtn.addEventListener('click', toggleTheme);
  }
}

// ========== FILTRES ET RECHERCHE ==========
function bindFilters() {
  els.searchInput.addEventListener('input', renderTable);
  els.filterStatut.addEventListener('change', renderTable);
  els.filterCategorie.addEventListener('change', renderTable);
  els.filterDeclarant.addEventListener('change', renderTable);

  // Bouton Réinitialiser
  const resetBtn = document.getElementById('resetFiltersBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      els.searchInput.value = '';
      els.filterStatut.value = 'tous';
      els.filterCategorie.value = 'toutes';
      els.filterDeclarant.value = 'tous';
      renderTable();
    });
  }
}

// ========== ÉVÉNEMENTS PRINCIPAUX ==========
function bindTableActions() {
  if (!els.tbody) return;
  
  els.tbody.addEventListener('click', e => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const id = Number(btn.getAttribute('data-id'));
    const action = btn.getAttribute('data-action');

    if (action === 'details') openDetails(id);
    if (action === 'delete') deleteDeclaration(id);
  });
}

function bindModalActions() {
  const markRestitueBtn = document.getElementById('markRestitueBtn');
  const deleteFromModalBtn = document.getElementById('deleteFromModalBtn');
  const addCommentBtn = document.getElementById('addCommentBtn');

  if (markRestitueBtn) {
    markRestitueBtn.addEventListener('click', markRestitue);
  }

  if (deleteFromModalBtn) {
    deleteFromModalBtn.addEventListener('click', () => {
      if (currentDetailId == null) return;
      deleteDeclaration(currentDetailId);
    });
  }

  if (addCommentBtn) {
    addCommentBtn.addEventListener('click', addCommentFromModal);
  }

  // Fermer modales
  document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.getAttribute('data-close')));
  });

  [els.detailModal].forEach(overlay => {
    if (!overlay) return;
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });
}

function bindExportButton() {
  const exportBtn = document.getElementById('exportPdfBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', exportToPDF);
  }
}

// ========== INITIALISATION ==========
function init() {
  loadData();
  initTheme();
  bindTableActions();
  bindModalActions();
  bindFilters();
  bindExportButton();
  renderAll();
}

// Attendre que le DOM soit chargé
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
