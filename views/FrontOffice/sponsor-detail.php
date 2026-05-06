<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Sponsor.php';
require_once __DIR__ . '/../../models/Event.php';

use Model\Sponsor;
use Model\Event;

$id = isset($_GET['id']) ? $_GET['id'] : null;
if(!$id) { header('Location: sponsors.php'); exit(); }

$sponsorModel = new Sponsor();
$eventModel = new Event();
$sponsor = $sponsorModel->getById($id);
if(!$sponsor) { header('Location: sponsors.php'); exit(); }
$event = null;
if(!empty($sponsor['evenement_id'])) $event = $eventModel->getById($sponsor['evenement_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoRide - <?= htmlspecialchars($sponsor['nom_entreprise']) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../style.css">
<style>
.detail-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
.btn-back { display: inline-flex; align-items: center; gap: 8px; background: rgba(108,117,125,0.3); padding: 0.5rem 1rem; border-radius: 30px; text-decoration: none; color: white; margin-bottom: 1rem; transition: all 0.3s; }
.btn-back:hover { background: rgba(108,117,125,0.5); }
.detail-card { background: rgba(255,255,255,0.05); border-radius: 24px; padding: 2rem; border: 1px solid rgba(97,179,250,0.2); }
.detail-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(97,179,250,0.2); }
.detail-header h1 { font-size: 1.8rem; color: #61B3FA; }
.detail-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.info-box { background: rgba(255,255,255,0.03); border-radius: 16px; padding: 1rem; }
.info-box .label { font-size: 0.7rem; color: #A7A9AC; text-transform: uppercase; margin-bottom: 0.3rem; }
.info-box .value { font-size: 1rem; font-weight: 600; color: white; }
.info-box.full-width { grid-column: span 2; }
.detail-logo { text-align: center; margin: 1rem 0; }
.detail-logo img { max-width: 150px; max-height: 150px; border-radius: 20px; background: white; padding: 10px; }
.sponsor-level { display: inline-block; padding: 0.3rem 1rem; border-radius: 30px; font-size: 0.8rem; font-weight: bold; }
.sponsor-level.gold { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; }
.sponsor-level.silver { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #333; }
.sponsor-level.bronze { background: linear-gradient(135deg, #CD7F32, #B8860B); color: white; }
.event-link { color: #61B3FA; text-decoration: none; }
.event-link:hover { text-decoration: underline; }
footer { text-align: center; padding: 1.5rem; color: #A7A9AC; border-top: 1px solid rgba(97,179,250,0.1); margin-top: 2rem; }
@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } .info-box.full-width { grid-column: span 1; } .detail-header h1 { font-size: 1.3rem; } }
</style>
</head>
<body>

<?php include_once __DIR__ . '/navbar.php'; ?>

<div class="detail-container">
    <a href="sponsors.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour aux sponsors</a>
    
    <div class="detail-card">
        <div class="detail-header">
            <h1><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h1>
            <?php 
            $type = strtolower(trim($sponsor['type_sponsor'] ?? ''));
            switch($type) {
                case 'gold':
                case 'principal': echo '<span class="sponsor-level gold">🏆 Sponsor Gold</span>'; break;
                case 'silver':
                case 'secondaire': echo '<span class="sponsor-level silver">⭐ Sponsor Silver</span>'; break;
                default: echo '<span class="sponsor-level bronze">🤝 Sponsor Bronze</span>';
            }
            ?>
        </div>
        
        <div class="detail-grid">
            <div class="info-box"><div class="label">ID SPONSOR</div><div class="value">#<?= $sponsor['id'] ?></div></div>
            <div class="info-box"><div class="label">STATUT</div><div class="value"><?php if($sponsor['statut'] == 'confirme'): ?>✅ Confirmé<?php elseif($sponsor['statut'] == 'en_attente'): ?>⏳ En attente<?php else: ?>❌ Refusé<?php endif; ?></div></div>
            <div class="info-box"><div class="label">MONTANT SPONSORING</div><div class="value"><?= number_format($sponsor['montant_sponsoring'], 0, ',', ' ') ?> DT</div></div>
            <div class="info-box"><div class="label">DATE D'AJOUT</div><div class="value"><?= date('d/m/Y', strtotime($sponsor['created_at'] ?? 'now')) ?></div></div>
            
            <?php if(!empty($event)): ?>
            <div class="info-box full-width"><div class="label">ÉVÉNEMENT ASSOCIÉ</div><div class="value"><a href="events-detail.php?id=<?= $event['id'] ?>" class="event-link"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($event['titre']) ?> (<?= date('d/m/Y', strtotime($event['date_evenement'])) ?>)</a></div></div>
            <?php endif; ?>
            
            <?php if(!empty($sponsor['description'])): ?>
            <div class="info-box full-width"><div class="label">DESCRIPTION</div><div class="value"><?= nl2br(htmlspecialchars($sponsor['description'])) ?></div></div>
            <?php endif; ?>
        </div>
        
        <?php if(!empty($sponsor['logo'])): ?>
        <div class="detail-logo"><img src="../../uploads/sponsors/<?= $sponsor['logo'] ?>" alt="Logo <?= htmlspecialchars($sponsor['nom_entreprise']) ?>"></div>
        <?php endif; ?>
    </div>
</div>

<footer><p><i class="fas fa-leaf"></i> Eco Ride © 2025</p></footer>
<script src="../../validation.js"></script>
</body>
</html>