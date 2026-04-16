<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/Sponsor.php';

use Model\Sponsor;

$sponsorModel = new Sponsor();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

$sponsors = $sponsorModel->getAllAdmin($search, $sort, $order, $page, $limit);
$totalSponsors = $sponsorModel->countAllAdmin($search);
$totalPages = ceil($totalSponsors / $limit);

// Export Excel (reste identique)
if(isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="sponsors_'.date('Y-m-d').'.xls"');
    echo "<table border='1'> hilab<th>ID</th><th>Entreprise</th><th>Montant</th><th>Type</th><th>Statut</th><th>Événement</th></tr>";
    foreach($sponsors as $s) {
        echo "<tr><td>{$s['id']}</td><td>{$s['nom_entreprise']}</td><td>{$s['montant_sponsoring']}</td><td>{$s['type_sponsor']}</td><td>{$s['statut']}</td><td>{$s['event_titre']}</td></tr>";
    }
    echo "</table>";
    exit();
}

// Suppression
if(isset($_GET['delete'])) {
    $sponsorModel->delete($_GET['delete']);
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
<title>Eco Ride - Gestion Sponsors</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link rel="stylesheet" href="../../../style.css">
<style>
@media print {
    .no-print { display: none; }
    .print-only { display: block; }
}
</style>
</head>
<body>

<nav class="navbar no-print">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE - ADMIN</h2></div>
<ul class="nav-links">
<li><a href="../dashboard.php">Dashboard</a></li>
<li><a href="../events/list.php">Événements</a></li>
<li><a href="list.php" class="active">Sponsors</a></li>
<li><a href="../../../index.php">Voir le site</a></li>
</ul>
</nav>

<div class="container no-print">
<div class="card">
<div class="card-header">
<h2><i class="fas fa-handshake"></i> Gestion des sponsors</h2>
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
        <option value="?sort=nom_entreprise&order=ASC&search=<?= urlencode($search) ?>" <?= ($sort=='nom_entreprise' && $order=='ASC') ? 'selected' : '' ?>>Nom (A→Z)</option>
        <option value="?sort=nom_entreprise&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='nom_entreprise' && $order=='DESC') ? 'selected' : '' ?>>Nom (Z→A)</option>
        <option value="?sort=montant_sponsoring&order=DESC&search=<?= urlencode($search) ?>" <?= ($sort=='montant_sponsoring' && $order=='DESC') ? 'selected' : '' ?>>Montant (plus élevé)</option>
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
if($_GET['success'] == 'added') echo '✓ Sponsor ajouté avec succès';
elseif($_GET['success'] == 'updated') echo '✓ Sponsor modifié avec succès';
elseif($_GET['success'] == 'deleted') echo '✓ Sponsor supprimé avec succès';
?>
</div>
<?php endif; ?>
</div>
</div>

<!-- CONTENU POUR LE PDF -->
<div id="pdf-content" style="padding:20px; background:white; color:black; display:none;">
    <h2 style="text-align:center; color:#0F4C5C;">Liste des sponsors</h2>
    <p style="text-align:center;">Date d'export : <?= date('d/m/Y H:i') ?></p>
    <table border="1" cellpadding="8" style="border-collapse:collapse;width:100%;">
        <thead style="background:#0F4C5C; color:white;">
            <tr><th>ID</th><th>Entreprise</th><th>Montant (DT)</th><th>Type</th><th>Statut</th><th>Événement</th></tr>
        </thead>
        <tbody>
            <?php foreach($sponsors as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= $s['nom_entreprise'] ?></td>
                <td><?= number_format($s['montant_sponsoring'], 0, ',', ' ') ?> DT</td>
                <td><?= $s['type_sponsor'] ?? '-' ?></td>
                <td><?= $s['statut'] ?></td>
                <td><?= $s['event_titre'] ?? 'Non assigné' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- TABLEAU VISIBLE -->
<div class="container">
<div class="card">
<?php if(empty($sponsors)): ?>
    <p style="text-align:center;padding:2rem;">Aucun sponsor trouvé</p>
<?php else: ?>
<div class="table-wrapper">
<table>
<thead>
<tr>
    <th><a href="?sort=id&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">ID <?= $sort=='id' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th><a href="?sort=nom_entreprise&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Entreprise <?= $sort=='nom_entreprise' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th><a href="?sort=montant_sponsoring&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Montant <?= $sort=='montant_sponsoring' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th>Type</th>
    <th><a href="?sort=statut&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>">Statut <?= $sort=='statut' ? ($order=='ASC' ? '↑' : '↓') : '' ?></a></th>
    <th>Événement</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($sponsors as $s): ?>
<tr>
    <td><?= $s['id'] ?></td>
    <td><?= htmlspecialchars($s['nom_entreprise']) ?></td>
    <td><?= number_format($s['montant_sponsoring'], 0, ',', ' ') ?> DT</td>
    <td><?= htmlspecialchars($s['type_sponsor'] ?? '-') ?></td>
    <td><?= $s['statut'] ?></td>
    <td><?= htmlspecialchars($s['event_titre'] ?? 'Non assigné') ?></td>
    <td>
        <a href="form.php?id=<?= $s['id'] ?>" class="btn-edit">Modifier</a>
        <a href="list.php?delete=<?= $s['id'] ?>" class="btn-delete" onclick="return confirmDelete()">Supprimer</a>
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

<footer class="no-print">
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
        filename: 'sponsors_<?= date('Y-m-d') ?>.pdf',
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