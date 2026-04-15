<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Historique | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root { --bleu-fonce:#1976D2; --bleu-clair:#61B3FA; --gris:#A7A9AC; --dark-bg:#0A1628; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,var(--dark-bg) 0%,#0D1F3A 100%); color:#fff; min-height:100vh; }
        .admin-container { display:flex; min-height:100vh; }

        /* Sidebar */
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

        /* Main */
        .main-content { flex:1; margin-left:280px; padding:2rem; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:2px solid rgba(97,179,250,.3); flex-wrap:wrap; gap:1rem; }
        .top-bar h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; }
        .top-bar h1 i { color:var(--bleu-clair); }
        .top-bar-right { display:flex; align-items:center; gap:.8rem; flex-wrap:wrap; }
        .nav-btn { background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.5rem 1.1rem; border-radius:25px; font-size:.85rem; text-decoration:none; display:flex; align-items:center; gap:7px; transition:all .3s; }
        .nav-btn:hover { background:rgba(25,118,210,.3); border-color:var(--bleu-clair); color:var(--bleu-clair); }
        .admin-badge { background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.5rem 1.1rem; border-radius:25px; font-size:.85rem; display:flex; align-items:center; gap:7px; text-decoration:none; transition:all .3s; }
        .admin-badge:hover { background:rgba(25,118,210,.3); border-color:var(--bleu-clair); color:var(--bleu-clair); }

        /* Stats */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1.2rem; margin-bottom:2rem; }
        .stat-box { background:rgba(255,255,255,.07); border-radius:18px; padding:1.2rem; text-align:center; border:1px solid rgba(97,179,250,.2); transition:all .3s; }
        .stat-box:hover { transform:translateY(-4px); border-color:var(--bleu-clair); }
        .stat-box i { font-size:2rem; margin-bottom:.4rem; display:block; }
        .stat-box .number { font-size:2rem; font-weight:700; background:linear-gradient(135deg,var(--bleu-clair),#fff); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .stat-box .label { color:var(--gris); font-size:.78rem; margin-top:.3rem; }
        .stat-box.gold i { color:#f1c40f; }
        .stat-box.green i { color:#27ae60; }
        .stat-box.red i { color:#e74c3c; }
        .stat-box.blue i { color:var(--bleu-clair); }
        .stat-box.purple i { color:#9b59b6; }

        /* Filtres */
        .filter-section { background:rgba(255,255,255,.05); border:1px solid rgba(97,179,250,.15); border-radius:16px; padding:1.2rem 1.5rem; margin-bottom:1.5rem; }
        .filter-section form { display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end; }
        .filter-group { display:flex; flex-direction:column; gap:.4rem; }
        .filter-group label { font-size:.78rem; color:var(--bleu-clair); }
        .filter-group input, .filter-group select { background:rgba(255,255,255,.08); border:1px solid rgba(97,179,250,.3); color:#fff; padding:.5rem .9rem; border-radius:10px; font-size:.85rem; outline:none; }
        .filter-group select option { background:#0D1F3A; }
        .btn-filter { background:linear-gradient(135deg,var(--bleu-fonce),var(--bleu-clair)); color:#fff; border:none; padding:.55rem 1.3rem; border-radius:10px; font-size:.85rem; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all .3s; }
        .btn-filter:hover { transform:translateY(-2px); }
        .btn-reset { background:rgba(231,76,60,.15); border:1px solid rgba(231,76,60,.3); color:#e74c3c; padding:.55rem 1.1rem; border-radius:10px; font-size:.85rem; cursor:pointer; text-decoration:none; display:flex; align-items:center; gap:6px; transition:all .3s; }
        .btn-reset:hover { background:rgba(231,76,60,.3); }

        /* Table */
        .table-container { background:rgba(255,255,255,.05); border-radius:20px; overflow:hidden; border:1px solid rgba(97,179,250,.15); }
        .table-header { padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(97,179,250,.15); }
        .table-header h3 { font-size:1rem; color:var(--bleu-clair); display:flex; align-items:center; gap:8px; }
        .result-count { font-size:.82rem; color:var(--gris); background:rgba(255,255,255,.08); padding:.3rem .8rem; border-radius:20px; }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(25,118,210,.2); }
        thead th { padding:.9rem 1rem; text-align:left; font-size:.78rem; text-transform:uppercase; letter-spacing:.8px; color:var(--bleu-clair); }
        tbody tr { border-bottom:1px solid rgba(255,255,255,.05); transition:all .2s; }
        tbody tr:hover { background:rgba(97,179,250,.06); }
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
<div class="admin-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h2>EcoRide</h2>
            <p>Administration</p>
        </div>
        <nav><ul>
            <li><a href="admin_vehicules.php"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="admin_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <li><a href="../frontoffice/mes_vehicules.php"><i class="fas fa-arrow-left"></i> Retour FO</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul></nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-chart-line"></i> Historique Global</h1>
            <div class="top-bar-right">
                <a href="../frontoffice/vehicules_disponibles.php" class="nav-btn"><i class="fas fa-car"></i> Covoiturages</a>
                <a href="../frontoffice/mes_reservations.php" class="nav-btn"><i class="fas fa-calendar-check"></i> Réservations</a>
                <a href="../frontoffice/mes_vehicules.php" class="nav-btn"><i class="fas fa-key"></i> Mes véhicules</a>
                <a href="admin_historique.php" class="nav-btn"><i class="fas fa-chart-line"></i> Historique global</a>
                <a href="admin_vehicules.php" class="admin-badge"><i class="fas fa-shield-alt"></i> Admin</a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box blue">
                <i class="fas fa-list-alt"></i>
                <div class="number"><?= $stats['total'] ?? 0 ?></div>
                <div class="label">Total réservations</div>
            </div>
            <div class="stat-box green">
                <i class="fas fa-check-circle"></i>
                <div class="number"><?= $stats['confirmees'] ?? 0 ?></div>
                <div class="label">Confirmées</div>
            </div>
            <div class="stat-box red">
                <i class="fas fa-times-circle"></i>
                <div class="number"><?= $stats['annulees'] ?? 0 ?></div>
                <div class="label">Annulées</div>
            </div>
            <div class="stat-box gold">
                <i class="fas fa-clock"></i>
                <div class="number"><?= $stats['en_attente'] ?? 0 ?></div>
                <div class="label">En attente</div>
            </div>
            <div class="stat-box purple">
                <i class="fas fa-users"></i>
                <div class="number"><?= $stats['passagers'] ?? 0 ?></div>
                <div class="label">Passagers uniques</div>
            </div>
            <div class="stat-box blue">
                <i class="fas fa-car"></i>
                <div class="number"><?= $stats['vehicules'] ?? 0 ?></div>
                <div class="label">Véhicules utilisés</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Statut</label>
                    <select name="statut">
                        <option value="">Tous</option>
                        <option value="confirmee" <?= ($_GET['statut']??'')==='confirmee'?'selected':'' ?>>✅ Confirmée</option>
                        <option value="annulee"   <?= ($_GET['statut']??'')==='annulee'  ?'selected':'' ?>>❌ Annulée</option>
                        <option value="en_attente"<?= ($_GET['statut']??'')==='en_attente'?'selected':'' ?>>⏳ En attente</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Du</label>
                    <input type="date" name="date_debut" value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Au</label>
                    <input type="date" name="date_fin" value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filtrer</button>
                <a href="admin_historique.php" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
            </form>
        </div>

        <!-- Tableau -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-table"></i> Résultats</h3>
                <span class="result-count"><?= count($reservations) ?> entrée<?= count($reservations) > 1 ? 's' : '' ?></span>
            </div>
            <?php if (empty($reservations)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Aucun résultat pour ces filtres.</p>
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
                <tbody>
                <?php foreach ($reservations as $r):
                    $badgeClass = match($r['statut']) {
                        'confirmee'  => 'badge-confirmee',
                        'annulee'    => 'badge-annulee',
                        default      => 'badge-attente'
                    };
                    $badgeLabel = match($r['statut']) {
                        'confirmee'  => '✅ Confirmée',
                        'annulee'    => '❌ Annulée',
                        default      => '⏳ En attente'
                    };
                ?>
                    <tr>
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
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>