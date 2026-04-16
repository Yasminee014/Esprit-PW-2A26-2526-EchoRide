<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Event.php';

use Model\Event;

$eventModel = new Event();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_evenement';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;

$events = $eventModel->getAll($search, $sort, $order, $page, $limit);
$totalEvents = $eventModel->countAll($search);
$totalPages = ceil($totalEvents / $limit);

$nextOrder = ($order === 'ASC') ? 'DESC' : 'ASC';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Événements</title>
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
<div class="card">
<div class="card-header">
<h2><i class="fas fa-calendar-alt"></i> Tous les événements</h2>
<div class="search-sort-bar" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
        <input type="hidden" name="sort" value="<?= $sort ?>">
        <input type="hidden" name="order" value="<?= $order ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    
    <div class="sort-box">
        <select onchange="window.location.href=this.value">
            <option value="?sort=date_evenement&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='date_evenement' && $order=='ASC') ? 'selected' : '' ?>>Date (plus proche)</option>
            <option value="?sort=date_evenement&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='date_evenement' && $order=='DESC') ? 'selected' : '' ?>>Date (plus lointain)</option>
            <option value="?sort=titre&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='titre' && $order=='ASC') ? 'selected' : '' ?>>Titre (A→Z)</option>
            <option value="?sort=titre&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='titre' && $order=='DESC') ? 'selected' : '' ?>>Titre (Z→A)</option>
            <option value="?sort=ville&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='ville' && $order=='ASC') ? 'selected' : '' ?>>Ville (A→Z)</option>
            <option value="?sort=ville&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='ville' && $order=='DESC') ? 'selected' : '' ?>>Ville (Z→A)</option>
            <option value="?sort=nb_places&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='nb_places' && $order=='DESC') ? 'selected' : '' ?>>Places (plus → moins)</option>
        </select>
    </div>
    
    <?php if($search): ?>
        <a href="events.php" class="reset-btn"><i class="fas fa-times"></i> Réinitialiser</a>
    <?php endif; ?>
</div>
</div>

<?php if(empty($events)): ?>
    <p style="text-align:center;padding:2rem;">Aucun événement trouvé</p>
<?php else: ?>
<div class="events-grid">
    <?php foreach($events as $event): ?>
    <div class="event-card">
        <div class="event-image">
            <img src="../../uploads/events/<?= $event['image'] ?? 'default.jpg' ?>" 
                 alt="<?= htmlspecialchars($event['titre']) ?>"
                 onerror="this.src='../../uploads/events/default.jpg'">
        </div>
        <h3><?= htmlspecialchars($event['titre']) ?></h3>
        
        <!-- DESCRIPTION -->
        <p class="event-description">
            <i class="fas fa-align-left"></i> 
            <?= htmlspecialchars(substr($event['description'] ?? 'Aucune description', 0, 120)) ?>...
        </p>
        
        <p><i class="fas fa-tag"></i> <?= htmlspecialchars($event['type']) ?></p>
        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['ville']) ?></p>
        <p><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($event['date_evenement'])) ?></p>
        <p><i class="fas fa-users"></i> <?= $event['nb_places'] ?> places</p>
        <p><span class="badge"><?= $event['statut'] ?></span></p>
        <a href="events-detail.php?id=<?= $event['id'] ?>" class="btn-primary">Voir détails</a>
    </div>
    <?php endforeach; ?>
</div>

<?php if($totalPages > 1): ?>
<div class="pagination">
    <?php if($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>">« Précédent</a>
    <?php endif; ?>
    <?php for($i = 1; $i <= $totalPages; $i++): ?>
        <?php if($i == $page): ?>
            <span class="active"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&order=<?= $order ?>">Suivant »</a>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
</div>

<footer><p>Eco Ride © 2025</p></footer>

<script src="../../validation.js"></script>
</body>
</html>