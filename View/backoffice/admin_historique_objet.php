<?php
declare(strict_types=1);
session_start();
$_SESSION['is_admin'] = true;

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/LostFoundRepository.php';

$pdo = Database::getInstance();
$repo = new LostFoundRepository($pdo);

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: lostfound_admin.php');
    exit;
}

// Déclaration principale
$stmt = $pdo->prepare('SELECT * FROM declarations WHERE id = :id');
$stmt->execute([':id' => $id]);
$declaration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$declaration) {
    header('Location: lostfound_admin.php');
    exit;
}

// Commentaires/signalements liés
$commentaires = $repo->findCommentsByDeclaration($id);

// Stats pour la fiche
$totalComments = count($commentaires);

// Helpers
$passagersMap = [1 => 'Sophie Martin', 2 => 'Youssef Belaid', 3 => 'Camille Bernard', 4 => 'Antoine Girard', 5 => 'Lea Martin'];
$trajetMap = [201 => 'Paris → Lyon', 202 => 'Lille → Bruxelles', 203 => 'Marseille → Nice', 204 => 'Bordeaux → Toulouse', 205 => 'Nantes → Rennes'];

function getDeclarantLabel(array $d, array $passagersMap): string {
    if (!empty($d['passager_id'])) {
        return $passagersMap[$d['passager_id']] ?? ('Passager #' . $d['passager_id']);
    }
    return 'Anonyme — ' . ($d['anonyme_nom'] ?? 'Externe');
}
function categorieLabel(string $cat): string {
    return ['electronique' => 'Électronique', 'vetement' => 'Vêtement', 'document' => 'Document', 'bagage' => 'Bagage', 'autre' => 'Autre'][$cat] ?? ucfirst($cat);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historique objet #<?= $id ?> — EcoRide</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/lostfound_admin.css">
<style>
/* PAGE-SPECIFIC OVERRIDES */
.histo-page-wrap { display: flex; min-height: 100vh; }

/* STATS CARDS */
.stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.6rem; }
.stat-card { background: rgba(255,255,255,.07); border: 1px solid rgba(78,163,255,.18); border-radius: 14px; padding: 1.2rem; text-align: center; transition: transform .3s; }
.stat-card:hover { transform: translateY(-3px); border-color: #4EA3FF; }
.stat-card i { font-size: 1.8rem; color: #4EA3FF; margin-bottom: .35rem; display: block; }
.stat-card .num { font-size: 2rem; font-weight: 700; background: linear-gradient(135deg, #4EA3FF, #fff); -webkit-background-clip: text; background-clip: text; color: transparent; }
.stat-card .lbl { color: #A7A9AC; font-size: .75rem; }

/* FICHE DECLARATION */
.decl-card { background: rgba(255,255,255,.07); border: 1px solid rgba(78,163,255,.16); border-radius: 20px; padding: 1.5rem; margin-bottom: 2rem; }
.decl-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
.decl-card h2 { color: #4EA3FF; font-size: 1.15rem; margin-bottom: .4rem; }
.decl-card-meta { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: .5rem; }
.decl-card-meta span { background: rgba(78,163,255,.15); padding: .2rem .8rem; border-radius: 20px; font-size: .75rem; display: flex; align-items: center; gap: 5px; }
.decl-photo { width: 90px; height: 90px; object-fit: cover; border-radius: 12px; border: 2px solid rgba(78,163,255,.3); flex-shrink: 0; }
.decl-photo-placeholder { width: 90px; height: 90px; background: rgba(78,163,255,.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 2px dashed rgba(78,163,255,.3); flex-shrink: 0; }
.decl-photo-placeholder i { font-size: 2rem; color: rgba(78,163,255,.45); }
.decl-desc { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.06); border-radius: 12px; padding: 1rem; font-size: .88rem; line-height: 1.7; color: #CFE6FF; margin-top: 1rem; }

/* BADGES */
.bdg { display: inline-flex; align-items: center; gap: 5px; padding: .3rem .9rem; border-radius: 20px; font-size: .78rem; font-weight: 600; }
.bdg-perdu { background: rgba(231,76,60,.15); color: #e74c3c; border: 1px solid rgba(231,76,60,.3); }
.bdg-retrouve { background: rgba(52,152,219,.15); color: #3498db; border: 1px solid rgba(52,152,219,.3); }
.bdg-restitue { background: rgba(39,174,96,.15); color: #27ae60; border: 1px solid rgba(39,174,96,.3); }

/* SECTION TITLE */
.section-title { font-size: 1rem; margin-bottom: 1rem; color: #4EA3FF; display: flex; align-items: center; gap: 8px; }

/* HISTORIQUE TABLE */
.histo-full-table { width: 100%; border-collapse: collapse; font-size: .82rem; background: rgba(255,255,255,.03); border-radius: 14px; overflow: hidden; }
.histo-full-table th { text-align: left; padding: .8rem 1rem; background: rgba(78,163,255,.12); color: #4EA3FF; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
.histo-full-table td { padding: .85rem 1rem; border-bottom: 1px solid rgba(255,255,255,.05); vertical-align: middle; }
.histo-full-table tbody tr:last-child td { border-bottom: none; }
.histo-full-table tbody tr:hover td { background: rgba(78,163,255,.04); }
.histo-full-table code { background: rgba(78,163,255,.18); padding: .1rem .4rem; border-radius: 6px; font-size: .78rem; }

/* EMPTY */
.empty-hist { text-align: center; padding: 3rem; color: #A7A9AC; }
.empty-hist i { font-size: 3rem; opacity: .3; margin-bottom: 1rem; display: block; }

/* BTN BACK */
.btn-back { background: rgba(78,163,255,.15); border: 1px solid rgba(78,163,255,.3); padding: .5rem 1.1rem; border-radius: 20px; color: #4EA3FF; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: .85rem; transition: all .2s; margin-bottom: 1.5rem; }
.btn-back:hover { background: rgba(78,163,255,.28); }

/* MAIN CONTENT */
.main { flex: 1; margin-left: 280px; padding: 1.6rem; }
@media(max-width: 768px) { .sidebar { display: none; } .main { margin-left: 0; } }

body.light-mode .decl-card { background: #fff; color: #333; }
body.light-mode .histo-full-table { background: #fff; }
body.light-mode .histo-full-table th { background: rgba(78,163,255,.1); }
</style>
</head>
<body>
<div class="histo-page-wrap">

<!-- SIDEBAR (identique à lostfound_admin.php) -->
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
            <li><a href="dashboard_event.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
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

    <!-- BOUTON RETOUR -->
    <a href="lostfound_admin.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour aux objets perdus
    </a>

    <!-- STATS -->
    <div class="stat-cards">
        <div class="stat-card">
            <i class="fas fa-hashtag"></i>
            <div class="num">#<?= $id ?></div>
            <div class="lbl">Déclaration</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-tag"></i>
            <div class="num"><?= htmlspecialchars(categorieLabel($declaration['categorie'] ?? '')) ?></div>
            <div class="lbl">Catégorie</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-comments"></i>
            <div class="num"><?= $totalComments ?></div>
            <div class="lbl">Commentaire(s)</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-alt"></i>
            <div class="num"><?= $declaration['date_perte'] ? date('d/m', strtotime($declaration['date_perte'])) : '—' ?></div>
            <div class="lbl">Date de perte</div>
        </div>
    </div>

    <!-- FICHE DÉCLARATION -->
    <div class="decl-card">
        <div class="decl-card-header">
            <div style="flex:1">
                <h2><i class="fas fa-search-location"></i> <?= htmlspecialchars($declaration['titre'] ?? $declaration['description'] ?? 'Déclaration #' . $id) ?></h2>
                <div class="decl-card-meta">
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars(categorieLabel($declaration['categorie'] ?? '')) ?></span>
                    <span><i class="fas fa-calendar"></i> <?= $declaration['date_perte'] ? date('d/m/Y', strtotime($declaration['date_perte'])) : '—' ?></span>
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars(getDeclarantLabel($declaration, $passagersMap)) ?></span>
                    <?php if (!empty($declaration['trajet_id'])): ?>
                    <span><i class="fas fa-route"></i> <?= 'Trajet #' . intval($declaration['trajet_id']) . (isset($trajetMap[$declaration['trajet_id']]) ? ' · ' . $trajetMap[$declaration['trajet_id']] : '') ?></span>
                    <?php endif; ?>
                    <?php if (!empty($declaration['lieu_perte'])): ?>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($declaration['lieu_perte']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
                $statut = $declaration['statut'] ?? 'perdu';
                $statutLabel = ['perdu' => '⚠️ Perdu', 'retrouve' => '🔍 Retrouvé', 'restitue' => '✅ Restitué'][$statut] ?? $statut;
            ?>
            <span class="bdg bdg-<?= $statut ?>"><?= $statutLabel ?></span>
            <?php if (!empty($declaration['photo_url'])): ?>
                <img src="../../assets/uploads/<?= htmlspecialchars($declaration['photo_url']) ?>" alt="Photo objet" class="decl-photo">
            <?php endif; ?>
        </div>
        <div class="decl-desc">
            <?= nl2br(htmlspecialchars($declaration['description'] ?? '')) ?>
        </div>
    </div>

    <!-- HISTORIQUE DES COMMENTAIRES -->
    <div class="section-title">
        <i class="fas fa-history"></i> Historique des signalements
    </div>

    <?php if (empty($commentaires)): ?>
        <div class="empty-hist">
            <i class="fas fa-comment-slash"></i>
            <p>Aucun signalement enregistré pour cette déclaration</p>
        </div>
    <?php else: ?>
        <table class="histo-full-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commentaires as $c): ?>
                <tr>
                    <td><code>#<?= intval($c['id']) ?></code></td>
                    <td>
                        <?php if (!empty($c['user_id'])): ?>
                            <i class="fas fa-user"></i> <?= htmlspecialchars($c['user_nom'] ?? 'Utilisateur #' . $c['user_id']) ?>
                        <?php else: ?>
                            <i class="fas fa-user-secret"></i> <?= htmlspecialchars($c['user_nom'] ?? 'Anonyme') ?>
                        <?php endif; ?>
                    </td>
                    <td><i class="fas fa-calendar-day"></i> <?= $c['created_at'] ? date('d/m/Y H:i', strtotime($c['created_at'])) : '—' ?></td>
                    <td><?= nl2br(htmlspecialchars($c['message'] ?? '')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</main>
</div>

<script>
function toggleTheme() {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    const icon = document.getElementById('themeBtn').querySelector('i');
    icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
}
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    const btn = document.getElementById('themeBtn');
    if (btn) btn.querySelector('i').className = 'fas fa-sun';
}
</script>
</body>
</html>
