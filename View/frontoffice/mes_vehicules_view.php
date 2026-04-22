<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
            transition: background 0.3s, color 0.3s;
        }
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
        body.light-mode .vehicule-card,
        body.light-mode .stat-card {
            background: #fff;
            border-color: #e0e0e0;
            color: #333;
        }
        body.light-mode .hero-small {
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }
        body.light-mode .filter-group {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        body.light-mode .filter-group input,
        body.light-mode .filter-group select {
            color: #333;
        }

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
        .dropdown-btn:hover { background: rgba(255,255,255,0.2); }
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .btn-add {
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        .btn-add:hover { transform: translateY(-2px); }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
        .stat-icon {
            width: 45px;
            height: 45px;
            background: rgba(97,179,250,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }
        .stat-icon.blue { color: #61B3FA; }
        .stat-icon.gold { color: #f1c40f; }
        .stat-icon.green { color: #27ae60; }
        .stat-info .stat-number { font-size: 1.5rem; font-weight: bold; }
        .stat-info .stat-label { font-size: 0.7rem; color: #A7A9AC; }

        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            background: rgba(255,255,255,0.05);
            padding: 1rem;
            border-radius: 16px;
        }
        .filter-group {
            background: rgba(255,255,255,0.08);
            border-radius: 30px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-group i { color: #61B3FA; }
        .filter-group input, .filter-group select {
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            padding: 0.3rem;
        }
        .btn-reset {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            cursor: pointer;
        }

        .vehicules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        .vehicule-card {
            background: rgba(255,255,255,0.07);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(97,179,250,0.15);
        }
        .vehicule-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-color: #61B3FA; }

        .card-image {
            height: 160px;
            overflow: hidden;
            background: #1a1a2e;
        }
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .vehicule-card:hover .card-image img { transform: scale(1.05); }

        .card-header {
            background: linear-gradient(135deg, rgba(25,118,210,0.3), rgba(97,179,250,0.05));
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(97,179,250,0.15);
        }
        .card-header h3 { font-size: 1rem; display: flex; align-items: center; gap: 8px; }

        .badge {
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-dispo { background: rgba(39,174,96,0.2); color: #27ae60; border: 1px solid rgba(39,174,96,0.4); }
        .badge-indispo { background: rgba(231,76,60,0.2); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
        .badge-maint { background: rgba(241,196,15,0.2); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); }

        .card-body { padding: 1rem; }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .info-item { display: flex; flex-direction: column; gap: 2px; }
        .info-label { font-size: 0.65rem; color: #A7A9AC; text-transform: uppercase; }
        .info-value { font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 5px; }
        .info-value code { color: #61B3FA; background: rgba(97,179,250,0.1); padding: 2px 5px; border-radius: 5px; }

        .resa-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 0.8rem 0;
            padding-top: 0.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .resa-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .resa-badge.total { background: rgba(97,179,250,0.15); color: #61B3FA; }
        .resa-badge.attente { background: rgba(241,196,15,0.15); color: #f1c40f; }
        .resa-badge.confirmee { background: rgba(39,174,96,0.15); color: #27ae60; }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.8rem;
        }
        .btn-action {
            flex: 1;
            padding: 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s;
            border: none;
        }
        .btn-details { background: rgba(97,179,250,0.15); color: #61B3FA; border: 1px solid rgba(97,179,250,0.4); }
        .btn-details:hover { background: rgba(97,179,250,0.3); transform: translateY(-2px); }
        .btn-edit { background: rgba(241,196,15,0.15); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); }
        .btn-edit:hover { background: rgba(241,196,15,0.3); transform: translateY(-2px); }
        .btn-delete { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
        .btn-delete:hover { background: rgba(231,76,60,0.3); transform: translateY(-2px); }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .toast.success { background: #27ae60; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .vehicules-grid { grid-template-columns: 1fr; }
            .card-actions { flex-direction: column; }
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
                <a href="mes_vehicules.php" class="active"><i class="fas fa-key"></i> Mes véhicules</a>
                <a href="mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
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

    <div class="hero-small">
        <div class="hero-small-content">
            <h2><i class="fas fa-car"></i> Mes Véhicules</h2>
            <p>Gérez votre flotte de véhicules personnel</p>
        </div>
        <div class="hero-small-icon"><i class="fas fa-car-side"></i></div>
    </div>

    <div class="page-header">
        <h1><i class="fas fa-car"></i> Mes Véhicules</h1>
        <a href="mes_vehicules.php?action=add" class="btn-add"><i class="fas fa-plus"></i> Ajouter un véhicule</a>
    </div>

    <?php
        $totalVehicules = count($vehicules ?? []);
        $totalAttente = 0; $totalConfirmees = 0;
        foreach ($vehicules ?? [] as $v) {
            $counts = $resaCounts[$v['id']] ?? ['en_attente'=>0, 'confirmee'=>0];
            $totalAttente += $counts['en_attente'];
            $totalConfirmees += $counts['confirmee'];
        }
    ?>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-car"></i></div><div class="stat-info"><div class="stat-number"><?= $totalVehicules ?></div><div class="stat-label">Véhicule(s)</div></div></div>
        <div class="stat-card"><div class="stat-icon gold"><i class="fas fa-clock"></i></div><div class="stat-info"><div class="stat-number"><?= $totalAttente ?></div><div class="stat-label">En attente</div></div></div>
        <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-info"><div class="stat-number"><?= $totalConfirmees ?></div><div class="stat-label">Confirmée(s)</div></div></div>
    </div>

    <div class="filters-bar">
        <div class="filter-group"><i class="fas fa-search"></i><input type="text" id="searchVehicle" placeholder="Rechercher un véhicule..."></div>
        <div class="filter-group"><i class="fas fa-filter"></i><select id="filterStatut"><option value="">Tous les statuts</option><option value="disponible">Disponible</option><option value="indisponible">Indisponible</option><option value="en_maintenance">En maintenance</option></select></div>
        <button id="resetFiltersVehicle" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</button>
    </div>

    <?php if (empty($vehicules)): ?>
        <div class="empty-state"><i class="fas fa-car-side"></i><p>Vous n'avez pas encore de véhicule enregistré.</p><a href="mes_vehicules.php?action=add" class="btn-add" style="margin-top: 1rem; display: inline-flex;"><i class="fas fa-plus"></i> Ajouter un véhicule</a></div>
    <?php else: ?>
        <div class="vehicules-grid" id="vehiculesGrid">
            <?php foreach ($vehicules as $v): ?>
            <div class="vehicule-card" data-statut="<?= $v['statut'] ?>" data-nom="<?= strtolower($v['marque'] . ' ' . $v['modele']) ?>">
               <div class="card-image">
    <?php 
    // Chemin correct pour l'image (depuis la racine du projet)
    $photoPath = '/ecoride/assets/uploads/vehicules/' . ($v['photo'] ?? '');
    $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
    
    if (!empty($v['photo']) && file_exists($fullServerPath)): 
    ?>
        <img src="<?= $photoPath ?>" 
             alt="<?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?>"
             style="width: 100%; height: 100%; object-fit: cover;">
    <?php else: ?>
        <div style="background: linear-gradient(135deg, #1976D2, #0F3B6E); height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column;">
            <i class="fas fa-car" style="font-size: 50px; color: rgba(255,255,255,0.3); margin-bottom: 10px;"></i>
            <div style="background: rgba(0,0,0,0.4); padding: 5px 15px; border-radius: 20px;">
                <span style="color: white; font-size: 14px;"><?= htmlspecialchars($v['marque']) ?></span>
                <span style="color: #61B3FA;"> <?= htmlspecialchars($v['modele']) ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>
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
                    <div class="info-grid">
                        <div class="info-item"><span class="info-label">Immatriculation</span><span class="info-value"><code><?= htmlspecialchars($v['immatriculation']) ?></code></span></div>
                        <div class="info-item"><span class="info-label">Couleur</span><span class="info-value"><?= htmlspecialchars($v['couleur'] ?? '—') ?></span></div>
                        <div class="info-item"><span class="info-label">Capacité</span><span class="info-value"><i class="fas fa-users"></i> <?= $v['capacite'] ?> places</span></div>
                        <div class="info-item"><span class="info-label">Climatisation</span><span class="info-value"><?= $v['climatisation'] ? '<i class="fas fa-snowflake" style="color:#61B3FA;"></i> Oui' : '<i class="fas fa-sun" style="color:#f1c40f;"></i> Non' ?></span></div>
                    </div>
                    <?php $counts = $resaCounts[$v['id']] ?? ['en_attente'=>0, 'confirmee'=>0, 'total'=>0]; ?>
                    <div class="resa-badges">
                        <?php if ($counts['total'] == 0): ?>
                            <span class="resa-badge"><i class="fas fa-calendar-times"></i> Aucune réservation</span>
                        <?php else: ?>
                            <span class="resa-badge total"><i class="fas fa-calendar-alt"></i> <?= $counts['total'] ?> total</span>
                            <?php if ($counts['en_attente'] > 0): ?><span class="resa-badge attente"><i class="fas fa-clock"></i> <?= $counts['en_attente'] ?> attente</span><?php endif; ?>
                            <?php if ($counts['confirmee'] > 0): ?><span class="resa-badge confirmee"><i class="fas fa-check-circle"></i> <?= $counts['confirmee'] ?> confirmée</span><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <button class="btn-action btn-details" onclick="openDetailsModal(<?= htmlspecialchars(json_encode($v), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-info-circle"></i> Détails</button>
                        <a href="mes_vehicules.php?action=edit&id=<?= $v['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                        <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer ce véhicule ?')">
                            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                            <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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

const searchInput = document.getElementById('searchVehicle');
const filterStatut = document.getElementById('filterStatut');
const resetBtn = document.getElementById('resetFiltersVehicle');
const cards = document.querySelectorAll('.vehicule-card');

function filterVehicles() {
    const searchTerm = searchInput.value.toLowerCase();
    const statut = filterStatut.value;
    cards.forEach(card => {
        let show = true;
        const nom = card.dataset.nom || '';
        if (searchTerm && !nom.includes(searchTerm)) show = false;
        if (statut && card.dataset.statut !== statut) show = false;
        card.style.display = show ? 'block' : 'none';
    });
}
searchInput.addEventListener('input', filterVehicles);
filterStatut.addEventListener('change', filterVehicles);
resetBtn.addEventListener('click', () => { searchInput.value = ''; filterStatut.value = ''; filterVehicles(); });

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
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

function getColorFromName(couleur) {
    const colors = { 'rouge': '#e74c3c', 'red': '#e74c3c', 'bleu': '#1976D2', 'blue': '#1976D2', 'vert': '#27ae60', 'green': '#27ae60', 'noir': '#2c3e50', 'black': '#2c3e50', 'blanc': '#ecf0f1', 'white': '#ecf0f1', 'gris': '#7f8c8d', 'jaune': '#f1c40f' };
    return colors[couleur?.toLowerCase()] || '#61B3FA';
}
function getStatutLabel(statut) {
    const statuts = { 'disponible': '<span style="color:#27ae60;"><i class="fas fa-check-circle"></i> Disponible</span>', 'indisponible': '<span style="color:#e74c3c;"><i class="fas fa-times-circle"></i> Indisponible</span>', 'en_maintenance': '<span style="color:#f1c40f;"><i class="fas fa-tools"></i> En maintenance</span>' };
    return statuts[statut] || statut;
}
function openDetailsModal(vehicule) {
    const modal = document.createElement('div');
    modal.id = 'detailsModal';
    modal.style.cssText = 'display:flex;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);backdrop-filter:blur(5px);z-index:1000;align-items:center;justify-content:center;';
    modal.innerHTML = `<div style="background:linear-gradient(145deg,#0D1F3A,#122A4A);border-radius:20px;width:90%;max-width:450px;padding:1.5rem;border:1px solid rgba(97,179,250,0.3);"><div style="display:flex;justify-content:space-between;margin-bottom:1rem;padding-bottom:0.5rem;border-bottom:1px solid rgba(97,179,250,0.2);"><h3><i class="fas fa-info-circle" style="color:#61B3FA;"></i> Détails du véhicule</h3><button onclick="this.closest(\'#detailsModal\').remove()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;">&times;</button></div>
    <div><div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);"><span style="color:#A7A9AC;">Marque</span><span>${vehicule.marque || '—'}</span></div>
    <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);"><span style="color:#A7A9AC;">Modèle</span><span>${vehicule.modele || '—'}</span></div>
    <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);"><span style="color:#A7A9AC;">Immatriculation</span><span><code style="color:#61B3FA;">${vehicule.immatriculation || '—'}</code></span></div>
    <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);"><span style="color:#A7A9AC;">Couleur</span><span><span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${getColorFromName(vehicule.couleur)};margin-right:8px;"></span>${vehicule.couleur || '—'}</span></div>
    <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);"><span style="color:#A7A9AC;">Capacité</span><span>${vehicule.capacite || '—'} places</span></div>
    <div style="display:flex;justify-content:space-between;padding:0.6rem 0;border-bottom:1px solid rgba(255,255,255,0.05);"><span style="color:#A7A9AC;">Climatisation</span><span>${vehicule.climatisation ? '<i class="fas fa-check-circle" style="color:#27ae60;"></i> Oui' : '<i class="fas fa-times-circle" style="color:#e74c3c;"></i> Non'}</span></div>
    <div style="display:flex;justify-content:space-between;padding:0.6rem 0;"><span style="color:#A7A9AC;">Statut</span><span>${getStatutLabel(vehicule.statut)}</span></div></div>
    <div style="margin-top:1rem;"><button onclick="this.closest('#detailsModal').remove()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;padding:0.5rem 1rem;border-radius:10px;cursor:pointer;">Fermer</button></div></div>`;
    document.body.appendChild(modal);
}
</script>
</body>
</html>