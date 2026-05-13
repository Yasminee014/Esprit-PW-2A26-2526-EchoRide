<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../../Model/Event.php';
require_once __DIR__ . '/../../Model/Sponsor.php';
include_once __DIR__ . '/partials/navbar.php';
use Model\Event;
use Model\Sponsor;

$id = isset($_GET['id']) ? $_GET['id'] : null;
if(!$id) { header('Location: events.php'); exit(); }

$eventModel = new Event();
$sponsorModel = new Sponsor();
$event = $eventModel->getWithSponsors($id);
$sponsors = $sponsorModel->getByEventId($id);
if(!$event) { header('Location: events.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoRide - <?= htmlspecialchars($event['titre']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../style.css">
<style>
.detail-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
.btn-back { display: inline-flex; align-items: center; gap: 8px; background: rgba(108,117,125,0.3); padding: 0.5rem 1rem; border-radius: 30px; text-decoration: none; color: white; margin-bottom: 1rem; transition: all 0.3s; }
.btn-back:hover { background: rgba(108,117,125,0.5); }
.detail-card { background: rgba(255,255,255,0.05); border-radius: 24px; padding: 2rem; border: 1px solid rgba(97,179,250,0.2); }
.detail-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(97,179,250,0.2); }
.detail-header h1 { font-size: 1.8rem; color: #61B3FA; }
.detail-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.detail-info div { background: rgba(255,255,255,0.03); padding: 0.8rem; border-radius: 12px; }
.detail-info label { font-size: 0.7rem; color: #A7A9AC; text-transform: uppercase; display: block; }
.detail-info p { font-size: 1rem; font-weight: 600; margin-top: 0.3rem; }
.detail-description { margin: 1.5rem 0; padding: 1rem; background: rgba(10,47,68,0.3); border-radius: 12px; }
.detail-description h3 { color: #61B3FA; margin-bottom: 0.5rem; }
.detail-image { text-align: center; margin: 1rem 0; }
.detail-image img { max-width: 100%; max-height: 300px; border-radius: 15px; }
.detail-sponsors { margin-top: 1.5rem; }
.detail-sponsors h3 { color: #61B3FA; margin-bottom: 1rem; }
.sponsors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; }
.sponsor-card { background: rgba(10,47,68,0.5); padding: 1rem; border-radius: 10px; text-align: center; transition: all 0.3s; }
.sponsor-card:hover { transform: translateY(-3px); background: rgba(97,179,250,0.05); }
.sponsor-card h4 { color: #61B3FA; margin-bottom: 0.3rem; }
.sponsor-level { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; }
.sponsor-level.gold { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
.sponsor-level.silver { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #333; }
.sponsor-level.bronze { background: linear-gradient(135deg, #CD7F32, #B8860B); color: white; }
.btn-participate { width: 100%; background: linear-gradient(135deg, #28a745, #1e7e34); color: white; padding: 0.8rem; border: none; border-radius: 30px; cursor: pointer; margin-top: 1rem; font-size: 1rem; transition: all 0.3s; }
.btn-participate:hover { transform: translateY(-2px); }
.badge-ouvert { background: rgba(39,174,96,0.2); color: #27ae60; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; display: inline-block; }
.badge-complet { background: rgba(241,196,15,0.2); color: #f1c40f; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; display: inline-block; }
.badge-annule { background: rgba(231,76,60,0.2); color: #e74c3c; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; display: inline-block; }
footer { text-align: center; padding: 1.5rem; color: #A7A9AC; border-top: 1px solid rgba(97,179,250,0.1); margin-top: 2rem; }
@media (max-width: 768px) { .detail-info { grid-template-columns: 1fr; } .detail-header h1 { font-size: 1.3rem; } }
</style>
</head>
<body>


<div class="detail-container">
    <a href="events.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
    <div class="detail-card">
        <div class="detail-header">
            <h1><?= htmlspecialchars($event['titre']) ?></h1>
            <?php 
            $statutClass = '';
            switch($event['statut']) {
                case 'ouvert': $statutClass = 'badge-ouvert'; break;
                case 'complet': $statutClass = 'badge-complet'; break;
                case 'annule': $statutClass = 'badge-annule'; break;
                default: $statutClass = 'badge-ouvert';
            }
            ?>
            <span class="<?= $statutClass ?>"><?= $event['statut'] ?></span>
        </div>
        <div class="detail-info">
            <div><label>Type</label><p><?= htmlspecialchars($event['type']) ?></p></div>
            <div><label>Ville</label><p><?= htmlspecialchars($event['ville']) ?></p></div>
            <div><label>Date et heure</label><p><?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></p></div>
            <div><label>Places disponibles</label><p><?= $event['nb_places'] ?> places</p></div>
        </div>
        <?php if(!empty($event['description'])): ?>
        <div class="detail-description"><h3><i class="fas fa-align-left"></i> Description</h3><p><?= nl2br(htmlspecialchars($event['description'])) ?></p></div>
        <?php endif; ?>
        <?php if(!empty($event['image']) && $event['image'] != 'default.jpg'): ?>
        <div class="detail-image"><img src="../../uploads/events/<?= $event['image'] ?>" alt="Image de l'événement"></div>
        <?php endif; ?>
        <?php if(!empty($sponsors)): ?>
        <div class="detail-sponsors">
            <h3><i class="fas fa-handshake"></i> Sponsors de l'événement</h3>
            <div class="sponsors-grid">
                <?php foreach($sponsors as $sponsor): ?>
                <div class="sponsor-card">
                    <h4><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h4>
                    <?php 
                    $type = strtolower(trim($sponsor['type_sponsor'] ?? ''));
                    switch($type) {
                        case 'gold':
                        case 'principal': echo '<span class="sponsor-level gold">🏆 Gold</span>'; break;
                        case 'silver':
                        case 'secondaire': echo '<span class="sponsor-level silver">⭐ Silver</span>'; break;
                        default: echo '<span class="sponsor-level bronze">🤝 Bronze</span>';
                    }
                    ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <button class="btn-participate" onclick="alert('Merci de votre intérêt ! Réservation à venir.')"><i class="fas fa-check-circle"></i> Participer à cet événement</button>
    </div>
</div>

<footer><p><i class="fas fa-leaf"></i> Eco Ride © 2025</p></footer>
<script src="../../validation.js"></script>
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>