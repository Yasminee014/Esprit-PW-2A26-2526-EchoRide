<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($vehicules))    $vehicules    = [];
if (!isset($reservations)) $reservations = [];
if (!isset($historiqueGlobal)) $historiqueGlobal = [];
if (!isset($stats))        $stats = ['confirmees' => 0, 'annulees' => 0, 'en_attente' => 0];
if (!isset($userId))       $userId = $_SESSION['user_id'] ?? 0;

$historiqueConfirme = array_values(array_filter($historiqueGlobal, function ($h) {
    return ($h['type_ligne'] ?? '') === 'reservation' && strtolower($h['statut'] ?? '') === 'confirmee';
}));

/* ── Détection du mode : liste ou page détail ── */
$mode     = $_GET['mode'] ?? 'liste';   // 'liste' | 'vehicule' | 'reservation'
$detailId = intval($_GET['id'] ?? 0);

/* ── Récupération de l'item à afficher ── */
$detailVehicule    = null;
$detailReservation = null;
$linkedResas       = [];

if ($mode === 'vehicule' && $detailId) {
    foreach ($vehicules as $v) {
        if ($v['id'] == $detailId) { $detailVehicule = $v; break; }
    }
    foreach ($reservations as $r) {
        if ($r['vehicule_id'] == $detailId) $linkedResas[] = $r;
    }
}

if ($mode === 'reservation' && $detailId) {
    foreach ($reservations as $r) {
        if ($r['id'] == $detailId) { $detailReservation = $r; break; }
    }
    if ($detailReservation) {
        foreach ($vehicules as $v) {
            if ($v['id'] == $detailReservation['vehicule_id']) { $detailVehicule = $v; break; }
        }
    }
}

/* URL de base (même page, sans query string) */
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php
        if ($mode === 'vehicule' && $detailVehicule)
            echo htmlspecialchars($detailVehicule['marque'].' '.$detailVehicule['modele']).' | EcoRide';
        elseif ($mode === 'reservation' && $detailReservation)
            echo 'Reservation #'.$detailReservation['id'].' | EcoRide';
        else
            echo 'Mon Historique | EcoRide';
    ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0A1628; color: #fff; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }

        /* Hero */
        .hero-small {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            border-radius: 20px; padding: 1.5rem 2rem; margin-bottom: 2rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .hero-small h2 { font-size: 1.5rem; margin-bottom: 0.3rem; }
        .hero-small p  { color: rgba(255,255,255,0.8); font-size: 0.85rem; }
        .hero-small-icon { font-size: 3rem; opacity: 0.4; }

        /* Stats */
        .stats-row {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem; margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255,255,255,0.07); border-radius: 16px; padding: 1rem;
            display: flex; align-items: center; gap: 1rem; border: 1px solid rgba(97,179,250,0.2);
        }
        .stat-card .icon {
            width: 45px; height: 45px; background: rgba(97,179,250,0.15);
            border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .stat-card .icon.blue  { color: #61B3FA; }
        .stat-card .icon.green { color: #27ae60; }
        .stat-card .icon.red   { color: #e74c3c; }
        .stat-card .icon.gold  { color: #f1c40f; }
        .stat-card .num { font-size: 1.5rem; font-weight: bold; }
        .stat-card .lbl { font-size: 0.7rem; color: #A7A9AC; }

        /* Tabs */
        .tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid rgba(97,179,250,0.15); }
        .tab-btn {
            padding: 0.7rem 1.5rem; background: none; border: none;
            color: #A7A9AC; font-size: 0.9rem; cursor: pointer;
            border-bottom: 3px solid transparent; transition: color 0.2s;
        }
        .tab-btn.active { color: #61B3FA; border-bottom-color: #61B3FA; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Table */
        .table-wrap {
            background: rgba(255,255,255,0.05); border-radius: 16px;
            overflow-x: auto; border: 1px solid rgba(97,179,250,0.15);
        }
        .table-top {
            padding: 1rem; display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid rgba(97,179,250,0.15);
        }
        .table-top h3 { font-size: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.8rem; color: #61B3FA; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
        td { padding: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.87rem; vertical-align: middle; }
        tbody tr:hover { background: rgba(97,179,250,0.04); }
        .table-wrap table th:first-child, .table-wrap table td:first-child { padding-left: 1rem; }
        .table-wrap table th:last-child, .table-wrap table td:last-child { padding-right: 1rem; }
        .table-wrap table td small { color: #8ea4bf; font-size: 0.75rem; }
        .col-trajet { min-width: 220px; }
        .col-dest { min-width: 260px; }
        .cell-muted { color: #8ea4bf; }
        .amount { font-weight: 700; color: #61B3FA; white-space: nowrap; }

        /* Badges */
        .badge { padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .badge-disponible     { background: rgba(39,174,96,0.2);  color: #27ae60; }
        .badge-indisponible   { background: rgba(231,76,60,0.2);  color: #e74c3c; }
        .badge-en_maintenance { background: rgba(241,196,15,0.2); color: #f1c40f; }
        .badge-confirmee      { background: rgba(39,174,96,0.2);  color: #27ae60; }
        .badge-annulee        { background: rgba(231,76,60,0.2);  color: #e74c3c; }
        .badge-en_attente     { background: rgba(241,196,15,0.2); color: #f1c40f; }

        code { background: rgba(97,179,250,0.12); padding: 2px 6px; border-radius: 6px; color: #61B3FA; font-size: 0.82rem; }

        /* Btn Détails - icône uniquement (rond) */
        .btn-detail {
            background: rgba(97,179,250,0.15); border: 1px solid rgba(97,179,250,0.3);
            color: #61B3FA; width: 34px; height: 34px; border-radius: 50%;
            cursor: pointer; font-size: 0.9rem; transition: all 0.2s;
            display: inline-flex; align-items: center; justify-content: center;
            text-decoration: none;
        }
        .btn-detail:hover { background: rgba(97,179,250,0.3); transform: translateY(-1px) scale(1.05); }

        /* ══ PAGE DÉTAIL ══ */
        .back-btn {
            display: inline-flex; align-items: center; gap: 8px;
            color: #61B3FA; text-decoration: none; font-size: 0.88rem;
            margin-bottom: 1.5rem; padding: 0.5rem 1.1rem;
            background: rgba(97,179,250,0.1); border: 1px solid rgba(97,179,250,0.25);
            border-radius: 30px; transition: all 0.2s;
        }
        .back-btn:hover { background: rgba(97,179,250,0.22); transform: translateX(-3px); }

        .detail-page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 768px) { .detail-page { grid-template-columns: 1fr; } }

        .detail-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(97,179,250,0.2);
            border-radius: 18px; overflow: hidden;
        }

        .dc-header {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            padding: 1rem 1.4rem;
            display: flex; align-items: center; gap: 10px;
        }
        .dc-header .icon-circle {
            width: 36px; height: 36px; background: rgba(255,255,255,0.2);
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;
        }
        .dc-header h3 { font-size: 1rem; }

        .dc-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.75rem 1.4rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dc-row:last-child { border-bottom: none; }
        .dc-lbl { color: #8899BB; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .dc-val { font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }

        .color-dot {
            display: inline-block; width: 14px; height: 14px;
            border-radius: 50%; border: 2px solid rgba(255,255,255,0.25); flex-shrink: 0;
        }

        /* Liste réservations liées */
        .resa-link-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.75rem 1.4rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-decoration: none; color: inherit; transition: background 0.2s;
        }
        .resa-link-item:last-child { border-bottom: none; }
        .resa-link-item:hover { background: rgba(97,179,250,0.06); }

        .alert-notfound {
            background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.3);
            border-radius: 14px; padding: 2rem; text-align: center; color: #e74c3c;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>

<div class="container">

    <!-- ══ HEADER — toujours visible ══ -->
    <div class="hero-small">
        <div class="hero-small-content">
            <?php if ($mode === 'vehicule' && $detailVehicule): ?>
                <h2><i class="fas fa-car"></i> <?= htmlspecialchars($detailVehicule['marque'].' '.$detailVehicule['modele']) ?></h2>
                <p>Détail du véhicule &middot; <code><?= htmlspecialchars($detailVehicule['immatriculation']) ?></code></p>
            <?php elseif ($mode === 'reservation' && $detailReservation): ?>
                <h2><i class="fas fa-calendar-check"></i> Réservation #<?= $detailReservation['id'] ?></h2>
                <p>Détail de la réservation &middot; <?= date('d/m/Y', strtotime($detailReservation['date_reservation'])) ?></p>
            <?php else: ?>
                <h2><i class="fas fa-history"></i> Mon Historique</h2>
                <p>Retrouvez tous vos véhicules et réservations passées</p>
            <?php endif; ?>
        </div>
        <div class="hero-small-icon">
            <i class="fas <?= $mode === 'vehicule' ? 'fa-car' : ($mode === 'reservation' ? 'fa-calendar-check' : 'fa-chart-line') ?>"></i>
        </div>
    </div>

    <!-- ══ STATS — toujours visibles ══ -->
    <div class="stats-row">
        <div class="stat-card"><div class="icon blue"><i class="fas fa-car"></i></div><div><div class="num"><?= count($vehicules) ?></div><div class="lbl">Véhicule(s)</div></div></div>
        <div class="stat-card"><div class="icon blue"><i class="fas fa-calendar-alt"></i></div><div><div class="num"><?= count($historiqueGlobal) ?></div><div class="lbl">Lignes historique</div></div></div>
        <div class="stat-card"><div class="icon green"><i class="fas fa-check-circle"></i></div><div><div class="num"><?= $stats['confirmees'] ?></div><div class="lbl">Confirmée(s)</div></div></div>
        <div class="stat-card"><div class="icon red"><i class="fas fa-times-circle"></i></div><div><div class="num"><?= $stats['annulees'] ?></div><div class="lbl">Annulée(s)</div></div></div>
        <div class="stat-card"><div class="icon gold"><i class="fas fa-hourglass-half"></i></div><div><div class="num"><?= $stats['en_attente'] ?></div><div class="lbl">En attente</div></div></div>
    </div>

    <?php if ($mode === 'liste'): ?>
    <!-- ════════════════════════════════════════
         MODE LISTE
    ════════════════════════════════════════ -->

    <?php if (empty($historiqueConfirme)): ?>
        <div class="table-wrap"><div style="text-align:center;padding:2rem;color:#8899AA;">Aucune réservation confirmée.</div></div>
    <?php else: ?>
        <div class="table-wrap">
            <div class="table-top"><h3>Historique global</h3></div>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Trajet</th>
                        <th>Marque</th>
                        <th>Matricule</th>
                        <th>Clim</th>
                        <th>Destination</th>
                        <th>Place réservée</th>
                        <th>Prix / jour</th>
                        <th>Heure</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Montant total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historiqueConfirme as $h): ?>
                    <tr>
                        <td>
                            <?php if (($h['type_ligne'] ?? '') === 'vehicule'): ?>
                                <span class="badge badge-disponible">Véhicule</span>
                            <?php else: ?>
                                <span class="badge badge-confirmee">Réservation</span>
                            <?php endif; ?>
                        </td>
                        <td class="col-trajet">
                            <strong><?= htmlspecialchars($h['point_depart'] ?? '—') ?></strong> &rarr; <strong><?= htmlspecialchars($h['point_arrive'] ?? '—') ?></strong><br>
                            <small><?= !empty($h['distance_total']) ? htmlspecialchars((string)$h['distance_total']) . ' km' : '—' ?></small>
                        </td>
                        <td><strong><?= htmlspecialchars($h['marque'] ?? '—') ?></strong></td>
                        <td><code><?= htmlspecialchars($h['immatriculation'] ?? '—') ?></code></td>
                        <td><?= !empty($h['climatisation']) ? 'Oui' : 'Non' ?></td>
                        <td class="col-dest"><?= htmlspecialchars($h['destinations'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($h['arrets_reserves'] ?? '—') ?></td>
                        <td>
                            <?php
                                $prixParJour = (float)($h['prix_total'] ?? 0);
                                if (!empty($h['date_debut']) && !empty($h['date_fin'])) {
                                    $days = max(1, (int)((strtotime($h['date_fin']) - strtotime($h['date_debut'])) / 86400) + 1);
                                    $prixParJour = $prixParJour / $days;
                                }
                            ?>
                            <?= number_format($prixParJour, 2) ?> DT
                        </td>
                        <td><?= !empty($h['heure']) ? htmlspecialchars(substr((string)$h['heure'], 0, 5)) : '—' ?></td>
                        <td><?= !empty($h['date_debut']) ? date('d/m/Y', strtotime($h['date_debut'])) : '—' ?></td>
                        <td><?= !empty($h['date_fin']) ? date('d/m/Y', strtotime($h['date_fin'])) : '—' ?></td>
                        <td class="amount">
                            <?php if (($h['type_ligne'] ?? '') === 'vehicule'): ?>
                                —
                            <?php else: ?>
                                <?= number_format((float)($h['prix_total'] ?? 0), 2) ?> DT
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php elseif ($mode === 'vehicule'): ?>
    <!-- ════════════════════════════════════════
         MODE DETAIL VEHICULE - SANS BOUTON MODIFIER
    ════════════════════════════════════════ -->

    <a class="back-btn" href="<?= $baseUrl ?>">
        <i class="fas fa-arrow-left"></i> Retour à l'historique
    </a>

    <?php if (!$detailVehicule): ?>
        <div class="alert-notfound"><i class="fas fa-exclamation-circle fa-2x"></i><br><br>Véhicule introuvable.</div>
    <?php else: ?>

    <?php
    $couleurMap = ['rouge'=>'#e74c3c','bleu'=>'#1976D2','vert'=>'#27ae60','noir'=>'#1a1a2e','blanc'=>'#ecf0f1','gris'=>'#7f8c8d','jaune'=>'#f1c40f','orange'=>'#e67e22','violet'=>'#9b59b6','marron'=>'#795548','rose'=>'#e91e63'];
    $couleurHex = $couleurMap[strtolower($detailVehicule['couleur'] ?? '')] ?? '#61B3FA';
    $statutLabels = ['disponible'=>'✅ Disponible','indisponible'=>'❌ Indisponible','en_maintenance'=>'🔧 En maintenance'];
    ?>

    <div class="detail-page">

        <!-- Infos générales - SANS BOUTON MODIFIER -->
        <div class="detail-card">
            <div class="dc-header">
                <div class="icon-circle"><i class="fas fa-car"></i></div>
                <h3>Informations générales</h3>
            </div>
            <div class="dc-row"><span class="dc-lbl">Marque</span><span class="dc-val"><?= htmlspecialchars($detailVehicule['marque']) ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Modèle</span><span class="dc-val"><?= htmlspecialchars($detailVehicule['modele']) ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Immatriculation</span><span class="dc-val"><code><?= htmlspecialchars($detailVehicule['immatriculation']) ?></code></span></div>
            <div class="dc-row"><span class="dc-lbl">Couleur</span><span class="dc-val"><span class="color-dot" style="background:<?= $couleurHex ?>"></span><?= htmlspecialchars($detailVehicule['couleur'] ?? 'Non spécifiée') ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Capacité</span><span class="dc-val"><i class="fas fa-users" style="color:#61B3FA"></i>&nbsp;<?= $detailVehicule['capacite'] ?> places</span></div>
            <div class="dc-row"><span class="dc-lbl">Climatisation</span><span class="dc-val"><?= ($detailVehicule['climatisation'] ?? 0) ? '✅ Oui' : '❌ Non' ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Statut</span><span class="dc-val"><?= $statutLabels[$detailVehicule['statut']] ?? htmlspecialchars($detailVehicule['statut']) ?></span></div>
            <?php if (!empty($detailVehicule['description'])): ?>
            <div class="dc-row"><span class="dc-lbl">Description</span><span class="dc-val" style="font-weight:400;font-size:0.83rem;color:#ccc"><?= htmlspecialchars($detailVehicule['description']) ?></span></div>
            <?php endif; ?>
            <!-- SUPPRESSION DU BOUTON MODIFIER -->
        </div>

        <!-- Réservations liées -->
        <div class="detail-card">
            <div class="dc-header">
                <div class="icon-circle"><i class="fas fa-calendar-alt"></i></div>
                <h3>Réservations liées (<?= count($linkedResas) ?>)</h3>
            </div>
            <?php if (empty($linkedResas)): ?>
                <div style="padding:2rem;text-align:center;color:#8899AA;">Aucune réservation pour ce véhicule.</div>
            <?php else: ?>
                <?php foreach ($linkedResas as $lr): ?>
                <a class="resa-link-item" href="<?= $baseUrl ?>?mode=reservation&id=<?= $lr['id'] ?>">
                    <span>
                        <strong>#<?= $lr['id'] ?></strong>
                        &nbsp;<span style="color:#8899AA;font-size:0.82rem"><?= date('d/m/Y', strtotime($lr['date_reservation'])) ?></span>
                    </span>
                    <span>
                        <span class="badge badge-<?= $lr['statut'] ?>"><?= $lr['statut'] === 'confirmee' ? 'Confirmée' : ($lr['statut'] === 'annulee' ? 'Annulée' : 'En attente') ?></span>
                        &nbsp;<i class="fas fa-chevron-right" style="color:#61B3FA;font-size:0.7rem"></i>
                    </span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    <?php endif; ?>

    <?php elseif ($mode === 'reservation'): ?>
    <!-- ════════════════════════════════════════
         MODE DETAIL RESERVATION - SANS BOUTONS D'ACTION
    ════════════════════════════════════════ -->

    <a class="back-btn" href="<?= $baseUrl ?>">
        <i class="fas fa-arrow-left"></i> Retour à l'historique
    </a>

    <?php if (!$detailReservation): ?>
        <div class="alert-notfound"><i class="fas fa-exclamation-circle fa-2x"></i><br><br>Réservation introuvable.</div>
    <?php else: ?>

    <?php
    $statutLabel = ['confirmee'=>'✅ Confirmée','annulee'=>'❌ Annulée','en_attente'=>'⏳ En attente'];
    $couleurMap  = ['rouge'=>'#e74c3c','bleu'=>'#1976D2','vert'=>'#27ae60','noir'=>'#1a1a2e','blanc'=>'#ecf0f1','gris'=>'#7f8c8d','jaune'=>'#f1c40f','orange'=>'#e67e22','violet'=>'#9b59b6','marron'=>'#795548','rose'=>'#e91e63'];
    ?>

    <div class="detail-page">

        <!-- Infos réservation - SANS BOUTONS -->
        <div class="detail-card">
            <div class="dc-header">
                <div class="icon-circle"><i class="fas fa-calendar-check"></i></div>
                <h3>Réservation #<?= $detailReservation['id'] ?></h3>
            </div>
            <div class="dc-row"><span class="dc-lbl">Date</span><span class="dc-val"><i class="fas fa-calendar" style="color:#61B3FA"></i>&nbsp;<?= date('d F Y', strtotime($detailReservation['date_reservation'])) ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Statut</span><span class="dc-val"><?= $statutLabel[$detailReservation['statut']] ?? htmlspecialchars($detailReservation['statut']) ?></span></div>
            <?php if (!empty($detailReservation['trajet_id'])): ?>
            <div class="dc-row"><span class="dc-lbl">Trajet lié</span><span class="dc-val"><i class="fas fa-route" style="color:#61B3FA"></i>&nbsp;#<?= $detailReservation['trajet_id'] ?></span></div>
            <?php endif; ?>
            <?php if (!empty($detailReservation['note'])): ?>
            <div class="dc-row"><span class="dc-lbl">Note</span><span class="dc-val" style="font-weight:400;color:#ccc"><?= htmlspecialchars($detailReservation['note']) ?></span></div>
            <?php endif; ?>
            <!-- SUPPRESSION DES BOUTONS Annuler/Supprimer/Modifier -->
        </div>

        <!-- Véhicule concerné -->
        <div class="detail-card">
            <div class="dc-header">
                <div class="icon-circle"><i class="fas fa-car"></i></div>
                <h3>Véhicule concerné</h3>
            </div>
            <?php if (!$detailVehicule): ?>
                <div style="padding:2rem;text-align:center;color:#8899AA;">Informations véhicule indisponibles.</div>
            <?php else: ?>
            <?php $couleurHex = $couleurMap[strtolower($detailVehicule['couleur'] ?? '')] ?? '#61B3FA'; ?>
            <div class="dc-row"><span class="dc-lbl">Marque / Modèle</span><span class="dc-val"><strong><?= htmlspecialchars($detailVehicule['marque']) ?></strong>&nbsp;<?= htmlspecialchars($detailVehicule['modele']) ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Immatriculation</span><span class="dc-val"><code><?= htmlspecialchars($detailVehicule['immatriculation']) ?></code></span></div>
            <div class="dc-row"><span class="dc-lbl">Couleur</span><span class="dc-val"><span class="color-dot" style="background:<?= $couleurHex ?>"></span><?= htmlspecialchars($detailVehicule['couleur'] ?? '—') ?></span></div>
            <div class="dc-row"><span class="dc-lbl">Capacité</span><span class="dc-val"><i class="fas fa-users" style="color:#61B3FA"></i>&nbsp;<?= $detailVehicule['capacite'] ?> places</span></div>
            <div class="dc-row"><span class="dc-lbl">Climatisation</span><span class="dc-val"><?= ($detailVehicule['climatisation'] ?? 0) ? '✅ Oui' : '❌ Non' ?></span></div>
            <?php endif; ?>
        </div>

    </div>

    <?php endif; ?>
    <?php endif; /* fin mode */ ?>

</div><!-- /.container -->
</body>
</html>