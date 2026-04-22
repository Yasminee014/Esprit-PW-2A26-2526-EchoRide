<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Les variables $vehicules viennent du contrôleur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Véhicules Disponibles | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
            transition: background 0.3s, color 0.3s;
        }

        /* ===== MODE CLAIR ===== */
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
        body.light-mode .hero-section {
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }
        body.light-mode .info-tag {
            background: #f0f0f0;
            color: #555;
        }
        body.light-mode .filter-group {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        body.light-mode .filter-group input,
        body.light-mode .filter-group select {
            color: #333;
        }

        /* ===== NAVBAR ===== */
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

        .hero-section {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }
        .hero-content h1 { font-size: 2rem; margin-bottom: 0.5rem; }
        .hero-content h1 .highlight { color: #61B3FA; }
        .hero-content p { color: rgba(255,255,255,0.8); margin-bottom: 1rem; }
        .hero-stats { display: flex; gap: 2rem; margin: 1rem 0; }
        .hero-stats .stat { text-align: center; }
        .hero-stats .stat .number { font-size: 1.5rem; font-weight: bold; }
        .hero-stats .stat .label { font-size: 0.7rem; opacity: 0.7; }
        .hero-btn {
            background: white;
            color: #1976D2;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        .hero-btn:hover { transform: translateY(-2px); }
        .hero-icon { font-size: 5rem; opacity: 0.5; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

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
        .filter-group select option { background: #0D1F3A; }
        .btn-reset {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.4);
            color: #e74c3c;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            cursor: pointer;
        }

        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .count-badge {
            background: rgba(97,179,250,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .vehicules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .vehicule-card {
            background: rgba(255,255,255,0.07);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(97,179,250,0.15);
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }
        .vehicule-card:nth-child(1) { animation-delay: 0.1s; }
        .vehicule-card:nth-child(2) { animation-delay: 0.2s; }
        .vehicule-card:nth-child(3) { animation-delay: 0.3s; }
        .vehicule-card:nth-child(4) { animation-delay: 0.4s; }
        .vehicule-card:nth-child(5) { animation-delay: 0.5s; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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

        .card-content { padding: 1rem; }
        .card-title { font-size: 1.1rem; font-weight: bold; margin-bottom: 0.3rem; }
        .card-driver { font-size: 0.8rem; color: #A7A9AC; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 5px; }
        .card-info { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 0.8rem 0; }
        .info-tag {
            background: rgba(255,255,255,0.1);
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .btn-reserver {
            width: 100%;
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            border: none;
            color: white;
            padding: 0.7rem;
            border-radius: 30px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: opacity 0.3s;
        }
        .btn-reserver:hover { opacity: 0.9; }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
        }
        .empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }

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
            font-size: 0.85rem;
        }
        .toast.success { background: #27ae60; color: white; }
        .toast.error { background: #e74c3c; color: white; }
        .toast.info { background: #1976D2; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .hero-section { flex-direction: column; text-align: center; }
            .hero-stats { justify-content: center; }
            .filters-bar { flex-direction: column; align-items: stretch; }
            .vehicules-grid { grid-template-columns: 1fr; }
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
                <a href="vehicules_disponibles.php" class="active"><i class="fas fa-car"></i> Covoiturages</a>
                <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Mes réservations</a>
                <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
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

    <div class="hero-section">
        <div class="hero-content">
            <h1>Trouvez votre <span class="highlight">véhicule idéal</span></h1>
            <p>Réservez facilement un véhicule pour vos déplacements en toute confiance</p>
            <div class="hero-stats">
                <div class="stat"><div class="number"><?= count($vehicules) ?>+</div><div class="label">Véhicules</div></div>
                <div class="stat"><div class="number">24/7</div><div class="label">Service client</div></div>
                <div class="stat"><div class="number">100%</div><div class="label">Sécurisé</div></div>
            </div>
            <a href="#vehicules" class="hero-btn">Explorer les véhicules <i class="fas fa-arrow-down"></i></a>
        </div>
        <div class="hero-icon"><i class="fas fa-car-side"></i></div>
    </div>

    <div class="filters-bar">
        <div class="filter-group"><i class="fas fa-search"></i><input type="text" id="searchInput" placeholder="Rechercher..."></div>
        <div class="filter-group"><i class="fas fa-users"></i><select id="filterPlaces"><option value="">Tous les véhicules</option><option value="4">4 places</option><option value="5">5 places</option><option value="7">7 places</option></select></div>
        <div class="filter-group"><i class="fas fa-snowflake"></i><select id="filterClim"><option value="">Climatisation</option><option value="1">Avec clim</option><option value="0">Sans clim</option></select></div>
        <button id="resetFilters" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</button>
    </div>

    <div class="section-title" id="vehicules">
        <h2><i class="fas fa-car"></i> Véhicules Disponibles</h2>
        <span class="count-badge"><?= count($vehicules) ?> véhicules</span>
    </div>

    <?php if (empty($vehicules)): ?>
        <div class="empty-state"><i class="fas fa-car-side"></i><p>Aucun véhicule disponible pour le moment.</p></div>
    <?php else: ?>
        <div class="vehicules-grid" id="vehiculesGrid">
            <?php foreach ($vehicules as $v): 
                $photoPath = '/ecoride/assets/uploads/vehicules/' . ($v['photo'] ?? '');
                $fullServerPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
            ?>
            <div class="vehicule-card" data-marque="<?= strtolower($v['marque']) ?>" data-places="<?= $v['capacite'] ?>" data-clim="<?= $v['climatisation'] ?>">
                <div class="card-image">
                    <?php if (!empty($v['photo']) && file_exists($fullServerPath)): ?>
                        <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?>">
                    <?php else: ?>
                        <div style="background: linear-gradient(135deg, #1976D2, #0F3B6E); height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                            <i class="fas fa-car" style="font-size: 60px; color: rgba(255,255,255,0.3); margin-bottom: 10px;"></i>
                            <div style="background: rgba(0,0,0,0.4); padding: 6px 20px; border-radius: 30px;">
                                <span style="color: white; font-size: 16px; font-weight: bold;"><?= htmlspecialchars($v['marque']) ?></span>
                                <span style="color: #61B3FA; font-size: 14px;"> <?= htmlspecialchars($v['modele']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <div class="card-title"><?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?></div>
                    <div class="card-driver"><i class="fas fa-user"></i> <?= htmlspecialchars($v['prenom'] . ' ' . $v['nom']) ?></div>
                    <div class="card-info">
                        <span class="info-tag"><i class="fas fa-id-card"></i> <?= htmlspecialchars($v['immatriculation']) ?></span>
                        <span class="info-tag"><i class="fas fa-users"></i> <?= $v['capacite'] ?> places</span>
                        <span class="info-tag"><?= $v['climatisation'] ? '<i class="fas fa-snowflake" style="color:#61B3FA;"></i> Clim' : '<i class="fas fa-sun" style="color:#f1c40f;"></i> Pas de clim' ?></span>
                    </div>
                    <a href="reserver_vehicule.php?vehicule_id=<?= $v['id'] ?>" class="btn-reserver"><i class="fas fa-calendar-plus"></i> Réserver</a>
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

const searchInput = document.getElementById('searchInput');
const filterPlaces = document.getElementById('filterPlaces');
const filterClim = document.getElementById('filterClim');
const resetBtn = document.getElementById('resetFilters');
const cards = document.querySelectorAll('.vehicule-card');
const countBadge = document.querySelector('.count-badge');

function filterVehicules() {
    const searchTerm = searchInput.value.toLowerCase();
    const places = filterPlaces.value;
    const clim = filterClim.value;
    let visible = 0;
    cards.forEach(card => {
        let show = true;
        const title = card.querySelector('.card-title').innerText.toLowerCase();
        const driver = card.querySelector('.card-driver').innerText.toLowerCase();
        if (searchTerm && !title.includes(searchTerm) && !driver.includes(searchTerm)) show = false;
        if (places && card.dataset.places != places) show = false;
        if (clim !== '' && card.dataset.clim != clim) show = false;
        card.style.display = show ? 'block' : 'none';
        if (show) visible++;
    });
    countBadge.innerText = visible + ' véhicules';
}

searchInput.addEventListener('input', filterVehicules);
filterPlaces.addEventListener('change', filterVehicules);
filterClim.addEventListener('change', filterVehicules);
resetBtn.addEventListener('click', () => { searchInput.value = ''; filterPlaces.value = ''; filterClim.value = ''; filterVehicules(); });

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}`;
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