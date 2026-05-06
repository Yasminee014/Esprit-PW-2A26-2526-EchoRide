<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models/Event.php';
require_once __DIR__ . '/models/Sponsor.php';

use Model\Event;
use Model\Sponsor;

$eventModel = new Event();
$sponsorModel = new Sponsor();

$upcomingEvents = $eventModel->getUpcoming();
$sponsors = $sponsorModel->getActive();
$totalEvents = $eventModel->countAllEvents();
$totalSponsors = $sponsorModel->countAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoRide - Covoiturage Intelligent</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
.hero-modern {
    background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
    padding: 5rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.hero-modern h1 { font-size: 2.5rem; background: linear-gradient(135deg, #61B3FA, #1976D2); -webkit-background-clip: text; background-clip: text; color: transparent; }
.hero-modern p { color: #A7A9AC; max-width: 600px; margin: 0 auto; }
.stats-banner-modern { display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; margin: -2rem auto 2rem; position: relative; z-index: 2; }
.stat-item-modern { background: linear-gradient(135deg, #1976D2, #0F3B6E); border-radius: 20px; padding: 1rem 2rem; text-align: center; min-width: 150px; }
.stat-number { font-size: 2rem; font-weight: 800; }
.stat-label { font-size: 0.8rem; color: rgba(255,255,255,0.8); }
.container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
.section-title-modern { text-align: center; margin-bottom: 2rem; position: relative; }
.section-title-modern h2 { color: #61B3FA; font-size: 1.8rem; }
.section-line { width: 60px; height: 3px; background: linear-gradient(90deg, #1976D2, #61B3FA); margin: 0.5rem auto; }
.calendar-icon-link {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(97,179,250,0.15);
    padding: 0.5rem 1rem;
    border-radius: 30px;
    text-decoration: none;
    color: #61B3FA;
    font-size: 0.85rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.calendar-icon-link:hover {
    background: #61B3FA;
    color: white;
}
.events-grid-modern { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 2rem; }
.event-card-modern { background: rgba(255,255,255,0.05); border-radius: 16px; overflow: hidden; transition: all 0.3s; border: 1px solid rgba(97,179,250,0.1); }
.event-card-modern:hover { transform: translateY(-5px); border-color: #61B3FA; }
.event-image-modern { position: relative; height: 200px; overflow: hidden; }
.event-image-modern img { width: 100%; height: 100%; object-fit: cover; }
.event-badge { position: absolute; top: 10px; right: 10px; background: #1976D2; padding: 0.2rem 0.6rem; border-radius: 15px; font-size: 0.7rem; }
.event-category { position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.6); padding: 0.2rem 0.6rem; border-radius: 15px; font-size: 0.7rem; }
.event-content-modern { padding: 1rem; }
.event-title-modern { color: #61B3FA; font-size: 1.1rem; margin-bottom: 0.5rem; }
.event-meta-modern { display: flex; gap: 1rem; font-size: 0.8rem; color: #A7A9AC; margin-bottom: 0.5rem; }
.event-desc-modern { font-size: 0.85rem; color: #ccc; line-height: 1.4; margin-bottom: 1rem; }
.event-footer-modern { display: flex; justify-content: space-between; align-items: center; }
.event-price-modern { background: rgba(97,179,250,0.15); padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem; color: #61B3FA; }
.btn-modern { background: linear-gradient(90deg, #1976D2, #0F3B6E); padding: 0.4rem 1rem; border-radius: 25px; text-decoration: none; color: white; font-size: 0.8rem; transition: all 0.3s; }
.btn-modern:hover { transform: translateX(3px); }
.sponsors-grid-modern { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.5rem; }
.sponsor-card-modern { background: rgba(255,255,255,0.05); border-radius: 16px; padding: 1rem; text-align: center; transition: all 0.3s; }
.sponsor-card-modern:hover { transform: translateY(-3px); border-color: #61B3FA; background: rgba(97,179,250,0.05); }
.sponsor-logo-modern { width: 70px; height: 70px; object-fit: contain; margin-bottom: 0.5rem; border-radius: 12px; background: white; padding: 5px; }
.sponsor-icon-modern { width: 70px; height: 70px; background: rgba(97,179,250,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem; }
.sponsor-icon-modern i { font-size: 2rem; color: #61B3FA; }
.sponsor-card-modern h3 { font-size: 0.9rem; margin-bottom: 0.3rem; }
.sponsor-level-modern { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; }
.sponsor-level-modern.gold { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
.sponsor-level-modern.silver { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #333; }
.sponsor-level-modern.bronze { background: linear-gradient(135deg, #CD7F32, #B8860B); color: white; }
.btn-discover { display: inline-block; margin-top: 0.5rem; padding: 0.3rem 0.8rem; background: rgba(97,179,250,0.15); border-radius: 20px; text-decoration: none; color: #61B3FA; font-size: 0.7rem; transition: all 0.3s; }
.btn-discover:hover { background: #61B3FA; color: white; }
.newsletter-section { background: linear-gradient(135deg, #1976D2, #0F3B6E); border-radius: 20px; padding: 2rem; text-align: center; margin: 2rem 0; }
.newsletter-form { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
.newsletter-form input { padding: 0.5rem 1rem; border-radius: 25px; border: none; width: 250px; }
.newsletter-form button { background: #0A1628; color: white; border: none; padding: 0.5rem 1rem; border-radius: 25px; cursor: pointer; }
.features { padding: 2rem; background: rgba(255,255,255,0.02); }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; text-align: center; }
.feature-card i { font-size: 2rem; color: #61B3FA; margin-bottom: 0.5rem; }
footer { text-align: center; padding: 1.5rem; color: #A7A9AC; border-top: 1px solid rgba(97,179,250,0.1); margin-top: 2rem; }
@media (max-width: 768px) { .events-grid-modern { grid-template-columns: 1fr; } .hero-modern h1 { font-size: 1.8rem; } .calendar-icon-link { position: static; transform: none; margin-top: 1rem; display: inline-flex; } .section-title-modern { display: flex; flex-direction: column; align-items: center; gap: 1rem; } }
</style>
</head>
<body>

<?php include_once __DIR__ . '/views/frontoffice/navbar.php'; ?>

<div class="hero-modern">
    <h1>Voyagez autrement avec <span class="highlight">Eco Ride</span></h1>
    <p>Découvrez des événements uniques et rejoignez une communauté engagée pour une mobilité durable</p>
</div>

<div class="stats-banner-modern">
    <div class="stat-item-modern"><i class="fas fa-calendar-alt"></i><div class="stat-number"><?= $totalEvents ?></div><div class="stat-label">Événements</div></div>
    <div class="stat-item-modern"><i class="fas fa-handshake"></i><div class="stat-number"><?= $totalSponsors ?></div><div class="stat-label">Sponsors</div></div>
    <div class="stat-item-modern"><i class="fas fa-users"></i><div class="stat-number">+5000</div><div class="stat-label">Participants</div></div>
</div>

<div class="container">
    <div class="section-title-modern">
        <h2>Événements à venir</h2>
        <div class="section-line"></div>
        <!-- ICÔNE CALENDRIER -->
        <a href="views/frontoffice/calendar.php" class="calendar-icon-link">
            <i class="fas fa-calendar-alt"></i> Voir le calendrier
        </a>
    </div>
    
    <?php if(!empty($upcomingEvents)): ?>
    <div class="events-grid-modern">
        <?php foreach(array_slice($upcomingEvents, 0, 3) as $event): ?>
        <div class="event-card-modern">
            <div class="event-image-modern"><img src="uploads/events/<?= $event['image'] ?? 'default.jpg' ?>" alt="..."><span class="event-badge"><?= date('d M', strtotime($event['date_evenement'])) ?></span><span class="event-category"><?= htmlspecialchars($event['type']) ?></span></div>
            <div class="event-content-modern"><h3 class="event-title-modern"><?= htmlspecialchars($event['titre']) ?></h3><div class="event-meta-modern"><span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['ville']) ?></span><span><i class="fas fa-clock"></i> <?= date('H:i', strtotime($event['date_evenement'])) ?></span></div><p class="event-desc-modern"><?= htmlspecialchars(substr($event['description'] ?? 'Découvrez cet événement', 0, 100)) ?>...</p><div class="event-footer-modern"><span class="event-price-modern"><?= (isset($event['prix']) && $event['prix'] > 0) ? $event['prix'] . ' DT' : 'Gratuit' ?></span><a href="views/frontoffice/events-detail.php?id=<?= $event['id'] ?>" class="btn-modern">Découvrir <i class="fas fa-arrow-right"></i></a></div></div>
        </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center; margin-top:2rem;"><a href="views/frontoffice/events.php" class="btn-modern" style="padding:0.8rem 2rem;">Voir tous les événements <i class="fas fa-arrow-right"></i></a></div>
    <?php else: ?><p style="text-align:center;">Aucun événement à venir</p><?php endif; ?>
</div>

<div class="container">
    <div class="section-title-modern"><h2>Nos Sponsors</h2><div class="section-line"></div></div>
    <?php if(!empty($sponsors)): ?>
    <div class="sponsors-grid-modern">
        <?php foreach(array_slice($sponsors, 0, 5) as $sponsor): ?>  <!-- 5 sponsors seulement -->
        <div class="sponsor-card-modern">
            <?php if(!empty($sponsor['logo'])): ?><img src="uploads/sponsors/<?= $sponsor['logo'] ?>" class="sponsor-logo-modern"><?php else: ?><div class="sponsor-icon-modern"><i class="fas fa-building"></i></div><?php endif; ?>
            <h3><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h3>
            <?php 
            $type = strtolower(trim($sponsor['type_sponsor'] ?? ''));
            switch($type) {
                case 'gold':
                case 'principal': echo '<span class="sponsor-level-modern gold">🏆 Gold</span>'; break;
                case 'silver':
                case 'secondaire': echo '<span class="sponsor-level-modern silver">⭐ Silver</span>'; break;
                default: echo '<span class="sponsor-level-modern bronze">🤝 Bronze</span>';
            }
            ?>
            <a href="views/frontoffice/sponsor-detail.php?id=<?= $sponsor['id'] ?>" class="btn-discover">Voir plus <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center; margin-top:2rem;"><a href="views/frontoffice/sponsors.php" class="btn-modern" style="padding:0.8rem 2rem;">Voir tous nos sponsors <i class="fas fa-arrow-right"></i></a></div>
    <?php else: ?><p style="text-align:center;">Aucun sponsor</p><?php endif; ?>
</div>

<div class="container">
    <div class="newsletter-section">
        <h3>Restez informé !</h3>
        <p>Recevez les dernières actualités et offres exclusives</p>
        <form class="newsletter-form" onsubmit="alert('Merci pour votre inscription !'); return false;">
            <input type="email" placeholder="Votre adresse email" required>
            <button type="submit">S'abonner <i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<div class="features">
    <div class="section-title-modern"><h2>Notre Solution</h2><div class="section-line"></div></div>
    <div class="features-grid">
        <div class="feature-card"><i class="fas fa-calendar-alt"></i><h3>Gestion événements</h3><p>Créez et gérez vos événements facilement</p></div>
        <div class="feature-card"><i class="fas fa-handshake"></i><h3>Sponsors</h3><p>Gérez vos partenaires et sponsors</p></div>
        <div class="feature-card"><i class="fas fa-chart-line"></i><h3>Statistiques</h3><p>Tableau de bord complet et analytiques</p></div>
        <div class="feature-card"><i class="fas fa-mobile-alt"></i><h3>Responsive</h3><p>Accessible sur tous vos appareils</p></div>
    </div>
</div>

<footer><p><i class="fas fa-leaf"></i> Eco Ride © 2025 - Covoiturage Intelligent et Écologique</p></footer>
<script src="validation.js"></script>
</body>
</html>