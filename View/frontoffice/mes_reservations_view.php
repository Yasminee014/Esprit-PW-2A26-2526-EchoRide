<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
            transition: background 0.3s, color 0.3s;
        }

        /* Mode clair (même structure que vehicules_disponibles) */
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
        body.light-mode .reservation-card,
        body.light-mode .stat-card {
            background: #fff;
            border-color: #e0e0e0;
            color: #333;
        }
        body.light-mode .hero-small {
            background: linear-gradient(135deg, #1565C0, #0D47A1);
        }
        body.light-mode .filter-btn {
            background: #fff;
            border-color: #e0e0e0;
            color: #333;
        }

        /* NAVBAR (identique à vehicules_disponibles) */
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

        /* Stats Cards */
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
        .stat-icon.total { color: #61B3FA; }
        .stat-icon.waiting { color: #f1c40f; }
        .stat-icon.confirmed { color: #27ae60; }
        .stat-icon.cancelled { color: #e74c3c; }
        .stat-info .stat-number { font-size: 1.5rem; font-weight: bold; }
        .stat-info .stat-label { font-size: 0.7rem; color: #A7A9AC; }

        /* Filtres */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }
        .filter-btn {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(97,179,250,0.3);
            color: #fff;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filter-btn:hover { background: rgba(97,179,250,0.2); }
        .filter-btn.active { background: rgba(25,118,210,0.4); border-color: #61B3FA; color: #61B3FA; }

        /* Liste réservations */
        .reservations-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .reservation-card {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1rem;
            border-left: 4px solid;
            transition: transform 0.3s, box-shadow 0.3s;
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }
        .reservation-card:nth-child(1) { animation-delay: 0.1s; }
        .reservation-card:nth-child(2) { animation-delay: 0.2s; }
        .reservation-card:nth-child(3) { animation-delay: 0.3s; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .reservation-card:hover { transform: translateX(5px); box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
        .reservation-card.confirmee { border-left-color: #27ae60; }
        .reservation-card.en_attente { border-left-color: #f1c40f; }
        .reservation-card.annulee { border-left-color: #e74c3c; opacity: 0.7; }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }
        .vehicle-name { font-size: 1rem; font-weight: bold; display: flex; align-items: center; gap: 8px; }
        .vehicle-name i { color: #61B3FA; }

        .status-badge {
            padding: 0.25rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .status-badge.confirmee { background: rgba(39,174,96,0.2); color: #27ae60; border: 1px solid rgba(39,174,96,0.4); }
        .status-badge.en_attente { background: rgba(241,196,15,0.2); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); animation: pulse 2s infinite; }
        .status-badge.annulee { background: rgba(231,76,60,0.2); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

        .card-details {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 0.5rem 0;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: #A7A9AC;
        }
        .detail-item i { color: #61B3FA; width: 16px; }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.8rem;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            border: none;
        }
        .btn-view { background: rgba(97,179,250,0.15); color: #61B3FA; border: 1px solid rgba(97,179,250,0.4); }
        .btn-view:hover { background: rgba(97,179,250,0.3); transform: translateY(-2px); }
        .btn-cancel { background: rgba(241,196,15,0.15); color: #f1c40f; border: 1px solid rgba(241,196,15,0.4); }
        .btn-cancel:hover { background: rgba(241,196,15,0.3); transform: translateY(-2px); }
        .btn-delete { background: rgba(231,76,60,0.15); color: #e74c3c; border: 1px solid rgba(231,76,60,0.4); }
        .btn-delete:hover { background: rgba(231,76,60,0.3); transform: translateY(-2px); }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
        }
        .empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: linear-gradient(145deg, #0D1F3A, #122A4A);
            border-radius: 20px;
            width: 90%;
            max-width: 450px;
            padding: 1.5rem;
            border: 1px solid rgba(97,179,250,0.3);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(97,179,250,0.2);
        }
        .modal-close {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .detail-label { color: #A7A9AC; }

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
        .toast.error { background: #e74c3c; color: white; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
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
                <a href="mes_reservations.php" class="active"><i class="fas fa-calendar-check"></i> Mes réservations</a>
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

    <!-- Hero Small -->
    <div class="hero-small">
        <div class="hero-small-content">
            <h2><i class="fas fa-calendar-check"></i> Mes Réservations</h2>
            <p>Gérez vos réservations en cours et consultez votre historique</p>
        </div>
        <div class="hero-small-icon"><i class="fas fa-calendar-alt"></i></div>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Vous n'avez aucune réservation pour le moment.</p>
            <a href="vehicules_disponibles.php" class="btn-view" style="margin-top: 1rem; display: inline-block;">Trouver un véhicule</a>
        </div>
    <?php else: ?>
        <?php
            $total = count($reservations);
            $enAttente = 0; $confirmees = 0; $annulees = 0;
            foreach ($reservations as $r) {
                if ($r['statut'] === 'en_attente') $enAttente++;
                elseif ($r['statut'] === 'confirmee') $confirmees++;
                elseif ($r['statut'] === 'annulee') $annulees++;
            }
        ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon total"><i class="fas fa-list-alt"></i></div><div class="stat-info"><div class="stat-number"><?= $total ?></div><div class="stat-label">Total réservations</div></div></div>
            <div class="stat-card"><div class="stat-icon waiting"><i class="fas fa-clock"></i></div><div class="stat-info"><div class="stat-number"><?= $enAttente ?></div><div class="stat-label">En attente</div></div></div>
            <div class="stat-card"><div class="stat-icon confirmed"><i class="fas fa-check-circle"></i></div><div class="stat-info"><div class="stat-number"><?= $confirmees ?></div><div class="stat-label">Confirmées</div></div></div>
            <div class="stat-card"><div class="stat-icon cancelled"><i class="fas fa-times-circle"></i></div><div class="stat-info"><div class="stat-number"><?= $annulees ?></div><div class="stat-label">Annulées</div></div></div>
        </div>

        <!-- Filtres -->
        <div class="filters-bar">
            <button class="filter-btn all active" data-filter="all"><i class="fas fa-list"></i> Toutes</button>
            <button class="filter-btn waiting" data-filter="en_attente"><i class="fas fa-clock"></i> En attente</button>
            <button class="filter-btn confirmed" data-filter="confirmee"><i class="fas fa-check-circle"></i> Confirmées</button>
            <button class="filter-btn cancelled" data-filter="annulee"><i class="fas fa-times-circle"></i> Annulées</button>
        </div>

        <!-- Liste réservations -->
        <div class="reservations-list" id="reservationsList">
            <?php foreach ($reservations as $r): ?>
            <div class="reservation-card <?= $r['statut'] ?>" data-statut="<?= $r['statut'] ?>">
                <div class="card-header">
                    <div class="vehicle-name"><i class="fas fa-car"></i> <?= htmlspecialchars($r['marque'] . ' ' . $r['modele']) ?></div>
                    <div class="status-badge <?= $r['statut'] ?>">
                        <?php if ($r['statut'] === 'confirmee'): ?><i class="fas fa-check-circle"></i> Confirmée
                        <?php elseif ($r['statut'] === 'en_attente'): ?><i class="fas fa-hourglass-half"></i> En attente
                        <?php else: ?><i class="fas fa-times-circle"></i> Annulée<?php endif; ?>
                    </div>
                </div>
                <div class="card-details">
                    <div class="detail-item"><i class="fas fa-id-card"></i> <code><?= htmlspecialchars($r['immatriculation']) ?></code></div>
                    <div class="detail-item"><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($r['date_reservation'])) ?></div>
                </div>
                <div class="card-actions">
                    <button class="btn-action btn-view" onclick="voirDetails(<?= $r['id'] ?>, '<?= addslashes($r['marque'] . ' ' . $r['modele']) ?>', '<?= addslashes($r['immatriculation']) ?>', '<?= date('d/m/Y', strtotime($r['date_reservation'])) ?>', '<?= $r['statut'] ?>')"><i class="fas fa-eye"></i> Voir détails</button>
                    <?php if ($r['statut'] === 'en_attente'): ?>
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Annuler cette réservation ?')">
                        <input type="hidden" name="action" value="annuler"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn-action btn-cancel"><i class="fas fa-ban"></i> Annuler</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" style="margin:0;" onsubmit="return confirm('Supprimer cette réservation ?')">
                        <input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i> Supprimer</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Détails -->
<div id="detailsModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header"><h3><i class="fas fa-info-circle" style="color:#61B3FA;"></i> Détails de la réservation</h3><button class="modal-close" onclick="closeModal()">&times;</button></div>
        <div id="modalContent"></div>
        <div style="margin-top: 1rem;"><button onclick="closeModal()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 0.5rem 1rem; border-radius: 10px; cursor: pointer;">Fermer</button></div>
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

// Filtres
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.reservation-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.statut === filter) ? 'block' : 'none';
        });
    });
});

// Modal
const modal = document.getElementById('detailsModal');
function voirDetails(id, vehicule, immat, date, statut) {
    const statutClass = statut === 'confirmee' ? '✅ Confirmée' : (statut === 'en_attente' ? '⏳ En attente' : '❌ Annulée');
    document.getElementById('modalContent').innerHTML = `
        <div><div class="detail-row"><span class="detail-label">Véhicule</span><span>${vehicule}</span></div>
        <div class="detail-row"><span class="detail-label">Immatriculation</span><span><code style="color:#61B3FA;">${immat}</code></span></div>
        <div class="detail-row"><span class="detail-label">Date</span><span>${date}</span></div>
        <div class="detail-row"><span class="detail-label">Statut</span><span>${statutClass}</span></div>
        <div class="detail-row"><span class="detail-label">N° réservation</span><span>#${id}</span></div></div>`;
    modal.style.display = 'flex';
}
function closeModal() { modal.style.display = 'none'; }
modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });

// Toast
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Mode clair/sombre
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