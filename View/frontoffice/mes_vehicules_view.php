<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Les variables $vehicules, $resaCounts viennent du contrôleur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Véhicules | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bleu-fonce: #1976D2;
            --bleu-clair: #61B3FA;
            --blanc: #F4F5F7;
            --gris: #A7A9AC;
            --dark-bg: #0A1628;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, #0D1F3A 100%);
            color: #fff;
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .navbar {
            background: linear-gradient(90deg, var(--bleu-fonce), #0F3B6E);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }
        .navbar .logo i { color: var(--bleu-clair); }
        .navbar nav { display: flex; gap: .5rem; }
        .navbar nav a {
            color: #fff !important;
            text-decoration: none !important;
            padding: .5rem 1.2rem;
            border-radius: 25px;
            font-size: .88rem;
            font-weight: 500;
            transition: all .3s;
            border: 1px solid rgba(97,179,250,.35);
            background: rgba(255,255,255,.08);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .navbar nav a:hover {
            background: rgba(25,118,210,.3) !important;
            border-color: #61B3FA !important;
        }
        .navbar nav a.active {
            background: rgba(25,118,210,.35) !important;
            border-color: #61B3FA !important;
            color: #61B3FA !important;
        }

        /* ── Container ── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* ── Header ── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header h1 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header h1 i { color: var(--bleu-clair); }

        /* ── Bouton Ajouter (LIEN) ── */
        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--bleu-fonce), var(--bleu-clair));
            color: #fff;
            border: none;
            padding: 0.6rem 1.4rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(25,118,210,.4);
        }
        .btn-add:hover { transform: translateY(-2px); }

        /* ── Stats bar ── */
        .stats-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .stat-card {
            display: flex;
            align-items: center;
            gap: .8rem;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(97,179,250,.2);
            border-radius: 16px;
            padding: .7rem 1.2rem;
            min-width: 140px;
        }
        .stat-card .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .stat-card .stat-icon.blue { background: rgba(97,179,250,.15); color: var(--bleu-clair); }
        .stat-card .stat-icon.gold { background: rgba(241,196,15,.15); color: #f1c40f; }
        .stat-card .stat-icon.green { background: rgba(39,174,96,.15); color: #27ae60; }
        .stat-card .stat-num { font-size: 1.4rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: .72rem; color: var(--gris); margin-top: 2px; }

        /* ── Grille véhicules ── */
        .vehicules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .vehicule-card {
            background: rgba(255,255,255,.07);
            border-radius: 20px;
            border: 1px solid rgba(97,179,250,.2);
            overflow: hidden;
            transition: all .3s;
        }
        .vehicule-card:hover {
            transform: translateY(-5px);
            border-color: var(--bleu-clair);
            box-shadow: 0 10px 30px rgba(25,118,210,.2);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(25,118,210,.4), rgba(97,179,250,.1));
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h3 {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .badge {
            display: inline-block;
            padding: .25rem .8rem;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }
        .badge-dispo { background: rgba(39,174,96,.2); color: #27ae60; border: 1px solid rgba(39,174,96,.4); }
        .badge-indispo { background: rgba(231,76,60,.2); color: #e74c3c; border: 1px solid rgba(231,76,60,.4); }
        .badge-maint { background: rgba(241,196,15,.2); color: #f1c40f; border: 1px solid rgba(241,196,15,.4); }

        .card-body { padding: 1.5rem; }
        .card-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .8rem;
            margin-bottom: 1rem;
        }
        .info-item .info-label {
            font-size: .75rem;
            color: var(--gris);
            text-transform: uppercase;
            display: block;
        }
        .info-item .info-value {
            font-size: .95rem;
            font-weight: 500;
            display: block;
            margin-top: 3px;
        }
        .info-item .info-value code { color: var(--bleu-clair); font-family: monospace; }

        .resa-badges {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin: 1rem 0;
        }
        .resa-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: .25rem .7rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }
        .resa-badge.attente { background: rgba(241,196,15,.15); color: #f1c40f; }
        .resa-badge.confirmee { background: rgba(39,174,96,.15); color: #27ae60; }
        .resa-badge.vide { background: rgba(167,169,172,.1); color: var(--gris); }

        /* ── Actions (boutons Modifier et Supprimer) ── */
        .card-actions {
            display: flex;
            gap: .8rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .btn-edit {
            flex: 1;
            background: rgba(25,118,210,.2);
            border: 1px solid rgba(97,179,250,.3);
            color: var(--bleu-clair);
            padding: .6rem;
            border-radius: 12px;
            font-size: .85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: all .3s;
        }
        .btn-edit:hover { background: rgba(25,118,210,.4); }
        .btn-delete {
            background: rgba(231,76,60,.15);
            border: 1px solid rgba(231,76,60,.3);
            color: #e74c3c;
            padding: .6rem 1rem;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-delete:hover { background: rgba(231,76,60,.3); }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255,255,255,.05);
            border-radius: 20px;
        }
        .empty-state i { font-size: 4rem; color: rgba(97,179,250,.3); margin-bottom: 1rem; display: block; }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 14px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(39,174,96,.15); border: 1px solid rgba(39,174,96,.4); color: #27ae60; }
        .alert-error { background: rgba(231,76,60,.15); border: 1px solid rgba(231,76,60,.4); color: #e74c3c; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
    <nav>
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
        <a href="mes_vehicules.php" class="active"><i class="fas fa-key"></i> Mes véhicules</a>
        <a href="mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
        <a href="../backoffice/admin.php"><i class="fas fa-shield-alt"></i> Admin</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
</nav>

<div class="container">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php foreach ($_SESSION['errors'] as $e): ?>
                <?= htmlspecialchars($e) ?><br>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php
        $totalVehicules = count($vehicules ?? []);
        $totalAttente = 0;
        $totalConfirmees = 0;
        foreach ($vehicules ?? [] as $v) {
            $counts = $resaCounts[$v['id']] ?? ['en_attente'=>0, 'confirmee'=>0];
            $totalAttente += $counts['en_attente'];
            $totalConfirmees += $counts['confirmee'];
        }
    ?>

    <div class="page-header">
        <div style="display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap;">
            <h1><i class="fas fa-car"></i> Mes Véhicules</h1>
            <!-- ✅ LIEN VERS PAGE D'AJOUT (PAGE COMPLÈTE) -->
            <a href="mes_vehicules.php?action=add" class="btn-add">
                <i class="fas fa-plus"></i> Ajouter un véhicule
            </a>
        </div>
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-car"></i></div>
                <div><div class="stat-num"><?= $totalVehicules ?></div><div class="stat-label">Véhicule(s)</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-clock"></i></div>
                <div><div class="stat-num"><?= $totalAttente ?></div><div class="stat-label">En attente</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div><div class="stat-num"><?= $totalConfirmees ?></div><div class="stat-label">Confirmée(s)</div></div>
            </div>
        </div>
    </div>

    <?php if (empty($vehicules)): ?>
        <div class="empty-state">
            <i class="fas fa-car-side"></i>
            <p>Vous n'avez pas encore de véhicule enregistré.</p>
        </div>
    <?php else: ?>
        <div class="vehicules-grid">
            <?php foreach ($vehicules as $v): ?>
            <div class="vehicule-card">
                <div class="card-header">
                    <h3><i class="fas fa-car"></i> <?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?></h3>
                    <?php
                        $badgeClass = match($v['statut']) {
                            'disponible' => 'badge-dispo',
                            'indisponible' => 'badge-indispo',
                            'en_maintenance' => 'badge-maint',
                            default => 'badge-indispo'
                        };
                        $statutLabel = match($v['statut']) {
                            'disponible' => '✅ Disponible',
                            'indisponible' => '❌ Indisponible',
                            'en_maintenance' => '🔧 Maintenance',
                            default => $v['statut']
                        };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= $statutLabel ?></span>
                </div>
                <div class="card-body">
                    <div class="card-info">
                        <div class="info-item"><span class="info-label">Immatriculation</span><span class="info-value"><code><?= htmlspecialchars($v['immatriculation']) ?></code></span></div>
                        <div class="info-item"><span class="info-label">Couleur</span><span class="info-value"><?= htmlspecialchars($v['couleur'] ?? '—') ?></span></div>
                        <div class="info-item"><span class="info-label">Capacité</span><span class="info-value"><i class="fas fa-users"></i> <?= $v['capacite'] ?> places</span></div>
                        <div class="info-item"><span class="info-label">Climatisation</span><span class="info-value"><?= $v['climatisation'] ? '<i class="fas fa-snowflake"></i> Oui' : '<i class="fas fa-sun"></i> Non' ?></span></div>
                    </div>

                    <?php $counts = $resaCounts[$v['id']] ?? ['en_attente'=>0, 'confirmee'=>0, 'total'=>0]; ?>
                    <div class="resa-badges">
                        <?php if ($counts['total'] == 0): ?>
                            <span class="resa-badge vide"><i class="fas fa-calendar-times"></i> Aucune réservation</span>
                        <?php else: ?>
                            <?php if ($counts['en_attente'] > 0): ?>
                                <span class="resa-badge attente"><i class="fas fa-clock"></i> <?= $counts['en_attente'] ?> en attente</span>
                            <?php endif; ?>
                            <?php if ($counts['confirmee'] > 0): ?>
                                <span class="resa-badge confirmee"><i class="fas fa-check-circle"></i> <?= $counts['confirmee'] ?> confirmée(s)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <!-- ✅ LIEN VERS PAGE DE MODIFICATION (PAGE COMPLÈTE) -->
                        <a href="mes_vehicules.php?action=edit&id=<?= $v['id'] ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer ce véhicule ?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                            <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(() => { a.style.transition = 'opacity 0.5s'; a.style.opacity = '0'; }, 4000);
    setTimeout(() => a.remove(), 4500);
});
</script>
</body>
</html>