<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Event.php';

use Model\Event;

$eventModel = new Event();
$event = null;
$error = '';

$uploadDir = __DIR__ . '/../../../uploads/events/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if(isset($_GET['id'])) { $event = $eventModel->getById($_GET['id']); }

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageName = $event['image'] ?? 'default.jpg';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $imageName = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            if(isset($event['image']) && $event['image'] != 'default.jpg' && file_exists($uploadDir . $event['image'])) unlink($uploadDir . $event['image']);
        } else $error = "Format d'image non autorisé";
    }
    if(empty($error)) {
        if(isset($_POST['id']) && !empty($_POST['id'])) $eventModel->update($_POST['id'], $_POST, $imageName);
        else $eventModel->add($_POST, $imageName);
        header('Location: list.php?success=' . (isset($_POST['id']) ? 'updated' : 'added'));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eco Ride - <?= isset($event) ? 'Modifier' : 'Ajouter' ?> événement</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../../style.css">
<style>
.navbar-backoffice {
    background: linear-gradient(90deg, #1976D2, #0F3B6E);
    padding: 0.8rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
    position: sticky;
    top: 0;
    z-index: 100;
}
.navbar-backoffice .nav-left { display: flex; align-items: center; gap: 2rem; }
.navbar-backoffice .logo { display: flex; align-items: center; gap: 8px; font-size: 1.3rem; font-weight: 700; color: #fff; text-decoration: none; }
.navbar-backoffice .logo i { color: #61B3FA; font-size: 1.5rem; }
.navbar-backoffice .dropdown { position: relative; display: inline-block; }
.navbar-backoffice .dropdown-btn { background: rgba(255,255,255,0.1); color: #fff; padding: 0.6rem 1.2rem; border: 1px solid rgba(97,179,250,.4); border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
.navbar-backoffice .dropdown-content { display: none; position: absolute; top: 110%; left: 0; min-width: 240px; background: linear-gradient(145deg, #0D1F3A, #122A4A); border: 1px solid rgba(97,179,250,.3); border-radius: 12px; z-index: 200; }
.navbar-backoffice .dropdown-content.show { display: block; animation: fadeInDown 0.25s ease; }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.navbar-backoffice .dropdown-content a { display: flex; align-items: center; gap: 12px; padding: 0.8rem 1.2rem; color: #fff; text-decoration: none; font-size: 0.85rem; }
.navbar-backoffice .dropdown-content a i { width: 20px; color: #61B3FA; }
.navbar-backoffice .dropdown-content a:hover { background: rgba(97,179,250,.15); padding-left: 1.5rem; }
.navbar-backoffice .dropdown-divider { height: 1px; background: rgba(97,179,250,.2); margin: 0.3rem 0; }
.navbar-backoffice .nav-right .user-info { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1); padding: 0.4rem 1rem; border-radius: 30px; }
.navbar-backoffice .nav-right .user-info i { color: #61B3FA; }
@media (max-width: 768px) { .navbar-backoffice { padding: 0.6rem 1rem; } .navbar-backoffice .logo span, .navbar-backoffice .dropdown-btn span, .navbar-backoffice .user-info span { display: none; } }
.btn-generate-ia { background: linear-gradient(135deg, #8E44AD, #9B59B6); color: white; padding: 0.5rem 1rem; border: none; border-radius: 25px; cursor: pointer; margin-top: 0.5rem; transition: all 0.3s ease; width: 100%; }
.btn-generate-ia:hover { transform: translateY(-2px); }
</style>
</head>
<body>

<nav class="navbar-backoffice">
    <div class="nav-left">
        <a href="../../../index.php" class="logo"><i class="fas fa-leaf"></i><span>EcoRide - Admin</span></a>
        <div class="dropdown">
            <button class="dropdown-btn" onclick="toggleDropdown()"><i class="fas fa-bars"></i><span>Menu</span></button>
            <div class="dropdown-content" id="dropdownMenu">
                <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
                <a href="../sponsors/list.php"><i class="fas fa-handshake"></i> Sponsors</a>
                <div class="dropdown-divider"></div>
                <a href="../../../index.php"><i class="fas fa-globe"></i> Voir le site</a>
            </div>
        </div>
    </div>
    <div class="nav-right"><div class="user-info"><i class="fas fa-user-circle"></i><span>Administrateur</span></div></div>
</nav>

<div class="sidebar">
    <div class="sidebar-header"><div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE</h2></div></div>
    <div class="sidebar-nav">
        <div class="nav-section"><div class="nav-section-title">ADMINISTRATION</div>
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="list.php" class="active"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="../sponsors/list.php"><i class="fas fa-handshake"></i> Sponsors</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas <?= isset($event) ? 'fa-edit' : 'fa-plus' ?>"></i> <?= isset($event) ? 'Modifier' : 'Ajouter' ?> un événement</h2>
        </div>
        <?php if($error): ?><div class="alert-error" style="background:rgba(231,76,60,0.15); color:#e74c3c; padding:1rem; border-radius:10px; margin-bottom:1rem;"><?= $error ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateEventForm()">
            <?php if(isset($event)): ?><input type="hidden" name="id" value="<?= $event['id'] ?>"><?php endif; ?>
            <div class="form-group"><label>Titre *</label><input type="text" name="titre" id="titre" value="<?= htmlspecialchars($event['titre'] ?? '') ?>"><div id="titreError" class="error"></div></div>
            <div class="form-group"><label>Type *</label>
                <select name="type" id="type">
                    <option value="">Sélectionner</option>
                    <option value="concert" <?= (isset($event['type']) && $event['type'] == 'concert') ? 'selected' : '' ?>>Concert</option>
                    <option value="match" <?= (isset($event['type']) && $event['type'] == 'match') ? 'selected' : '' ?>>Match</option>
                    <option value="festival" <?= (isset($event['type']) && $event['type'] == 'festival') ? 'selected' : '' ?>>Festival</option>
                    <option value="sortie" <?= (isset($event['type']) && $event['type'] == 'sortie') ? 'selected' : '' ?>>Sortie</option>
                    <option value="autre" <?= (isset($event['type']) && $event['type'] == 'autre') ? 'selected' : '' ?>>Autre</option>
                </select>
                <div id="typeError" class="error"></div>
            </div>
            <div class="form-group"><label>Ville *</label><input type="text" name="ville" id="ville" value="<?= htmlspecialchars($event['ville'] ?? '') ?>"><div id="villeError" class="error"></div></div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" rows="4" placeholder="Description de l'événement..."><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                <button type="button" id="btnGenerateIA" class="btn-generate-ia"><i class="fas fa-robot"></i> ✨ Générer description avec IA</button>
                <div id="iaLoading" style="display:none; margin-top:10px;"><i class="fas fa-spinner fa-spin"></i> Génération en cours...</div>
            </div>
            <div class="form-group"><label>Date et heure *</label><input type="datetime-local" name="date_evenement" id="date_evenement" value="<?= isset($event['date_evenement']) ? date('Y-m-d\TH:i', strtotime($event['date_evenement'])) : '' ?>"><div id="dateError" class="error"></div></div>
            <div class="form-row">
                <div class="form-group"><label>Nombre de places *</label><input type="number" name="nb_places" id="nb_places" value="<?= $event['nb_places'] ?? '' ?>"><div id="placesError" class="error"></div></div>
                <div class="form-group"><label>Statut</label>
                    <select name="statut">
                        <option value="ouvert" <?= (isset($event['statut']) && $event['statut'] == 'ouvert') ? 'selected' : '' ?>>Ouvert</option>
                        <option value="complet" <?= (isset($event['statut']) && $event['statut'] == 'complet') ? 'selected' : '' ?>>Complet</option>
                        <option value="annule" <?= (isset($event['statut']) && $event['statut'] == 'annule') ? 'selected' : '' ?>>Annulé</option>
                    </select>
                </div>
            </div>
            <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*" id="imageInput"><div class="preview-image" id="imagePreview"><?php if(isset($event['image']) && $event['image'] != 'default.jpg'): ?><img src="../../../uploads/events/<?= $event['image'] ?>" style="max-width:150px; border-radius:10px;"><?php endif; ?></div></div>
            <button type="submit" class="btn-submit"><?= isset($event) ? 'Modifier' : 'Ajouter' ?></button>
        </form>
    </div>
</div>

<footer><p>Eco Ride © 2025</p></footer>

<script src="../../../validation.js"></script>
<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    const file = e.target.files[0];
    if(file) { const reader = new FileReader(); reader.onload = function(e) { const img = document.createElement('img'); img.src = e.target.result; img.style.maxWidth = '150px'; img.style.borderRadius = '10px'; preview.appendChild(img); }; reader.readAsDataURL(file); }
});
document.getElementById('btnGenerateIA').addEventListener('click', async function() {
    const titre = document.getElementById('titre').value, ville = document.getElementById('ville').value, type = document.getElementById('type').value;
    if(!titre) { alert('Veuillez saisir le titre'); return; }
    if(!ville) { alert('Veuillez saisir la ville'); return; }
    if(!type) { alert('Veuillez sélectionner le type'); return; }
    const btn = this, loader = document.getElementById('iaLoading');
    btn.disabled = true; loader.style.display = 'block'; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
    try {
        const response = await fetch('../../../controllers/api_generate_description.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ titre, ville, type }) });
        const data = await response.json();
        if(data.description) document.getElementById('description').value = data.description;
        else alert('Erreur');
    } catch(error) { alert('Erreur'); }
    finally { btn.disabled = false; loader.style.display = 'none'; btn.innerHTML = '<i class="fas fa-robot"></i> ✨ Générer description avec IA'; }
});
function toggleDropdown() { document.getElementById("dropdownMenu").classList.toggle("show"); }
window.onclick = function(e) { if (!e.target.matches('.dropdown-btn') && !e.target.closest('.dropdown-btn')) { var d = document.getElementById("dropdownMenu"); if (d.classList.contains('show')) d.classList.remove('show'); } }
</script>
</body>
</html>