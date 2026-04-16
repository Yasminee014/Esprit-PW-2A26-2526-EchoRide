<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Event.php';
require_once __DIR__ . '/../../Model/Sponsor.php';

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
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE - ADMIN</h2></div>
<ul class="nav-links">
<li><a href="dashboard.php" class="active">Dashboard</a></li>
<li><a href="events/list.php">Événements</a></li>
<li><a href="sponsors/list.php">Sponsors</a></li>
<li><a href="../../index.php">Voir le site</a></li>
</ul>
</nav>

<div class="container">

<div class="card-header">
<h2><i class="fas fa-tachometer-alt"></i> Tableau de bord</h2>
</div>

<!-- STATISTIQUES -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-calendar-week"></i>
        <h3><?= $totalEvents ?></h3>
        <p>Total événements</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-calendar-day"></i>
        <h3><?= $upcomingEvents ?></h3>
        <p>Événements à venir</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-handshake"></i>
        <h3><?= $totalSponsors ?></h3>
        <p>Sponsors</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-coins"></i>
        <h3><?= number_format($totalSponsoring, 0, ',', ' ') ?> DT</h3>
        <p>Sponsoring total</p>
    </div>
</div>

<!-- GRAPHIQUES -->
<div class="charts-grid">
    <div class="chart-card">
        <h3><i class="fas fa-chart-bar"></i> Top Sponsors</h3>
        <canvas id="sponsorsChart" height="250"></canvas>
    </div>
    <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> Événements par mois</h3>
        <canvas id="eventsChart" height="250"></canvas>
    </div>
</div>

<!-- TABLEAU ÉVÉNEMENTS RÉCENTS -->
<div class="card">
<div class="card-header">
<h2><i class="fas fa-calendar-alt"></i> Événements récents</h2>
<a href="events/form.php" class="btn-primary"><i class="fas fa-plus"></i> Ajouter</a>
</div>

<?php if(empty($recentEvents)): ?>
    <p style="text-align:center;padding:2rem;">Aucun événement</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>Titre</th>
<th>Ville</th>
<th>Date</th>
<th>Places</th>
<th>Statut</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($recentEvents as $e): ?>
<tr>
<td><?= htmlspecialchars($e['titre']) ?></td>
<td><?= htmlspecialchars($e['ville']) ?></td>
<td><?= date('d/m/Y', strtotime($e['date_evenement'])) ?></td>
<td><?= $e['nb_places'] ?></td>
<td><span class="badge"><?= $e['statut'] ?></span></td>
<td>
    <a href="events/form.php?id=<?= $e['id'] ?>" class="btn-edit">Modifier</a>
    <a href="events/list.php?delete=<?= $e['id'] ?>" class="btn-delete" onclick="return confirmDelete()">Supprimer</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>

<footer>
<p>Eco Ride © 2025 - Administration</p>
</footer>

<script src="../../validation.js"></script>
<script>
function confirmDelete() {
    return confirm('Supprimer ?');
}

// Graphique des sponsors
const sponsorsCtx = document.getElementById('sponsorsChart').getContext('2d');
new Chart(sponsorsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($topSponsors, 'nom_entreprise')) ?>,
        datasets: [{
            label: 'Montant sponsoring (DT)',
            data: <?= json_encode(array_column($topSponsors, 'montant_sponsoring')) ?>,
            backgroundColor: 'rgba(0, 180, 216, 0.7)',
            borderColor: '#00B4D8',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: { beginAtZero: true, ticks: { color: '#ccc' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            x: { ticks: { color: '#ccc' } }
        },
        plugins: { legend: { labels: { color: '#ccc' } } }
    }
});

// Graphique des événements par mois
const eventsCtx = document.getElementById('eventsChart').getContext('2d');
const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
const eventsData = Array(12).fill(0);
<?php foreach($monthlyStats as $stat): ?>
eventsData[<?= $stat['mois'] - 1 ?>] = <?= $stat['total'] ?>;
<?php endforeach; ?>

new Chart(eventsCtx, {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Nombre d\'événements',
            data: eventsData,
            backgroundColor: 'rgba(0, 180, 216, 0.1)',
            borderColor: '#00B4D8',
            borderWidth: 2,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#00B4D8',
            pointBorderColor: '#00B4D8',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, color: '#ccc' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            x: { ticks: { color: '#ccc' } }
        },
        plugins: { legend: { labels: { color: '#ccc' } } }
    }
});
</script>
</body>
</html>