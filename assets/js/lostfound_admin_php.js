const OBJETS_KEY = 'declarations';
const SIGNALEMENTS_KEY = 'commentaires';
const DECLARANTS_NON_INSCRITS_KEY = 'declarants_non_inscrits';
const INITIAL_OBJETS = (window.LOSTFOUND_ADMIN_CONFIG && window.LOSTFOUND_ADMIN_CONFIG.initialObjets) || [];
const INITIAL_SIGNALEMENTS = (window.LOSTFOUND_ADMIN_CONFIG && window.LOSTFOUND_ADMIN_CONFIG.initialSignalements) || [];

const trajetMap = {
  201: 'Paris -> Lyon',
  202: 'Lille -> Bruxelles',
  203: 'Marseille -> Nice',
  204: 'Bordeaux -> Toulouse',
  205: 'Nantes -> Rennes'
};

const passagersMap = {
  1: 'Sophie Martin',
  2: 'Youssef Belaid',
  3: 'Camille Bernard',
  4: 'Antoine Girard',
  5: 'Lea Martin'
};

let objets = [];
let signalements = [];
let declarantsNonInscrits = [];
let currentDetailId = null;

const els = {
  tbody: document.getElementById('declarationsTbody'),
  searchInput: document.getElementById('searchInput'),
  filterStatut: document.getElementById('filterStatut'),
  filterCategorie: document.getElementById('filterCategorie'),
  filterDeclarant: document.getElementById('filterDeclarant'),
  statTotal: document.getElementById('statTotal'),
  statPerdu: document.getElementById('statPerdu'),
  statRetrouve: document.getElementById('statRetrouve'),
  statRestitue: document.getElementById('statRestitue'),
  addModal: document.getElementById('addModal'),
  detailModal: document.getElementById('detailModal'),
  declarantCreateModal: document.getElementById('declarantCreateModal'),
  declarantsListModal: document.getElementById('declarantsListModal'),
  addForm: document.getElementById('addForm'),
  detailForm: document.getElementById('detailForm'),
  declarantCreateForm: document.getElementById('declarantCreateForm'),
  addAnonymous: document.getElementById('addAnonymous'),
  addAnonNameWrap: document.getElementById('addAnonNameWrap'),
  addAnonName: document.getElementById('addAnonName'),
  declarantName: document.getElementById('declarantName'),
  declarantsCount: document.getElementById('declarantsCount'),
  declarantsList: document.getElementById('declarantsList'),
  commentsList: document.getElementById('commentsList'),
  iaSuggestion: document.getElementById('iaSuggestion'),
  countBadge: document.getElementById('countBadge')
};

function seedDemoDataIfNeeded() {
}

function loadData() {
  objets = Array.isArray(INITIAL_OBJETS) ? JSON.parse(JSON.stringify(INITIAL_OBJETS)) : [];
  signalements = Array.isArray(INITIAL_SIGNALEMENTS) ? JSON.parse(JSON.stringify(INITIAL_SIGNALEMENTS)) : [];
}

function saveObjets() {
}

function saveSignalements() {
}

function normalizeDeclarantName(name) {
  return String(name || '').replace(/\s+/g, ' ').trim();
}

function loadDeclarantsNonInscrits() {
  try {
    const raw = JSON.parse(localStorage.getItem(DECLARANTS_NON_INSCRITS_KEY) || '[]');
    declarantsNonInscrits = Array.isArray(raw) ? raw.filter(item => item && typeof item.nom === 'string') : [];
  } catch (_) {
    declarantsNonInscrits = [];
  }
}

function saveDeclarantsNonInscrits() {
  localStorage.setItem(DECLARANTS_NON_INSCRITS_KEY, JSON.stringify(declarantsNonInscrits));
}

function ensureDeclarantsFromObjets() {
  let changed = false;
  objets.forEach(o => {
    const nom = normalizeDeclarantName(o.anonyme_nom || '');
    if (o.passager_id || !nom) return;
    const exists = declarantsNonInscrits.some(item => normalizeDeclarantName(item.nom).toLowerCase() === nom.toLowerCase());
    if (!exists) {
      declarantsNonInscrits.push({
        id: nextId(declarantsNonInscrits),
        nom,
        created_at: new Date().toISOString()
      });
      changed = true;
    }
  });
  if (changed) saveDeclarantsNonInscrits();
}

function addDeclarantNonInscrit(name) {
  const nom = normalizeDeclarantName(name);
  if (!nom) return false;

  const exists = declarantsNonInscrits.some(item => normalizeDeclarantName(item.nom).toLowerCase() === nom.toLowerCase());
  if (exists) return false;

  declarantsNonInscrits.push({
    id: nextId(declarantsNonInscrits),
    nom,
    created_at: new Date().toISOString()
  });
  saveDeclarantsNonInscrits();
  return true;
}

function clearDeclarantCreateValidation() {
  const formError = document.getElementById('declarantCreateError');
  const fieldError = document.getElementById('declarantNameError');
  if (formError) {
    formError.textContent = '';
    formError.classList.remove('show');
  }
  if (fieldError) {
    fieldError.textContent = '';
    fieldError.classList.remove('show');
  }
  if (els.declarantName) {
    els.declarantName.classList.remove('input-invalid');
  }
}

function setDeclarantCreateError(message) {
  const formError = document.getElementById('declarantCreateError');
  if (!formError) return;
  formError.textContent = message;
  formError.classList.add('show');
}

function setDeclarantNameError(message) {
  const fieldError = document.getElementById('declarantNameError');
  if (els.declarantName) els.declarantName.classList.add('input-invalid');
  if (!fieldError) return;
  fieldError.textContent = message;
  fieldError.classList.add('show');
}

function renderDeclarantsNonInscritsList() {
  if (!els.declarantsList || !els.declarantsCount) return;

  const rows = declarantsNonInscrits
    .slice()
    .sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));

  els.declarantsCount.textContent = rows.length + (rows.length > 1 ? ' declarants' : ' declarant');

  if (!rows.length) {
    els.declarantsList.innerHTML = '<div class="comment-item"><div class="comment-meta">Aucun declarant non inscrit</div><div class="comment-msg">Ajoutez un nouveau declarant depuis le menu.</div></div>';
    return;
  }

  els.declarantsList.innerHTML = rows.map(item => {
    return '<div class="comment-item declarant-row">' +
      '<div>' +
      '<div class="comment-msg">' + escapeHtml(item.nom) + '</div>' +
      '<div class="comment-meta">Ajoute le ' + escapeHtml(formatDate(item.created_at)) + '</div>' +
      '</div>' +
      '<button type="button" class="btn btn-outline" data-declarant-use="' + escapeHtml(item.nom) + '">Utiliser</button>' +
      '</div>';
  }).join('');
}

function submitServerAction(action, payload) {
  const form = document.createElement('form');
  form.method = 'post';
  form.action = 'lostfound_admin.php';

  const addField = (name, value) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value == null ? '' : String(value);
    form.appendChild(input);
  };

  addField('action', action);
  Object.keys(payload || {}).forEach((key) => addField(key, payload[key]));
  document.body.appendChild(form);
  form.submit();
}

function nextId(items) {
  return items.length ? Math.max(...items.map(i => i.id)) + 1 : 1;
}

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

function hasLengthBetween(value, min, max) {
  const len = (value || '').trim().length;
  return len >= min && len <= max;
}

function isValidDateYmd(value) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) return false;
  const parts = String(value).split('-').map(Number);
  if (parts.length !== 3) return false;
  const year = parts[0];
  const month = parts[1];
  const day = parts[2];
  const d = new Date(year, month - 1, day);
  return d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day;
}

function isDateNotInFuture(value) {
  if (!isValidDateYmd(value)) return false;
  const todayYmd = currentDateYmd();
  return value <= todayYmd;
}

function currentDateYmd() {
  const today = new Date();
  const y = String(today.getFullYear());
  const m = String(today.getMonth() + 1).padStart(2, '0');
  const d = String(today.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + d;
}

function isValidOptionalUrl(url) {
  if (!url) return true;
  try {
    const parsed = new URL(url);
    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
  } catch (_) {
    return false;
  }
}

function getImageValidationError(file) {
  if (!file) return null;
  const allowedTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
  const maxBytes = 2 * 1024 * 1024;

  if (!allowedTypes.includes(file.type)) {
    return 'Format image invalide. Utilisez JPG, PNG, WEBP ou GIF.';
  }

  if (file.size > maxBytes) {
    return 'Image trop lourde (max 2 Mo).';
  }

  return null;
}

function readFileAsDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result || ''));
    reader.onerror = () => reject(new Error('Lecture image impossible.'));
    reader.readAsDataURL(file);
  });
}

function getDeclarantLabel(o) {
  if (o.passager_id) return passagersMap[o.passager_id] || ('Passager #' + o.passager_id);
  return 'Anonyme - ' + (o.anonyme_nom || 'Externe');
}

function getDeclarantType(o) {
  return o.passager_id ? 'inscrit' : 'anonyme';
}

function getTrajetLabel(id) {
  return 'Trajet #' + id + (trajetMap[id] ? ' Â· ' + trajetMap[id] : '');
}

function statutBadge(statut) {
  if (statut === 'perdu') return '<span class="badge b-perdu">Perdu</span>';
  if (statut === 'retrouve') return '<span class="badge b-retrouve">Retrouve</span>';
  return '<span class="badge b-restitue">Restitue</span>';
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

function excerpt(text, max = 68) {
  if (!text) return '';
  return text.length > max ? text.slice(0, max - 1) + 'â€¦' : text;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text == null ? '' : String(text);
  return div.innerHTML;
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

function renderStats() {
  els.statTotal.textContent = objets.length;
  els.statPerdu.textContent = objets.filter(o => o.statut === 'perdu').length;
  els.statRetrouve.textContent = objets.filter(o => o.statut === 'retrouve').length;
  els.statRestitue.textContent = objets.filter(o => o.statut === 'restitue').length;
}

function renderCards() {
  const rows = getFilteredObjets().sort((a, b) => b.id - a.id);
  if (!els.tbody) return;
  if (!rows.length) {
    els.tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><i class="fas fa-inbox"></i>Aucune declaration ne correspond aux filtres.</div></td></tr>';
    if (els.countBadge) els.countBadge.textContent = '0 publication';
    return;
  }

  if (els.countBadge) els.countBadge.textContent = rows.length + (rows.length > 1 ? ' publications' : ' publication');

  els.tbody.innerHTML = rows.map(o => {
    return '<tr data-id="' + o.id + '">' +
      '<td><code>#' + o.id + '</code></td>' +
      '<td>' + escapeHtml(getDeclarantLabel(o)) + '</td>' +
      '<td>' + escapeHtml(getTrajetLabel(o.trajet_id)) + '</td>' +
      '<td><span class="badge b-cat">' + escapeHtml(categorieLabel(o.categorie)) + '</span></td>' +
      '<td>' + escapeHtml(excerpt(o.description, 95)) + '</td>' +
      '<td>' + statutBadge(o.statut) + '</td>' +
      '<td>' +
        '<div class="acts">' +
          '<button class="ic ic-view" type="button" data-action="details" data-id="' + o.id + '" aria-label="Details"><i class="fas fa-eye"></i></button>' +
          '<button class="ic ic-del" type="button" data-action="delete" data-id="' + o.id + '" aria-label="Supprimer"><i class="fas fa-trash"></i></button>' +
        '</div>' +
      '</td>' +
    '</tr>';
  }).join('');
}

function renderAll() {
  renderStats();
  renderCards();
}

function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

function clearAddForm() {
  els.addForm.reset();
  els.addAnonNameWrap.style.display = 'none';
  clearAddValidation();
}

function clearAddValidation() {
  const formError = document.getElementById('addFormError');
  formError.textContent = '';
  formError.classList.remove('show');

  ['addCategorie','addStatut','addTrajet','addDescription','addPhoto','addDate','addAnonName'].forEach(fieldId => {
    const input = document.getElementById(fieldId);
    const error = document.getElementById(fieldId + 'Error');
    if (input) input.classList.remove('input-invalid');
    if (error) {
      error.textContent = '';
      error.classList.remove('show');
    }
  });
}

function setAddFormError(message) {
  const formError = document.getElementById('addFormError');
  formError.textContent = message;
  formError.classList.add('show');
}

function setFieldError(fieldId, message) {
  const input = document.getElementById(fieldId);
  const error = document.getElementById(fieldId + 'Error');
  if (input) input.classList.add('input-invalid');
  if (error) {
    error.textContent = message;
    error.classList.add('show');
  }
}

function clearFieldError(fieldId) {
  const input = document.getElementById(fieldId);
  const error = document.getElementById(fieldId + 'Error');
  if (input) input.classList.remove('input-invalid');
  if (error) {
    error.textContent = '';
    error.classList.remove('show');
  }
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
      '<div class="comment-meta">' + escapeHtml(formatDateTime(s.date_signalement)) + ' Â· Conducteur #' + escapeHtml(String(s.conducteur_id)) + '</div>' +
      '<div class="comment-msg">' + escapeHtml(s.message) + '</div>' +
      '</div>';
  }).join('');
}

function extractKeywords(text) {
  if (!text) return [];
  const stop = new Set(['le','la','les','de','des','du','un','une','et','ou','dans','sur','avec','pour','par','au','aux','en','a','est','ce','cet','cette','se','sous']);
  return text
    .toLowerCase()
    .replace(/[^a-z0-9\s]/g, ' ')
    .split(/\s+/)
    .filter(w => w.length > 2 && !stop.has(w));
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
  const words = extractKeywords(last.message);

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
    els.iaSuggestion.textContent = 'ðŸ” IA : Aucun rapprochement clair detecte pour le dernier commentaire.';
    return;
  }

  const txt = 'ðŸ” IA : Cet objet ressemble a la declaration #' + best.id +
    ' (' + excerpt(best.description, 35) + ') faite par ' +
    getDeclarantLabel(best) + ' le ' + formatDate(best.date_perte) + '.';
  els.iaSuggestion.textContent = txt;
}

function deleteDeclaration(id) {
  const ok = confirm('Supprimer cette declaration et tous ses commentaires associes ?');
  if (!ok) return;
  submitServerAction('delete_declaration', { id });
}

function bindEvents() {
  const addDateInput = document.getElementById('addDate');
  const addTrajetInput = document.getElementById('addTrajet');
  if (addDateInput) {
    addDateInput.setAttribute('max', currentDateYmd());
  }
  if (addTrajetInput) {
    addTrajetInput.addEventListener('input', e => {
      e.target.value = String(e.target.value || '').replace(/[^\d]/g, '');
      clearFieldError('addTrajet');
    });
  }
  const addCategorie = document.getElementById('addCategorie');
  const addStatut = document.getElementById('addStatut');
  const addDescription = document.getElementById('addDescription');
  const addPhoto = document.getElementById('addPhoto');
  const addDate = document.getElementById('addDate');
  const addAnonName = document.getElementById('addAnonName');
  if (addCategorie) addCategorie.addEventListener('change', () => clearFieldError('addCategorie'));
  if (addStatut) addStatut.addEventListener('change', () => clearFieldError('addStatut'));
  if (addDescription) addDescription.addEventListener('input', () => clearFieldError('addDescription'));
  if (addPhoto) addPhoto.addEventListener('change', () => clearFieldError('addPhoto'));
  if (addDate) addDate.addEventListener('change', () => clearFieldError('addDate'));
  if (addAnonName) addAnonName.addEventListener('input', () => clearFieldError('addAnonName'));

  const openDeclarantsManagerBtn = document.getElementById('openDeclarantsManagerBtn');
  const openNewDeclarantFromListBtn = document.getElementById('openNewDeclarantFromListBtn');

  if (openDeclarantsManagerBtn) {
    openDeclarantsManagerBtn.addEventListener('click', () => {
      renderDeclarantsNonInscritsList();
      openModal('declarantsListModal');
    });
  }

  if (openNewDeclarantFromListBtn) {
    openNewDeclarantFromListBtn.addEventListener('click', () => {
      closeModal('declarantsListModal');
      clearDeclarantCreateValidation();
      if (els.declarantName) els.declarantName.value = '';
      openModal('declarantCreateModal');
    });
  }

  document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.getAttribute('data-close')));
  });

  [els.addModal, els.detailModal, els.declarantCreateModal, els.declarantsListModal].forEach(overlay => {
    if (!overlay) return;
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });

  if (els.declarantName) {
    els.declarantName.addEventListener('input', clearDeclarantCreateValidation);
  }

  if (els.declarantCreateForm) {
    els.declarantCreateForm.addEventListener('submit', e => {
      e.preventDefault();
      clearDeclarantCreateValidation();

      const nom = normalizeDeclarantName(els.declarantName ? els.declarantName.value : '');
      if (!hasLengthBetween(nom, 2, 80)) {
        setDeclarantCreateError('Veuillez corriger le champ en rouge.');
        setDeclarantNameError('Nom invalide (2 a 80 caracteres).');
        if (els.declarantName) els.declarantName.focus();
        return;
      }

      if (!addDeclarantNonInscrit(nom)) {
        setDeclarantCreateError('Ce declarant existe deja dans la liste.');
        setDeclarantNameError('Choisissez un nom different.');
        if (els.declarantName) els.declarantName.focus();
        return;
      }

      renderDeclarantsNonInscritsList();
      closeModal('declarantCreateModal');
      openModal('declarantsListModal');
    });
  }

  if (els.declarantsList) {
    els.declarantsList.addEventListener('click', e => {
      const btn = e.target.closest('button[data-declarant-use]');
      if (!btn) return;
      const nom = normalizeDeclarantName(btn.getAttribute('data-declarant-use') || '');
      if (!nom) return;

      if (els.addAnonymous && els.addAnonNameWrap && els.addAnonName) {
        els.addAnonymous.checked = true;
        els.addAnonNameWrap.style.display = 'block';
        els.addAnonName.value = nom;
        closeModal('declarantsListModal');
        openModal('addModal');
      }
    });
  }

  if (els.addAnonymous && els.addAnonNameWrap && els.addAnonName) {
    els.addAnonymous.addEventListener('change', () => {
      const show = els.addAnonymous.checked;
      els.addAnonNameWrap.style.display = show ? 'block' : 'none';
      if (!show) {
        els.addAnonName.value = '';
        clearFieldError('addAnonName');
      }
    });
  }

  if (els.addForm) {
    els.addForm.addEventListener('submit', async e => {
      e.preventDefault();
      clearAddValidation();

    const categorie = (document.getElementById('addCategorie').value || '').trim();
    const statut = (document.getElementById('addStatut').value || '').trim();
    const trajetRaw = (document.getElementById('addTrajet').value || '').trim();
    const description = (document.getElementById('addDescription').value || '').trim();
    const photoFile = document.getElementById('addPhoto').files[0] || null;
    const datePerte = (document.getElementById('addDate').value || '').trim();
    const anonymous = els.addAnonymous.checked;
    const anonName = (els.addAnonName.value || '').trim();

    const allowedCategories = ['electronique', 'vetement', 'document', 'bagage', 'autre'];
    const allowedStatus = ['perdu', 'retrouve', 'restitue'];
    const trajetId = Number(trajetRaw);
    const errors = {};

    if (!allowedCategories.includes(categorie)) {
      errors.addCategorie = 'Choisissez une categorie valide.';
    }

    if (!allowedStatus.includes(statut)) {
      errors.addStatut = 'Choisissez un statut valide.';
    }

    if (!Number.isInteger(trajetId) || trajetId < 1) {
      errors.addTrajet = 'Trajet ID invalide (entier positif attendu).';
    }

    if (!hasLengthBetween(description, 10, 1200)) {
      errors.addDescription = 'Description invalide (entre 10 et 1200 caracteres).';
    }

    if (!isValidDateYmd(datePerte) || !isDateNotInFuture(datePerte)) {
      errors.addDate = 'Date de perte invalide ou future.';
    }

    const imageError = getImageValidationError(photoFile);
    if (imageError) {
      errors.addPhoto = imageError;
    }

    if (anonymous && !hasLengthBetween(anonName, 2, 80)) {
      errors.addAnonName = 'Nom externe requis (2 a 80 caracteres).';
    }

    const errorFields = Object.keys(errors);
    if (errorFields.length > 0) {
      setAddFormError('Veuillez corriger les champs en rouge.');
      errorFields.forEach(fieldId => setFieldError(fieldId, errors[fieldId]));
      const firstField = document.getElementById(errorFields[0]);
      if (firstField) firstField.focus();
      return;
    }

    let photoUrl = '';
    if (photoFile) {
      try {
        photoUrl = await readFileAsDataUrl(photoFile);
      } catch (_) {
        setAddFormError('Impossible de lire l image selectionnee.');
        setFieldError('addPhoto', 'Reessayez avec une autre image.');
        return;
      }
    }

    if (anonymous) {
      addDeclarantNonInscrit(anonName);
    }

      submitServerAction('create_declaration', {
        titre: 'Declaration #' + Date.now(),
        description,
        categorie,
        statut,
        photo_url: photoUrl,
        date_perte: datePerte,
        trajet_id: trajetId,
        passager_id: anonymous ? '' : (Math.floor(Math.random() * 5) + 1),
        anonyme_nom: anonymous ? anonName : ''
      });
    });
  }

  if (els.tbody) {
    els.tbody.addEventListener('click', e => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;
      const id = Number(btn.getAttribute('data-id'));
      const action = btn.getAttribute('data-action');

      if (action === 'details') openDetails(id);
      if (action === 'delete') deleteDeclaration(id);
    });
  }

  if (els.searchInput) els.searchInput.addEventListener('input', renderCards);
  if (els.filterStatut) els.filterStatut.addEventListener('change', renderCards);
  if (els.filterCategorie) els.filterCategorie.addEventListener('change', renderCards);
  if (els.filterDeclarant) els.filterDeclarant.addEventListener('change', renderCards);

  if (els.detailForm) {
    els.detailForm.addEventListener('submit', e => {
      e.preventDefault();
    });
  }

  document.getElementById('markRestitueBtn').addEventListener('click', () => {
    if (currentDetailId == null) return;

    submitServerAction('mark_restitue', { id: currentDetailId });
  });

  document.getElementById('deleteFromModalBtn').addEventListener('click', () => {
    if (currentDetailId == null) return;

    if (!confirm('Supprimer cette declaration et tous ses commentaires associes ?')) {
      return;
    }

    deleteDeclaration(currentDetailId);
  });

  document.getElementById('addCommentBtn').addEventListener('click', () => {
    if (currentDetailId == null) return;

    const conducteurId = Number(document.getElementById('commentConducteurId').value);
    const message = (document.getElementById('commentMessage').value || '').trim();
    if (!conducteurId || !message) {
      alert('Renseignez conducteur_id et message.');
      return;
    }

    submitServerAction('add_comment', {
      declaration_id: currentDetailId,
      conducteur_id: conducteurId,
      message
    });
  });
}

function init() {
  loadData();
  loadDeclarantsNonInscrits();
  ensureDeclarantsFromObjets();
  bindEvents();
  renderDeclarantsNonInscritsList();
  renderAll();
}

init();

