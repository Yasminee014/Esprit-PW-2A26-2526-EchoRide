<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Model/Event.php';
require_once __DIR__ . '/Model/Sponsor.php';

use Model\Event;
use Model\Sponsor;

$eventModel = new Event();
$sponsorModel = new Sponsor();

$upcomingEvents = $eventModel->getUpcoming();
$sponsors = $sponsorModel->getActive();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Covoiturage Intelligent</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE</h2></div>
<ul class="nav-links">
<li><a href="index.php">Accueil</a></li>
<li><a href="View/FrontOffice/events.php">Événements</a></li>
<li><a href="View/BackOffice/dashboard.php">Admin</a></li>
</ul>
</nav>

<div class="hero">
<h1>Gérez vos <span class="highlight">événements facilement</span></h1>
<p>Découvrez et participez aux événements Eco Ride</p>
</div>

<div class="form-section">
<div class="form-container">
<div class="form-card">
<h2><i class="fas fa-calendar-alt"></i> Événements à venir</h2>
<?php if(!empty($upcomingEvents)): ?>
    <?php foreach($upcomingEvents as $event): ?>
    <div class="event-item">
        <div>
            <strong><?= htmlspecialchars($event['titre']) ?></strong><br>
            <small><?= htmlspecialchars($event['ville']) ?> - <?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></small>
        </div>
        <a href="View/FrontOffice/events-detail.php?id=<?= $event['id'] ?>" class="btn-small">Voir</a>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Aucun événement à venir</p>
<?php endif; ?>
</div>

<div class="form-card">
<h2><i class="fas fa-handshake"></i> Nos Sponsors</h2>
<?php if(!empty($sponsors)): ?>
    <?php foreach($sponsors as $sponsor): ?>
    <div class="sponsor-item">
        <div>
            <strong><?= htmlspecialchars($sponsor['nom_entreprise']) ?></strong>
            <br>
            <small>
            <?php 
            switch($sponsor['type_sponsor']) {
                case 'principal': echo '🏆 Sponsor Principal'; break;
                case 'secondaire': echo '⭐ Sponsor Secondaire'; break;
                case 'partenaire': echo '🤝 Partenaire'; break;
                default: echo '🤝 Partenaire';
            }
            ?>
            </small>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Aucun sponsor</p>
<?php endif; ?>
</div>
</div>
</div>

<div class="features">
<h2 style="text-align:center;margin-bottom:20px;">Notre Solution</h2>
<div class="features-grid">
<div class="feature-card"><i class="fas fa-calendar"></i><h3>Gestion événements</h3><p>Créez et gérez vos événements</p></div>
<div class="feature-card"><i class="fas fa-handshake"></i><h3>Sponsors</h3><p>Gérez vos partenaires</p></div>
<div class="feature-card"><i class="fas fa-chart-line"></i><h3>Statistiques</h3><p>Tableau de bord complet</p></div>
</div>
</div>

<footer><p><i class="fas fa-leaf"></i> Eco Ride © 2025 - Covoiturage Intelligent</p></footer>

<script src="validation.js"></script>
</body>
</html>