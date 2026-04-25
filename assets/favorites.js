/* ====================================================
   ECORIDE — favorites.js
   Système de favoris via localStorage (sans modif SQL)
   ==================================================== */

const FAVORITES_KEY = 'ecoride_favorites';

/* ── Lecture / Écriture ── */
function getFavorites() {
  try {
    return JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
  } catch { return []; }
}

function saveFavorites(favs) {
  localStorage.setItem(FAVORITES_KEY, JSON.stringify(favs));
}

function isFavorite(tripId) {
  return getFavorites().some(f => f.id_T == tripId);
}

function toggleFavorite(tripId) {
  const favs = getFavorites();
  const idx  = favs.findIndex(f => f.id_T == tripId);
  const trip = (typeof allTrips !== 'undefined') ? allTrips.find(t => t.id_T == tripId) : null;

  if (idx >= 0) {
    favs.splice(idx, 1);
    saveFavorites(favs);
    showToastFav(false, tripId);
  } else if (trip) {
    favs.push({ ...trip, savedAt: new Date().toISOString() });
    saveFavorites(favs);
    showToastFav(true, tripId);
  }

  // Mettre à jour tous les boutons cœur pour ce trajet
  updateFavButtons(tripId);

  // Rafraîchir la section favoris si visible
  const favTab = document.getElementById('tab-favoris');
  if (favTab && favTab.classList.contains('active')) renderFavorites();
}

function showToastFav(added, tripId) {
  const msg = added
    ? (typeof i18n !== 'undefined' ? i18n.t('fav.added') : 'Ajouté aux favoris')
    : (typeof i18n !== 'undefined' ? i18n.t('fav.removed') : 'Retiré des favoris');
  if (typeof toast !== 'undefined') toast(msg, true);
}

function updateFavButtons(tripId) {
  document.querySelectorAll(`.fav-btn[data-id="${tripId}"]`).forEach(btn => {
    const isFav = isFavorite(tripId);
    btn.innerHTML = isFav ? '<i class="fas fa-heart"></i>' : '<i class="far fa-heart"></i>';
    btn.style.color = isFav ? '#e74c3c' : 'var(--grey)';
    btn.style.background = isFav ? 'rgba(231,76,60,.15)' : 'rgba(255,255,255,.07)';
  });
}

/* ── Rendu du tab Favoris ── */
function renderFavorites() {
  const tbody  = document.getElementById('favBody');
  const countEl = document.getElementById('favCount');
  if (!tbody) return;

  const favs = getFavorites();
  if (countEl) countEl.textContent = favs.length + ' favori(s)';

  if (!favs.length) {
    const noFavTxt  = (typeof i18n !== 'undefined') ? i18n.t('fav.none')     : 'Aucun favori enregistré.';
    const noFavSub  = (typeof i18n !== 'undefined') ? i18n.t('fav.none_sub') : 'Cliquez sur ❤️ dans "Tous les trajets".';
    tbody.innerHTML = `
      <tr><td colspan="7">
        <div class="empty">
          <i class="fas fa-heart" style="color:rgba(231,76,60,.2);"></i>
          <p>${noFavTxt}</p>
          <p style="font-size:.75rem;opacity:.6;">${noFavSub}</p>
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
        <button class="abtn fav-btn" data-id="${t.id_T}"
          style="background:rgba(231,76,60,.15);color:#e74c3c;border:none;width:30px;height:30px;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.77rem;"
          onclick="toggleFavorite(${t.id_T})" title="Retirer des favoris">
          <i class="fas fa-heart"></i>
        </button>
        <button class="abtn abtn-res" title="Réserver"
          onclick="window.app.reserverTrajetAllTrips && window.app.reserverTrajetAllTrips(${t.id_T})">
          <i class="fas fa-ticket-alt"></i>
        </button>
      </div></td>
    </tr>`;
  }).join('');
}

/* ── Génère le bouton cœur pour une ligne de table ── */
function favBtn(tripId) {
  const fav = isFavorite(tripId);
  const color = fav ? '#e74c3c' : 'var(--grey)';
  const bg    = fav ? 'rgba(231,76,60,.15)' : 'rgba(255,255,255,.07)';
  const icon  = fav ? 'fas fa-heart' : 'far fa-heart';
  const title = fav
    ? (typeof i18n !== 'undefined' ? i18n.t('action.unfavorite') : 'Retirer des favoris')
    : (typeof i18n !== 'undefined' ? i18n.t('action.favorite')   : 'Ajouter aux favoris');

  return `<button class="abtn fav-btn" data-id="${tripId}"
    style="background:${bg};color:${color};border:none;width:30px;height:30px;border-radius:7px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.77rem;transition:all .2s;"
    onclick="toggleFavorite(${tripId})" title="${title}">
    <i class="${icon}"></i>
  </button>`;
}

/* ── Injection du tab Favoris dans le DOM ── */
function injectFavoritesTab() {
  // 1. Ajouter l'item de menu
  const menuFav = document.getElementById('menu-favoris');
  if (!menuFav) {
    const divider = document.querySelector('#dropdownMenu .dropdown-divider');
    if (divider) {
      const a = document.createElement('a');
      a.href    = '#';
      a.id      = 'menu-favoris';
      a.onclick = function() { navToTab('favoris'); return false; };
      a.innerHTML = '<i class="fas fa-heart" style="color:#e74c3c;"></i> <span data-i18n="nav.favorites">Mes favoris</span>';
      divider.insertAdjacentElement('beforebegin', a);
    }
  }

  // 2. Ajouter le tab content
  if (!document.getElementById('tab-favoris')) {
    const container = document.querySelector('.container');
    if (!container) return;

    const html = `
      <div id="tab-favoris" class="page-tab-content">
        <div class="section-indicator">
          <div class="si-icon"><i class="fas fa-heart" style="color:#e74c3c;"></i></div>
          <div>
            <div class="si-label" data-i18n="section.favorites">Mes favoris</div>
            <div class="si-sub" data-i18n="section.favorites_sub">Vos trajets sauvegardés</div>
          </div>
        </div>
        <div class="twrap">
          <div class="table-top">
            <h3><i class="fas fa-heart" style="color:#e74c3c;"></i> <span data-i18n="section.favorites">Mes favoris</span></h3>
            <span class="count-badge" id="favCount">0 favori(s)</span>
          </div>
          <table>
            <thead><tr>
              <th>ID</th>
              <th data-i18n="table.depart">Départ</th>
              <th data-i18n="table.arrivee">Arrivée</th>
              <th data-i18n="table.price">Prix (DT)</th>
              <th data-i18n="table.distance">Distance</th>
              <th>Sauvegardé le</th>
              <th data-i18n="table.action">Actions</th>
            </tr></thead>
            <tbody id="favBody"></tbody>
          </table>
        </div>
      </div>`;
    container.insertAdjacentHTML('beforeend', html);
  }
}

/* ── Patch de loadAllTrips pour ajouter le bouton ❤️ ── */
function patchLoadAllTrips() {
  const _orig = window.loadAllTrips;
  if (!_orig) return;

  window.loadAllTrips = function () {
    _orig();

    // Ajouter les boutons favoris dans allTripsBody après rendu
    setTimeout(() => {
      const rows = document.querySelectorAll('#allTripsBody tr');
      rows.forEach(row => {
        const chip = row.querySelector('.chip');
        if (!chip) return;
        const id = parseInt(chip.textContent.replace('#', ''));
        if (!id) return;

        // Vérifier si bouton déjà présent
        if (row.querySelector('.fav-btn')) return;

        const abtns = row.querySelector('.abtns');
        if (abtns) {
          abtns.insertAdjacentHTML('afterbegin', favBtn(id));
        }
      });
    }, 50);
  };
}

/* ── Initialisation ── */
document.addEventListener('DOMContentLoaded', () => {
  injectFavoritesTab();
  patchLoadAllTrips();

  // Patch navToTab pour gérer favoris
  const _origNav = window.navToTab;
  if (_origNav) {
    window.navToTab = function (tabName) {
      if (tabName === 'favoris') {
        closeDropdown && closeDropdown();
        document.querySelectorAll('.page-tab-content').forEach(t => t.classList.remove('active'));
        const tab = document.getElementById('tab-favoris');
        if (tab) tab.classList.add('active');
        document.querySelectorAll('.dropdown-content a[id^="menu-"]').forEach(a => a.classList.remove('active'));
        const menuEl = document.getElementById('menu-favoris');
        if (menuEl) menuEl.classList.add('active');
        renderFavorites();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return;
      }
      _origNav(tabName);
    };
  }
});

/* Exposer */
window.toggleFavorite = toggleFavorite;
window.isFavorite     = isFavorite;
window.favBtn         = favBtn;
window.renderFavorites = renderFavorites;