<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controller/LostFoundFrontController.php';

$pdo = Database::getInstance();
$controller = new LostFoundFrontController(new LostFoundFrontRepository($pdo));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = trim((string) ($_POST['action'] ?? ''));

  if ($action === 'create_declaration') {
    $controller->create($_POST);
  }

  if ($action === 'add_comment') {
    $controller->addComment(
      (int) ($_POST['declaration_id'] ?? 0),
      isset($_POST['conducteur_id']) ? (int) $_POST['conducteur_id'] : null,
      trim((string) ($_POST['message'] ?? ''))
    );
  }

  if ($action === 'update_declaration') {
    $controller->updateDeclaration($_POST);
  }

  if ($action === 'delete_declaration') {
    $controller->deleteDeclaration((int) ($_POST['id'] ?? 0));
  }

  header('Location: lostfound_front.php');
  exit;
}

$rawDeclarations = $controller->listAll();
$initialObjets = array_map(
  static fn(array $row): array => [
    'id' => (int) $row['id'],
    'title' => (string) ($row['titre'] ?? ''),
    'description' => (string) ($row['description'] ?? ''),
    'categorie' => (string) ($row['categorie'] ?? ''),
    'photo_url' => (string) ($row['photo_url'] ?? ''),
    'date_perte' => (string) ($row['date_perte'] ?? ''),
    'statut' => (string) ($row['statut'] ?? 'perdu'),
    'trajet_id' => (int) ($row['trajet_id'] ?? 0),
    'passager_id' => isset($row['passager_id']) ? (int) $row['passager_id'] : null,
    'anonyme_nom' => $row['anonyme_nom'] ?? null,
    'lieu_perte' => (string) ($row['lieu_perte'] ?? ''),
  ],
  $rawDeclarations
);

$initialTotalDeclarations = count($initialObjets);
$initialOpenDeclarations = count(array_filter($initialObjets, static fn(array $obj): bool => (string) ($obj['statut'] ?? 'perdu') === 'perdu'));
$initialResolvedDeclarations = count(array_filter($initialObjets, static fn(array $obj): bool => in_array((string) ($obj['statut'] ?? 'perdu'), ['retrouve', 'restitue'], true)));

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
  <title>Objets perdus | EcoRide Front Office</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/lostfound_front.css">
    <style>
/* ========== NAVBAR ========== */
.navbar-modern {
    background: linear-gradient(135deg, #1976D2 0%, #0F3B6E 100%);
    padding: 1.2rem 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-wrap: wrap;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.navbar-modern .logo {
    display: flex;
    flex-direction: column;
    text-decoration: none;
}

.navbar-modern .logo-text {
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: white;
    line-height: 1.3;
}

.navbar-modern .logo-img {
    width: 52px;
    height: 52px;
    object-fit: contain;
    filter: drop-shadow(0 2px 6px rgba(0,0,0,0.4));
    transition: transform 0.3s ease;
}

.navbar-modern .logo:hover .logo-img {
    transform: scale(1.08) rotate(-3deg);
}

.navbar-modern .logo {
    flex-direction: row !important;
    align-items: center;
    gap: 10px;
}

.navbar-modern .logo-tagline {
    font-size: 0.65rem;
    color: rgba(255,255,255,0.7);
    letter-spacing: 0.5px;
}

.menu-toggle {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    font-size: 1.2rem;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    cursor: pointer;
    display: none;
    transition: all 0.3s;
}

.menu-toggle:hover {
    background: rgba(255,255,255,0.25);
}

.nav-links {
    display: flex;
    gap: 0.8rem;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    flex-wrap: wrap;
}

/* Tous les boutons normaux */
.nav-links li a {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: white;
    border: none;
    cursor: pointer;
}

.nav-links li a:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* Bouton actif */
.nav-links li a.active {
    background: #0A1628;
    color: white;
    box-shadow: 0 2px 8px rgba(10,22,40,0.3);
}

/* ========== BOUTON ADMIN - STYLE ROUGE SEMI-TRANSPARENT ========== */
.nav-links .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-links .admin-btn:hover {
    background: rgba(231,76,60,0.35);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(231,76,60,0.2);
}

/* ========== BOUTON PROFIL ========== */
.profile-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #2F6FA5;
    border: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s;
    color: #FFFFFF;
    font-size: 0.9rem;
    font-weight: 500;
}

.profile-btn:hover {
    background: #3C82C4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(60,130,196,0.3);
}

.profile-avatar {
    width: 28px;
    height: 28px;
    background: #5FA8E0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar i {
    font-size: 0.8rem;
    color: #FFFFFF;
}

.profile-btn span {
    color: #FFFFFF;
}

.profile-btn i.fa-chevron-down {
    font-size: 0.7rem;
    margin-left: 5px;
    color: #FFFFFF;
}

/* ========== BOUTON MODE SOMBRE/CLAIR ========== */
.theme-btn {
    background: rgba(255,255,255,0.15);
    border: none;
    color: white;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.theme-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(15deg);
}

/* ========== MENU DÉROULANT PROFIL ========== */
.profile-dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    width: 280px;
    background: #0F2A44;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    z-index: 1000;
    overflow: hidden;
    margin-top: 10px;
    backdrop-filter: blur(10px);
}

.dropdown-menu.show {
    display: block;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* En-tête du profil */
.dropdown-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem;
    background: #163A5C;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.dropdown-header .avatar {
    width: 45px;
    height: 45px;
    background: #5FA8E0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dropdown-header .avatar i {
    font-size: 1.2rem;
    color: white;
}

.dropdown-header .user-info {
    display: flex;
    flex-direction: column;
}

.dropdown-header .user-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #CFE6FF;
}

.dropdown-header .user-role {
    font-size: 0.65rem;
    color: rgba(207,230,255,0.7);
}

/* Liens du menu */
.dropdown-links {
    padding: 0.5rem 0;
}

.dropdown-links a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1rem;
    margin: 0 0.5rem;
    border-radius: 10px;
    color: #CFE6FF;
    background: transparent;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.dropdown-links a i {
    width: 22px;
    color: #5FA8E0;
    font-size: 1rem;
}

.dropdown-links a:hover {
    background: rgba(255,255,255,0.05);
}

.dropdown-links a.active {
    background: #1E4F7A;
    position: relative;
}

.dropdown-links a.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #5FA8E0;
    border-radius: 0 3px 3px 0;
}

.dropdown-divider {
    height: 1px;
    background: rgba(255,255,255,0.08);
    margin: 0.5rem 0;
}

.dropdown-actions a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.7rem 1rem;
    margin: 0 0.5rem 0.5rem 0.5rem;
    border-radius: 10px;
    color: #FF5C5C;
    background: transparent;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.dropdown-actions a i {
    width: 22px;
    color: #FF5C5C;
    font-size: 1rem;
}

.dropdown-actions a:hover {
    background: rgba(255,92,92,0.15);
}

body.light-mode {
    background: #f5f5f5;
    color: #333;
}

body.light-mode .navbar-modern {
    background: linear-gradient(135deg, #1565C0, #0D47A1);
}

@media (max-width: 768px) {
    .navbar-modern {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
        padding: 1rem;
    }
    
    .menu-toggle {
        display: block;
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    
    .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        margin-top: 1rem;
        gap: 0.8rem;
    }
    
    .nav-links.show {
        display: flex;
    }
    
    .nav-links li a,
    .nav-links .admin-btn,
    .profile-btn,
    .theme-btn {
        padding: 0.7rem 1rem;
        display: block;
        text-align: center;
        width: 100%;
        border-radius: 30px;
    }
    
    .profile-dropdown {
        width: 100%;
    }
    
    .dropdown-menu {
        position: static;
        width: 100%;
        margin-top: 8px;
    }
    
    .dropdown-header {
        justify-content: center;
    }
    .modal{
      max-width: 6000px;
      padding-bottom: 300px;
    }
    

}
    </style>
</head>
<body>
<nav class="navbar-modern">
  <a href="http://localhost/index.php" class="logo">
    <img src="./Véhicules Disponibles _ EcoRide_files/photo.png" alt="EcoRide Logo" class="logo-img" onerror="this.style.display=&#39;none&#39;">
    <div>
      <div class="logo-text">ECO RIDE</div>
      <div class="logo-tagline">Covoiturage Intelligent</div>
    </div>
  </a>
    
  <button id="menuBtn" class="menu-toggle" onclick="toggleMenu()">
    <i class="fas fa-bars"></i>
  </button>
    
  <ul class="nav-links" id="navLinks">
    <!-- Bouton Accueil -->
    <li><a href="tous_les_trajets.php">Accueil</a></li>
        
    <li><a href="evenements.php">Événements</a></li>
    <li><a href="sponsors.php">Sponsors</a></li>
    <li><a href="tous_les_trajets.php">Covoiturage</a></li>
    <li><a href="lostfound_front.php" class="active"><i class="fas fa-search"></i> Lost &amp; Found</a></li>
        
    <li class="profile-dropdown">
      <button class="profile-btn" onclick="toggleProfileDropdown(event)">
        <div class="profile-avatar">
          <i class="fas fa-user"></i>
        </div>
        <span id="currentUserName">Profil</span>
        <i class="fas fa-chevron-down"></i>
      </button>
      <div class="dropdown-menu" id="profileDropdown">
        <div class="dropdown-header">
          <div class="avatar">
            <i class="fas fa-user"></i>
          </div>
          <div class="user-info">
            <div class="user-name">Utilisateur</div>
            <div class="user-role">Membre EcoRide</div>
          </div>
        </div>
                
        <div class="dropdown-links">
          <a href="http://localhost/ecoride/View/frontoffice/vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
          <a href="http://localhost/ecoride/View/frontoffice/mes_trajets.php"><i class="fas fa-map-marker-alt"></i> Mes trajets</a>
          <a href="http://localhost/ecoride/View/frontoffice/mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
          <a href="http://localhost/ecoride/View/frontoffice/mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
          <a href="http://localhost/ecoride/View/frontoffice/mes_favoris.php"><i class="fas fa-heart"></i> Mes favoris</a>
                    
          <!-- NOUVEAUX LIENS - juste en dessous de mes favoris -->
          <a href="/ecoride/index.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a>
          <a href="mes_objets_perdus.php"><i class="fas fa-search"></i> Mes objets perdus</a>
        </div>
                
        <div class="dropdown-divider"></div>
                
        <div class="dropdown-actions">
          <a href="http://localhost/ecoride/View/frontoffice/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
      </div>
    </li>
        
    <!-- Bouton Admin -->
    <li><a href="/ecoride/View/backoffice/admin.php" class="admin-btn">Admin</a></li>
        
    <li class="theme-li">
      <button class="theme-btn" type="button" id="themeToggle" title="Basculer entre noir et blanc" aria-label="Basculer entre noir et blanc">
        <i class="fas fa-moon"></i>
      </button>
    </li>
  </ul>
</nav>

<script>
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('show');
}

function toggleProfileDropdown(event) {
    event.stopPropagation();
    document.getElementById('profileDropdown').classList.toggle('show');
}

function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    
    const themeBtn = document.getElementById('themeBtn');
    const icon = themeBtn.querySelector('i');
    
    if (isLight) {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const themeBtn = document.getElementById('themeBtn');
    if (themeBtn) {
        const icon = themeBtn.querySelector('i');
        icon.className = 'fas fa-sun';
    }
}

window.onclick = function(event) {
    if (!event.target.closest('.profile-dropdown')) {
        var dropdown = document.getElementById('profileDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>

  <div class="container">
    <section class="hero-section">
      <div class="hero-content">
        <h1><i class="fas fa-box-open"></i> Suivi des <span class="highlight">objets perdus</span></h1>
        <p>Publiez, suivez les commentaires des conducteurs et trouvez rapidement votre objet grace aux mises a jour en temps reel.</p>
        <div class="hero-stats">
          <div class="stat"><div class="number" id="heroTotal"><?php echo htmlspecialchars((string) $initialTotalDeclarations); ?></div><div class="label">Declarations</div></div>
          <div class="stat"><div class="number" id="heroOpen"><?php echo htmlspecialchars((string) $initialOpenDeclarations); ?></div><div class="label">A retrouver</div></div>
          <div class="stat"><div class="number" id="heroResolved"><?php echo htmlspecialchars((string) $initialResolvedDeclarations); ?></div><div class="label">Resolues</div></div>
        </div>
        <button id="openPublishModalBtn" class="hero-btn" type="button">Nouvelle publication <i class="fas fa-plus"></i></button>
      </div>
      <div class="hero-icon"><i class="fas fa-magnifying-glass-location"></i></div>
    </section>

    <section class="filters-bar">
      <div class="filter-group"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Rechercher titre, lieu, declarant..."></div>
      <div class="filter-group"><i class="fas fa-layer-group"></i>
        <select id="filterCategory">
          <option value="">Toutes categories</option>
          <option value="electronique">Electronique</option>
          <option value="vetement">Vetement</option>
          <option value="document">Document</option>
          <option value="bagage">Bagage</option>
          <option value="autre">Autre</option>
        </select>
      </div>
      <div class="filter-group"><i class="fas fa-circle-info"></i>
        <select id="filterStatus">
          <option value="">Tous statuts</option>
          <option value="perdu">Perdu</option>
          <option value="retrouve">Retrouve</option>
          <option value="restitue">Restitue</option>
        </select>
      </div>
      <button id="resetFilters" class="btn-reset" type="button"><i class="fas fa-rotate-left"></i> Reinitialiser</button>
    </section>

    <section class="section-title" id="publications">
      <h2><i class="fas fa-thumbtack"></i> Publications objets perdus</h2>
      <span class="count-badge" id="countBadge"><?php echo htmlspecialchars((string) $initialTotalDeclarations); ?> publication<?php echo $initialTotalDeclarations > 1 ? 's' : ''; ?></span>
    </section>

    <section class="content-grid">
      <div class="lost-grid" id="rows">
        <?php if (!empty($initialObjets)): ?>
          <?php foreach ($initialObjets as $obj): ?>
            <article class="lost-card">
              <div class="card-head">
                <span class="card-id">#<?= htmlspecialchars((string) ($obj['id'] ?? '')) ?></span>
                <span class="status-badge <?= htmlspecialchars((string) ($obj['statut'] ?? 'perdu')) ?>"><?= htmlspecialchars((string) ($obj['statut'] ?? 'perdu')) ?></span>
              </div>
              <div class="card-content">
                <div class="card-title"><?= htmlspecialchars((string) ($obj['title'] ?? $obj['titre'] ?? 'Objet')) ?></div>
                <div class="card-desc"><?= htmlspecialchars((string) ($obj['description'] ?? '')) ?></div>
                <div class="tags">
                  <span class="tag"><i class="fas fa-user"></i> <?= htmlspecialchars((string) ($obj['anonyme_nom'] ?? ($obj['passager_id'] ?? 'Anonyme'))) ?></span>
                  <span class="tag"><i class="fas fa-layer-group"></i> <?= htmlspecialchars((string) ($obj['categorie'] ?? '')) ?></span>
                  <span class="tag"><i class="fas fa-location-dot"></i> <?= htmlspecialchars((string) ($obj['lieu_perte'] ?? '')) ?></span>
                  <span class="tag"><i class="fas fa-calendar-days"></i> <?= htmlspecialchars((string) ($obj['date_perte'] ?? '')) ?></span>
                </div>
                <div class="card-actions">
                  <button class="action-btn" data-action="detail" data-id="<?= htmlspecialchars((string) ($obj['id'] ?? '')) ?>">Details</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <div class="modal-bg" id="detailModal">
    <div class="modal">
      <div class="modal-head">
        <h3 class="modal-title" id="detailTitle">Details</h3>
        <button class="btn-outline" id="closeDetail" type="button">Fermer</button>
      </div>
      <div id="detailBody"></div>
    </div>
  </div>

  <div class="modal-bg" id="threadModal">
    <div class="modal">
      <div class="modal-head">
        <h3 class="modal-title" id="threadTitle">Sous-publications</h3>
        <button class="btn-outline" id="closeThread" type="button">Fermer</button>
      </div>
      <div class="thread-image">
        <div class="muted" style="margin-bottom:.4rem">Apercu image de la publication selectionnee</div>
        <img id="threadPreviewImage" alt="Image publication" src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1200&q=60">
      </div>
      <div id="threadBody"></div>
    </div>
  </div>

  <div class="modal-bg" id="usersModal">
    <div class="modal" style="max-width:540px;">
      <div class="users-modal-head">
        <h3 class="modal-title"><i class="fas fa-users"></i> Choisir un utilisateur</h3>
        <button class="btn-outline" id="closeUsers" type="button">Fermer</button>
      </div>
      <div class="switcher" id="userSwitcher"></div>
    </div>
  </div>

  <div class="modal-bg" id="publishModal">
    <div class="modal" style="max-width: 6200px; padding-bottom: 300px;">
      <div class="modal-head">
        <h3 class="modal-title"><i class="fas fa-pen-to-square"></i> Nouvelle declaration</h3>
        <button class="btn-outline" id="closePublish" type="button">Fermer</button>
      </div>
      <form id="publishForm" novalidate>
        <div id="selectedDeclarantInfo" class="muted" style="display:none;margin-bottom:.6rem;"></div>
        <div id="publishFormError" class="form-error" aria-live="polite"></div>
        <div class="fg">
          <label for="title">Titre</label>
          <input id="title" name="titre" type="text" placeholder="Ex: Sac noir oublie ligne B">
          <div id="titleError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Decrivez votre objet"></textarea>
          <div class="ai-tools">
            <button type="button" id="generateDescriptionAiBtn" class="btn-outline"><i class="fas fa-wand-magic-sparkles"></i> Description IA</button>
            <span id="descriptionAiHint" class="muted"></span>
          </div>
          <div id="descriptionError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="category">Categorie</label>
          <select id="category" name="categorie">
            <option value="">Choisir...</option>
            <option value="electronique">Electronique</option>
            <option value="vetement">Vetement</option>
            <option value="document">Document</option>
            <option value="bagage">Bagage</option>
            <option value="autre">Autre</option>
          </select>
          <div id="categoryError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="objectStatus">Statut de l'objet</label>
          <select id="objectStatus" name="statut">
            <option value="perdu">Perdu</option>
            <option value="retrouve">Retrouve</option>
            <option value="restitue">Restitue</option>
          </select>
          <div id="objectStatusError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="place">Lieu de perte</label>
          <input id="place" name="lieu_perte" type="text" placeholder="Ex: Gare centrale">
          <div id="placeError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="trajetId">ID trajet</label>
          <input id="trajetId" name="trajet_id" type="text" inputmode="numeric" placeholder="Ex: 201">
          <div id="trajetIdError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="photoFile">Photo (image)</label>
          <input id="photoFile" type="file" accept="image/png,image/jpeg,image/webp,image/gif">
          <div class="muted" style="margin-top:.3rem">Formats acceptes: JPG, PNG, WEBP, GIF (max 2 Mo)</div>
          <div id="photoFileError" class="field-error" aria-live="polite"></div>
        </div>
        <div class="fg">
          <label for="lostDate">Date de perte</label>
          <input id="lostDate" name="date_perte" type="date">
          <div id="lostDateError" class="field-error" aria-live="polite"></div>
        </div>
        <button class="btn-main" type="submit"><i class="fas fa-paper-plane"></i> Publier</button>
      </form>
    </div>
  </div>

  <div class="modal-bg" id="declarantCreateModalFront">
    <div class="modal" style="max-width:540px;">
      <div class="modal-head">
        <h3 class="modal-title"><i class="fas fa-user-plus"></i> Nouveau declarant non inscrit</h3>
        <button class="btn-outline" id="closeDeclarantCreateFront" type="button">Fermer</button>
      </div>
      <form id="declarantCreateFormFront" novalidate>
        <div id="declarantCreateErrorFront" class="form-error" aria-live="polite"></div>
        <div class="fg">
          <label for="declarantNameFront">Nom complet</label>
          <input id="declarantNameFront" type="text" placeholder="Ex: Nadia Benali">
          <div id="declarantNameFrontError" class="field-error" aria-live="polite"></div>
        </div>
        <button class="btn-main" type="submit"><i class="fas fa-save"></i> Ajouter</button>
      </form>
    </div>
  </div>

  <div class="modal-bg" id="declarantsListModalFront">
    <div class="modal" style="max-width:620px;">
      <div class="modal-head">
        <h3 class="modal-title"><i class="fas fa-list"></i> Declarants non inscrits</h3>
        <button class="btn-outline" id="closeDeclarantsListFront" type="button">Fermer</button>
      </div>
      <div class="comment-meta" id="declarantsCountFront">0 declarant</div>
      <div class="comment-list" id="declarantsListFront" style="margin-top:.5rem;"></div>
      <div class="mfooter">
        <button type="button" class="btn-outline" id="openNewDeclarantFromListFront">Nouveau declarant</button>
      </div>
    </div>
  </div>

  <div class="modal-bg" id="publicationsImageModal">
    <div class="modal" style="max-width:760px;">
      <div class="modal-head">
        <h3 class="modal-title"><i class="fas fa-images"></i> Mes publications en une image</h3>
        <button class="btn-outline" id="closePublicationsImage" type="button">Fermer</button>
      </div>
      <div class="thread-image">
        <div class="muted" style="margin-bottom:.4rem">Apercu global de vos objets perdus publies</div>
        <img id="publicationsSingleImage" alt="Mes publications" src="">
      </div>
    </div>
  </div>

    <script>
window.LOSTFOUND_FRONT_CONFIG = {
    initialObjets: <?php echo json_encode($initialObjets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    initialSignalements: <?php echo json_encode($initialSignalements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    initialStats: {
        total: <?php echo (int) $initialTotalDeclarations; ?>,
        open: <?php echo (int) $initialOpenDeclarations; ?>,
        resolved: <?php echo (int) $initialResolvedDeclarations; ?>
    }
};

console.log('✅ LOSTFOUND_FRONT_CONFIG chargé:', window.LOSTFOUND_FRONT_CONFIG);
</script>
  <script src="../../assets/js/lostfound_front_php.js"></script>
</body>
</html>