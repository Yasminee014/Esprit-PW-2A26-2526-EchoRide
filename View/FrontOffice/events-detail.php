<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Event.php';

use Model\Event;

$id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$id) {
    header('Location: events.php');
    exit();
}

$eventModel = new Event();
$event = $eventModel->getWithSponsors($id);

if(!$event) {
    header('Location: events.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - <?= htmlspecialchars($event['titre']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../style.css">
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE</h2></div>
<ul class="nav-links">
<li><a href="../../index.php">Accueil</a></li>
<li><a href="events.php">Événements</a></li>
<li><a href="../BackOffice/dashboard.php">Admin</a></li>
</ul>
</nav>

<div class="container">
<div class="detail-card" style="background:rgba(13,31,45,0.9);border-radius:20px;padding:2rem;border:1px solid rgba(0,180,216,0.3);">
<a href="events.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour aux événements</a>

<div class="detail-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
<h1 style="color:#00B4D8;font-size:1.8rem;"><?= htmlspecialchars($event['titre']) ?></h1>
<span class="badge">
    <?php 
    switch($event['statut']) {
        case 'ouvert': echo '🟢 Ouvert'; break;
        case 'complet': echo '🔴 Complet'; break;
        case 'annule': echo '⚫ Annulé'; break;
        default: echo $event['statut'];
    }
    ?>
</span>
</div>

<div class="detail-info">
<p><i class="fas fa-tag"></i> <strong>Type :</strong> <?= htmlspecialchars($event['type']) ?></p>
<p><i class="fas fa-map-marker-alt"></i> <strong>Lieu :</strong> <?= htmlspecialchars($event['ville']) ?></p>
<p><i class="fas fa-calendar"></i> <strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></p>
<p><i class="fas fa-users"></i> <strong>Places disponibles :</strong> <?= $event['nb_places'] ?> places</p>
</div>

<!-- ✅ DESCRIPTION COMPLÈTE -->
<?php if(!empty($event['description'])): ?>
<div class="detail-description">
    <h3><i class="fas fa-align-left"></i> Description</h3>
    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
</div>
<?php endif; ?>
<!-- FIN DESCRIPTION -->

<?php if(!empty($event['sponsors'])): ?>
<div class="sponsors-section" style="margin-top:1.5rem;">
<h3 style="color:#00B4D8;margin-bottom:1rem;"><i class="fas fa-handshake"></i> Sponsors de l'événement</h3>
<div class="sponsors-grid">
<?php foreach($event['sponsors'] as $sponsor): ?>
<div class="sponsor-card">
    <?php if(!empty($sponsor['logo'])): ?>
        <img src="../../uploads/sponsors/<?= $sponsor['logo'] ?>" alt="<?= htmlspecialchars($sponsor['nom_entreprise']) ?>" class="sponsor-logo">
    <?php else: ?>
        <div class="sponsor-logo-placeholder" style="width:80px;height:80px;background:rgba(255,255,255,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
            <i class="fas fa-building" style="font-size:40px;color:#00B4D8;"></i>
        </div>
    <?php endif; ?>
    <h4><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h4>
    <?php 
    $badgeClass = '';
    $badgeText = '';
    switch($sponsor['type_sponsor']) {
        case 'principal':
            $badgeClass = 'principal';
            $badgeText = '🏆 Sponsor Principal';
            break;
        case 'secondaire':
            $badgeClass = 'secondaire';
            $badgeText = '⭐ Sponsor Secondaire';
            break;
        case 'partenaire':
            $badgeClass = 'partenaire';
            $badgeText = '🤝 Partenaire';
            break;
        default:
            $badgeClass = 'partenaire';
            $badgeText = '🤝 Partenaire';
    }
    ?>
    <div class="sponsor-level <?= $badgeClass ?>"><?= $badgeText ?></div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php else: ?>
<div class="no-sponsors" style="text-align:center;padding:1rem;color:#6B6B6B;">
    <p><i class="fas fa-info-circle"></i> Aucun sponsor pour cet événement</p>
</div>
<?php endif; ?>

<button class="btn-participate" onclick="alert('Merci de votre intérêt ! Fonctionnalité de réservation à venir.')">
<i class="fas fa-check-circle"></i> Participer à cet événement
</button>
</div>
</div>

<footer><p>Eco Ride © 2025</p></footer>

<script src="../../validation.js"></script>
</body>
</html>