<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=showLogin');
    exit();
}
// $passager, $vehicles, $trips, $reclamations, $events, $lost_found injectés par le contrôleur
?>
<?php require_once __DIR__ . '/partials/partials.php'; ?>
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

        /* ══ MAIN ══ */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            padding: 0;
        }
        .page-content { padding: 2rem 2.5rem; }

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
            .page-content { padding:1rem; }
        }
    </style>
<?php render_nav_css(); ?>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<?php require_once __DIR__ . '/partials/partials.php'; ?>
<?php sidebar_dashboard('passagers'); ?>

<!-- ══ MAIN ══ -->
<div class="main-content">
    <div class="page-content">

        <?php navbar_dashboard(); ?>

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
<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>
