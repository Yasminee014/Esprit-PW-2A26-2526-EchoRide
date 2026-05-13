<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Sponsor.php';

use Model\Sponsor;

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sponsorModel = new Sponsor();

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8; // Number of sponsors per page
$offset = ($page - 1) * $limit;

// Get sponsors with pagination
$sponsors = $sponsorModel->getAllWithPagination('', 'nom_entreprise', 'ASC', $page, $limit);

// Get total count for pagination
$totalSponsors = $sponsorModel->countAllWithPagination();
$totalPages = ceil($totalSponsors / $limit);

if($sponsors === null) $sponsors = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoRide - Sponsors</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../style.css">
<style>
.sponsors-page { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
.page-header { text-align: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 2rem; color: #61B3FA; }
.page-header p { color: #A7A9AC; }
.sponsors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
.sponsor-card { background: rgba(255,255,255,0.05); border-radius: 16px; padding: 1.5rem; text-align: center; transition: all 0.3s; border: 1px solid rgba(97,179,250,0.1); }
.sponsor-card:hover { transform: translateY(-5px); border-color: #61B3FA; background: rgba(97,179,250,0.05); }
.sponsor-logo { width: 80px; height: 80px; object-fit: contain; margin: 0 auto 1rem; background: white; border-radius: 16px; padding: 10px; }
.sponsor-icon { width: 80px; height: 80px; background: rgba(97,179,250,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
.sponsor-icon i { font-size: 2.5rem; color: #61B3FA; }
.sponsor-card h3 { font-size: 1.1rem; margin-bottom: 0.5rem; }
.sponsor-level { display: inline-block; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.7rem; }
.sponsor-level.gold { background: linear-gradient(135deg, #FFD700, #FFA500); color: #333; font-weight: bold; }
.sponsor-level.silver { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #333; }
.sponsor-level.bronze { background: linear-gradient(135deg, #CD7F32, #B8860B); color: white; }
.sponsor-event { font-size: 0.75rem; color: #61B3FA; margin: 0.5rem 0; }
.btn-discover { display: inline-block; background: linear-gradient(90deg, #1976D2, #0F3B6E); padding: 0.5rem 1rem; border-radius: 25px; text-decoration: none; color: white; font-size: 0.8rem; margin-top: 0.5rem; transition: all 0.3s; }
.btn-discover:hover { transform: translateX(5px); background: linear-gradient(90deg, #61B3FA, #1976D2); }
.empty-state { text-align: center; padding: 4rem; background: rgba(255,255,255,0.05); border-radius: 20px; }
.empty-state i { font-size: 4rem; opacity: 0.3; margin-bottom: 1rem; }
.pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; flex-wrap: wrap; }
.pagination a, .pagination span { padding: 0.5rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.08); text-decoration: none; color: white; }
.pagination .active { background: #1976D2; }
footer { text-align: center; padding: 1.5rem; color: #A7A9AC; border-top: 1px solid rgba(97,179,250,0.1); margin-top: 2rem; }
@media (max-width: 768px) { .sponsors-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="sponsors-page">
    <div class="page-header">
        <h1><i class="fas fa-handshake"></i> Nos Sponsors</h1>
        <p>Découvrez les entreprises qui nous soutiennent et participent à nos événements</p>
    </div>

    <?php if(empty($sponsors)): ?>
        <div class="empty-state"><i class="fas fa-handshake"></i><h3>Aucun sponsor pour le moment</h3><p>Revenez plus tard pour découvrir nos partenaires</p></div>
    <?php else: ?>
    <div class="sponsors-grid">
        <?php foreach($sponsors as $sponsor): ?>
        <div class="sponsor-card">
            <?php if(!empty($sponsor['logo'])): ?>
                <img src="../../uploads/sponsors/<?= $sponsor['logo'] ?>" class="sponsor-logo" alt="<?= htmlspecialchars($sponsor['nom_entreprise']) ?>">
            <?php else: ?>
                <div class="sponsor-icon"><i class="fas fa-building"></i></div>
            <?php endif; ?>
            <h3><?= htmlspecialchars($sponsor['nom_entreprise']) ?></h3>
            <?php 
            $type = strtolower(trim($sponsor['type_sponsor'] ?? ''));
            switch($type) {
                case 'gold':
                case 'principal': echo '<div class="sponsor-level gold">🏆 Sponsor Gold</div>'; break;
                case 'silver':
                case 'secondaire': echo '<div class="sponsor-level silver">⭐ Sponsor Silver</div>'; break;
                default: echo '<div class="sponsor-level bronze">🤝 Sponsor Bronze</div>';
            }
            ?>
            <?php if(!empty($sponsor['event_titre'])): ?>
                <div class="sponsor-event"><i class="fas fa-calendar-alt"></i> Partenaire de : <?= htmlspecialchars($sponsor['event_titre']) ?></div>
            <?php endif; ?>
            <a href="sponsor-detail.php?id=<?= $sponsor['id'] ?>" class="btn-discover">Voir détails <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?><a href="?page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i> Précédent</a><?php endif; ?>
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php if($i == $page): ?><span class="active"><?= $i ?></span><?php else: ?><a href="?page=<?= $i ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if($page < $totalPages): ?><a href="?page=<?= $page+1 ?>">Suivant <i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<footer><p><i class="fas fa-leaf"></i> Eco Ride © 2025</p></footer>
<script src="../../validation.js"></script>
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>