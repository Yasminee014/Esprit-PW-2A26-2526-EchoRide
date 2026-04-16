<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/Sponsor.php';
require_once __DIR__ . '/../../../Model/Event.php';

use Model\Sponsor;
use Model\Event;

$sponsorModel = new Sponsor();
$eventModel = new Event();
$sponsor = null;
$events = $eventModel->getAll();
$error = '';

if($events === null) $events = [];

$uploadDir = __DIR__ . '/../../../uploads/sponsors/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if(isset($_GET['id'])) {
    $sponsor = $sponsorModel->getById($_GET['id']);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logoName = $sponsor['logo'] ?? null;
    
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $filename = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $logoName = time() . '_' . uniqid() . '.' . $ext;
            $destination = $uploadDir . $logoName;
            move_uploaded_file($_FILES['logo']['tmp_name'], $destination);
            
            if(isset($sponsor['logo']) && $sponsor['logo'] && file_exists($uploadDir . $sponsor['logo'])) {
                unlink($uploadDir . $sponsor['logo']);
            }
        } else {
            $error = "Format de logo non autorisé (JPG, PNG, GIF, SVG)";
        }
    }
    
    if(empty($error)) {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $sponsorModel->update($_POST['id'], $_POST, $logoName);
            header('Location: list.php?success=updated');
        } else {
            $sponsorModel->add($_POST, $logoName);
            header('Location: list.php?success=added');
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - <?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?> sponsor</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../../style.css">
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE - ADMIN</h2></div>
<ul class="nav-links">
<li><a href="../dashboard.php">Dashboard</a></li>
<li><a href="../events/list.php">Événements</a></li>
<li><a href="list.php">Sponsors</a></li>
</ul>
</nav>

<div class="container">
<a href="list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>

<div class="card">
<div class="card-header">
<h2><i class="fas <?= isset($sponsor) ? 'fa-edit' : 'fa-plus' ?>"></i> <?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?> un sponsor</h2>
</div>

<?php if($error): ?>
<div class="alert-error" style="background:rgba(220,53,69,0.2);color:#dc3545;padding:1rem;border-radius:10px;margin-bottom:1rem;"><?= $error ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" onsubmit="return validateSponsorForm()">
    <?php if(isset($sponsor)): ?>
        <input type="hidden" name="id" value="<?= $sponsor['id'] ?>">
    <?php endif; ?>
    
    <div class="form-group">
        <label>Nom entreprise *</label>
        <input type="text" name="nom_entreprise" id="nom_entreprise" value="<?= htmlspecialchars($sponsor['nom_entreprise'] ?? '') ?>">
        <div id="nomError" class="error"></div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label>Montant sponsoring (DT) *</label>
            <input type="number" name="montant_sponsoring" id="montant_sponsoring" step="0.01" value="<?= $sponsor['montant_sponsoring'] ?? '' ?>">
            <div id="montantError" class="error"></div>
        </div>
        
        <div class="form-group">
            <label>Type sponsor</label>
            <select name="type_sponsor">
                <option value="">Sélectionner</option>
                <option value="principal" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'principal') ? 'selected' : '' ?>>Principal</option>
                <option value="secondaire" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'secondaire') ? 'selected' : '' ?>>Secondaire</option>
                <option value="partenaire" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'partenaire') ? 'selected' : '' ?>>Partenaire</option>
            </select>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label>Statut</label>
            <select name="statut">
                <option value="en_attente" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                <option value="confirme" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'confirme') ? 'selected' : '' ?>>Confirmé</option>
                <option value="refuse" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'refuse') ? 'selected' : '' ?>>Refusé</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Événement associé</label>
            <select name="evenement_id">
                <option value="">Aucun</option>
                <?php foreach($events as $e): ?>
                <option value="<?= $e['id'] ?>" <?= (isset($sponsor['evenement_id']) && $sponsor['evenement_id'] == $e['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['titre']) ?> - <?= date('d/m/Y', strtotime($e['date_evenement'])) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label>Logo</label>
        <input type="file" name="logo" accept="image/*" id="logoInput">
        <div class="preview-logo" id="logoPreview">
            <?php if(isset($sponsor['logo']) && $sponsor['logo']): ?>
                <img src="../../../uploads/sponsors/<?= $sponsor['logo'] ?>" alt="Logo">
            <?php endif; ?>
        </div>
    </div>
    
    <button type="submit" class="btn-submit"><?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?></button>
</form>
</div>
</div>

<footer><p>Eco Ride © 2025</p></footer>

<script src="../../../validation.js"></script>
<script>
document.getElementById('logoInput').addEventListener('change', function(e) {
    const preview = document.getElementById('logoPreview');
    preview.innerHTML = '';
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.maxWidth = '100px';
            img.style.borderRadius = '10px';
            preview.appendChild(img);
        }
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>