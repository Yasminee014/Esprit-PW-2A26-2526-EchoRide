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

        /* Navbar */
        .navbar { background:linear-gradient(90deg,var(--bleu-fonce),#0F3B6E); padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 20px rgba(0,0,0,.3); position:sticky; top:0; z-index:100; }
        .navbar .logo { display:flex; align-items:center; gap:10px; font-size:1.3rem; font-weight:700; color:#fff; text-decoration:none; }
        .navbar .logo i { color:var(--bleu-clair); }
        .navbar nav a { color:#fff !important; text-decoration:none !important; padding:.5rem 1.2rem; border-radius:25px; font-size:.88rem; font-weight:500; transition:all .3s; border:1px solid rgba(97,179,250,.35); background:rgba(255,255,255,.08); display:inline-flex; align-items:center; gap:8px; margin:0 2px; }
        .navbar nav a:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; }
        .navbar nav a.active { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav { background:rgba(255,255,255,.08) !important; border-color:rgba(97,179,250,.35) !important; color:#fff !important; }
        .navbar nav a.admin-nav:hover { background:rgba(25,118,210,.3) !important; border-color:#61B3FA !important; color:#61B3FA !important; }

        .container { max-width:1100px; margin:0 auto; padding:2rem; }

        /* Header */
        .page-header { margin-bottom:1.8rem; }
        .page-header h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; margin-bottom:.4rem; }
        .page-header h1 i { color:var(--bleu-clair); }
        .page-header p { color:var(--gris); font-size:.9rem; }

        /* Stats */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:2rem; }
        .stat-card { background:rgba(255,255,255,.07); border:1px solid rgba(97,179,250,.2); border-radius:16px; padding:1.1rem 1.3rem; display:flex; align-items:center; gap:1rem; transition:all .3s; }
        .stat-card:hover { transform:translateY(-3px); border-color:var(--bleu-clair); }
        .stat-card .icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
        .stat-card .icon.blue   { background:rgba(97,179,250,.15); color:var(--bleu-clair); }
        .stat-card .icon.green  { background:rgba(39,174,96,.15);  color:#27ae60; }
        .stat-card .icon.red    { background:rgba(231,76,60,.15);  color:#e74c3c; }
        .stat-card .icon.purple { background:rgba(155,89,182,.15); color:#9b59b6; }
        .stat-card .num  { font-size:1.6rem; font-weight:700; line-height:1; }
        .stat-card .lbl  { font-size:.72rem; color:var(--gris); margin-top:2px; }

        /* Tableau */
        .table-wrap { background:rgba(255,255,255,.05); border-radius:20px; overflow:hidden; border:1px solid rgba(97,179,250,.15); }
        .table-top { padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(97,179,250,.15); flex-wrap:wrap; gap:.5rem; }
        .table-top h3 { font-size:1rem; color:var(--bleu-clair); display:flex; align-items:center; gap:8px; }
        .result-count { font-size:.8rem; color:var(--gris); background:rgba(255,255,255,.08); padding:.3rem .8rem; border-radius:20px; }

        /* Filtres inline */
        .filter-inline { display:flex; gap:.5rem; flex-wrap:wrap; padding:.8rem 1.5rem; background:rgba(255,255,255,.03); border-bottom:1px solid rgba(97,179,250,.08); }
        .f-btn { display:inline-flex; align-items:center; gap:5px; padding:.35rem .9rem; border-radius:20px; font-size:.78rem; font-weight:600; cursor:pointer; border:1px solid rgba(97,179,250,.2); background:rgba(255,255,255,.05); color:var(--gris); transition:all .25s; }
        .f-btn:hover { background:rgba(97,179,250,.15); color:#fff; }
        .f-btn.active { background:rgba(25,118,210,.3); color:#fff; border-color:var(--bleu-clair); }

        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(25,118,210,.2); }
        thead th { padding:.85rem 1rem; text-align:left; font-size:.76rem; text-transform:uppercase; letter-spacing:.8px; color:var(--bleu-clair); }
        tbody tr { border-bottom:1px solid rgba(255,255,255,.05); transition:all .2s; }
        tbody tr:hover { background:rgba(97,179,250,.05); }
        tbody td { padding:.85rem 1rem; font-size:.88rem; }

        .badge { display:inline-block; padding:.2rem .7rem; border-radius:20px; font-size:.75rem; font-weight:600; }
        .badge-confirmee { background:rgba(39,174,96,.2);  color:#27ae60; border:1px solid rgba(39,174,96,.35); }
        .badge-annulee   { background:rgba(231,76,60,.2);  color:#e74c3c; border:1px solid rgba(231,76,60,.35); }
        .badge-attente   { background:rgba(241,196,15,.2); color:#f1c40f; border:1px solid rgba(241,196,15,.35); }

        .empty-state { text-align:center; padding:3rem; color:var(--gris); }
        .empty-state i { font-size:3rem; display:block; margin-bottom:1rem; opacity:.3; }
        code { color:var(--bleu-clair); font-family:monospace; font-size:.85rem; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
    <nav>
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
        <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
        <a href="conducteur_historique.php" class="active"><i class="fas fa-user-clock"></i> Mon historique</a>
        
        <a href="../backoffice/admin.php" class="admin-nav"><i class="fas fa-shield-alt"></i> Admin</a>

        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
</nav>

<div class="container">

    <div class="page-header">
        <h1><i class="fas fa-user-clock"></i> Mon Historique Conducteur</h1>
        <p>Toutes les réservations effectuées sur vos véhicules</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="icon blue"><i class="fas fa-list"></i></div>
            <div><div class="num"><?= $stats['total'] ?></div><div class="lbl">Total réservations</div></div>
        </div>
        <div class="stat-card">
            <div class="icon green"><i class="fas fa-check-circle"></i></div>
            <div><div class="num"><?= $stats['confirmees'] ?></div><div class="lbl">Confirmées</div></div>
        </div>
        <div class="stat-card">
            <div class="icon red"><i class="fas fa-times-circle"></i></div>
            <div><div class="num"><?= $stats['annulees'] ?></div><div class="lbl">Annulées</div></div>
        </div>
        <div class="stat-card">
            <div class="icon purple"><i class="fas fa-car"></i></div>
            <div><div class="num"><?= $stats['vehicules_actifs'] ?></div><div class="lbl">Véhicules actifs</div></div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="table-wrap">
        <div class="table-top">
            <h3><i class="fas fa-table"></i> Détail des réservations</h3>
            <span class="result-count"><?= count($reservations) ?> entrée<?= count($reservations) > 1 ? 's' : '' ?></span>
        </div>

        <!-- Filtres JS -->
        <div class="filter-inline">
            <button class="f-btn active" onclick="filtrer('tous',this)"><i class="fas fa-list"></i> Tous</button>
            <button class="f-btn" onclick="filtrer('confirmee',this)"><i class="fas fa-check-circle" style="color:#27ae60"></i> Confirmées</button>
            <button class="f-btn" onclick="filtrer('annulee',this)"><i class="fas fa-times-circle" style="color:#e74c3c"></i> Annulées</button>
            <button class="f-btn" onclick="filtrer('en_attente',this)"><i class="fas fa-clock" style="color:#f1c40f"></i> En attente</button>
        </div>

        <?php if (empty($reservations)): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <p>Aucune réservation dans votre historique.</p>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th><i class="fas fa-car"></i> Véhicule</th>
                    <th><i class="fas fa-id-card"></i> Immat.</th>
                    <th><i class="fas fa-user"></i> Passager</th>
                    <th><i class="fas fa-calendar"></i> Date</th>
                    <th><i class="fas fa-info-circle"></i> Statut</th>
                </tr>
            </thead>
            <tbody id="histTable">
            <?php foreach ($reservations as $r):
                $badgeClass = match($r['statut']) { 'confirmee'=>'badge-confirmee','annulee'=>'badge-annulee',default=>'badge-attente' };
                $badgeLabel = match($r['statut']) { 'confirmee'=>'✅ Confirmée','annulee'=>'❌ Annulée',default=>'⏳ En attente' };
            ?>
                <tr data-statut="<?= $r['statut'] ?>">
                    <td style="color:var(--gris);font-size:.78rem;">#<?= $r['id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['marque'] . ' ' . $r['modele']) ?></strong></td>
                    <td><code><?= htmlspecialchars($r['immatriculation']) ?></code></td>
                    <td><?= htmlspecialchars(trim($r['passager_nom'] . ' ' . $r['passager_prenom'])) ?: '<span style="color:var(--gris)">—</span>' ?></td>
                    <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
    <?php endif; ?>
</div>

<script>
function filtrer(statut, btn) {
    document.querySelectorAll('.f-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#histTable tr').forEach(row => {
        row.style.display = (statut === 'tous' || row.dataset.statut === statut) ? '' : 'none';
    });
}
</script>
</body>
</html>