<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/LostFoundAdminController.php';

$pdo = Database::getConnection();
$controller = new LostFoundAdminController(new LostFoundRepository($pdo));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = trim((string) ($_POST['action'] ?? ''));

  if ($action === 'delete_declaration') {
    $controller->deleteDeclaration((int) ($_POST['id'] ?? 0));
  }

  if ($action === 'mark_restitue') {
    $controller->changeStatus((int) ($_POST['id'] ?? 0), 'restitue');
  }

  if ($action === 'add_comment') {
    $controller->addComment(
      (int) ($_POST['declaration_id'] ?? 0),
      isset($_POST['conducteur_id']) ? (int) $_POST['conducteur_id'] : null,
      trim((string) ($_POST['message'] ?? ''))
    );
  }

  if ($action === 'create_declaration') {
    $controller->create($_POST);
  }

  header('Location: lostfound_admin.php');
  exit;
}

$rawDeclarations = $controller->listWithPrediction();
$initialObjets = array_map(
  static fn(array $row): array => [
    'id' => (int) $row['id'],
    'description' => (string) ($row['description'] ?? ''),
    'categorie' => (string) ($row['categorie'] ?? ''),
    'photo_url' => (string) ($row['photo_url'] ?? ''),
    'date_perte' => (string) ($row['date_perte'] ?? ''),
    'statut' => (string) ($row['statut'] ?? 'perdu'),
    'trajet_id' => (int) ($row['trajet_id'] ?? 0),
    'passager_id' => isset($row['passager_id']) ? (int) $row['passager_id'] : null,
    'anonyme_nom' => $row['anonyme_nom'] ?? null,
    'ml_confidence_score' => (int) ($row['ml_confidence_score'] ?? 0),
    'ml_eta_hours' => (int) ($row['ml_eta_hours'] ?? 0),
    'ml_eta_label' => (string) ($row['ml_eta_label'] ?? ''),
    'ml_priority' => (string) ($row['ml_priority'] ?? 'low'),
    'ml_message' => (string) ($row['ml_message'] ?? ''),
  ],
  $rawDeclarations
);

$rawCommentaires = $controller->listComments();
$initialSignalements = array_map(
  static fn(array $row): array => [
    'id' => (int) $row['id'],
    'message' => (string) ($row['message'] ?? ''),
    'date_signalement' => (string) ($row['created_at'] ?? ''),
    'conducteur_id' => isset($row['user_id']) ? (int) $row['user_id'] : 0,
    'objet_id' => (int) ($row['declaration_id'] ?? 0),
  ],
  $rawCommentaires
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration - Objets perdus - EcoRide</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../assets/css/lostfound_admin.css">
<style>
  /* ========== HEADER STYLE ========== */
.admin-header {
    background: linear-gradient(90deg, #071C2F, #0A2A47, #0D355B);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-radius: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-logo {
    display: flex;
    flex-direction: column;
}

.admin-logo .logo-eco {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 1px;
    font-family: 'Poppins', sans-serif;
}

.admin-logo .logo-eco span:first-child {
    color: #4EA3FF;
}

.admin-logo .logo-eco span:last-child {
    color: #6BB8FF;
}

.admin-logo .logo-tagline {
    font-size: 0.65rem;
    color: #A8C1D9;
    margin-top: 2px;
}

.admin-nav {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.admin-nav a {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    background: transparent;
    color: #CFE6FF;
    font-family: 'Poppins', sans-serif;
}

.admin-nav a:hover {
    background: rgba(255,255,255,0.1);
    color: #FFFFFF;
}

/* ========== BOUTON PROFIL - STYLE BLEU ========== */
.admin-nav .profile-btn {
    background: #4A90E2;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.5rem 1.2rem;
}

.admin-nav .profile-btn:hover {
    background: #2563EB;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
}

.profile-avatar {
    width: 28px;
    height: 28px;
    background: #5FA8FF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar i {
    font-size: 0.8rem;
    color: #FFFFFF;
}

/* ========== BOUTON ADMIN - STYLE ROUGE ========== */
.admin-nav .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
}

.admin-nav .admin-btn:hover {
    background: rgba(231,76,60,0.35);
}

.theme-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.theme-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: rotate(15deg);
}
</style>
</head>
<body>
<div class="wrap">
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php" class="logo">
            <img src="../assets/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="passagers.php"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="trajets.php"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="destinations.php"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="evenements.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="http://localhost/ecoride_reclamations_mvc%20-%20Copie%20(2)/ecoride_reclamations_mvc%20-%20Copie%20(2)/admin_reclamations.php
"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin.php" ><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="http://localhost/objet_perdu1/objet_perdu1/objet_perdu1/objet_perdu/view/Front%20office/lostfound_front.php" class="active" ><i class="fas fa-search-location"></i> Lost &amp; Found</a></li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

  <main class="main">
    <div class="admin-header">
    <div class="admin-logo">
        <div class="logo-eco">
            <span>ECO</span> <span>RIDE</span>
        </div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
    <div class="admin-nav">
        <a href="http://localhost/ecoride/View/frontoffice/vehicules_disponibles.php">Voir Site</a>
        
        <!-- BOUTON PROFIL -->
        <a href="http://localhost/ecoride/View/backoffice/profil.php" class="profile-btn">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <span>Profil</span>
        </a>
        
        <a href="http://localhost/objet_perdu/objet_perdu/view/Front%20office/lostfound_front.php" class="admin-btn">Admin</a>
        <button class="theme-btn" type="button" id="themeBtn" title="Basculer entre noir et blanc" aria-label="Basculer entre noir et blanc">
            <i class="fas fa-moon"></i>
        </button>
    </div>
</div>



    <div class="stats">
      <div class="stat"><i class="fas fa-inbox"></i><div class="num" id="statTotal">0</div><div class="lbl">Total declarations</div></div>
      <div class="stat"><i class="fas fa-triangle-exclamation"></i><div class="num" id="statPerdu">0</div><div class="lbl">Objets perdus</div></div>
      <div class="stat"><i class="fas fa-magnifying-glass-location"></i><div class="num" id="statRetrouve">0</div><div class="lbl">Objets retrouves</div></div>
      <div class="stat"><i class="fas fa-handshake"></i><div class="num" id="statRestitue">0</div><div class="lbl">Objets restitues</div></div>
    </div>

    <div class="toolbar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Rechercher declaration, objet, declarant...">
      </div>
      <button id="exportPdfBtn" class="btn btn-pdf" type="button" style="background:rgba(231,76,60,.15);border:1px solid rgba(231,76,60,.4);color:#e74c3c;padding:.5rem 1.1rem;border-radius:18px;font-size:.84rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;transition:all 0.3s;font-family:'Poppins',sans-serif;"><i class="fas fa-file-pdf"></i> Exporter PDF</button>
    </div>

    <div class="filters">
      <select id="filterStatut" class="st-sel">
        <option value="tous">Statut : Tous</option>
        <option value="perdu">Perdu</option>
        <option value="retrouve">Retrouve</option>
        <option value="restitue">Restitue</option>
      </select>
      <select id="filterCategorie" class="st-sel">
        <option value="toutes">Categorie : Toutes</option>
        <option value="electronique">Electronique</option>
        <option value="vetement">Vetement</option>
        <option value="document">Document</option>
        <option value="bagage">Bagage</option>
        <option value="autre">Autre</option>
      </select>
      <select id="filterDeclarant" class="st-sel">
        <option value="tous">Declarant : Tous</option>
        <option value="inscrit">Inscrit</option>
        <option value="anonyme">Anonyme</option>
      </select>
      <button id="resetFiltersBtn" class="btn-reset" type="button" style="background:rgba(231,76,60,.2);border:1px solid rgba(231,76,60,.4);color:#e74c3c;padding:.5rem 1rem;border-radius:18px;cursor:pointer;font-size:.84rem;transition:all 0.3s;"><i class="fas fa-times"></i> Réinitialiser</button>
    </div>

    <section class="section-title" style="margin-top:1rem; display: flex; justify-content: space-between; align-items: center;">
      <h2><i class="fas fa-thumbtack"></i> Publications objets perdus</h2>
      <span class="count-badge" id="countBadge">0 publication</span>
    </section>

    <div class="sort-toolbar" style="margin-bottom: 1rem; display: flex; gap: 0.5rem; align-items: center; justify-content: flex-end;">
      <button id="sortBtn" class="btn" type="button" style="background: rgba(78,163,255,0.2); border: 1px solid rgba(78,163,255,0.4); color: #4EA3FF; padding: 0.5rem 1.2rem; border-radius: 18px; font-size: 0.84rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.3s; font-family: 'Poppins', sans-serif;"><i class="fas fa-arrow-down-up"></i> Trier</button>
      <select id="sortCriteria" class="st-sel" style="padding: 0.5rem 1rem; border-radius: 18px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); color: #CFE6FF; font-family: 'Poppins', sans-serif; cursor: pointer;">
        <option value="priority_desc">Priorité (Haute → Basse)</option>
        <option value="confidence_desc">Confiance IA (↓)</option>
        <option value="confidence_asc">Confiance IA (↑)</option>
        <option value="date_desc">Date perte (Récent)</option>
        <option value="date_asc">Date perte (Ancien)</option>
        <option value="id_desc">ID (Nouveau)</option>
        <option value="id_asc">ID (Ancien)</option>
        <option value="categorie_asc">Catégorie (A-Z)</option>
        <option value="statut_asc">Statut</option>
      </select>
    </div>

    <div class="tbl-wrap">
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Declarant</th>
              <th>Trajet</th>
              <th>Categorie</th>
              <th>Description</th>
              <th>Confiance IA</th>
              <th>Priorite</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="declarationsTbody">
            <?php if (!empty($initialObjets)): ?>
              <?php foreach ($initialObjets as $obj): ?>
                <tr>
                  <td><code>#<?= htmlspecialchars((string) ($obj['id'] ?? '')) ?></code></td>
                  <td><?= htmlspecialchars((string) ($obj['anonyme_nom'] ?? ($obj['passager_id'] ?? 'Anonyme'))) ?></td>
                  <td>Trajet #<?= htmlspecialchars((string) ($obj['trajet_id'] ?? '')) ?></td>
                  <td><span class="badge b-cat"><?= htmlspecialchars((string) ($obj['categorie'] ?? '')) ?></span></td>
                  <td><?= htmlspecialchars(substr((string) ($obj['description'] ?? ''), 0, 68)) ?></td>
                  <td><span class="badge b-confidence"><?= htmlspecialchars((string) ($obj['ml_confidence_score'] ?? 0)) ?>%</span></td>
                  <td><span class="badge b-priority-<?= htmlspecialchars((string) ($obj['ml_priority'] ?? 'low')) ?>"><?= htmlspecialchars((string) strtoupper((string) ($obj['ml_priority'] ?? 'low'))) ?></span></td>
                  <td><span class="badge b-<?= htmlspecialchars((string) ($obj['statut'] ?? 'perdu')) ?>"><?= htmlspecialchars((string) ($obj['statut'] ?? 'perdu')) ?></span></td>
                  <td><div class="acts">
                    <button class="ic ic-view" title="Details" data-action="details" data-id="<?= htmlspecialchars((string) ($obj['id'] ?? '')) ?>"><i class="fas fa-eye"></i></button>
                    <button class="ic ic-del" title="Supprimer" data-action="delete" data-id="<?= htmlspecialchars((string) ($obj['id'] ?? '')) ?>"><i class="fas fa-trash"></i></button>
                  </div></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<div class="overlay" id="detailModal">
  <div class="modal">
    <h2><i class="fas fa-circle-info"></i> Details de la declaration</h2>
    <form id="detailForm">
      <div class="fgrid">
        <div class="fg">
          <label for="detailId">ID</label>
          <input id="detailId" type="text" readonly>
        </div>
        <div class="fg">
          <label for="detailTrajet">Trajet ID</label>
          <input id="detailTrajet" type="text" inputmode="numeric">
        </div>
      </div>
      <div class="fgrid">
        <div class="fg">
          <label for="detailCategorie">Categorie</label>
          <select id="detailCategorie">
            <option value="electronique">Electronique</option>
            <option value="vetement">Vetement</option>
            <option value="document">Document</option>
            <option value="bagage">Bagage</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div class="fg">
          <label for="detailStatut">Statut</label>
          <select id="detailStatut">
            <option value="perdu">Perdu</option>
            <option value="retrouve">Retrouve</option>
            <option value="restitue">Restitue</option>
          </select>
        </div>
      </div>
      <div class="fg">
        <label for="detailDescription">Description</label>
        <textarea id="detailDescription"></textarea>
      </div>

      <div class="section">
        <h3><i class="fas fa-comments"></i> Commentaires</h3>
        <div class="comment-list" id="commentsList"></div>
        <div class="fgrid" style="margin-top:.7rem;">
          <div class="fg">
            <label for="commentConducteurId">Conducteur ID</label>
            <input id="commentConducteurId" type="text" inputmode="numeric" placeholder="Ex: 31">
          </div>
          <div class="fg">
            <label for="commentMessage">Nouveau commentaire</label>
            <input id="commentMessage" type="text" placeholder="Ex: Sac retrouve cote conducteur">
          </div>
        </div>
        <button type="button" class="btn btn-outline" id="addCommentBtn"><i class="fas fa-plus"></i> Ajouter commentaire</button>
      </div>

      <div class="section">
        <h3><i class="fas fa-robot"></i> IA commentaires</h3>
        <div class="ia-box" id="iaSuggestion">Aucun commentaire pour le moment.</div>
      </div>

      <div class="mfooter">
        <button type="button" class="btn btn-outline" data-close="detailModal">Fermer</button>
        <button type="button" class="btn btn-warning" id="markRestitueBtn"><i class="fas fa-handshake"></i> Restituer</button>
        <button type="button" class="btn btn-danger" id="deleteFromModalBtn"><i class="fas fa-trash"></i> Supprimer</button>
      </div>
    </form>
  </div>
</div>

<div class="overlay" id="declarantCreateModal">
  <div class="modal">
    <h2><i class="fas fa-user-plus"></i> Nouveau declarant non inscrit</h2>
    <form id="declarantCreateForm" novalidate>
      <div id="declarantCreateError" class="form-error" aria-live="polite"></div>
      <div class="fg">
        <label for="declarantName">Nom complet</label>
        <input id="declarantName" type="text" placeholder="Ex: Nadia Benali">
        <div id="declarantNameError" class="field-error" aria-live="polite"></div>
      </div>
      <div class="form-note">Ce declarant sera disponible dans la liste des non inscrits.</div>
      <div class="mfooter">
        <button type="button" class="btn btn-outline" data-close="declarantCreateModal">Annuler</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Ajouter</button>
      </div>
    </form>
  </div>
</div>

<div class="overlay" id="declarantsListModal">
  <div class="modal">
    <h2><i class="fas fa-list"></i> Declarants non inscrits</h2>
    <div class="section" style="margin-top:0;padding-top:0;border-top:none;">
      <div class="comment-meta" id="declarantsCount">0 declarant</div>
      <div class="comment-list" id="declarantsList"></div>
    </div>
    <div class="mfooter">
      <button type="button" class="btn btn-primary" id="openNewDeclarantFromListBtn"><i class="fas fa-user-plus"></i> Nouveau declarant</button>
      <button type="button" class="btn btn-outline" data-close="declarantsListModal">Fermer</button>
    </div>
  </div>
</div>

<style>
/* ========== HISTORIQUE PANEL (Version Admin Objets Perdus) ========== */
.hist-fab {
    position: fixed;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    z-index: 490;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.hist-fab-btn {
    background: linear-gradient(180deg, #1976D2, #0D47A1);
    border: none;
    border-radius: 14px 0 0 14px;
    padding: 1rem .65rem;
    cursor: pointer;
    box-shadow: -4px 0 18px rgba(25,118,210,.45);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .55rem;
    color: #fff;
    transition: .25s;
    position: relative;
}
.hist-fab-btn:hover {
    padding-right: .9rem;
    box-shadow: -6px 0 24px rgba(25,118,210,.65);
}
.hist-fab-btn i { font-size: 1.1rem; }
.hist-fab-label {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    transform: rotate(180deg);
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
}
.hist-fab-count {
    background: #e74c3c;
    color: #fff;
    font-size: .6rem;
    font-weight: 800;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hist-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,.6);
    z-index: 500;
}
.hist-overlay.open { display: block; }
.hist-panel {
    position: fixed;
    right: 0;
    top: 0;
    height: 100%;
    width: 400px;
    background: #0f1e36;
    border-left: 1px solid rgba(97,179,250,.18);
    z-index: 501;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform .3s ease;
}
.hist-panel.open { transform: translateX(0); }
body.light-mode .hist-panel { background: #fff; border-left: 1px solid #ddd; }
.hist-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
}
.hist-header h3 { font-size: .95rem; font-weight: 700; display: flex; align-items: center; gap: .5rem; }
.hist-header h3 i { color: #61B3FA; }
.hist-close {
    background: none;
    border: none;
    color: rgba(255,255,255,.5);
    font-size: 1.1rem;
    cursor: pointer;
}
.hist-close:hover { color: #fff; }
.hist-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.2rem;
}
.hist-item {
    display: flex;
    align-items: flex-start;
    gap: .8rem;
    padding: .8rem 0;
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.hist-item:last-child { border-bottom: none; }
.hist-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .75rem;
}
.hist-dot.perdu { background: rgba(241,196,15,.2); color: #f1c40f; }
.hist-dot.retrouve { background: rgba(52,152,219,.2); color: #3498db; }
.hist-dot.restitue { background: rgba(39,174,96,.2); color: #27ae60; }
.hist-info .hi-title { font-size: .83rem; font-weight: 600; margin-bottom: .2rem; }
.hist-info .hi-date { font-size: .7rem; color: rgba(255,255,255,.4); }
.hist-empty { text-align: center; padding: 3rem 1rem; color: rgba(255,255,255,.3); }
.hist-empty i { font-size: 2rem; margin-bottom: .5rem; display: block; }
.bst { padding: .15rem .5rem; border-radius: 12px; font-size: .65rem; white-space: nowrap; }
.bst.perdu { background: rgba(241,196,15,.15); color: #f1c40f; }
.bst.retrouve { background: rgba(52,152,219,.15); color: #3498db; }
.bst.restitue { background: rgba(39,174,96,.15); color: #27ae60; }
</style>

<!-- Bouton flottant historique -->
<div class="hist-fab">
    <button class="hist-fab-btn" onclick="openHist()" title="Historique des objets perdus">
        <i class="fas fa-history"></i>
        <span class="hist-fab-label">Historique</span>
        <div class="hist-fab-count" id="histCount">0</div>
    </button>
</div>

<!-- Panneau latéral historique -->
<div class="hist-overlay" id="histOverlay" onclick="closeHist()"></div>
<div class="hist-panel" id="histPanel">
    <div class="hist-header">
        <h3><i class="fas fa-history"></i> Historique des déclarations</h3>
        <button class="hist-close" onclick="closeHist()"><i class="fas fa-times"></i></button>
    </div>
    <div class="hist-body" id="histBody">
        <div class="hist-empty"><i class="fas fa-clock"></i>Chargement...</div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
window.LOSTFOUND_ADMIN_CONFIG = {
  initialObjets: <?php echo json_encode($initialObjets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
  initialSignalements: <?php echo json_encode($initialSignalements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
};
</script>
<script src="../assets/js/lostfound_admin_php_new.js"></script>

<script>
// Historique panel functions
function openHist() {
    document.getElementById('histPanel').classList.add('open');
    document.getElementById('histOverlay').classList.add('open');
    renderHistoryPanel();
}

function closeHist() {
    document.getElementById('histPanel').classList.remove('open');
    document.getElementById('histOverlay').classList.remove('open');
}

function renderHistoryPanel() {
    const histBody = document.getElementById('histBody');
    if (!histBody) return;
    
    if (!objets || objets.length === 0) {
        histBody.innerHTML = '<div class="hist-empty"><i class="fas fa-inbox"></i>Aucune déclaration dans l\'historique</div>';
        return;
    }

    const sorted = [...objets].sort((a, b) => b.id - a.id);
    
    let html = '';
    sorted.forEach(obj => {
        const statut = obj.statut || 'perdu';
        const icon = statut === 'restitue' ? 'fa-handshake' : (statut === 'retrouve' ? 'fa-check' : 'fa-search');
        const dateStr = obj.date_perte ? new Date(obj.date_perte).toLocaleDateString('fr-FR') : 'Date inconnue';
        const declarant = obj.passager_id ? ('Passager #' + obj.passager_id) : (obj.anonyme_nom || 'Anonyme');
        const description = (obj.description || '').substring(0, 50) + (obj.description?.length > 50 ? '...' : '');
        
        html += `
            <div class="hist-item">
                <div class="hist-dot ${statut}">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="hist-info">
                    <div class="hi-title">
                        #${obj.id} - ${obj.categorie || 'Catégorie inconnue'}
                        <span class="bst ${statut}" style="margin-left:8px;">
                            ${statut.charAt(0).toUpperCase() + statut.slice(1)}
                        </span>
                    </div>
                    <div class="hi-date">
                        ${declarant} • ${dateStr}
                    </div>
                    <div class="hi-date" style="margin-top:4px; color: #61B3FA;">
                        ${description}
                    </div>
                </div>
            </div>
        `;
    });
    
    histBody.innerHTML = html;
    document.getElementById('histCount').textContent = objets.length;
}

// Fermer avec Echap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeHist();
});

// Mettre à jour le compteur au démarrage
setTimeout(() => {
    if (document.getElementById('histCount')) {
        document.getElementById('histCount').textContent = objets?.length || 0;
    }
}, 100);
</script>
</body>
</html>

