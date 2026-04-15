<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gestion des réservations | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --bleu-fonce:#1976D2; --bleu-clair:#61B3FA; --blanc:#F4F5F7; --gris:#A7A9AC; --dark-bg:#0A1628; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%); color:#fff; min-height:100vh; }
        .admin-container { display:flex; min-height:100vh; }

        .sidebar { width:280px; background:linear-gradient(180deg,var(--bleu-fonce) 0%,#0F3B6E 100%); padding:2rem 1rem; position:fixed; height:100vh; overflow-y:auto; box-shadow:4px 0 20px rgba(0,0,0,.3); }
        .sidebar .logo { margin-bottom:2rem; padding-bottom:1rem; border-bottom:2px solid var(--bleu-clair); text-align:center; }
        .sidebar .logo i { font-size:48px; color:var(--bleu-clair); margin-bottom:10px; display:block; }
        .sidebar .logo h2 { background:linear-gradient(135deg,#fff,var(--bleu-clair)); -webkit-background-clip:text; background-clip:text; color:transparent; font-size:1.5rem; }
        .sidebar .logo p { color:var(--gris); font-size:.8rem; }
        .sidebar nav ul { list-style:none; }
        .sidebar nav ul li { margin-bottom:.5rem; }
        .sidebar nav ul li a { display:flex; align-items:center; gap:12px; padding:.8rem 1rem; color:#fff; text-decoration:none; border-radius:12px; transition:all .3s; }
        .sidebar nav ul li a i { width:24px; color:var(--bleu-clair); }
        .sidebar nav ul li a:hover, .sidebar nav ul li a.active { background:rgba(255,255,255,.15); border-left:3px solid var(--bleu-clair); }
        .sidebar nav ul li a:hover i, .sidebar nav ul li a.active i { color:#fff; }

        .main-content { flex:1; margin-left:280px; padding:2rem; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:2px solid rgba(97,179,250,.3); }
        .top-bar h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; color:var(--blanc); }
        .top-bar h1 i { color:var(--bleu-clair); }
        .top-bar-right { display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; }
        .nav-btn { background: rgba(255,255,255,.08); border: 1px solid rgba(97,179,250,.3); color: #fff; padding: .5rem 1.1rem; border-radius: 25px; font-size: .85rem; text-decoration: none; display: flex; align-items: center; gap: 7px; transition: all .3s; }
        .nav-btn:hover { background: rgba(25,118,210,.3); border-color: var(--bleu-clair); color: var(--bleu-clair); }
        .admin-badge { background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.5rem 1.1rem; border-radius:25px; font-size:.85rem; display:flex; align-items:center; gap:7px; text-decoration:none; transition:all .3s; }
        .admin-badge:hover { background:rgba(25,118,210,.3); border-color:var(--bleu-clair); color:var(--bleu-clair); }

        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.5rem; margin-bottom:2rem; }
        .stat-box { background:rgba(255,255,255,.08); border-radius:20px; padding:1.5rem; text-align:center; transition:all .3s; border:1px solid rgba(97,179,250,.2); cursor:pointer; }
        .stat-box:hover { transform:translateY(-5px); border-color:var(--bleu-clair); box-shadow:0 10px 30px rgba(25,118,210,.2); }
        .stat-box i { font-size:2.5rem; color:var(--bleu-clair); margin-bottom:.5rem; display:block; }
        .stat-box .number { font-size:2.5rem; font-weight:bold; background:linear-gradient(135deg,var(--bleu-clair),#fff); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .stat-box .label { color:var(--gris); margin-top:.5rem; }

        .filter-bar { display:flex; gap:.8rem; margin-bottom:1.5rem; flex-wrap:wrap; }
        .filter-btn { background:rgba(255,255,255,.1); border:1px solid rgba(97,179,250,.3); color:var(--gris); padding:.5rem 1.2rem; border-radius:20px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .3s; }
        .filter-btn:hover, .filter-btn.active { background:rgba(25,118,210,.3); border-color:var(--bleu-clair); color:var(--bleu-clair); }

        .table-container { background:rgba(255,255,255,.05); border-radius:20px; overflow:hidden; border:1px solid rgba(97,179,250,.15); }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(25,118,210,.3); }
        thead th { padding:1rem; text-align:left; font-size:.85rem; text-transform:uppercase; letter-spacing:1px; color:var(--bleu-clair); }
        tbody tr { border-bottom:1px solid rgba(255,255,255,.05); transition:all .3s; }
        tbody tr:last-child { border-bottom:none; }
        tbody tr:hover { background:rgba(97,179,250,.07); }
        tbody td { padding:.9rem 1rem; font-size:.9rem; vertical-align:middle; }

        .badge { display:inline-block; padding:.25rem .8rem; border-radius:20px; font-size:.78rem; font-weight:600; }
        .badge-attente  { background:rgba(241,196,15,.2);  color:#f1c40f; border:1px solid rgba(241,196,15,.4); }
        .badge-confirmee{ background:rgba(39,174,96,.2);   color:#27ae60; border:1px solid rgba(39,174,96,.4);  }
        .badge-annulee  { background:rgba(231,76,60,.2);   color:#e74c3c; border:1px solid rgba(231,76,60,.4);  }

        .statut-select { background:rgba(255,255,255,.1); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.3rem .6rem; border-radius:8px; font-size:.82rem; cursor:pointer; outline:none; }

        .action-buttons { display:flex; gap:8px; }
        .btn-icon { width:36px; height:36px; border:none; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.9rem; transition:all .3s; }
        .btn-icon.delete { background:rgba(231,76,60,.2); color:#e74c3c; }
        .btn-icon:hover { transform:scale(1.15); }

        .alert { padding:1rem 1.5rem; border-radius:14px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:.9rem; }
        .alert-success { background:rgba(39,174,96,.15); border:1px solid rgba(39,174,96,.4); color:#27ae60; }
        .alert-error   { background:rgba(231,76,60,.15);  border:1px solid rgba(231,76,60,.4);  color:#e74c3c; }

        .empty-state { text-align:center; padding:3rem; color:var(--gris); }
        .empty-state i { font-size:3rem; color:rgba(97,179,250,.3); margin-bottom:1rem; display:block; }
        code { color:var(--bleu-clair); font-family:monospace; font-size:.88rem; }
    </style>
</head>
<body>
<div class="admin-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h2>EcoRide</h2>
            <p>Administration</p>
        </div>
        <nav><ul>
            <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li><a href="admin_users.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="admin_vehicules.php"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="admin_reservations.php" class="active"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <li><a href="admin_trajets.php"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="admin_reclamations.php"><i class="fas fa-exclamation-triangle"></i> Réclamations</a></li>
            <li><a href="admin_lost_found.php"><i class="fas fa-search"></i> Lost & Found</a></li>
            <li><a href="admin_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul></nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-calendar-check"></i> Gestion des Réservations</h1>
            <div class="top-bar-right">
                <a href="../frontoffice/vehicules_disponibles.php" class="nav-btn"><i class="fas fa-car"></i> Covoiturages</a>
                <a href="../frontoffice/mes_reservations.php" class="nav-btn"><i class="fas fa-calendar-check"></i> Réservations</a>
                <a href="../frontoffice/mes_vehicules.php" class="nav-btn"><i class="fas fa-key"></i> Mes véhicules</a>
                <a href="admin_historique.php" class="nav-btn"><i class="fas fa-chart-line"></i> Historique global</a>
                <a href="admin_vehicules.php" class="admin-badge"><i class="fas fa-shield-alt"></i> Admin</a>
            </div>
        </div>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['errors'][0]) ?></div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box"><i class="fas fa-calendar-alt"></i><div class="number"><?= $stats['total'] ?></div><div class="label">Total réservations</div></div>
            <div class="stat-box"><i class="fas fa-clock"></i><div class="number"><?= $stats['en_attente'] ?></div><div class="label">En attente</div></div>
            <div class="stat-box"><i class="fas fa-check-circle"></i><div class="number"><?= $stats['confirmees'] ?></div><div class="label">Confirmées</div></div>
            <div class="stat-box"><i class="fas fa-times-circle"></i><div class="number"><?= $stats['annulees'] ?></div><div class="label">Annulées</div></div>
        </div>

        <!-- Filtres -->
        <div class="filter-bar">
            <a href="admin_reservations.php" class="filter-btn <?= empty($_GET['statut']) ? 'active':'' ?>">Toutes</a>
            <a href="?statut=en_attente"  class="filter-btn <?= ($_GET['statut']??'')==='en_attente'  ? 'active':'' ?>">⏳ En attente</a>
            <a href="?statut=confirmee"   class="filter-btn <?= ($_GET['statut']??'')==='confirmee'   ? 'active':'' ?>">✅ Confirmées</a>
            <a href="?statut=annulee"     class="filter-btn <?= ($_GET['statut']??'')==='annulee'     ? 'active':'' ?>">❌ Annulées</a>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Passager</th>
                        <th>Véhicule</th>
                        <th>Immatriculation</th>
                        <th>Date réservation</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Aucune réservation trouvée</p>
                        </div>
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['passager_prenom'] . ' ' . $r['passager_nom']) ?></td>
                        <td><strong><?= htmlspecialchars($r['marque']) ?></strong> <?= htmlspecialchars($r['modele']) ?></td>
                        <td><code><?= htmlspecialchars($r['immatriculation']) ?></code></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['date_reservation']))) ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update_statut">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <select name="statut" class="statut-select" onchange="this.form.submit()">
                                    <option value="en_attente" <?= $r['statut']==='en_attente' ?'selected':'' ?>>⏳ En attente</option>
                                    <option value="confirmee"  <?= $r['statut']==='confirmee'  ?'selected':'' ?>>✅ Confirmée</option>
                                    <option value="annulee"    <?= $r['statut']==='annulee'    ?'selected':'' ?>>❌ Annulée</option>
                                </select>
                            </form>
                        </td>
                        <td class="action-buttons">
                            <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer cette réservation ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn-icon delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => a.style.opacity = '0', 4000);
        setTimeout(() => a.remove(), 4500);
    });
</script>
</body>
</html>