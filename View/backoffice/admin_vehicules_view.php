<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gestion des véhicules | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
        }
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: #0D1F3A;
            padding: 2rem 1rem;
            position: fixed;
            height: 100vh;
        }
        .sidebar .logo { text-align: center; margin-bottom: 2rem; }
        .sidebar .logo i { font-size: 48px; color: #61B3FA; }
        .sidebar .logo h2 { color: #61B3FA; margin-top: 10px; }
        .sidebar nav ul { list-style: none; }
        .sidebar nav ul li { margin-bottom: 0.5rem; }
        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1rem;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
        }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active { background: #1976D2; }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1976D2;
        }
        .top-bar h1 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .top-bar h1 i { color: #61B3FA; }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-box {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
        }
        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
            color: #61B3FA;
        }
        .stat-box .label { color: #A7A9AC; font-size: 0.8rem; }
        .actions-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .search-box input {
            background: rgba(255,255,255,0.1);
            border: 1px solid #1976D2;
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            width: 250px;
        }
        .btn-add {
            background: #1976D2;
            color: #fff;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .table-container {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(25,118,210,0.3); }
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            vertical-align: middle;
        }
        .car-image-cell { width: 70px; }
        .car-image-cell img {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 8px;
        }
        .statut-select {
            background: rgba(255,255,255,0.1);
            border: 1px solid #1976D2;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn-icon {
            background: rgba(25,118,210,0.3);
            border: none;
            color: #61B3FA;
            width: 32px;
            height: 32px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .btn-icon.delete { background: rgba(231,76,60,0.3); color: #e74c3c; }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success { background: rgba(39,174,96,0.2); border: 1px solid #27ae60; color: #27ae60; }
        .alert-error { background: rgba(231,76,60,0.2); border: 1px solid #e74c3c; color: #e74c3c; }
        code { background: rgba(0,0,0,0.3); padding: 2px 5px; border-radius: 4px; }
        .empty-state { text-align: center; padding: 3rem; color: #A7A9AC; }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="admin-container">

    <aside class="sidebar">
        <div class="logo"><i class="fas fa-leaf"></i><h2>EcoRide</h2><p>Administration</p></div>
        <nav><ul>
            <li><a href="admin_vehicules.php" class="active"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="admin_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a></li>
            <li><a href="admin_historique.php"><i class="fas fa-chart-line"></i> Historique</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul></nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1><i class="fas fa-car"></i> Gestion des Véhicules</h1>
            <div class="top-bar-right">
                <a href="../frontoffice/vehicules_disponibles.php" class="btn-add" style="background: transparent; border: 1px solid #1976D2;"><i class="fas fa-user"></i> Espace utilisateur</a>
            </div>
        </div>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?php foreach ($_SESSION['errors'] as $e): ?><?= htmlspecialchars($e) ?><br><?php endforeach; ?></div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stat-box"><div class="number"><?= $stats['total'] ?? 0 ?></div><div class="label">Total véhicules</div></div>
            <div class="stat-box"><div class="number"><?= $stats['disponibles'] ?? 0 ?></div><div class="label">Disponibles</div></div>
            <div class="stat-box"><div class="number"><?= $stats['maintenance'] ?? 0 ?></div><div class="label">En maintenance</div></div>
            <div class="stat-box"><div class="number"><?= $stats['indisponibles'] ?? 0 ?></div><div class="label">Indisponibles</div></div>
        </div>

        <div class="actions-bar">
            <form method="GET" style="margin:0;"><div class="search-box"><i class="fas fa-search"></i><input type="text" name="search" id="searchInput" placeholder="Marque, modèle, immat..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div></form>
            <a href="admin_ajouter_vehicule.php" class="btn-add"><i class="fas fa-plus"></i> Ajouter un véhicule</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Conducteur</th>
                        <th>Marque / Modèle</th>
                        <th>Immatriculation</th>
                        <th>Couleur</th>
                        <th>Places</th>
                        <th>Clim</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($vehicules)): ?>
                    <tr><td colspan="10"><div class="empty-state"><i class="fas fa-car-side"></i><p>Aucun véhicule trouvé</p></div></td></tr>
                <?php else: ?>
                    <?php foreach ($vehicules as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td class="car-image-cell">
    <?php 
    $photoPath = '/ecoride/assets/uploads/vehicules/' . ($v['photo'] ?? '');
    $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
    
    if (!empty($v['photo']) && file_exists($fullServerPath)): 
    ?>
        <img src="<?= $photoPath ?>" 
             alt="<?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?>"
             style="width: 60px; height: 45px; object-fit: cover; border-radius: 8px;">
    <?php else: ?>
        <div style="width: 60px; height: 45px; background: #1976D2; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-car" style="color: white; font-size: 20px;"></i>
        </div>
    <?php endif; ?>
</td>
                        <td><?= htmlspecialchars(($v['prenom'] ?? '') . ' ' . ($v['nom'] ?? '')) ?></td>
                        <td><strong><?= htmlspecialchars($v['marque']) ?></strong> <?= htmlspecialchars($v['modele']) ?></td>
                        <td><code><?= htmlspecialchars($v['immatriculation']) ?></code></td>
                        <td><?= htmlspecialchars($v['couleur'] ?? '—') ?></td>
                        <td><?= $v['capacite'] ?></td>
                        <td><?= $v['climatisation'] ? '<i class="fas fa-snowflake" style="color:#61B3FA;"></i>' : '<i class="fas fa-sun" style="color:#f1c40f;"></i>' ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update_statut">
                                <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <select name="statut" class="statut-select" onchange="this.form.submit()">
                                    <option value="disponible" <?= $v['statut']==='disponible' ? 'selected':'' ?>>✓ Disponible</option>
                                    <option value="indisponible" <?= $v['statut']==='indisponible' ? 'selected':'' ?>>✗ Indisponible</option>
                                    <option value="en_maintenance" <?= $v['statut']==='en_maintenance' ? 'selected':'' ?>>⚙ Maintenance</option>
                                </select>
                            </form>
                        </td>
                        <td class="action-buttons">
                            <a href="admin_modifier_vehicule.php?id=<?= $v['id'] ?>" class="btn-icon"><i class="fas fa-edit"></i></a>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer ce véhicule ?')">
                                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $v['id'] ?>">
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
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(this._t);
    this._t = setTimeout(() => this.form.submit(), 400);
});
setTimeout(() => { document.querySelectorAll('.alert').forEach(a => a.style.display = 'none'); }, 4000);
</script>
</body>
</html>