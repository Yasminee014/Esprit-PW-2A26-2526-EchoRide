const OBJETS_KEY = 'declarations';
    const SIGNALEMENTS_KEY = 'commentaires';
  const DECLARANTS_NON_INSCRITS_KEY = 'declarants_non_inscrits';
    const INITIAL_OBJETS = (window.LOSTFOUND_FRONT_CONFIG && window.LOSTFOUND_FRONT_CONFIG.initialObjets) || [];
    const INITIAL_SIGNALEMENTS = (window.LOSTFOUND_FRONT_CONFIG && window.LOSTFOUND_FRONT_CONFIG.initialSignalements) || [];

    const users = [
      { id: 1, name: 'Sophie Martin' },
      { id: 2, name: 'Youssef Belaid' },
      { id: 3, name: 'Camille Bernard' },
      { id: 4, name: 'Antoine Girard' },
      { id: 5, name: 'Lea Martin' }
    ];

    const trajetMap = {
      201: 'Paris -> Lyon',
      202: 'Lille -> Bruxelles',
      203: 'Marseille -> Nice',
      204: 'Bordeaux -> Toulouse',
      205: 'Nantes -> Rennes'
    };

    const els = {
      rows: document.getElementById('rows'),
      userSwitcher: document.getElementById('userSwitcher'),
      currentUserName: document.getElementById('currentUserName'),
      signInBtn: document.getElementById('signInBtn'),
      signUpBtn: document.getElementById('signUpBtn'),
      searchInput: document.getElementById('searchInput'),
      filterCategory: document.getElementById('filterCategory'),
      filterStatus: document.getElementById('filterStatus'),
      resetFilters: document.getElementById('resetFilters'),
      countBadge: document.getElementById('countBadge'),
      heroTotal: document.getElementById('heroTotal'),
      heroOpen: document.getElementById('heroOpen'),
      heroResolved: document.getElementById('heroResolved'),
      publishForm: document.getElementById('publishForm'),
      publishModal: document.getElementById('publishModal'),
      detailModal: document.getElementById('detailModal'),
      threadModal: document.getElementById('threadModal'),
      declarantCreateModalFront: document.getElementById('declarantCreateModalFront'),
      declarantsListModalFront: document.getElementById('declarantsListModalFront'),
      declarantCreateFormFront: document.getElementById('declarantCreateFormFront'),
      declarantNameFront: document.getElementById('declarantNameFront'),
      declarantsCountFront: document.getElementById('declarantsCountFront'),
      declarantsListFront: document.getElementById('declarantsListFront'),
      selectedDeclarantInfo: document.getElementById('selectedDeclarantInfo'),
      generateDescriptionAiBtn: document.getElementById('generateDescriptionAiBtn'),
      descriptionAiHint: document.getElementById('descriptionAiHint'),
      publicationsImageModal: document.getElementById('publicationsImageModal'),
      publicationsSingleImage: document.getElementById('publicationsSingleImage')
    };

    let currentUserId = 1;
    let activePostId = 0;
    let objets = [];
    let signalements = [];
    let declarantsNonInscrits = [];
    let selectedDeclarantNonInscrit = '';

    function seedDemoDataIfNeeded() {
    }

    function saveObjets() {
    }

    function saveSignalements() {
    }

    function nextId(list) {
      if (!list.length) {
        return 1;
      }
      return list.reduce((max, item) => Math.max(max, Number(item.id) || 0), 0) + 1;
    }

   function loadData() {
    // Forcer l'utilisation des données PHP, ignorer localStorage
    if (window.LOSTFOUND_FRONT_CONFIG && window.LOSTFOUND_FRONT_CONFIG.initialObjets) {
        objets = JSON.parse(JSON.stringify(window.LOSTFOUND_FRONT_CONFIG.initialObjets));
        signalements = JSON.parse(JSON.stringify(window.LOSTFOUND_FRONT_CONFIG.initialSignalements || []));
        
        // Mettre à jour localStorage pour la cohérence
        localStorage.setItem('declarations', JSON.stringify(objets));
        localStorage.setItem('commentaires', JSON.stringify(signalements));
    } else {
        // Fallback vers localStorage si config PHP manquante
        objets = JSON.parse(localStorage.getItem('declarations') || '[]');
        signalements = JSON.parse(localStorage.getItem('commentaires') || '[]');
    }
    
    console.log('Objets chargés :', objets.length);
}

    function normalizeDeclarantName(name) {
      return String(name || '').replace(/\s+/g, ' ').trim();
    }

    function loadDeclarantsNonInscrits() {
      try {
        const raw = JSON.parse(localStorage.getItem(DECLARANTS_NON_INSCRITS_KEY) || '[]');
        declarantsNonInscrits = Array.isArray(raw) ? raw.filter((item) => item && typeof item.nom === 'string') : [];
      } catch (_) {
        declarantsNonInscrits = [];
      }
    }

    function saveDeclarantsNonInscrits() {
      localStorage.setItem(DECLARANTS_NON_INSCRITS_KEY, JSON.stringify(declarantsNonInscrits));
    }

    function addDeclarantNonInscrit(name) {
      const nom = normalizeDeclarantName(name);
      if (!nom) {
        return false;
      }
      const exists = declarantsNonInscrits.some((item) => normalizeDeclarantName(item.nom).toLowerCase() === nom.toLowerCase());
      if (exists) {
        return false;
      }
      declarantsNonInscrits.push({ id: nextId(declarantsNonInscrits), nom, created_at: nowIso() });
      saveDeclarantsNonInscrits();
      return true;
    }

    function ensureDeclarantsFromObjets() {
      let changed = false;
      objets.forEach((obj) => {
        if (obj.passager_id) {
          return;
        }
        const nom = normalizeDeclarantName(obj.anonyme_nom || '');
        if (!nom) {
          return;
        }
        const exists = declarantsNonInscrits.some((item) => normalizeDeclarantName(item.nom).toLowerCase() === nom.toLowerCase());
        if (!exists) {
          declarantsNonInscrits.push({ id: nextId(declarantsNonInscrits), nom, created_at: nowIso() });
          changed = true;
        }
      });
      if (changed) {
        saveDeclarantsNonInscrits();
      }
    }

    function updateSelectedDeclarantInfo() {
      if (!els.selectedDeclarantInfo) {
        return;
      }
      if (!selectedDeclarantNonInscrit) {
        els.selectedDeclarantInfo.style.display = 'none';
        els.selectedDeclarantInfo.textContent = '';
        return;
      }
      els.selectedDeclarantInfo.style.display = 'block';
      els.selectedDeclarantInfo.textContent = 'Declarant non inscrit selectionne: ' + selectedDeclarantNonInscrit;
    }

    function renderDeclarantsFrontList() {
      if (!els.declarantsListFront || !els.declarantsCountFront) {
        return;
      }
      const rows = declarantsNonInscrits.slice().sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
      els.declarantsCountFront.textContent = rows.length + (rows.length > 1 ? ' declarants' : ' declarant');

      if (!rows.length) {
        els.declarantsListFront.innerHTML = '<div class="comment"><div class="comment-meta">Aucun declarant non inscrit</div><div>Ajoutez un declarant depuis le menu.</div></div>';
        return;
      }

      els.declarantsListFront.innerHTML = rows.map((item) => (
        '<div class="comment declarant-row">' +
          '<div>' +
            '<div>' + escapeHtml(item.nom) + '</div>' +
            '<div class="comment-meta">Ajoute le ' + escapeHtml(formatDate(item.created_at)) + '</div>' +
          '</div>' +
          '<button type="button" class="btn-outline" data-declarant-use-front="' + escapeHtml(item.nom) + '">Utiliser</button>' +
        '</div>'
      )).join('');
    }

    function submitServerAction(action, payload) {
      const form = document.createElement('form');
      form.method = 'post';
      form.action = 'lostfound_front.php';

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

    function nowIso() {
      return new Date().toISOString();
    }

    function escapeHtml(value) {
      const div = document.createElement('div');
      div.textContent = String(value == null ? '' : value);
      return div.innerHTML;
    }

    function labelCategory(value) {
      const map = {
        electronique: 'Electronique',
        vetement: 'Vetement',
        document: 'Document',
        bagage: 'Bagage',
        autre: 'Autre'
      };
      return map[value] || value || 'Autre';
    }

    function formatDate(dateValue) {
      const d = new Date(dateValue);
      if (Number.isNaN(d.getTime())) {
        return dateValue || '-';
      }
      return d.toLocaleDateString('fr-FR');
    }

    function formatDateTime(dateValue) {
      const d = new Date(dateValue);
      if (Number.isNaN(d.getTime())) {
        return dateValue || '-';
      }
      return d.toLocaleString('fr-FR');
    }

    function hasLengthBetween(value, min, max) {
      const len = (value || '').trim().length;
      return len >= min && len <= max;
    }

    function isValidDateYmd(value) {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(value || '')) {
        return false;
      }
      const parts = String(value).split('-').map(Number);
      if (parts.length !== 3) {
        return false;
      }
      const [year, month, day] = parts;
      const d = new Date(year, month - 1, day);
      return d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day;
    }

    function isDateNotInFuture(value) {
      if (!isValidDateYmd(value)) {
        return false;
      }
      const todayYmd = currentDateYmd();
      return value <= todayYmd;
    }

    function getImageValidationError(file) {
      if (!file) {
        return null;
      }

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

    function currentDateYmd() {
      const today = new Date();
      const y = String(today.getFullYear());
      const m = String(today.getMonth() + 1).padStart(2, '0');
      const d = String(today.getDate()).padStart(2, '0');
      return y + '-' + m + '-' + d;
    }

    function generateAiDescriptionText() {
      const title = document.getElementById('title').value.trim();
      const category = labelCategory(document.getElementById('category').value.trim() || 'autre');
      const place = document.getElementById('place').value.trim();
      const date = document.getElementById('lostDate').value.trim();
      const status = statusLabel(document.getElementById('objectStatus').value.trim() || 'perdu');

      const safeTitle = title || 'Objet personnel';
      const safePlace = place || 'lieu non precise';
      const safeDate = isValidDateYmd(date) ? formatDate(date) : 'date non precisee';

      return 'Objet: ' + safeTitle + '. Categorie: ' + category + '. Statut actuel: ' + status + '. ' +
        'Perdu a ' + safePlace + ' le ' + safeDate + '. Merci de contacter le declarant si vous avez une information utile.';
    }

    function drawWrappedText(ctx, text, x, y, maxWidth, lineHeight, maxLines) {
      const words = String(text || '').split(/\s+/);
      let line = '';
      let lineCount = 0;

      for (let i = 0; i < words.length; i += 1) {
        const testLine = line ? line + ' ' + words[i] : words[i];
        const width = ctx.measureText(testLine).width;
        if (width > maxWidth && line) {
          ctx.fillText(line, x, y + (lineCount * lineHeight));
          line = words[i];
          lineCount += 1;
          if (lineCount >= maxLines - 1) {
            break;
          }
        } else {
          line = testLine;
        }
      }

      if (lineCount < maxLines && line) {
        ctx.fillText(line, x, y + (lineCount * lineHeight));
      }
    }

    function buildPublicationsSingleImage(rows) {
      const items = rows.slice(0, 9);
      const cols = 3;
      const cardW = 350;
      const cardH = 220;
      const gap = 24;
      const padding = 34;
      const rowsCount = Math.max(1, Math.ceil(items.length / cols));
      const width = padding * 2 + cols * cardW + (cols - 1) * gap;
      const height = padding * 2 + rowsCount * cardH + (rowsCount - 1) * gap + 70;

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext('2d');

      const bg = ctx.createLinearGradient(0, 0, width, height);
      bg.addColorStop(0, '#0A1628');
      bg.addColorStop(1, '#0F3B6E');
      ctx.fillStyle = bg;
      ctx.fillRect(0, 0, width, height);

      ctx.fillStyle = '#ffffff';
      ctx.font = '700 34px Segoe UI';
      ctx.fillText('Mes publications objets perdus', padding, 50);

      items.forEach((obj, index) => {
        const row = Math.floor(index / cols);
        const col = index % cols;
        const x = padding + col * (cardW + gap);
        const y = padding + 30 + row * (cardH + gap);

        ctx.fillStyle = 'rgba(255,255,255,0.10)';
        ctx.fillRect(x, y, cardW, cardH);
        ctx.strokeStyle = 'rgba(97,179,250,0.50)';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, cardW, cardH);

        ctx.fillStyle = '#61B3FA';
        ctx.font = '700 20px Segoe UI';
        ctx.fillText('#' + obj.id + ' - ' + labelCategory(obj.categorie || 'autre'), x + 14, y + 30);

        ctx.fillStyle = '#ffffff';
        ctx.font = '600 19px Segoe UI';
        drawWrappedText(ctx, titleLabel(obj), x + 14, y + 62, cardW - 28, 24, 2);

        ctx.fillStyle = '#cfe3ff';
        ctx.font = '500 16px Segoe UI';
        drawWrappedText(ctx, descLabel(obj), x + 14, y + 118, cardW - 28, 20, 3);

        ctx.fillStyle = '#9ed0ff';
        ctx.font = '600 15px Segoe UI';
        ctx.fillText('Statut: ' + statusLabel(obj.statut || 'perdu'), x + 14, y + cardH - 34);
        ctx.fillText('Date: ' + formatDate(obj.date_perte), x + 14, y + cardH - 14);
      });

      return canvas.toDataURL('image/png');
    }

    function clearPublishValidation() {
      const formError = document.getElementById('publishFormError');
      formError.textContent = '';
      formError.classList.remove('show');

      ['title','description','category','objectStatus','place','photoFile','lostDate'].forEach((fieldId) => {
        const input = document.getElementById(fieldId);
        const error = document.getElementById(fieldId + 'Error');
        if (input) input.classList.remove('input-invalid');
        if (error) {
          error.textContent = '';
          error.classList.remove('show');
        }
      });
    }

    function setPublishFormError(message) {
      const formError = document.getElementById('publishFormError');
      formError.textContent = message;
      formError.classList.add('show');
    }

    function setPublishFieldError(fieldId, message) {
      const input = document.getElementById(fieldId);
      const error = document.getElementById(fieldId + 'Error');
      if (input) input.classList.add('input-invalid');
      if (error) {
        error.textContent = message;
        error.classList.add('show');
      }
    }

    function clearPublishFieldError(fieldId) {
      const input = document.getElementById(fieldId);
      const error = document.getElementById(fieldId + 'Error');
      if (input) input.classList.remove('input-invalid');
      if (error) {
        error.textContent = '';
        error.classList.remove('show');
      }
    }

    function declarantName(obj) {
      if (obj.passager_id) {
        const user = users.find((u) => u.id === Number(obj.passager_id));
        return user ? user.name : ('Passager #' + obj.passager_id);
      }
      return 'Anonyme - ' + (obj.anonyme_nom || 'Externe');
    }

    function placeLabel(obj) {
      if (obj.lieu_perte && String(obj.lieu_perte).trim() !== '') {
        return obj.lieu_perte;
      }
      return trajetMap[obj.trajet_id] || ('Trajet #' + (obj.trajet_id || '-'));
    }

    function titleLabel(obj) {
      if (obj.title && String(obj.title).trim() !== '') {
        return String(obj.title).trim();
      }
      const text = String(obj.description || '').trim();
      if (!text) {
        return 'Objet sans titre';
      }
      return text.length > 38 ? text.slice(0, 38) + '...' : text;
    }

    function descLabel(obj) {
      const text = String(obj.description || '').trim();
      if (!text) {
        return 'Aucune description';
      }
      return text.length > 120 ? text.slice(0, 120) + '...' : text;
    }

    function statusClass(status) {
      if (status === 'retrouve') {
        return 'status-retrouve';
      }
      if (status === 'restitue') {
        return 'status-restitue';
      }
      return 'status-perdu';
    }

    function statusLabel(status) {
      if (status === 'retrouve') {
        return 'Retrouve';
      }
      if (status === 'restitue') {
        return 'Restitue';
      }
      return 'Perdu';
    }

    function commentsForObject(objetId) {
      return signalements
        .filter((row) => Number(row.objet_id) === Number(objetId))
        .sort((a, b) => new Date(a.date_signalement) - new Date(b.date_signalement));
    }

    function getObjectById(id) {
      return objets.find((o) => Number(o.id) === Number(id));
    }

    function getFilteredObjets() {
      const q = (els.searchInput.value || '').trim().toLowerCase();
      const selectedCategory = (els.filterCategory.value || '').trim();
      const selectedStatus = (els.filterStatus.value || '').trim();

      return objets.filter((obj) => {
        const title = titleLabel(obj).toLowerCase();
        const desc = descLabel(obj).toLowerCase();
        const declarant = declarantName(obj).toLowerCase();
        const place = placeLabel(obj).toLowerCase();

        const searchOk = !q || title.includes(q) || desc.includes(q) || declarant.includes(q) || place.includes(q);
        const categoryOk = !selectedCategory || (obj.categorie || '') === selectedCategory;
        const statusOk = !selectedStatus || (obj.statut || 'perdu') === selectedStatus;

        return searchOk && categoryOk && statusOk;
      });
    }

    function renderStats() {
      const total = objets.length;
      const open = objets.filter((obj) => (obj.statut || 'perdu') === 'perdu').length;
      const resolved = objets.filter((obj) => ['retrouve', 'restitue'].includes(obj.statut || 'perdu')).length;

      els.heroTotal.textContent = String(total);
      els.heroOpen.textContent = String(open);
      els.heroResolved.textContent = String(resolved);
    }

    function renderUsers() {
      els.userSwitcher.innerHTML = users.map((u) => (
        '<button class="chip ' + (u.id === currentUserId ? 'active' : '') + '" data-user="' + u.id + '">' + escapeHtml(u.name) + '</button>'
      )).join('');

      els.userSwitcher.querySelectorAll('[data-user]').forEach((btn) => {
        btn.addEventListener('click', () => {
          currentUserId = Number(btn.getAttribute('data-user'));
          els.currentUserName.textContent = 'Profil';
          renderUsers();
          renderAll();
        });
      });
    }

    function renderRows() {
      const rows = getFilteredObjets().sort((a, b) => Number(b.id) - Number(a.id));
      els.countBadge.textContent = rows.length + (rows.length > 1 ? ' publications' : ' publication');

      if (!rows.length) {
        els.rows.innerHTML = '<div class="empty-state"><i class="fas fa-inbox"></i><p>Aucune publication ne correspond aux filtres.</p></div>';
        return;
      }

      els.rows.innerHTML = rows.map((obj, index) => {
        const owner = Number(obj.passager_id) === currentUserId;
        const commentsCount = commentsForObject(obj.id).length;
        const ownerActions = owner
          ? '<button class="action-btn" data-action="edit" data-id="' + obj.id + '"><i class="fas fa-pen"></i> Modifier</button>' +
            '<button class="action-btn action-danger" data-action="delete" data-id="' + obj.id + '"><i class="fas fa-trash"></i> Supprimer</button>'
          : '<button class="action-btn" type="button" disabled>Lecture seule</button><button class="action-btn" type="button" disabled>-</button>';

        return '<article class="lost-card" style="animation-delay:' + (index * 0.06).toFixed(2) + 's">' +
          '<div class="card-head">' +
            '<span class="card-id">#' + obj.id + '</span>' +
            '<span class="status-badge ' + statusClass(obj.statut || 'perdu') + '"><i class="fas fa-circle"></i> ' + escapeHtml(statusLabel(obj.statut || 'perdu')) + '</span>' +
          '</div>' +
          '<div class="card-content">' +
            '<div class="card-title">' + escapeHtml(titleLabel(obj)) + '</div>' +
            '<div class="card-desc">' + escapeHtml(descLabel(obj)) + '</div>' +
            '<div class="tags">' +
              '<span class="tag"><i class="fas fa-user"></i> ' + escapeHtml(declarantName(obj)) + '</span>' +
              '<span class="tag"><i class="fas fa-layer-group"></i> ' + escapeHtml(labelCategory(obj.categorie || 'autre')) + '</span>' +
              '<span class="tag"><i class="fas fa-location-dot"></i> ' + escapeHtml(placeLabel(obj)) + '</span>' +
              '<span class="tag"><i class="fas fa-calendar-days"></i> ' + escapeHtml(formatDate(obj.date_perte)) + '</span>' +
              '<span class="tag"><i class="fas fa-comments"></i> ' + commentsCount + '</span>' +
            '</div>' +
            '<div class="card-actions">' +
              '<button class="action-btn" data-action="detail" data-id="' + obj.id + '"><i class="fas fa-eye"></i> Details</button>' +
              '<button class="action-btn" data-action="thread" data-id="' + obj.id + '"><i class="fas fa-comments"></i> Commentaires</button>' +
              ownerActions +
            '</div>' +
          '</div>' +
        '</article>';
      }).join('');
    }

    function renderAll() {
      renderStats();
      renderRows();
    }

    function openDetail(id) {
      const obj = getObjectById(id);
      if (!obj) {
        return;
      }

      activePostId = id;
      const comments = commentsForObject(id);
      document.getElementById('detailTitle').textContent = 'Details publication #' + obj.id;

      const commentsHtml = comments.length
        ? comments.map((c) => (
          '<div class="comment"><div class="comment-meta">#' + c.id + ' - Conducteur #' + escapeHtml(String(c.conducteur_id)) + ' - ' + escapeHtml(formatDateTime(c.date_signalement)) + '</div><div>' + escapeHtml(c.message) + '</div></div>'
        )).join('')
        : '<div class="comment"><div class="comment-meta">Aucun commentaire</div><div>Pas encore de signalement conducteur.</div></div>';

      document.getElementById('detailBody').innerHTML =
        '<div class="comment" style="margin-bottom:.7rem">' +
          '<div class="modal-title" style="margin-bottom:.35rem">' + escapeHtml(titleLabel(obj)) + '</div>' +
          '<div class="muted">Declarant: ' + escapeHtml(declarantName(obj)) + '</div>' +
          '<div class="muted">Categorie: ' + escapeHtml(labelCategory(obj.categorie || 'autre')) + ' | Statut: ' + escapeHtml(statusLabel(obj.statut || 'perdu')) + '</div>' +
          '<div class="muted">Lieu: ' + escapeHtml(placeLabel(obj)) + ' | Date: ' + escapeHtml(formatDate(obj.date_perte)) + '</div>' +
          '<p style="margin-top:.45rem">' + escapeHtml(obj.description || '') + '</p>' +
        '</div>' +
        '<div class="modal-title" style="margin-bottom:.5rem">Commentaires</div>' +
        commentsHtml +
        '<form id="addCommentForm" class="comment-box">' +
          '<textarea id="newComment" placeholder="Ajouter un commentaire conducteur"></textarea>' +
          '<button class="btn-main" style="margin-top:.5rem" type="submit"><i class="fas fa-paper-plane"></i> Publier commentaire</button>' +
        '</form>';

      els.detailModal.classList.add('open');

      const addCommentForm = document.getElementById('addCommentForm');
      if (addCommentForm) {
        addCommentForm.addEventListener('submit', (e) => {
          e.preventDefault();
          const newComment = document.getElementById('newComment');
          const msg = newComment ? newComment.value.trim() : '';
          if (!msg) {
            return;
          }

          submitServerAction('add_comment', {
            declaration_id: id,
            conducteur_id: 100 + currentUserId,
            message: msg
          });
        });
      }
    }

    function openThread(id) {
      const obj = getObjectById(id);
      if (!obj) {
        return;
      }

      activePostId = id;
      const comments = commentsForObject(id);
      document.getElementById('threadTitle').textContent = 'Commentaires publication #' + obj.id;
      document.getElementById('threadPreviewImage').src = obj.photo_url || 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=60';

      const commentsHtml = comments.length
        ? comments.map((c) => (
          '<div class="comment"><div class="comment-meta">#' + c.id + ' - Conducteur #' + escapeHtml(String(c.conducteur_id)) + ' - ' + escapeHtml(formatDateTime(c.date_signalement)) + '</div><div>' + escapeHtml(c.message) + '</div></div>'
        )).join('')
        : '<div class="comment"><div class="comment-meta">Aucun commentaire</div><div>Ajoutez le premier commentaire pour cette publication.</div></div>';

      document.getElementById('threadBody').innerHTML = commentsHtml +
        '<form id="newRootComment" class="comment-box">' +
          '<textarea id="rootMsg" placeholder="Nouveau commentaire principal"></textarea>' +
          '<button class="btn-main" style="margin-top:.5rem" type="submit"><i class="fas fa-plus"></i> Publier</button>' +
        '</form>';

      els.threadModal.classList.add('open');

      const newRootComment = document.getElementById('newRootComment');
      if (newRootComment) {
        newRootComment.addEventListener('submit', (e) => {
          e.preventDefault();
          const rootMsg = document.getElementById('rootMsg');
          const message = rootMsg ? rootMsg.value.trim() : '';
          if (!message) {
            return;
          }

          submitServerAction('add_comment', {
            declaration_id: id,
            conducteur_id: 100 + currentUserId,
            message
          });
        });
      }
    }

    function editPost(id) {
      const obj = getObjectById(id);
      if (!obj || Number(obj.passager_id) !== currentUserId) {
        return;
      }

      const nextTitle = prompt('Nouveau titre:', titleLabel(obj));
      if (nextTitle === null) {
        return;
      }

      const nextDescription = prompt('Nouvelle description:', obj.description || '');
      if (nextDescription === null) {
        return;
      }

      const nextCategory = prompt('Categorie (electronique, vetement, document, bagage, autre):', obj.categorie || 'autre');
      if (nextCategory === null) {
        return;
      }

      const nextPlace = prompt('Lieu de perte:', obj.lieu_perte || placeLabel(obj));
      if (nextPlace === null) {
        return;
      }

      const nextDate = prompt('Date de perte (YYYY-MM-DD):', obj.date_perte || '');
      if (nextDate === null) {
        return;
      }

      const allowed = ['electronique', 'vetement', 'document', 'bagage', 'autre'];
      const normalizedCategory = String(nextCategory).trim().toLowerCase();
      const normalizedTitle = String(nextTitle).trim();
      const normalizedDescription = String(nextDescription).trim();
      const normalizedPlace = String(nextPlace).trim();
      const normalizedDate = String(nextDate).trim();

      if (!hasLengthBetween(normalizedTitle, 5, 120)
        || !hasLengthBetween(normalizedDescription, 10, 1200)
        || !allowed.includes(normalizedCategory)
        || !hasLengthBetween(normalizedPlace, 3, 120)
        || !isValidDateYmd(normalizedDate)
        || !isDateNotInFuture(normalizedDate)) {
        alert('Modification invalide. Verifiez les champs saisis.');
        return;
      }

      submitServerAction('update_declaration', {
        id,
        titre: normalizedTitle,
        description: normalizedDescription,
        categorie: normalizedCategory,
        lieu_perte: normalizedPlace,
        date_perte: normalizedDate
      });
    }

    function removePost(id) {
      const obj = getObjectById(id);
      if (!obj || Number(obj.passager_id) !== currentUserId) {
        return;
      }

      if (!confirm('Supprimer cette publication ?')) {
        return;
      }

      submitServerAction('delete_declaration', { id });
    }

    function bindRowsActions() {
      els.rows.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) {
          return;
        }
        const action = btn.getAttribute('data-action');
        const id = Number(btn.getAttribute('data-id'));

        if (action === 'detail') {
          openDetail(id);
          return;
        }

        if (action === 'thread') {
          openThread(id);
          return;
        }

        if (action === 'edit') {
          editPost(id);
          return;
        }

        if (action === 'delete') {
          removePost(id);
        }
      });
    }

    function bindFilters() {
      els.searchInput.addEventListener('input', renderRows);
      els.filterCategory.addEventListener('change', renderRows);
      els.filterStatus.addEventListener('change', renderRows);

      els.resetFilters.addEventListener('click', () => {
        els.searchInput.value = '';
        els.filterCategory.value = '';
        els.filterStatus.value = '';
        renderRows();
      });
    }

    function bindPublishForm() {
      ['title','description','place'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('input', () => clearPublishFieldError(fieldId));
      });
      ['category','objectStatus','lostDate','photoFile','trajetId'].forEach((fieldId) => {
        document.getElementById(fieldId).addEventListener('change', () => clearPublishFieldError(fieldId));
      });

      els.publishForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearPublishValidation();

        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        const category = document.getElementById('category').value.trim();
        const objectStatus = document.getElementById('objectStatus').value.trim();
        const place = document.getElementById('place').value.trim();
        const trajetId = document.getElementById('trajetId').value.trim();
        const photoFile = document.getElementById('photoFile').files[0] || null;
        const date = document.getElementById('lostDate').value.trim();
        const allowedCategories = ['electronique', 'vetement', 'document', 'bagage', 'autre'];
        const allowedStatus = ['perdu', 'retrouve', 'restitue'];
        const errors = {};

        if (!hasLengthBetween(title, 5, 120)) {
          errors.title = 'Titre invalide (entre 5 et 120 caracteres).';
        }

        if (!hasLengthBetween(description, 10, 1200)) {
          errors.description = 'Description invalide (entre 10 et 1200 caracteres).';
        }

        if (!allowedCategories.includes(category)) {
          errors.category = 'Choisissez une categorie valide.';
        }

        if (!allowedStatus.includes(objectStatus)) {
          errors.objectStatus = 'Choisissez un statut valide.';
        }

        if (!hasLengthBetween(place, 3, 120)) {
          errors.place = 'Lieu de perte invalide (entre 3 et 120 caracteres).';
        }

        if (!/^[0-9]+$/.test(trajetId) || Number(trajetId) <= 0) {
          errors.trajetId = 'ID trajet invalide. Utilisez un nombre positif.';
        }

        if (!isValidDateYmd(date) || !isDateNotInFuture(date)) {
          errors.lostDate = 'Date de perte invalide ou future.';
        }

        const imageError = getImageValidationError(photoFile);
        if (imageError) {
          errors.photoFile = imageError;
        }

        const errorFields = Object.keys(errors);
        if (errorFields.length > 0) {
          setPublishFormError('Veuillez corriger les champs en rouge.');
          errorFields.forEach((fieldId) => setPublishFieldError(fieldId, errors[fieldId]));
          const firstField = document.getElementById(errorFields[0]);
          if (firstField) firstField.focus();
          return;
        }

        let photoUrl = '';
        if (photoFile) {
          try {
            photoUrl = await readFileAsDataUrl(photoFile);
          } catch (_) {
            setPublishFormError('Impossible de lire l image selectionnee.');
            setPublishFieldError('photoFile', 'Reessayez avec une autre image.');
            return;
          }
        }

        submitServerAction('create_declaration', {
          titre: title,
          description,
          categorie: category,
          statut: objectStatus,
          lieu_perte: place,
          trajet_id: Number(trajetId),
          photo_url: photoUrl,
          date_perte: date,
          passager_id: selectedDeclarantNonInscrit ? '' : currentUserId,
          anonyme_nom: selectedDeclarantNonInscrit ? selectedDeclarantNonInscrit : ''
        });

        if (selectedDeclarantNonInscrit) {
          addDeclarantNonInscrit(selectedDeclarantNonInscrit);
          selectedDeclarantNonInscrit = '';
          updateSelectedDeclarantInfo();
        }
      });

      if (els.generateDescriptionAiBtn) {
        els.generateDescriptionAiBtn.addEventListener('click', () => {
          const description = document.getElementById('description');
          description.value = generateAiDescriptionText();
          clearPublishFieldError('description');
          if (els.descriptionAiHint) {
            els.descriptionAiHint.textContent = 'Description generee par IA locale.';
          }
        });
      }
    }

    function bindModals() {
      const closeDetail = document.getElementById('closeDetail');
      const closeThread = document.getElementById('closeThread');
      const closePublish = document.getElementById('closePublish');
      const closePublicationsImage = document.getElementById('closePublicationsImage');
      const closeUsers = document.getElementById('closeUsers');
      const closeDeclarantCreateFront = document.getElementById('closeDeclarantCreateFront');
      const closeDeclarantsListFront = document.getElementById('closeDeclarantsListFront');
      const openNewDeclarantFromListFront = document.getElementById('openNewDeclarantFromListFront');
      const openPublishModalBtn = document.getElementById('openPublishModalBtn');
      const usersModal = document.getElementById('usersModal');

      if (closeDetail) closeDetail.addEventListener('click', () => els.detailModal.classList.remove('open'));
      if (closeThread) closeThread.addEventListener('click', () => els.threadModal.classList.remove('open'));
      if (closePublish) closePublish.addEventListener('click', () => els.publishModal.classList.remove('open'));
      if (closePublicationsImage) closePublicationsImage.addEventListener('click', () => els.publicationsImageModal.classList.remove('open'));
      if (closeUsers && usersModal) closeUsers.addEventListener('click', () => usersModal.classList.remove('open'));
      if (closeDeclarantCreateFront) closeDeclarantCreateFront.addEventListener('click', () => els.declarantCreateModalFront.classList.remove('open'));
      if (closeDeclarantsListFront) closeDeclarantsListFront.addEventListener('click', () => els.declarantsListModalFront.classList.remove('open'));
      if (openNewDeclarantFromListFront) {
        openNewDeclarantFromListFront.addEventListener('click', () => {
          els.declarantsListModalFront.classList.remove('open');
          els.declarantCreateModalFront.classList.add('open');
        });
      }

      [els.detailModal, els.threadModal, els.publishModal, els.publicationsImageModal, usersModal, els.declarantCreateModalFront, els.declarantsListModalFront].filter(Boolean).forEach((modal) => {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) {
            modal.classList.remove('open');
          }
        });
      });

      if (openPublishModalBtn) {
        openPublishModalBtn.addEventListener('click', () => {
          els.publishModal.classList.add('open');
          const titleInput = document.getElementById('title');
          if (titleInput) titleInput.focus();
        });
      }

      els.declarantCreateFormFront.addEventListener('submit', (e) => {
        e.preventDefault();
        const formError = document.getElementById('declarantCreateErrorFront');
        const fieldError = document.getElementById('declarantNameFrontError');
        const nom = normalizeDeclarantName(els.declarantNameFront.value);

        formError.textContent = '';
        formError.classList.remove('show');
        fieldError.textContent = '';
        fieldError.classList.remove('show');
        els.declarantNameFront.classList.remove('input-invalid');

        if (!hasLengthBetween(nom, 2, 80)) {
          formError.textContent = 'Veuillez corriger le champ en rouge.';
          formError.classList.add('show');
          fieldError.textContent = 'Nom invalide (2 a 80 caracteres).';
          fieldError.classList.add('show');
          els.declarantNameFront.classList.add('input-invalid');
          return;
        }

        if (!addDeclarantNonInscrit(nom)) {
          formError.textContent = 'Ce declarant existe deja.';
          formError.classList.add('show');
          return;
        }

        els.declarantNameFront.value = '';
        renderDeclarantsFrontList();
        els.declarantCreateModalFront.classList.remove('open');
        els.declarantsListModalFront.classList.add('open');
      });

      els.declarantsListFront.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-declarant-use-front]');
        if (!btn) {
          return;
        }
        selectedDeclarantNonInscrit = normalizeDeclarantName(btn.getAttribute('data-declarant-use-front') || '');
        updateSelectedDeclarantInfo();
        els.declarantsListModalFront.classList.remove('open');
        els.publishModal.classList.add('open');
      });
    }

    function bindNavbar() {
      const menuBtn = document.getElementById('menuBtn');
      const dropdown = document.getElementById('dropdownMenu');
      const quickThreadLink = document.getElementById('quickThreadLink');
      const openUsersTab = document.getElementById('openUsersTab');
      const openNewDeclarantFrontBtn = document.getElementById('openNewDeclarantFrontBtn');
      const openDeclarantsListFrontBtn = document.getElementById('openDeclarantsListFrontBtn');
      const openPublicationsImageBtn = document.getElementById('openPublicationsImageBtn');

      if (menuBtn && dropdown) {
        menuBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          dropdown.classList.toggle('show');
        });

        window.addEventListener('click', (e) => {
          if (!e.target.closest('.dropdown')) {
            dropdown.classList.remove('show');
          }
        });
      }

      if (quickThreadLink) {
        quickThreadLink.addEventListener('click', (e) => {
          e.preventDefault();
          if (dropdown) dropdown.classList.remove('show');
          if (!objets.length) {
            return;
          }
          const target = getObjectById(activePostId) || objets[objets.length - 1];
          if (target) {
            openThread(target.id);
          }
        });
      }

      if (openUsersTab) {
        openUsersTab.addEventListener('click', (e) => {
          e.preventDefault();
          if (dropdown) dropdown.classList.remove('show');
          const usersModal = document.getElementById('usersModal');
          if (usersModal) usersModal.classList.add('open');
        });
      }

      if (openNewDeclarantFrontBtn) {
        openNewDeclarantFrontBtn.addEventListener('click', (e) => {
          e.preventDefault();
          if (dropdown) dropdown.classList.remove('show');
          if (els.declarantCreateModalFront) els.declarantCreateModalFront.classList.add('open');
        });
      }

      if (openDeclarantsListFrontBtn) {
        openDeclarantsListFrontBtn.addEventListener('click', (e) => {
          e.preventDefault();
          if (dropdown) dropdown.classList.remove('show');
          renderDeclarantsFrontList();
          if (els.declarantsListModalFront) els.declarantsListModalFront.classList.add('open');
        });
      }

      if (openPublicationsImageBtn) {
        openPublicationsImageBtn.addEventListener('click', (e) => {
          e.preventDefault();
          if (dropdown) dropdown.classList.remove('show');
          const rows = objets.slice().sort((a, b) => Number(b.id) - Number(a.id));
          if (!rows.length) {
            return;
          }
          if (els.publicationsSingleImage) els.publicationsSingleImage.src = buildPublicationsSingleImage(rows);
          if (els.publicationsImageModal) els.publicationsImageModal.classList.add('open');
        });
      }

      if (els.signInBtn) {
        els.signInBtn.addEventListener('click', () => {
          alert('Sign in: fonctionnalite a connecter au module authentification.');
        });
      }

      if (els.signUpBtn) {
        els.signUpBtn.addEventListener('click', () => {
          alert('Sign up: fonctionnalite a connecter au module inscription.');
        });
      }

      const themeToggle = document.getElementById('themeToggle');
      if (!localStorage.getItem('theme_front_lostfound')) {
        localStorage.setItem('theme_front_lostfound', 'dark');
      }
      if (localStorage.getItem('theme_front_lostfound') === 'light') {
        document.body.classList.add('light-mode');
        if (themeToggle) {
          themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
      } else if (themeToggle) {
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      }

      if (themeToggle) {
        themeToggle.addEventListener('click', () => {
          document.body.classList.toggle('light-mode');
          const isLight = document.body.classList.contains('light-mode');
          localStorage.setItem('theme_front_lostfound', isLight ? 'light' : 'dark');
          themeToggle.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
      }
    }

    function init() {
      document.getElementById('lostDate').setAttribute('max', currentDateYmd());

      const current = users.find((u) => u.id === currentUserId);
      if (els.currentUserName) {
        els.currentUserName.textContent = 'Profil';
      }

      loadData();
      loadDeclarantsNonInscrits();
      ensureDeclarantsFromObjets();

      bindRowsActions();
      bindFilters();
      bindPublishForm();
      bindModals();
      bindNavbar();
      renderUsers();
      renderDeclarantsFrontList();
      updateSelectedDeclarantInfo();
      renderAll();

      window.addEventListener('storage', () => {
        renderAll();
      });
    }

    init();

