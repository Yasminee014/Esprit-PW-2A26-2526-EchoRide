<?php
declare(strict_types=1);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../../controller/LostFoundAdminController.php';
require_once __DIR__ . '/../../Config/Database.php';

$pdo = Database::getInstance();
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
    'id'                 => (int) $row['id'],
    'description'        => (string) ($row['description'] ?? ''),
    'categorie'          => (string) ($row['categorie'] ?? ''),
    'photo_url'          => (string) ($row['photo_url'] ?? ''),
    'date_perte'         => (string) ($row['date_perte'] ?? ''),
    'statut'             => (string) ($row['statut'] ?? 'perdu'),
    'trajet_id'          => (int) ($row['trajet_id'] ?? 0),
    'passager_id'        => isset($row['passager_id']) ? (int) $row['passager_id'] : null,
    'anonyme_nom'        => $row['anonyme_nom'] ?? null,
    'ml_confidence_score' => (int) ($row['ml_confidence_score'] ?? 0),
    'ml_eta_hours'       => (int) ($row['ml_eta_hours'] ?? 0),
    'ml_eta_label'       => (string) ($row['ml_eta_label'] ?? ''),
    'ml_priority'        => (string) ($row['ml_priority'] ?? 'low'),
    'ml_message'         => (string) ($row['ml_message'] ?? ''),
  ],
  $rawDeclarations
);

$rawCommentaires = $controller->listComments();
$initialSignalements = array_map(
  static fn(array $row): array => [
    'id'               => (int) $row['id'],
    'message'          => (string) ($row['message'] ?? ''),
    'date_signalement' => (string) ($row['created_at'] ?? ''),
    'conducteur_id'    => isset($row['user_id']) ? (int) $row['user_id'] : 0,
    'objet_id'         => (int) ($row['declaration_id'] ?? 0),
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/lostfound_admin.css">
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
.admin-logo { display: flex; flex-direction: column; }
.admin-logo .logo-eco { font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; }
.admin-logo .logo-eco span:first-child { color: #4EA3FF; }
.admin-logo .logo-eco span:last-child { color: #6BB8FF; }
.admin-logo .logo-tagline { font-size: 0.65rem; color: #A8C1D9; margin-top: 2px; }
.admin-nav { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }
.admin-nav a {
    text-decoration: none;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
    background: transparent;
    color: #CFE6FF;
}
.admin-nav a:hover { background: rgba(255,255,255,0.1); color: #FFFFFF; }
.admin-nav .profile-btn {
    background: #003050;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
}
.admin-nav .profile-btn:hover { background: #002050; transform: translateY(-2px); }
.profile-avatar {
    width: 28px; height: 28px;
    background: #5FA8FF;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.profile-avatar i { font-size: 0.8rem; color: #FFFFFF; }
.admin-nav .admin-btn {
    background: rgba(231,76,60,0.2);
    border: 1px solid rgba(231,76,60,0.4);
    color: #e74c3c;
    padding: 0.5rem 1.2rem;
    border-radius: 30px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s;
}
.admin-nav .admin-btn:hover { background: rgba(231,76,60,0.35); }
.theme-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    width: 38px; height: 38px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    transition: all 0.3s;
    display: flex; align-items: center; justify-content: center;
    color: white;
}
.theme-btn:hover { background: rgba(255,255,255,0.2); transform: rotate(15deg); }

.navbar-modern .logo {
    display: flex;
    flex-direction: row !important;
    align-items: center;
    gap: 10px;
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

.nav-links li a.active {
    background: #0A1628;
    color: white;
    box-shadow: 0 2px 8px rgba(10,22,40,0.3);
}

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
}
</style>
</head>
<body>
<div class="wrap">
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php" class="logo">
            <img src="../../assets/images/photo.png" alt="EcoRide Logo" class="logo-img">
            <div class="logo-text">EcoRide</div>
            <div class="logo-tagline">ADMINISTRATION</div>
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <nav>
        <ul>
            <li><a href="admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin_trajet.php?page=passagers"><i class="fas fa-users"></i> Passagers</a></li>
            <li><a href="admin_trajet.php?page=trajets"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="admin_trajet.php?page=destinations"><i class="fas fa-map-pin"></i> Destinations</a></li>
            <li><a href="admin_trajet.php?page=evenements"><i class="fas fa-calendar-alt"></i> Événements</a></li>
            <li><a href="/ecoride/View/backoffice/admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin.php"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="lostfound_admin.php" class="active"><i class="fas fa-search-location"></i> Objets perdus</a></li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>

  <main class="main">
    <!-- HEADER -->
    <div class="admin-header">
      <div class="admin-logo">
        <div class="logo-eco"><span>ECO</span> <span>RIDE</span></div>
        <div class="logo-tagline">Covoiturage Intelligent</div>
      </div>
      <div class="admin-nav">
        <a href="/ecoride/View/frontoffice/tous_les_trajets.php">Voir site</a>
        <a href="profil.php" class="profile-btn">
          <div class="profile-avatar"><i class="fas fa-user"></i></div>
          <span>Profil</span>
        </a>
        <a href="admin.php" class="admin-btn">Admin</a>
        <button class="theme-btn" onclick="toggleTheme()" id="themeBtn"><i class="fas fa-moon"></i></button>
      </div>
    </div>
        
      <button id="menuBtn" class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
      </button>

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




<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
window.LOSTFOUND_ADMIN_CONFIG = {
  initialObjets: <?php echo json_encode($initialObjets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
  initialSignalements: <?php echo json_encode($initialSignalements, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
};
</script>
<script src="../../assets/js/lostfound_admin_php_new.js"></script>

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
    
    const themeBtn = document.getElementById('themeToggle');
    const icon = themeBtn.querySelector('i');
    
    if (isLight) {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const themeBtn = document.getElementById('themeToggle');
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
</body>
</html>