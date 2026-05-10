<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/Event.php';
include_once __DIR__ . '/navbar.php';
use Model\Event;

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$eventModel = new Event();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_evenement';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;

$events = $eventModel->getAllWithPagination($search, $sort, $order, $page, $limit);
$totalEvents = $eventModel->countAllWithPagination($search);
$totalPages = ceil($totalEvents / $limit);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoRide - Événements</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../style.css">
</head>
<body>
<style>
.page-header { background: linear-gradient(135deg, #0A1628, #0D1F3A); padding: 3rem 2rem; text-align: center; margin-bottom: 2rem; }
.page-header h1 { font-size: 2rem; color: #61B3FA; }
.page-header p { color: #A7A9AC; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
.search-filter-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 60px; }
.search-form { display: flex; gap: 0.5rem; flex: 1; }
.search-form input { flex: 1; padding: 0.8rem 1rem; border-radius: 40px; border: none; background: rgba(255,255,255,0.1); color: white; }
.search-form button { background: #1976D2; border: none; padding: 0 1.5rem; border-radius: 40px; color: white; cursor: pointer; }
.sort-select { padding: 0.8rem 1rem; border-radius: 40px; background: rgba(255,255,255,0.1); color: white; border: none; }
.reset-btn { background: rgba(108,117,125,0.3); padding: 0.8rem 1.2rem; border-radius: 40px; text-decoration: none; color: white; }
.page-wrapper { min-height: 100vh; display: flex; flex-direction: column; }
.page-content { flex: 1; }
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
.pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; flex-wrap: wrap; }
.pagination a, .pagination span { padding: 0.5rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.08); text-decoration: none; color: white; }
.pagination .active { background: #1976D2; }
.empty-state { text-align: center; padding: 3rem; background: rgba(255,255,255,0.05); border-radius: 20px; }
.empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }
footer { text-align: center; padding: 1.5rem; color: #A7A9AC; border-top: 1px solid rgba(97,179,250,0.1); margin-top: 2rem; }
@media (max-width: 768px) { .events-grid-modern { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<?php include_once __DIR__ . '/navbar.php'; ?>

<div class="page-header">
    <h1><i class="fas fa-calendar-alt"></i> Tous les événements</h1>
    <p>Découvrez les événements à venir et réservez votre place</p>
</div>

<div class="container">
    <div class="search-filter-bar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Rechercher un événement..." value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="sort" value="<?= $sort ?>">
            <input type="hidden" name="order" value="<?= $order ?>">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
        <select class="sort-select" onchange="window.location.href=this.value">
            <option value="?sort=date_evenement&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='date_evenement' && $order=='ASC') ? 'selected' : '' ?>>📅 Date (plus proche)</option>
            <option value="?sort=date_evenement&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='date_evenement' && $order=='DESC') ? 'selected' : '' ?>>📅 Date (plus lointain)</option>
            <option value="?sort=titre&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='titre' && $order=='ASC') ? 'selected' : '' ?>>🔤 Titre (A→Z)</option>
            <option value="?sort=titre&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='titre' && $order=='DESC') ? 'selected' : '' ?>>🔤 Titre (Z→A)</option>
            <option value="?sort=ville&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='ville' && $order=='ASC') ? 'selected' : '' ?>>📍 Ville (A→Z)</option>
        </select>
        <?php if($search): ?><a href="events.php" class="reset-btn"><i class="fas fa-times"></i> Réinitialiser</a><?php endif; ?>
    </div>

    <?php if(empty($events)): ?>
        <div class="empty-state"><i class="fas fa-search"></i><h3>Aucun événement trouvé</h3><p>Essayez une autre recherche</p><a href="events.php" class="btn-modern">Voir tous</a></div>
    <?php else: ?>
    <div class="events-grid-modern">
        <?php foreach($events as $event): ?>
        <div class="event-card-modern">
            <div class="event-image-modern"><img src="../../uploads/events/<?= $event['image'] ?? 'default.jpg' ?>" alt="..."><span class="event-badge"><?= date('d M', strtotime($event['date_evenement'])) ?></span><span class="event-category"><?= htmlspecialchars($event['type']) ?></span></div>
            <div class="event-content-modern"><h3 class="event-title-modern"><?= htmlspecialchars($event['titre']) ?></h3><div class="event-meta-modern"><span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['ville']) ?></span><span><i class="fas fa-clock"></i> <?= date('H:i', strtotime($event['date_evenement'])) ?></span></div><p class="event-desc-modern"><?= htmlspecialchars(substr($event['description'] ?? 'Découvrez cet événement', 0, 100)) ?>...</p><div class="event-footer-modern"><span class="event-price-modern"><?= (isset($event['prix']) && $event['prix'] > 0) ? $event['prix'] . ' DT' : 'Gratuit' ?></span><a href="events-detail.php?id=<?= $event['id'] ?>" class="btn-modern">Découvrir <i class="fas fa-arrow-right"></i></a></div></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?><a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>"><i class="fas fa-chevron-left"></i> Précédent</a><?php endif; ?>
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <?php if($i == $page): ?><span class="active"><?= $i ?></span><?php else: ?><a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if($page < $totalPages): ?><a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>">Suivant <i class="fas fa-chevron-right"></i></a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<footer><p><i class="fas fa-leaf"></i> Eco Ride © 2025</p></footer>
<script src="../../validation.js"></script>
<?php require_once __DIR__ . '/chatbot_widget.php'; ?>
</body>
</html>