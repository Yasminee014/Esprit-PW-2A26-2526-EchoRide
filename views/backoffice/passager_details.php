<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}
// $passager, $vehicles, $trips, $reclamations, $events, $lost_found injectés par le contrôleur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride — Détails passager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Poppins','Segoe UI',sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            color: #F4F5F7;
        }

        /* ══ SIDEBAR ══ */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #1976D2 0%, #1565C0 40%, #0F3B6E 100%);
            position: fixed;
            height: 100vh;
            padding: 2rem 1rem;
            overflow-y: auto;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        .sidebar .logo { text-align:center; margin-bottom:2rem; }
        .sidebar .logo h2 { color:#61B3FA; margin-top:10px; }
        .sidebar .logo .admin-label {
            font-size:0.62rem; letter-spacing:0.2em;
            color:rgba(255,255,255,.6); text-transform:uppercase; margin-top:4px;
        }
        .sidebar nav ul { list-style:none; }
        .sidebar nav ul li { margin-bottom:0.5rem; }
        .sidebar nav ul li a {
            display:flex; align-items:center; gap:12px;
            padding:0.8rem 1rem; color:#fff; text-decoration:none; border-radius:8px;
            transition:background 0.2s;
        }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active { background:rgba(255,255,255,.18); }
        .nav-section {
            font-size:0.62rem; font-weight:700; letter-spacing:0.18em;
            color:rgba(255,255,255,.55); text-transform:uppercase;
            padding:0 1rem 0.5rem; margin-top:0.5rem;
        }
        .sidebar-footer { margin-top:auto; border-top:1px solid rgba(255,255,255,.15); padding-top:1rem; }
        .logout-btn {
            display:flex; align-items:center; gap:12px;
            padding:0.8rem 1rem; color:#fff; text-decoration:none; border-radius:8px;
            transition:background 0.2s;
        }
        .logout-btn:hover { background:rgba(255,255,255,.15); }

        /* ══ MAIN ══ */
        .main-content {
            margin-left: 240px;
            width: calc(100% - 240px);
            min-height: 100vh;
            padding: 0;
        }
        .page-content { padding: 2rem 2.5rem; }

        /* ══ NAVBAR (identique dashboard) ══ */
        .top-bar {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom: 2rem;
            background: linear-gradient(90deg, #0D2350 0%, #0F3166 50%, #0D2350 100%);
            border-radius: 16px;
            padding: 0.75rem 1.5rem;
            border: 1px solid rgba(97,179,250,0.18);
            box-shadow: 0 4px 24px rgba(0,0,0,0.25);
            position: sticky; top: 1rem; z-index: 600;
        }
        .navbar-logo { display:flex; flex-direction:column; line-height:1.2; }
        .navbar-logo strong { font-size:1.1rem; font-weight:800; color:#61B3FA; letter-spacing:0.05em; }
        .navbar-logo span { font-size:0.62rem; color:rgba(255,255,255,.75); letter-spacing:0.08em; }
        .top-bar-right { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; }
        .btn-top {
            background:transparent; color:#fff; padding:0.4rem 1rem;
            border-radius:20px; text-decoration:none; font-size:0.88rem;
            font-weight:500; border:none; transition:background 0.2s; white-space:nowrap; cursor:pointer;
        }
        .btn-top:hover { background:rgba(255,255,255,.12); }
        .btn-admin-profile {
            display:inline-flex; align-items:center; gap:8px;
            background:#922B21; color:#fff; border:none;
            padding:0.4rem 1.1rem 0.4rem 0.4rem; border-radius:25px;
            font-size:0.9rem; cursor:pointer; font-weight:700;
            transition:all 0.3s; text-decoration:none;
        }
        .btn-admin-profile:hover { background:#C0392B; }
        .admin-avatar-btn {
            width:30px; height:30px; border-radius:50%; overflow:hidden;
            display:flex; align-items:center; justify-content:center;
            background:rgba(255,255,255,.2); border:2px solid rgba(255,255,255,.5);
        }
        .admin-avatar-btn img { width:100%; height:100%; object-fit:cover; }
        .btn-admin-plain {
            display:inline-flex; align-items:center; gap:6px;
            background:transparent; color:#E74C3C;
            border:1px solid rgba(231,76,60,.45); padding:0.4rem 1.1rem;
            border-radius:25px; font-size:0.9rem; font-weight:700;
            text-decoration:none; transition:all 0.2s; cursor:pointer;
        }
        .btn-admin-plain:hover { background:rgba(231,76,60,.12); }
        .btn-theme-toggle {
            width:34px; height:34px; border-radius:50%;
            background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.22);
            color:#fff; font-size:0.92rem; display:inline-flex; align-items:center;
            justify-content:center; cursor:pointer; transition:all 0.25s;
        }
        .btn-theme-toggle:hover { background:rgba(255,255,255,.18); }

        /* ══ BREADCRUMB ══ */
        .breadcrumb {
            display:flex; align-items:center; gap:8px;
            font-size:0.85rem; color:#A7A9AC; margin-bottom:1.5rem;
        }
        .breadcrumb a { color:#61B3FA; text-decoration:none; }
        .breadcrumb a:hover { text-decoration:underline; }
        .breadcrumb i { font-size:0.7rem; }

        /* ══ PANEL DÉTAILS — même style que le drawer du dashboard ══ */
        .details-panel {
            background: linear-gradient(160deg, #0D1F3A 0%, #091525 100%);
            border: 1px solid rgba(25,118,210,0.5);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7);
            overflow: hidden;
            max-width: 860px;
            margin: 0 auto;
        }

        .panel-header {
            display:flex; align-items:center; justify-content:space-between;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid rgba(25,118,210,0.3);
            background: rgba(25,118,210,0.12);
        }
        .panel-header h2 {
            color: #61B3FA;
            font-size: 1rem; font-weight:700;
            display:flex; align-items:center; gap:10px;
            margin:0;
        }
        .btn-back-panel {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.2);
            color:#F4F5F7; padding:0.35rem 0.9rem; border-radius:20px;
            font-size:0.82rem; font-weight:600; text-decoration:none;
            transition:background 0.2s; cursor:pointer;
        }
        .btn-back-panel:hover { background:rgba(255,255,255,.14); }

        .panel-body { padding: 1.4rem; }

        /* ══ SECTIONS — identiques au modal dashboard ══ */
        .modal-section {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 1rem;
        }
        .modal-section:last-child { border-bottom:none; margin-bottom:0; }

        .modal-section h3 {
            color: #1976D2;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
            display:flex; align-items:center; gap:8px;
        }

        .modal-section .empty {
            color: #A7A9AC;
            font-style: italic;
            padding: 0.5rem;
        }

        .detail-item {
            background: rgba(10,47,68,0.5);
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        .detail-item:last-child { margin-bottom:0; }
        .detail-item strong { color: #1976D2; }

        .detail-badge {
            display:inline-block;
            padding:0.2rem 0.5rem;
            border-radius:12px;
            font-size:0.7rem;
            margin-left:0.5rem;
        }
        .badge-vehicle    { background:rgba(25,118,210,0.2);  color:#1976D2; }
        .badge-trip       { background:rgba(0,255,136,0.2);   color:#00ff88; }
        .badge-reclamation{ background:rgba(255,165,0,0.2);   color:#ffa500; }
        .badge-event      { background:rgba(255,68,68,0.2);   color:#ff6666; }
        .badge-lost       { background:rgba(255,68,68,0.2);   color:#ff6666; }

        /* ══ ACTIONS PASSAGER ══ */
        .panel-actions {
            display:flex; gap:0.75rem; flex-wrap:wrap; padding:1rem 1.4rem 0;
        }
        .btn-action {
            display:inline-flex; align-items:center; gap:8px;
            padding:0.5rem 1.2rem; border-radius:25px; font-size:0.85rem;
            font-weight:600; cursor:pointer; border:none; text-decoration:none;
            transition:all 0.2s;
        }
        .btn-edit  { background:rgba(25,118,210,0.15); color:#61B3FA; border:1px solid rgba(25,118,210,0.4); }
        .btn-edit:hover { background:rgba(25,118,210,0.3); }
        .btn-ban   { background:rgba(255,68,68,0.12); color:#ff4444; border:1px solid rgba(255,68,68,0.4); }
        .btn-ban:hover { background:rgba(255,68,68,0.25); }
        .btn-unban { background:rgba(0,204,106,0.12); color:#00cc6a; border:1px solid rgba(0,204,106,0.4); }
        .btn-unban:hover { background:rgba(0,204,106,0.25); }

        /* ══ LIGHT MODE ══ */
        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .details-panel { background:rgba(255,255,255,.95) !important; }
        body.light-mode .detail-item { background:rgba(0,0,0,.04) !important; }
        body.light-mode .detail-item strong { color:#1565C0 !important; }

        @media (max-width:768px) {
            .main-content { margin-left:0; width:100%; }
            .sidebar { display:none; }
            .page-content { padding:1rem; }
        }
    </style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<div class="sidebar">
    <div>
        <div class="logo">
            <img src="<?= BASE_URL ?>uploads/photos/photo.png" alt="EcoRide Logo"
                 style="width:60px;height:60px;object-fit:contain;background:transparent;vertical-align:middle;">
            <h2>EcoRide</h2>
            <div class="admin-label">Administration</div>
        </div>
        <div style="height:1px;background:rgba(255,255,255,.15);margin-bottom:1.2rem;"></div>
        <div class="nav-section">Gestion</div>
        <nav>
            <ul>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                        <i class="fas fa-gauge-high"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="active">
                        <i class="fas fa-users"></i> Passagers
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                        <i class="fas fa-route"></i> Trajets
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                        <i class="fas fa-calendar-alt"></i> Événements
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                        <i class="fas fa-exclamation-circle"></i> Réclamations
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                        <i class="fas fa-car"></i> Véhicules
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard">
                        <i class="fas fa-magnifying-glass"></i> Objets perdus
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</div>

<!-- ══ MAIN ══ -->
<div class="main-content">
    <div class="page-content">

        <!-- NAVBAR -->
        <div class="top-bar">
            <div class="navbar-logo">
                <strong>ECO RIDE</strong>
                <span>Covoiturage Intelligent</span>
            </div>
            <div class="top-bar-right">
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#hero" class="btn-top">Accueil</a>
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#evenements" class="btn-top">Événements</a>
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm#sponsors" class="btn-top">Sponsors</a>
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=covoiturage" class="btn-top">Covoiturage</a>
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=lostFound" class="btn-top">Lost &amp; Found</a>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showProfile" class="btn-admin-profile">
                    <div class="admin-avatar-btn">
                        <?php if (!empty($_SESSION['admin_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="">
                        <?php else: ?>
                            <i class="fas fa-user-shield"></i>
                        <?php endif; ?>
                    </div>
                    Profil
                </a>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="btn-admin-plain">Admin</a>
                <button class="btn-theme-toggle" onclick="toggleTheme()" title="Mode sombre / clair">
                    <i class="fas fa-moon themeIcon"></i>
                </button>
            </div>
        </div>

        <!-- BREADCRUMB -->
        <!-- PANEL DÉTAILS -->
        <div class="details-panel">

            <!-- En-tête du panel -->
            <div class="panel-header">
                <h2>
                    <i class="fas fa-user-circle"></i>
                    Détails du passager
                </h2>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard&tab=passagers" class="btn-back-panel">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <!-- Corps du panel -->
            <div class="panel-body">

                <!-- Informations personnelles -->
                <div class="modal-section">
                    <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                    <div class="detail-item"><strong>ID:</strong> <?= $passager['id'] ?></div>
                    <div class="detail-item"><strong>Nom:</strong> <?= htmlspecialchars($passager['prenom'] . ' ' . $passager['nom']) ?></div>
                    <div class="detail-item"><strong>Email:</strong> <?= htmlspecialchars($passager['email']) ?></div>
                    <div class="detail-item"><strong>Téléphone:</strong> <?= htmlspecialchars($passager['telephone'] ?? 'Non renseigné') ?></div>
                    <div class="detail-item"><strong>Statut:</strong> <?= $passager['statut'] === 'actif' ? 'Actif' : 'Banni' ?></div>
                    <div class="detail-item"><strong>Date d'inscription:</strong> <?= date('d/m/Y', strtotime($passager['created_at'])) ?></div>
                </div>

                <!-- Véhicules -->
                <div class="modal-section">
                    <h3>
                        <i class="fas fa-car"></i> Véhicules
                        <span class="detail-badge badge-vehicle"><?= count($vehicles) ?></span>
                    </h3>
                    <?php if (empty($vehicles)): ?>
                        <div class="empty">Aucun véhicule enregistré</div>
                    <?php else: ?>
                        <?php foreach ($vehicles as $v): ?>
                            <div class="detail-item">
                                <strong><?= htmlspecialchars($v['brand'] . ' ' . $v['model']) ?></strong>
                                — <?= htmlspecialchars($v['plate']) ?>
                                — <?= htmlspecialchars($v['color']) ?>
                                — <?= $v['seats'] ?> places
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Trajets -->
                <div class="modal-section">
                    <h3>
                        <i class="fas fa-route"></i> Trajets
                        <span class="detail-badge badge-trip"><?= count($trips) ?></span>
                    </h3>
                    <?php if (empty($trips)): ?>
                        <div class="empty">Aucun trajet proposé</div>
                    <?php else: ?>
                        <?php foreach ($trips as $t): ?>
                            <div class="detail-item">
                                <strong><?= htmlspecialchars($t['departure']) ?> → <?= htmlspecialchars($t['arrival']) ?></strong>
                                — <?= htmlspecialchars($t['date']) ?> <?= htmlspecialchars($t['time']) ?>
                                — <?= htmlspecialchars($t['price']) ?> DT
                                — <?= $t['available'] ?>/<?= $t['seats'] ?> places
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Réclamations -->
                <div class="modal-section">
                    <h3>
                        <i class="fas fa-exclamation-triangle"></i> Réclamations
                        <span class="detail-badge badge-reclamation"><?= count($reclamations) ?></span>
                    </h3>
                    <?php if (empty($reclamations)): ?>
                        <div class="empty">Aucune réclamation</div>
                    <?php else: ?>
                        <?php foreach ($reclamations as $r): ?>
                            <div class="detail-item">
                                <strong><?= htmlspecialchars($r['title'] ?? 'Sans titre') ?></strong>
                                — <?= htmlspecialchars($r['status'] ?? 'En attente') ?>
                                — <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Événements -->
                <div class="modal-section">
                    <h3>
                        <i class="fas fa-calendar-alt"></i> Événements
                        <span class="detail-badge badge-event"><?= count($events) ?></span>
                    </h3>
                    <?php if (empty($events)): ?>
                        <div class="empty">Aucun événement</div>
                    <?php else: ?>
                        <?php foreach ($events as $e): ?>
                            <div class="detail-item">
                                <strong><?= htmlspecialchars($e['title'] ?? 'Sans titre') ?></strong>
                                — <?= htmlspecialchars($e['date'] ?? 'Date non définie') ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Objets perdus / trouvés -->
                <div class="modal-section">
                    <h3>
                        <i class="fas fa-search"></i> Objets perdus/trouvés
                        <span class="detail-badge badge-lost"><?= count($lost_found) ?></span>
                    </h3>
                    <?php if (empty($lost_found)): ?>
                        <div class="empty">Aucun objet signalé</div>
                    <?php else: ?>
                        <?php foreach ($lost_found as $l): ?>
                            <div class="detail-item">
                                <strong><?= htmlspecialchars($l['item_name'] ?? 'Objet') ?></strong>
                                — <?= htmlspecialchars($l['status'] ?? 'En cours') ?>
                                — <?= date('d/m/Y', strtotime($l['created_at'])) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div><!-- /panel-body -->
        </div><!-- /details-panel -->

    </div><!-- /page-content -->
</div><!-- /main-content -->

<script>
    function toggleTheme() {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        document.querySelectorAll('.themeIcon').forEach(i => {
            i.className = isLight ? 'fas fa-sun themeIcon' : 'fas fa-moon themeIcon';
        });
        localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
    }
    (function() {
        if (localStorage.getItem('ecoride_theme') === 'light') {
            document.body.classList.add('light-mode');
            document.querySelectorAll('.themeIcon').forEach(i => { i.className = 'fas fa-sun themeIcon'; });
        }
    })();
</script>
</body>
</html>
