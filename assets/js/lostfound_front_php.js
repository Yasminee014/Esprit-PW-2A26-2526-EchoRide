(function () {
  'use strict';

  const CFG = window.LOSTFOUND_FRONT_CONFIG || { initialObjets: [], initialSignalements: [], initialStats: {}, currentUserId: 0 };
  let objets = JSON.parse(JSON.stringify(CFG.initialObjets || []));
  const currentUserId = parseInt(CFG.currentUserId || 0, 10);

  // ─── DOM ───────────────────────────────────────────────────
  const publishModalBg    = document.getElementById('publishModal');
  const publishForm       = document.getElementById('publishForm');
  const openPublishBtn    = document.getElementById('openPublishModalBtn');
  const closePublishBtn   = document.getElementById('closePublish');
  const detailModalBg     = document.getElementById('detailModal');
  const detailTitleEl     = document.getElementById('detailTitle');
  const detailBodyEl      = document.getElementById('detailBody');
  const closeDetailBtn    = document.getElementById('closeDetail');
  const threadModalBg     = document.getElementById('threadModal');
  const closeThreadBtn    = document.getElementById('closeThread');
  const searchInput       = document.getElementById('searchInput');
  const filterCategory    = document.getElementById('filterCategory');
  const filterStatus      = document.getElementById('filterStatus');
  const resetFiltersBtn   = document.getElementById('resetFilters');
  const rowsContainer     = document.getElementById('rows');
  const countBadgeEl      = document.getElementById('countBadge');
  const heroTotalEl       = document.getElementById('heroTotal');
  const heroOpenEl        = document.getElementById('heroOpen');
  const heroResolvedEl    = document.getElementById('heroResolved');
  const publishFormError  = document.getElementById('publishFormError');

  let editMode = false;
  let editId   = null;

  // ─── HELPERS ───────────────────────────────────────────────
  function esc(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function openModal(bg) {
    if (bg) bg.style.display = 'flex';
  }
  function closeModal(bg) {
    if (bg) bg.style.display = 'none';
  }

  function statusLabel(s) {
    return { perdu: 'Perdu', retrouve: 'Retrouvé', restitue: 'Restitué' }[s] || s;
  }

  function submitHiddenForm(fields) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'lostfound_front.php';
    form.style.display = 'none';
    Object.keys(fields).forEach(function (k) {
      var inp = document.createElement('input');
      inp.type  = 'hidden';
      inp.name  = k;
      inp.value = fields[k] == null ? '' : String(fields[k]);
      form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
  }

  function updateStats() {
    var total    = objets.length;
    var open     = objets.filter(function (o) { return o.statut === 'perdu'; }).length;
    var resolved = objets.filter(function (o) { return o.statut === 'retrouve' || o.statut === 'restitue'; }).length;
    if (heroTotalEl)   heroTotalEl.textContent   = total;
    if (heroOpenEl)    heroOpenEl.textContent     = open;
    if (heroResolvedEl) heroResolvedEl.textContent = resolved;
  }

  // ─── RENDU DES CARTES ──────────────────────────────────────
  function renderCards(list) {
    if (!rowsContainer) return;
    if (!list || list.length === 0) {
      rowsContainer.innerHTML =
        '<div class="empty-state"><i class="fas fa-box-open"></i>' +
        '<p>Aucune publication trouvée</p></div>';
      if (countBadgeEl) countBadgeEl.textContent = '0 publication';
      return;
    }
    rowsContainer.innerHTML = list.map(function (obj) {
      var titre    = esc(obj.title || obj.titre || 'Objet');
      var statut   = esc(obj.statut || 'perdu');
      var categorie = esc(obj.categorie || '—');
      var lieu     = esc(obj.lieu_perte || '—');
      var datePerte = esc(obj.date_perte || '—');
      var desc     = esc(obj.description || '');
      var nom      = esc(obj.anonyme_nom || (obj.passager_id ? '#' + obj.passager_id : 'Anonyme'));
      var isOwner  = currentUserId > 0 && parseInt(obj.passager_id, 10) === currentUserId;
      return [
        '<article class="lost-card">',
          '<div class="card-head">',
            '<span class="card-id">#' + obj.id + '</span>',
            '<span class="status-badge status-' + statut + '">' + statusLabel(obj.statut) + '</span>',
          '</div>',
          '<div class="card-content">',
            '<div class="card-title">' + titre + '</div>',
            '<div class="card-desc">' + desc + '</div>',
            '<div class="tags">',
              '<span class="tag"><i class="fas fa-user"></i> ' + nom + '</span>',
              '<span class="tag"><i class="fas fa-layer-group"></i> ' + categorie + '</span>',
              '<span class="tag"><i class="fas fa-location-dot"></i> ' + lieu + '</span>',
              '<span class="tag"><i class="fas fa-calendar-days"></i> ' + datePerte + '</span>',
            '</div>',
            '<div class="card-actions">',
              '<button class="action-btn" data-action="detail" data-id="' + obj.id + '">Détails</button>',
              isOwner
                ? '<button class="action-btn action-danger" data-action="delete-decl" data-id="' + obj.id + '"><i class="fas fa-trash"></i></button>'
                : '',
            '</div>',
          '</div>',
        '</article>',
      ].join('');
    }).join('');

    if (countBadgeEl) {
      countBadgeEl.textContent = list.length + ' publication' + (list.length > 1 ? 's' : '');
    }
  }

  // ─── FILTRES ───────────────────────────────────────────────
  function getFiltered() {
    var search = searchInput ? searchInput.value.toLowerCase().trim() : '';
    var cat    = filterCategory ? filterCategory.value : '';
    var stat   = filterStatus   ? filterStatus.value   : '';
    return objets.filter(function (o) {
      var titre = (o.title || o.titre || '').toLowerCase();
      var lieu  = (o.lieu_perte || '').toLowerCase();
      var nom   = (o.anonyme_nom || '').toLowerCase();
      if (search && !titre.includes(search) && !lieu.includes(search) && !nom.includes(search)) return false;
      if (cat  && o.categorie !== cat)  return false;
      if (stat && o.statut    !== stat) return false;
      return true;
    });
  }

  function applyFilters() {
    renderCards(getFiltered());
  }

  if (searchInput)    searchInput.addEventListener('input',   applyFilters);
  if (filterCategory) filterCategory.addEventListener('change', applyFilters);
  if (filterStatus)   filterStatus.addEventListener('change',   applyFilters);
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', function () {
      if (searchInput)    searchInput.value    = '';
      if (filterCategory) filterCategory.value = '';
      if (filterStatus)   filterStatus.value   = '';
      applyFilters();
    });
  }

  // ─── MODAL FERMETURE ────────────────────────────────────────
  [publishModalBg, detailModalBg, threadModalBg].forEach(function (bg) {
    if (bg) bg.addEventListener('click', function (e) { if (e.target === bg) closeModal(bg); });
  });
  if (closePublishBtn) closePublishBtn.addEventListener('click', function () { closeModal(publishModalBg); });
  if (closeDetailBtn)  closeDetailBtn.addEventListener('click',  function () { closeModal(detailModalBg); });
  if (closeThreadBtn)  closeThreadBtn.addEventListener('click',  function () { closeModal(threadModalBg); });

  // ─── OUVRIR MODAL CRÉATION ─────────────────────────────────
  if (openPublishBtn) {
    openPublishBtn.addEventListener('click', function () {
      editMode = false;
      editId   = null;
      if (publishForm) publishForm.reset();
      if (publishFormError) publishFormError.innerHTML = '';
      var title = publishModalBg ? publishModalBg.querySelector('.modal-title') : null;
      if (title) title.innerHTML = '<i class="fas fa-pen-to-square"></i> Nouvelle déclaration';
      var submitBtn = publishForm ? publishForm.querySelector('[type="submit"]') : null;
      if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier';
      openModal(publishModalBg);
    });
  }

  // ─── SOUMISSION DU FORMULAIRE (CRÉER / MODIFIER) ───────────
  if (publishForm) {
    publishForm.addEventListener('submit', function (e) {
      e.preventDefault();

      var titre      = publishForm.querySelector('[name="titre"]').value.trim();
      var description = publishForm.querySelector('[name="description"]').value.trim();
      var categorie  = publishForm.querySelector('[name="categorie"]').value;
      var statut     = publishForm.querySelector('[name="statut"]').value   || 'perdu';
      var lieu_perte = publishForm.querySelector('[name="lieu_perte"]').value.trim();
      var trajet_id  = publishForm.querySelector('[name="trajet_id"]').value.trim() || '0';
      var date_perte = publishForm.querySelector('[name="date_perte"]').value;

      var errors = [];
      if (!titre)      errors.push('Le titre est obligatoire.');
      if (!description) errors.push('La description est obligatoire.');
      if (!categorie)  errors.push('La catégorie est obligatoire.');
      if (!date_perte) errors.push('La date de perte est obligatoire.');

      if (errors.length > 0) {
        if (publishFormError) publishFormError.innerHTML = errors.join('<br>');
        return;
      }
      if (publishFormError) publishFormError.innerHTML = '';

      var fields = {
        titre:       titre,
        description: description,
        categorie:   categorie,
        statut:      statut,
        lieu_perte:  lieu_perte,
        trajet_id:   trajet_id,
        date_perte:  date_perte,
        photo_url:   ''
      };

      if (editMode && editId) {
        fields.action = 'update_declaration';
        fields.id     = editId;
      } else {
        fields.action       = 'create_declaration';
        fields.passager_id  = currentUserId || '';
        fields.anonyme_nom  = '';
      }

      submitHiddenForm(fields);
    });
  }

  // ─── MODAL DÉTAIL ──────────────────────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action="detail"]');
    if (!btn) return;

    var id  = parseInt(btn.getAttribute('data-id'), 10);
    var obj = objets.find(function (o) { return o.id === id; });
    if (!obj) return;

    var titre   = esc(obj.title || obj.titre || 'Objet');
    var statut  = esc(obj.statut || 'perdu');
    var isOwner = currentUserId > 0 && parseInt(obj.passager_id, 10) === currentUserId;

    if (detailTitleEl) detailTitleEl.textContent = obj.title || obj.titre || 'Détail';

    if (detailBodyEl) {
      var photo = obj.photo_url
        ? '<img src="' + esc(obj.photo_url) + '" alt="Photo" style="width:100%;max-height:200px;object-fit:cover;border-radius:10px;margin-bottom:1rem;">'
        : '';

      detailBodyEl.innerHTML = [
        photo,
        '<div style="display:grid;gap:.55rem;margin-bottom:1rem;">',
          '<div><span style="color:#A7A9AC;">Catégorie&nbsp;:</span> <strong>' + esc(obj.categorie || '—') + '</strong></div>',
          '<div><span style="color:#A7A9AC;">Lieu&nbsp;:</span> <strong>' + esc(obj.lieu_perte || '—') + '</strong></div>',
          '<div><span style="color:#A7A9AC;">Date de perte&nbsp;:</span> <strong>' + esc(obj.date_perte || '—') + '</strong></div>',
          '<div><span style="color:#A7A9AC;">Statut&nbsp;:</span> <span class="status-badge status-' + statut + '">' + statusLabel(obj.statut) + '</span></div>',
          '<div style="margin-top:.3rem;"><span style="color:#A7A9AC;">Description&nbsp;:</span><br><span style="line-height:1.6;">' + esc(obj.description || '—') + '</span></div>',
        '</div>',
        isOwner
          ? '<div style="display:flex;gap:.6rem;flex-wrap:wrap;">' +
              '<button class="btn-main" data-action="edit-decl" data-id="' + id + '" style="flex:1;min-width:120px;"><i class="fas fa-pen"></i> Modifier</button>' +
              '<button class="action-btn action-danger" data-action="delete-decl" data-id="' + id + '" style="flex:0 0 auto;padding:.55rem 1rem;"><i class="fas fa-trash"></i> Supprimer</button>' +
            '</div>'
          : '',
      ].join('');
    }

    openModal(detailModalBg);
  });

  // ─── ÉDITER (depuis modal détail) ──────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action="edit-decl"]');
    if (!btn) return;

    var id  = parseInt(btn.getAttribute('data-id'), 10);
    var obj = objets.find(function (o) { return o.id === id; });
    if (!obj) return;

    closeModal(detailModalBg);
    editMode = true;
    editId   = id;

    if (publishForm) {
      publishForm.querySelector('[name="titre"]').value      = obj.title || obj.titre || '';
      publishForm.querySelector('[name="description"]').value = obj.description || '';
      publishForm.querySelector('[name="categorie"]').value  = obj.categorie || '';
      publishForm.querySelector('[name="statut"]').value     = obj.statut || 'perdu';
      publishForm.querySelector('[name="lieu_perte"]').value = obj.lieu_perte || '';
      publishForm.querySelector('[name="trajet_id"]').value  = obj.trajet_id || '';
      publishForm.querySelector('[name="date_perte"]').value = obj.date_perte || '';

      if (publishFormError) publishFormError.innerHTML = '';

      var title = publishModalBg ? publishModalBg.querySelector('.modal-title') : null;
      if (title) title.innerHTML = '<i class="fas fa-pen"></i> Modifier la déclaration #' + id;

      var submitBtn = publishForm.querySelector('[type="submit"]');
      if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-save"></i> Enregistrer les modifications';
    }

    openModal(publishModalBg);
  });

  // ─── SUPPRIMER ─────────────────────────────────────────────
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action="delete-decl"]');
    if (!btn) return;

    var id = parseInt(btn.getAttribute('data-id'), 10);
    if (!confirm('Supprimer cette déclaration ? Cette action est irréversible.')) return;

    submitHiddenForm({ action: 'delete_declaration', id: id });
  });

  // ─── IA DESCRIPTION (assistance simple) ────────────────────
  var aiBtn = document.getElementById('generateDescriptionAiBtn');
  if (aiBtn) {
    aiBtn.addEventListener('click', function () {
      var titreField = publishForm ? publishForm.querySelector('[name="titre"]') : null;
      var descField  = publishForm ? publishForm.querySelector('[name="description"]') : null;
      var hint       = document.getElementById('descriptionAiHint');
      if (!titreField || !titreField.value.trim()) {
        if (hint) hint.textContent = 'Entrez un titre d\'abord.';
        return;
      }
      if (hint) hint.textContent = '';
      if (descField && !descField.value.trim()) {
        var t = titreField.value.trim();
        descField.value = 'Objet « ' + t + ' » perdu. Si vous l\'avez trouvé, merci de contacter le déclarant via cette plateforme.';
      }
    });
  }

  // ─── INITIALISATION ────────────────────────────────────────
  renderCards(objets);
  updateStats();

})();
