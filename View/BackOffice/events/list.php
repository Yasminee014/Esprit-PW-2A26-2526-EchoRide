<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/Event.php';

use Model\Event;

$eventModel = new Event();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

$events = $eventModel->getAllAdmin($search, $sort, $order, $page, $limit);
$totalEvents = $eventModel->countAllAdmin($search);
$totalPages = ceil($totalEvents / $limit);

// Export Excel
if(isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="evenements_'.date('Y-m-d').'.xls"');
    echo "<table border='1'> franch<th>ID</th><th>Titre</th><th>Type</th><th>Ville</th><th>Date</th><th>Places</th><th>Statut</th></tr>";
    foreach($events as $e) {
        echo "<tr><td>{$e['id']}</td><td>{$e['titre']}</td><td>{$e['type']}</td><td>{$e['ville']}</td><td>".date('d/m/Y H:i', strtotime($e['date_evenement']))."</td><td>{$e['nb_places']}</td><td>{$e['statut']}</td></tr>";
    }
    echo "</table>";
    exit();
}

// Suppression
if(isset($_GET['delete'])) {
    $eventModel->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit();
}

$nextOrder = ($order === 'ASC') ? 'DESC' : 'ASC';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - Gestion Événements</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link rel="stylesheet" href="../../../style.css">
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE - ADMIN</h2></div>
<ul class="nav-links">
<li><a href="../dashboard.php">Dashboard</a></li>
<li><a href="list.php" class="active">Événements</a></li>
<li><a href="../sponsors/list.php">Sponsors</a></li>
<li><a href="../../../index.php">Voir le site</a></li>
</ul>
</nav>

<div class="container">
<div class="card">
<div class="card-header">
<h2><i class="fas fa-calendar-alt"></i> Gestion des événements</h2>
<div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
        <input type="hidden" name="sort" value="<?= $sort ?>">
        <input type="hidden" name="order" value="<?= $order ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <select class="sort-bar" onchange="window.location.href=this.value">
        <option value="?sort=id&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='id' && $order=='DESC') ? 'selected' : '' ?>>ID (plus récent)</option>
        <option value="?sort=id&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='id' && $order=='ASC') ? 'selected' : '' ?>>ID (plus ancien)</option>
        <option value="?sort=titre&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='titre' && $order=='ASC') ? 'selected' : '' ?>>Titre (A→Z)</option>
        <option value="?sort=titre&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='titre' && $order=='DESC') ? 'selected' : '' ?>>Titre (Z→A)</option>
        <option value="?sort=date_evenement&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='date_evenement' && $order=='ASC') ? 'selected' : '' ?>>Date (plus proche)</option>
        <option value="?sort=date_evenement&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='date_evenement' && $order=='DESC') ? 'selected' : '' ?>>Date (plus lointain)</option>
        <option value="?sort=nb_places&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='nb_places' && $order=='DESC') ? 'selected' : '' ?>>Places (plus → moins)</option>
        <option value="?sort=statut&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='statut' && $order=='ASC') ? 'selected' : '' ?>>Statut (A→Z)</option>
    </select>
    <a href="list.php" class="reset-btn"><i class="fas fa-times"></i> Réinitialiser</a>
</div>
</div>

<div class="card-header" style="margin-top:1rem;">
    <div style="display:flex;gap:0.5rem;">
        <a href="?export_excel=1&search=<?= urlencode($search) ?>" class="btn-export-excel"><i class="fas fa-file-excel"></i> Excel</a>
        <button onclick="exportToPDF()" class="btn-export-pdf"><i class="fas fa-file-pdf"></i> PDF</button>
        <a href="form.php" class="btn-primary"><i class="fas fa-plus"></i> Ajouter</a>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="alert-success">
<?php 
if($_GET['success'] == 'added') echo '✓ Événement ajouté avec succès';
elseif($_GET['success'] == 'updated') echo '✓ Événement modifié avec succès';
elseif($_GET['success'] == 'deleted') echo '✓ Événement supprimé avec succès';
?>
</div>
<?php endif; ?>
</div>
</div>

<!-- CONTENU POUR LE PDF (caché) -->
<div id="pdf-content" style="padding:20px; background:white; color:black; display:none;">
    <h2 style="text-align:center; color:#0F4C5C;">Liste des événements</h2>
    <p style="text-align:center;">Date d'export : <?= date('d/m/Y H:i') ?></p>
    <table border="1" cellpadding="8" style="border-collapse:collapse;width:100%;">
        <thead style="background:#0F4C5C; color:white;">
            <tr><th>ID</th><th>Titre</th><th>Type</th><th>Ville</th><th>Date</th><th>Places</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <?php foreach($events as $e): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['titre']) ?></td>
                <td><?= htmlspecialchars($e['type']) ?></td>
                <td><?= htmlspecialchars($e['ville']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($e['date_evenement'])) ?></td>
                <td><?= $e['nb_places'] ?></td>
                <td><?= $e['statut'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- TABLEAU VISIBLE -->
<div class="container">
<div class="card">
<?php if(empty($events)): ?>
    <p style="text-align:center;padding:2rem;">Aucun événement trouvé</p>
<?php else: ?>
<div class="table-wrapper">
<table>
<thead>
<tr>
    <th><a href="?sort=id&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">ID <?= $sort=='id' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th><a href="?sort=titre&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Titre <?= $sort=='titre' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th>Type</th>
    <th>Ville</th>
    <th><a href="?sort=date_evenement&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Date <?= $sort=='date_evenement' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th><a href="?sort=nb_places&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Places <?= $sort=='nb_places' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th><a href="?sort=statut&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Statut <?= $sort=='statut' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($events as $e): ?>
<tr>
    <td><?= $e['id'] ?></td>
    <td><?= htmlspecialchars($e['titre']) ?></td>
    <td><?= htmlspecialchars($e['type']) ?></td>
    <td><?= htmlspecialchars($e['ville']) ?></td>
    <td><?= date('d/m/Y H:i', strtotime($e['date_evenement'])) ?></td>
    <td><?= $e['nb_places'] ?></td>
    <td><span class="badge"><?= $e['statut'] ?></span></td>
    <td>
        <a href="form.php?id=<?= $e['id'] ?>" class="btn-edit">Modifier</a>
        <a href="list.php?delete=<?= $e['id'] ?>" class="btn-delete" onclick="return confirmDelete()">Supprimer</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
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

<footer>
    <p>Eco Ride © 2025 - Administration</p>
</footer>

<script src="../../../validation.js"></script>
<script>
function confirmDelete() {
    return confirm('Supprimer ?');
}

function exportToPDF() {
    const element = document.getElementById('pdf-content');
    element.style.display = 'block';
    const opt = {
        margin: [10, 10, 10, 10],
        filename: 'evenements_<?= date('Y-m-d') ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save().then(() => {
        element.style.display = 'none';
    });
}
</script>
</body>
</html>