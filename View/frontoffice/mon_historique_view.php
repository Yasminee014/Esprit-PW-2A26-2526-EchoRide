<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Historique | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
            transition: background 0.3s, color 0.3s;
        }

        /* Mode clair */
        body.light-mode {
            background: #f5f5f5;
            color: #333;
        }
        body.light-mode .navbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        body.light-mode .navbar .logo,
        body.light-mode .navbar .dropdown-btn,
        body.light-mode .navbar .user-info {
            color: #1976D2;
        }
        body.light-mode .dropdown-content {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        body.light-mode .dropdown-content a {
            color: #333;
        }
        body.light-mode .table-wrap {
            background: #fff;
            border-color: #e0e0e0;
        }
        body.light-mode .hero-small {
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }
        body.light-mode .stat-card {
            background: #fff;
            border-color: #e0e0e0;
        }

        /* NAVBAR (identique) */
        .navbar {
            background: linear-gradient(90deg, #1976D2, #0F3B6E);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-left { display: flex; align-items: center; gap: 2rem; }
        .logo { display: flex; align-items: center; gap: 8px; font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; }
        .logo i { color: #61B3FA; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-btn {
            background: rgba(255,255,255,0.1);
            color: #fff;
            padding: 0.6rem 1.2rem;
            border: 1px solid rgba(97,179,250,.4);
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            min-width: 220px;
            background: linear-gradient(145deg, #0D1F3A, #122A4A);
            border: 1px solid rgba(97,179,250,.3);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,.4);
            z-index: 200;
            overflow: hidden;
        }
        .dropdown-content.show { display: block; animation: fadeInDown 0.25s ease; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-content a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.8rem 1.2rem;
            color: #fff;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .dropdown-content a i { width: 20px; color: #61B3FA; }
        .dropdown-content a:hover { background: rgba(97,179,250,.15); padding-left: 1.5rem; }
        .dropdown-content a.active { background: rgba(25,118,210,.3); border-left: 3px solid #61B3FA; }
        .dropdown-divider { height: 1px; background: rgba(97,179,250,.2); margin: 0.3rem 0; }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
        }
        .theme-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            cursor: pointer;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }

        /* Hero Small */
        .hero-small {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hero-small h2 { font-size: 1.5rem; margin-bottom: 0.3rem; }
        .hero-small p { color: rgba(255,255,255,0.8); font-size: 0.85rem; }
        .hero-small-icon { font-size: 3rem; opacity: 0.4; }

        /* Stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid rgba(97,179,250,0.2);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); border-color: #61B3FA; }
        .stat-card .icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .stat-card .icon.blue { background: rgba(97,179,250,0.15); color: #61B3FA; }
        .stat-card .icon.green { background: rgba(39,174,96,0.15); color: #27ae60; }
        .stat-card .icon.red { background: rgba(231,76,60,0.15); color: #e74c3c; }
        .stat-card .icon.gold { background: rgba(241,196,15,0.15); color: #f1c40f; }
        .stat-card .num { font-size: 1.5rem; font-weight: bold; }
        .stat-card .lbl { font-size: 0.7rem; color: #A7A9AC; }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid rgba(97,179,250,0.15);
        }
        .tab-btn {
            padding: 0.7rem 1.5rem;
            background: none;
            border: none;
            color: #A7A9AC;
            font-size: 0.9rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn.active { color: #61B3FA; border-bottom-color: #61B3FA; }
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Table */
        .table-wrap {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(97,179,250,0.15);
        }
        .table-top {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(97,179,250,0.15);
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.8rem; color: #61B3FA; font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .badge {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        .badge-disponible { background: rgba(39,174,96,0.2); color: #27ae60; }
        .badge-indisponible { background: rgba(231,76,60,0.2); color: #e74c3c; }
        .badge-en_maintenance { background: rgba(241,196,15,0.2); color: #f1c40f; }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        .toast.info { background: #1976D2; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0%); opacity: 1; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .stats-row { grid-template-columns: 1fr 1fr; }
            .table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <a href="../index.php" class="logo"><i class="fas fa-leaf"></i><span>EcoRide</span></a>
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleDropdown()"><i class="fas fa-bars"></i><span>Menu</span></button>
            <div class="dropdown-content" id="dropdownMenu">
                <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
                <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Mes réservations</a>
                <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
                <a href="mon_historique.php" class="active"><i class="fas fa-history"></i> Mon historique</a>
                <div class="dropdown-divider"></div>
                <a href="../backoffice/admin.php" class="admin-link"><i class="fas fa-shield-alt"></i> Administration</a>
                <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </div>
    </div>
    <div class="nav-right">
        <button id="themeToggle" class="theme-btn"><i class="fas fa-moon"></i></button>
        <div class="user-info"><i class="fas fa-user-circle"></i><span><?= $_SESSION['user_name'] ?? 'Utilisateur' ?></span></div>
    </div>
</nav>

<div class="container">

    <!-- Hero Small -->
    <div class="hero-small">
        <div class="hero-small-content">
            <h2><i class="fas fa-history"></i> Mon Historique</h2>
            <p>Retrouvez tous vos véhicules et réservations passées</p>
        </div>
        <div class="hero-small-icon"><i class="fas fa-chart-line"></i></div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card"><div class="icon blue"><i class="fas fa-car"></i></div><div><div class="num"><?= count($vehicules) ?></div><div class="lbl">Véhicule(s)</div></div></div>
        <div class="stat-card"><div class="icon blue"><i class="fas fa-calendar-alt"></i></div><div><div class="num"><?= count($reservations) ?></div><div class="lbl">Réservation(s)</div></div></div>
        <div class="stat-card"><div class="icon green"><i class="fas fa-check-circle"></i></div><div><div class="num"><?= $stats['confirmees'] ?></div><div class="lbl">Confirmée(s)</div></div></div>
        <div class="stat-card"><div class="icon red"><i class="fas fa-times-circle"></i></div><div><div class="num"><?= $stats['annulees'] ?></div><div class="lbl">Annulée(s)</div></div></div>
        <div class="stat-card"><div class="icon gold"><i class="fas fa-hourglass-half"></i></div><div><div class="num"><?= $stats['en_attente'] ?></div><div class="lbl">En attente</div></div></div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('vehicules')"><i class="fas fa-car"></i> Véhicules <span class="count">(<?= count($vehicules) ?>)</span></button>
        <button class="tab-btn" onclick="switchTab('reservations')"><i class="fas fa-calendar-check"></i> Réservations <span class="count">(<?= count($reservations) ?>)</span></button>
    </div>

    <!-- Tab Véhicules -->
    <div id="tab-vehicules" class="tab-content active">
        <?php if (empty($vehicules)): ?>
            <div class="table-wrap"><div class="empty-state" style="text-align:center;padding:2rem;"><i class="fas fa-car-side"></i><p>Aucun véhicule enregistré.</p></div></div>
        <?php else: ?>
            <div class="table-wrap">
                <div class="table-top"><h3><i class="fas fa-car"></i> Mes véhicules</h3><span class="count-badge"><?= count($vehicules) ?> véhicule(s)</span></div>
                <table>
                    <thead><tr><th>#</th><th>Marque / Modèle</th><th>Immatriculation</th><th>Places</th><th>Clim</th><th>Couleur</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($vehicules as $v): ?>
                        <tr>
                            <td><?= $v['id'] ?></td>
                            <td><strong><?= htmlspecialchars($v['marque']) ?></strong> <?= htmlspecialchars($v['modele']) ?></td>
                            <td><code><?= htmlspecialchars($v['immatriculation']) ?></code></td>
                            <td><?= $v['capacite'] ?></td>
                            <td><?= $v['climatisation'] ? '<i class="fas fa-snowflake" style="color:#61B3FA;"></i>' : '<i class="fas fa-sun" style="color:#f1c40f;"></i>' ?></td>
                            <td><?= htmlspecialchars($v['couleur'] ?? '—') ?></td>
                            <td><span class="badge badge-<?= $v['statut'] ?>"><?= $v['statut'] === 'disponible' ? '✅ Disponible' : ($v['statut'] === 'indisponible' ? '❌ Indisponible' : '🔧 Maintenance') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab Réservations -->
    <div id="tab-reservations" class="tab-content">
        <?php if (empty($reservations)): ?>
            <div class="table-wrap"><div class="empty-state" style="text-align:center;padding:2rem;"><i class="fas fa-calendar-times"></i><p>Aucune réservation.</p></div></div>
        <?php else: ?>
            <div class="table-wrap">
                <div class="table-top"><h3><i class="fas fa-calendar-check"></i> Mes réservations</h3><span class="count-badge"><?= count($reservations) ?> réservation(s)</span></div>
                <table>
                    <thead><tr><th>#</th><th>Rôle</th><th>Véhicule</th><th>Immatriculation</th><th>Date</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservations as $r):
                            $role = (isset($r['vehicule_owner_id']) && $r['vehicule_owner_id'] == $userId) ? 'conducteur' : 'passager';
                        ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><span class="badge" style="background:<?= $role === 'conducteur' ? 'rgba(97,179,250,0.15)' : 'rgba(155,89,182,0.15)'; ?>;color:<?= $role === 'conducteur' ? '#61B3FA' : '#9b59b6'; ?>"><i class="fas fa-<?= $role === 'conducteur' ? 'steering-wheel' : 'user'; ?>"></i> <?= ucfirst($role) ?></span></td>
                            <td><strong><?= htmlspecialchars($r['marque'] ?? '—') ?></strong> <?= htmlspecialchars($r['modele'] ?? '') ?></td>
                            <td><code><?= htmlspecialchars($r['immatriculation'] ?? '—') ?></code></td>
                            <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                            <td><span class="badge badge-<?= $r['statut'] ?>"><?= $r['statut'] === 'confirmee' ? '✅ Confirmée' : ($r['statut'] === 'annulee' ? '❌ Annulée' : '⏳ En attente') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDropdown() { document.getElementById("dropdownMenu").classList.toggle("show"); }
window.onclick = function(event) {
    if (!event.target.matches('.dropdown-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) dropdowns[i].classList.remove('show');
        }
    }
}

function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`tab-${tab}`).classList.add('active');
    event.target.classList.add('active');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

const themeToggle = document.getElementById('themeToggle');
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
}
themeToggle.addEventListener('click', () => {
    document.body.classList.toggle('light-mode');
    const isLight = document.body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    themeToggle.innerHTML = isLight ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    showToast(isLight ? 'Mode clair activé' : 'Mode sombre activé', 'info');
});
</script>
</body>
</html>