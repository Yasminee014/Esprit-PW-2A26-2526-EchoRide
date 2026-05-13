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
.hero {
    min-height: 40vh;
    display:flex; align-items:center; justify-content:center;
    text-align:left; padding:2rem;
    margin: 6rem auto 2rem auto;
    width: 95%;
    max-width: 1200px;
    border-radius: 20px;
    background-color: #0F3B6E;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
.hero-content {
    width: 100%;
    max-width: 1200px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}
.hero-text {
    flex: 1;
}
.hero-logo {
    flex: 1;
    display: flex;
    justify-content: flex-end;
    animation: float 3s ease-in-out infinite;
}
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}
.hero h1 { font-size: 2.5rem; margin-bottom: 1rem; color: #fff; }
.hero .highlight { color: #61B3FA; }
.hero p { color: #E2E8F0; font-size: 1.1rem; margin-bottom: 2rem; }
.hero-stats { display: flex; gap: 2rem; margin-top: 1.5rem; flex-wrap: wrap; }
.hero-stat-item { display: flex; align-items: center; gap: 0.5rem; color: #E2E8F0; font-size: 0.85rem; }
.hero-stat-item i { color: #61B3FA; font-size: 0.9rem; }
.hero-stat-item .stat-number { font-weight: 700; color: #fff; font-size: 0.9rem; }
.hero-stat-item .stat-label { color: #E2E8F0; }
.hero-logo img {
    width: 300px;
    height: auto;
    object-fit: contain;
    background: transparent;
    filter: drop-shadow(0 4px 32px rgba(25,118,210,0.55));
}
.stats-banner-modern { display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap; margin: 2rem auto 4rem; position: relative; z-index: 2; }
.stat-item-modern { text-align: center; min-width: 150px; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.stat-item-modern i { color: #61B3FA; font-size: 1.5rem; }
.stat-number { font-size: 2rem; font-weight: 800; color: #0F3B6E; }
.stat-label { font-size: 0.8rem; color: #61B3FA; }
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
.site-footer {
    background: #0F3B6E;
    color: #ffffff;
    padding: 2rem 0.5rem 1rem;
    margin: 4rem auto 0;
    max-width: 1200px;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 20px;
    box-shadow: 0 -10px 40px rgba(0,0,0,0.05);
    font-family: 'Inter', sans-serif;
    text-align: left;
}
.footer-container {
    max-width: 100%;
    padding: 0 1rem;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr;
    gap: 3rem;
}
.footer-brand {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    gap: 1.5rem;
}
.footer-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
}
.footer-logo svg {
    width: 32px;
    height: 32px;
}
.footer-desc {
    color: #B0C4DE;
    line-height: 1.6;
    font-size: 0.95rem;
    margin: 0;
}
.social-icons {
    display: flex;
    gap: 1rem;
}
.social-icons a {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    text-decoration: none;
}
.social-icons a:hover {
    background: #61B3FA;
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(97, 179, 250, 0.4);
}
.footer-col h3 {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 0.5rem;
}
.footer-col h3::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 3px;
    background: #61B3FA;
    border-radius: 2px;
}
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}
.footer-links li a {
    color: #B0C4DE;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.footer-links li a::before {
    content: '\f105';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 0.8rem;
    color: #61B3FA;
    transition: transform 0.3s ease;
}
.footer-links li a:hover {
    color: #61B3FA;
    transform: translateX(5px);
}
.footer-links li a:hover::before {
    transform: translateX(3px);
}
.newsletter-text {
    color: #B0C4DE;
    font-size: 0.95rem;
    margin-bottom: 1rem;
    line-height: 1.5;
}
.newsletter-form {
    display: flex;
    background: rgba(255,255,255,0.1);
    border-radius: 30px;
    overflow: hidden;
    padding: 0.3rem;
    border: 1px solid rgba(255,255,255,0.05);
}
.newsletter-form input {
    flex: 1;
    background: transparent;
    border: none;
    padding: 0.8rem 1.2rem;
    color: #fff;
    outline: none;
    width: 100%;
}
.newsletter-form input::placeholder {
    color: rgba(255,255,255,0.5);
}
.newsletter-form button {
    background: #61B3FA;
    color: #fff;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.newsletter-form button:hover {
    background: #fff;
    color: #0F3B6E;
    transform: scale(1.05);
}
.footer-contact-bar {
    width: 100%;
    background: rgba(255,255,255,0.05);
    padding: 1rem 0;
    margin-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
}
.footer-contact-bar div {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 3rem;
    font-size: 1rem;
    color: #fff;
    text-align: left;
}
.footer-contact-bar span {
    white-space: nowrap;
}
.footer-contact-bar i {
    color: #61B3FA;
    margin-right: 10px;
    font-size: 1.2rem;
}
.footer-bottom {
    max-width: 100%;
    margin: 2rem 1rem 0;
    padding-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: flex-start;
    align-items: center;
    color: #B0C4DE;
    font-size: 0.9rem;
    flex-wrap: wrap;
    gap: 1rem;
    text-align: left;
}
.footer-bottom-links {
    display: flex;
    gap: 1.5rem;
}
.footer-bottom-links a {
    color: #B0C4DE;
    text-decoration: none;
    transition: color 0.3s ease;
}
.footer-bottom-links a:hover {
    color: #61B3FA;
}
.love-text {
    text-align: center;
    width: 100%;
    margin-top: 1rem;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
}
@media (max-width: 992px) {
    .footer-container {
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        text-align: center;
    }
    .footer-logo, .social-icons, .newsletter-form {
        justify-content: center;
    }
    .footer-col h3::after {
        left: 50%;
        transform: translateX(-50%);
    }
    .footer-links li a {
        justify-content: center;
    }
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
}
@media (max-width: 768px) { .events-grid-modern { grid-template-columns: 1fr; } .hero h1 { font-size: 1.8rem; } .calendar-icon-link { position: static; transform: none; margin-top: 1rem; display: inline-flex; } .section-title-modern { display: flex; flex-direction: column; align-items: center; gap: 1rem; } }
</style>
</head>
<body>
<?php include_once __DIR__ . '/View/frontoffice/partials/navbar.php'; ?>

<section class="hero" id="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>Bienvenue sur <span class="highlight">Eco Ride</span></h1>
            <p>La plateforme de covoiturage intelligente et écologique</p>
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="stat-number">8</span>
                    <span class="stat-label">événements</span>
                </div>
                <div class="hero-stat-item">
                    <i class="fas fa-handshake"></i>
                    <span class="stat-number">8</span>
                    <span class="stat-label">sponsors</span>
                </div>
                <div class="hero-stat-item">
                    <i class="fas fa-users"></i>
                    <span class="stat-number">+5000</span>
                    <span class="stat-label">participants</span>
                </div>
            </div>
        </div>
        <div class="hero-logo">
            <img src="<?= BASE_URL ?>assets/images/photo.png" alt="EcoRide Logo" style="width:300px;height:auto;object-fit:contain;background:transparent;filter:drop-shadow(0 4px 32px rgba(25,118,210,0.55));">
        </div>
    </div>
</section>

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

<div class="features">
    <div class="section-title-modern"><h2>Comment ça marche</h2><div class="section-line"></div></div>
    <div class="features-grid">
        <div class="feature-card"><i class="fas fa-user-plus"></i><h3>1. Créer un compte</h3><p>Inscrivez-vous et choisissez votre rôle dans la communauté.</p></div>
        <div class="feature-card"><i class="fas fa-user-cog"></i><h3>2. Configurer le profil</h3><p>Complétez votre profil et vérifiez votre compte.</p></div>
        <div class="feature-card"><i class="fas fa-hands-helping"></i><h3>3. Commencer à contribuer</h3><p>Collectez des déchets, effectuez des achats ou faites des dons.</p></div>
        <div class="feature-card"><i class="fas fa-leaf"></i><h3>4. Avoir un impact</h3><p>Suivez vos contributions environnementales et votre progression.</p></div>
    </div>
</div>

<div class="features">
    <div class="section-title-modern"><h2>Notre Solution</h2><div class="section-line"></div></div>
    <div class="features-grid">
        <div class="feature-card"><i class="fas fa-brain"></i><h3>Covoiturage Intelligent</h3><p>Optimisation des trajets</p></div>
        <div class="feature-card"><i class="fas fa-shield-alt"></i><h3>Pratique et Sécurisée</h3><p>Plateforme web sécurisée</p></div>
        <div class="feature-card"><i class="fas fa-chart-line"></i><h3>Plus économique</h3><p>Réduction des coûts</p></div>
        <div class="feature-card"><i class="fas fa-mobile-alt"></i><h3>Application simple</h3><p>Interface intuitive</p></div>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="footer-logo">
                <span>Eco Ride</span>
            </div>
            <p class="footer-desc">Voyagez facilement, économisez de l'argent et réduisez votre impact écologique grâce au covoiturage.</p>
            <div class="social-icons">
                <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" title="X/Twitter"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
        <div class="footer-col">
            <h3>Liens Rapides</h3>
            <ul class="footer-links">
                <li><a href="#">Accueil</a></li>
                <li><a href="#">Rechercher un trajet</a></li>
                <li><a href="#">Publier un trajet</a></li>
                <li><a href="#">Mes réservations</a></li>
                <li><a href="#">À propos</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Services</h3>
            <ul class="footer-links">
                <li><a href="#">Covoiturage quotidien</a></li>
                <li><a href="#">Trajets longue distance</a></li>
                <li><a href="#">Réservation instantanée</a></li>
                <li><a href="#">Paiement sécurisé</a></li>
                <li><a href="#">Support 24/7</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Restez connectés</h3>
            <p class="newsletter-text">Recevez les nouveautés, offres et conseils de voyage.</p>
            <form class="newsletter-form" onsubmit="event.preventDefault();">
                <input type="email" placeholder="Votre adresse email..." required>
                <button type="submit" title="S'abonner"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
    <div class="footer-contact-bar">
        <div>
            <span><i class="fas fa-envelope"></i> ecoride@gmail.com</span>
            <span><i class="fas fa-phone-alt"></i> 72100411</span>
        </div>
    </div>
    <div class="footer-bottom">
        <div>© 2026 Tous droits réservés.</div>
        <div class="footer-bottom-links">
            <a href="#">Politique de confidentialité</a>
            <span>|</span>
            <a href="#">Conditions d'utilisation</a>
        </div>
        <div class="love-text">Conçu avec ❤️ pour une mobilité plus écologique.</div>
    </div>
</footer>
<script src="validation.js"></script>
</body>
</html>