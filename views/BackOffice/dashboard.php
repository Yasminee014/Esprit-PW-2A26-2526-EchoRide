<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Event.php';
require_once __DIR__ . '/../../models/Sponsor.php';

use Model\Event;
use Model\Sponsor;

$eventModel = new Event();
$sponsorModel = new Sponsor();

$totalEvents = $eventModel->countAllEvents();
$upcomingEvents = $eventModel->countUpcomingEvents();
$totalSponsors = $sponsorModel->countAll();
$totalSponsoring = $sponsorModel->getTotalMontant();
$recentEvents = $eventModel->getUpcoming(5);
$topSponsors = $sponsorModel->getTopSponsors(5);
$monthlyStats = $eventModel->getStatsByMonth();

if($recentEvents === null) $recentEvents = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Dashboard Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../../style.css">
<style>
/* Navbar intégrée */
.navbar-backoffice {
    background: linear-gradient(90deg, #1976D2, #0F3B6E);
    padding: 0.8rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar-backoffice .nav-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.navbar-backoffice .logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
}

.navbar-backoffice .logo i {
    color: #61B3FA;
    font-size: 1.5rem;
}

.navbar-backoffice .dropdown {
    position: relative;
    display: inline-block;
}

.navbar-backoffice .dropdown-btn {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding: 0.6rem 1.2rem;
    border: 1px solid rgba(97,179,250,.4);
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.navbar-backoffice .dropdown-btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: #61B3FA;
}

.navbar-backoffice .dropdown-content {
    display: none;
    position: absolute;
    top: 110%;
    left: 0;
    min-width: 240px;
    background: linear-gradient(145deg, #0D1F3A, #122A4A);
    border: 1px solid rgba(97,179,250,.3);
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,.4);
    z-index: 200;
    overflow: hidden;
}

.navbar-backoffice .dropdown-content.show {
    display: block;
    animation: fadeInDown 0.25s ease;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.navbar-backoffice .dropdown-content a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.8rem 1.2rem;
    color: #fff;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.navbar-backoffice .dropdown-content a i {
    width: 20px;
    color: #61B3FA;
}

.navbar-backoffice .dropdown-content a:hover {
    background: rgba(97,179,250,.15);
    padding-left: 1.5rem;
}

.navbar-backoffice .dropdown-content a.active {
    background: rgba(25,118,210,.3);
    border-left: 3px solid #61B3FA;
}

.navbar-backoffice .dropdown-divider {
    height: 1px;
    background: rgba(97,179,250,.2);
    margin: 0.3rem 0;
}

.navbar-backoffice .nav-right .user-info {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.1);
    padding: 0.4rem 1rem;
    border-radius: 30px;
    font-size: 0.85rem;
}

.navbar-backoffice .nav-right .user-info i {
    font-size: 1.2rem;
    color: #61B3FA;
}

@media (max-width: 768px) {
    .navbar-backoffice {
        padding: 0.6rem 1rem;
    }
    .navbar-backoffice .logo span,
    .navbar-backoffice .dropdown-btn span,
    .navbar-backoffice .user-info span {
        display: none;
    }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-backoffice">
    <div class="nav-left">
        <a href="../../index.php" class="logo">
            <i class="fas fa-leaf"></i>
            <span>EcoRide - Admin</span>
        </a>
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleDropdown()">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </button>
            <div class="dropdown-content" id="dropdownMenu">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="events/list.php">
                    <i class="fas fa-calendar-alt"></i> Événements
                </a>
                <a href="sponsors/list.php">
                    <i class="fas fa-handshake"></i> Sponsors
                </a>
                <div class="dropdown-divider"></div>
                <a href="../../index.php">
                    <i class="fas fa-globe"></i> Voir le site
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
    <div class="nav-right">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span>Administrateur</span>
        </div>
    </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-leaf"></i>
            <h2>ECO RIDE</h2>
        </div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">ADMINISTRATION</div>
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="events/list.php">
                <i class="fas fa-calendar-alt"></i> Événements
            </a>
            <a href="sponsors/list.php">
                <i class="fas fa-handshake"></i> Sponsors
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">GESTION</div>
            <a href="#"><i class="fas fa-car"></i> Véhicules</a>
            <a href="#"><i class="fas fa-ticket-alt"></i> Réservations</a>
            <a href="#"><i class="fas fa-history"></i> Historique</a>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= $totalEvents ?></h3>
                <p>Total événements</p>
            </div>
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= $upcomingEvents ?></h3>
                <p>Événements à venir</p>
            </div>
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= $totalSponsors ?></h3>
                <p>Sponsors actifs</p>
            </div>
            <div class="stat-icon"><i class="fas fa-handshake"></i></div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h3><?= number_format($totalSponsoring, 0, ',', ' ') ?> DT</h3>
                <p>Sponsoring total</p>
            </div>
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">
        <div class="card">
            <h3 style="color:var(--bleu-clair); margin-bottom:1rem;"><i class="fas fa-chart-bar"></i> Top 5 Sponsors</h3>
            <canvas id="sponsorsChart" height="250"></canvas>
        </div>
        <div class="card">
            <h3 style="color:var(--bleu-clair); margin-bottom:1rem;"><i class="fas fa-chart-line"></i> Événements par mois</h3>
            <canvas id="eventsChart" height="250"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-calendar-alt"></i> Événements récents</h2>
            <a href="events/form.php" class="btn-primary"><i class="fas fa-plus"></i> Ajouter</a>
        </div>
        <?php if(empty($recentEvents)): ?>
            <p style="text-align:center;padding:2rem; color:var(--gris);">Aucun événement</p>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Titre</th><th>Ville</th><th>Date</th><th>Places</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($recentEvents as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['titre']) ?></td>
                    <td><?= htmlspecialchars($e['ville']) ?></td>
                    <td><?= date('d/m/Y', strtotime($e['date_evenement'])) ?></td>
                    <td><?= $e['nb_places'] ?></td>
                    <td><span class="badge-<?= $e['statut'] == 'ouvert' ? 'ouvert' : ($e['statut'] == 'complet' ? 'complet' : 'annule') ?>"><?= $e['statut'] ?></span></td>
                    <td>
                        <a href="events/form.php?id=<?= $e['id'] ?>" class="btn-edit">Modifier</a>
                        <a href="events/list.php?delete=<?= $e['id'] ?>" class="btn-delete" onclick="return confirmDelete()">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer><p>Eco Ride © 2025 - Administration</p></footer>

<script src="../../validation.js"></script>
<script>
function confirmDelete() { return confirm('Supprimer ?'); }
function toggleDropdown() {
    document.getElementById("dropdownMenu").classList.toggle("show");
}
window.onclick = function(e) {
    if (!e.target.matches('.dropdown-btn') && !e.target.closest('.dropdown-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) dropdowns[i].classList.remove('show');
        }
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var dropdown = document.getElementById("dropdownMenu");
        if (dropdown.classList.contains('show')) dropdown.classList.remove('show');
    }
});

// Graphiques
new Chart(document.getElementById('sponsorsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($topSponsors, 'nom_entreprise')) ?>,
        datasets: [{ label: 'Montant sponsoring (DT)', data: <?= json_encode(array_column($topSponsors, 'montant_sponsoring')) ?>, backgroundColor: 'rgba(97,179,250,0.7)', borderColor: '#61B3FA', borderRadius: 8 }]
    },
    options: { responsive: true, scales: { y: { ticks: { color: '#A7A9AC' }, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { ticks: { color: '#A7A9AC' } } }, plugins: { legend: { labels: { color: '#A7A9AC' } } } }
});

const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
const eventsData = Array(12).fill(0);
<?php foreach($monthlyStats as $stat): ?> eventsData[<?= $stat['mois'] - 1 ?>] = <?= $stat['total'] ?>; <?php endforeach; ?>
new Chart(document.getElementById('eventsChart'), {
    type: 'line',
    data: { labels: months, datasets: [{ label: 'Nombre d\'événements', data: eventsData, backgroundColor: 'rgba(97,179,250,0.1)', borderColor: '#61B3FA', borderWidth: 3, fill: true, tension: 0.3, pointBackgroundColor: '#61B3FA', pointBorderColor: '#61B3FA' }] },
    options: { responsive: true, scales: { y: { ticks: { color: '#A7A9AC', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' } }, x: { ticks: { color: '#A7A9AC' } } }, plugins: { legend: { labels: { color: '#A7A9AC' } } } }
});
</script>
</body>
</html>