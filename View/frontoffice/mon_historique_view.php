<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Historique | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --bleu-fonce:#1976D2; --bleu-clair:#61B3FA; --gris:#A7A9AC; --dark-bg:#0A1628; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%); color:#fff; min-height:100vh; }

        /* ── Navbar ── */
        .navbar { background:linear-gradient(90deg,var(--bleu-fonce),#0F3B6E); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,.3); position:sticky; top:0; z-index:100; }
        .navbar .logo { display:flex; align-items:center; gap:10px; font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none; }
        .navbar .logo i { color:var(--bleu-clair); }
        .navbar nav a { color:#fff !important; text-decoration:none !important; padding:.5rem 1.2rem; border-radius:25px; font-size:.88rem; font-weight:500; transition:all .3s; border:1px solid rgba(97,179,250,.35); background:rgba(255,255,255,.08); display:inline-flex; align-items:center; gap:8px; margin:0 2px; }
        .navbar nav a:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; }
        .navbar nav a.active { background:rgba(25,118,210,.35) !important; border-color:#61B3FA !important; color:#61B3FA !important; }
        .navbar nav a.admin-nav { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; color:#61B3FA !important; }

        /* ── Container ── */
        .container { max-width:1100px; margin:0 auto; padding:2rem; }

        /* ── Header ── */
        .page-header { margin-bottom:2rem; }
        .page-header h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; margin-bottom:.4rem; }
        .page-header h1 i { color:var(--bleu-clair); }
        .page-header p { color:var(--gris); font-size:.9rem; }

        /* ── Stats ── */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:2rem; }
        .stat-card { background:rgba(255,255,255,.07); border:1px solid rgba(97,179,250,.2); border-radius:16px; padding:1.1rem 1.3rem; display:flex; align-items:center; gap:1rem; transition:all .3s; }
        .stat-card:hover { transform:translateY(-3px); border-color:var(--bleu-clair); }
        .stat-card .icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
        .stat-card .icon.blue   { background:rgba(97,179,250,.15); color:var(--bleu-clair); }
        .stat-card .icon.green  { background:rgba(39,174,96,.15);  color:#27ae60; }
        .stat-card .icon.red    { background:rgba(231,76,60,.15);  color:#e74c3c; }
        .stat-card .icon.gold   { background:rgba(241,196,15,.15); color:#f1c40f; }
        .stat-card .num  { font-size:1.6rem; font-weight:700; line-height:1; }
        .stat-card .lbl  { font-size:.72rem; color:var(--gris); margin-top:2px; }

        /* ── Tabs ── */
        .tabs { display:flex; gap:.5rem; margin-bottom:1.5rem; border-bottom:2px solid rgba(97,179,250,.15); padding-bottom:0; }
        .tab-btn { padding:.7rem 1.5rem; border:none; background:none; color:var(--gris); font-size:.9rem; font-weight:600; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .25s; display:flex; align-items:center; gap:8px; font-family:inherit; }
        .tab-btn:hover { color:#fff; }
        .tab-btn.active { color:var(--bleu-clair); border-bottom-color:var(--bleu-clair); }
        .tab-btn .count { background:rgba(97,179,250,.2); color:var(--bleu-clair); border-radius:10px; padding:1px 8px; font-size:.75rem; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }

        /* ── Filtres ── */
        .filter-bar { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.2rem; }
        .f-btn { display:inline-flex; align-items:center; gap:5px; padding:.4rem 1rem; border-radius:20px; font-size:.8rem; font-weight:600; cursor:pointer; border:1px solid rgba(97,179,250,.2); background:rgba(255,255,255,.05); color:var(--gris); transition:all .25s; }
        .f-btn:hover { background:rgba(97,179,250,.15); color:#fff; }
        .f-btn.active { background:rgba(25,118,210,.3); color:#fff; border-color:var(--bleu-clair); }
        .f-btn.f-confirmee.active { background:rgba(39,174,96,.2); border-color:#27ae60; color:#27ae60; }
        .f-btn.f-annulee.active   { background:rgba(231,76,60,.2); border-color:#e74c3c; color:#e74c3c; }
        .f-btn.f-attente.active   { background:rgba(241,196,15,.2); border-color:#f1c40f; color:#f1c40f; }

        /* ── Table ── */
        .table-wrap { background:rgba(255,255,255,.05); border-radius:20px; overflow:hidden; border:1px solid rgba(97,179,250,.15); }
        .table-top { padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(97,179,250,.15); flex-wrap:wrap; gap:.5rem; }
        .table-top h3 { font-size:1rem; color:var(--bleu-clair); display:flex; align-items:center; gap:8px; }
        .result-count { font-size:.8rem; color:var(--gris); background:rgba(255,255,255,.08); padding:.3rem .8rem; border-radius:20px; }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(25,118,210,.2); }
        thead th { padding:.85rem 1rem; text-align:left; font-size:.76rem; text-transform:uppercase; letter-spacing:.8px; color:var(--bleu-clair); }
        tbody tr { border-bottom:1px solid rgba(255,255,255,.05); transition:all .2s; }
        tbody tr:hover { background:rgba(97,179,250,.05); }
        tbody tr.hidden-row { display:none; }
        tbody td { padding:.85rem 1rem; font-size:.88rem; }

        /* ── Badges ── */
        .badge { display:inline-block; padding:.25rem .8rem; border-radius:20px; font-size:.75rem; font-weight:600; }
        .badge-confirmee { background:rgba(39,174,96,.2);   color:#27ae60; border:1px solid rgba(39,174,96,.4); }
        .badge-annulee   { background:rgba(231,76,60,.2);   color:#e74c3c; border:1px solid rgba(231,76,60,.4); }
        .badge-attente   { background:rgba(241,196,15,.2);  color:#f1c40f; border:1px solid rgba(241,196,15,.4); }
        .badge-disponible      { background:rgba(39,174,96,.2);   color:#27ae60; border:1px solid rgba(39,174,96,.4); }
        .badge-indisponible    { background:rgba(231,76,60,.2);   color:#e74c3c; border:1px solid rgba(231,76,60,.4); }
        .badge-en_maintenance  { background:rgba(241,196,15,.2);  color:#f1c40f; border:1px solid rgba(241,196,15,.4); }

        /* ── Rôle badge ── */
        .role-badge { display:inline-flex; align-items:center; gap:5px; padding:.2rem .7rem; border-radius:12px; font-size:.75rem; font-weight:600; }
        .role-conducteur { background:rgba(97,179,250,.15); color:var(--bleu-clair); border:1px solid rgba(97,179,250,.3); }
        .role-passager   { background:rgba(155,89,182,.15); color:#9b59b6; border:1px solid rgba(155,89,182,.3); }

        /* ── Clim icon ── */
        .clim-yes { color:var(--bleu-clair); }
        .clim-no  { color:var(--gris); }

        /* ── Empty ── */
        .empty-state { text-align:center; padding:3rem 2rem; }
        .empty-state i { font-size:3rem; color:rgba(97,179,250,.3); margin-bottom:1rem; display:block; }
        .empty-state p { color:var(--gris); font-size:.9rem; }

        code { color:var(--bleu-clair); font-family:monospace; font-size:.85rem; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
    <nav>
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
        <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
        <a href="mon_historique.php" class="active"><i class="fas fa-history"></i> Mon historique</a>
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="../backoffice/admin.php" class="admin-nav"><i class="fas fa-shield-alt"></i> Admin</a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
</nav>

<div class="container">

    <!-- Header -->
    <div class="page-header">
        <h1><i class="fas fa-history"></i> Mon Historique</h1>
        <p>Retrouvez tous vos véhicules et réservations passées</p>
    </div>

    <!-- Stats globales -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="icon blue"><i class="fas fa-car"></i></div>
            <div>
                <div class="num"><?= count($vehicules) ?></div>
                <div class="lbl">Véhicule(s)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon blue"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <div class="num"><?= count($reservations) ?></div>
                <div class="lbl">Réservation(s)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="num"><?= $stats['confirmees'] ?></div>
                <div class="lbl">Confirmée(s)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon red"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="num"><?= $stats['annulees'] ?></div>
                <div class="lbl">Annulée(s)</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon gold"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="num"><?= $stats['en_attente'] ?></div>
                <div class="lbl">En attente</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('vehicules', this)">
            <i class="fas fa-car"></i> Véhicules
            <span class="count"><?= count($vehicules) ?></span>
        </button>
        <button class="tab-btn" onclick="switchTab('reservations', this)">
            <i class="fas fa-calendar-check"></i> Réservations
            <span class="count"><?= count($reservations) ?></span>
        </button>
    </div>

    <!-- ═══ TAB : VÉHICULES ═══ -->
    <div id="tab-vehicules" class="tab-content active">
        <?php if (empty($vehicules)): ?>
            <div class="empty-state">
                <i class="fas fa-car-side"></i>
                <p>Vous n'avez aucun véhicule enregistré.</p>
            </div>
        <?php else: ?>
        <div class="table-wrap">
            <div class="table-top">
                <h3><i class="fas fa-car"></i> Mes véhicules</h3>
                <span class="result-count" id="veh-count"><?= count($vehicules) ?> véhicule(s)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Marque / Modèle</th>
                        <th>Immatriculation</th>
                        <th>Places</th>
                        <th>Clim</th>
                        <th>Couleur</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicules as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td><strong><?= htmlspecialchars($v['marque']) ?></strong> <span style="color:var(--gris)"><?= htmlspecialchars($v['modele']) ?></span></td>
                        <td><code><?= htmlspecialchars($v['immatriculation']) ?></code></td>
                        <td><?= $v['capacite'] ?></td>
                        <td>
                            <?php if ($v['climatisation']): ?>
                                <i class="fas fa-snowflake clim-yes" title="Climatisation"></i>
                            <?php else: ?>
                                <span class="clim-no">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($v['couleur'] ?? '—') ?></td>
                        <td>
                            <?php
                            $s = $v['statut'];
                            $labels = ['disponible'=>'Disponible','indisponible'=>'Indisponible','en_maintenance'=>'Maintenance'];
                            ?>
                            <span class="badge badge-<?= $s ?>"><?= $labels[$s] ?? $s ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══ TAB : RÉSERVATIONS ═══ -->
    <div id="tab-reservations" class="tab-content">
        <?php if (empty($reservations)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>Vous n'avez aucune réservation enregistrée.</p>
            </div>
        <?php else: ?>

        <!-- Filtres -->
        <div class="filter-bar">
            <button class="f-btn active" onclick="filterResa('all', this)"><i class="fas fa-list"></i> Toutes <span><?= count($reservations) ?></span></button>
            <button class="f-btn f-confirmee" onclick="filterResa('confirmee', this)"><i class="fas fa-check-circle"></i> Confirmées <span><?= $stats['confirmees'] ?></span></button>
            <button class="f-btn f-annulee"   onclick="filterResa('annulee', this)"><i class="fas fa-times-circle"></i> Annulées <span><?= $stats['annulees'] ?></span></button>
            <button class="f-btn f-attente"   onclick="filterResa('attente', this)"><i class="fas fa-hourglass-half"></i> En attente <span><?= $stats['en_attente'] ?></span></button>
        </div>

        <div class="table-wrap">
            <div class="table-top">
                <h3><i class="fas fa-calendar-check"></i> Mes réservations</h3>
                <span class="result-count" id="resa-count"><?= count($reservations) ?> réservation(s)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Rôle</th>
                        <th>Véhicule</th>
                        <th>Immatriculation</th>
                        <th>Date réservation</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody id="resa-tbody">
                    <?php foreach ($reservations as $r):
                        // Rôle : conducteur si le véhicule lui appartient, passager sinon
                        $role = (isset($r['vehicule_owner_id']) && $r['vehicule_owner_id'] == $userId) ? 'conducteur' : 'passager';
                        $statut = $r['statut'];
                    ?>
                    <tr data-statut="<?= $statut ?>">
                        <td><?= $r['id'] ?></td>
                        <td>
                            <?php if ($role === 'conducteur'): ?>
                                <span class="role-badge role-conducteur"><i class="fas fa-steering-wheel"></i> Conducteur</span>
                            <?php else: ?>
                                <span class="role-badge role-passager"><i class="fas fa-user"></i> Passager</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($r['marque'] ?? '—') ?></strong> <span style="color:var(--gris)"><?= htmlspecialchars($r['modele'] ?? '') ?></span></td>
                        <td><code><?= htmlspecialchars($r['immatriculation'] ?? '—') ?></code></td>
                        <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                        <td>
                            <?php
                            $labels = ['confirmee'=>'Confirmée','annulee'=>'Annulée','en_attente'=>'En attente'];
                            ?>
                            <span class="badge badge-<?= $statut ?>"><?= $labels[$statut] ?? $statut ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /container -->

<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

function filterResa(statut, btn) {
    document.querySelectorAll('.filter-bar .f-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('#resa-tbody tr');
    let count = 0;
    rows.forEach(row => {
        if (statut === 'all' || row.dataset.statut === statut) {
            row.classList.remove('hidden-row');
            count++;
        } else {
            row.classList.add('hidden-row');
        }
    });
    document.getElementById('resa-count').textContent = count + ' réservation(s)';
}
</script>
</body>
</html>
