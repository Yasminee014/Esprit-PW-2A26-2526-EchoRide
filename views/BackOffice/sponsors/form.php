<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../models/Sponsor.php';
require_once __DIR__ . '/../../../models/Event.php';

use Model\Sponsor;
use Model\Event;

$sponsorModel = new Sponsor();
$eventModel = new Event();
$sponsor = null;
$events = $eventModel->getAll();
$error = '';

$uploadDir = __DIR__ . '/../../../uploads/sponsors/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if(isset($_GET['id'])) { $sponsor = $sponsorModel->getById($_GET['id']); }

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logoName = $sponsor['logo'] ?? null;
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $logoName = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
            if(isset($sponsor['logo']) && $sponsor['logo'] && file_exists($uploadDir . $sponsor['logo'])) unlink($uploadDir . $sponsor['logo']);
        } else $error = "Format de logo non autorisé";
    }
    if(empty($error)) {
        if(isset($_POST['id']) && !empty($_POST['id'])) $sponsorModel->update($_POST['id'], $_POST, $logoName);
        else $sponsorModel->add($_POST, $logoName);
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
<title>Eco Ride - <?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?> sponsor</title>
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
                <a href="../events/list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
                <a href="list.php"><i class="fas fa-handshake"></i> Sponsors</a>
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
            <a href="../events/list.php"><i class="fas fa-calendar-alt"></i> Événements</a>
            <a href="list.php" class="active"><i class="fas fa-handshake"></i> Sponsors</a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas <?= isset($sponsor) ? 'fa-edit' : 'fa-plus' ?>"></i> <?= isset($sponsor) ? 'Modifier' : 'Ajouter' ?> un sponsor</h2>
        </div>
        <?php if($error): ?><div class="alert-error" style="background:rgba(231,76,60,0.15); color:#e74c3c; padding:1rem; border-radius:10px; margin-bottom:1rem;"><?= $error ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateSponsorForm()">
            <?php if(isset($sponsor)): ?><input type="hidden" name="id" value="<?= $sponsor['id'] ?>"><?php endif; ?>
            <div class="form-group"><label>Nom entreprise *</label><input type="text" name="nom_entreprise" id="nom_entreprise" value="<?= htmlspecialchars($sponsor['nom_entreprise'] ?? '') ?>"><div id="nomError" class="error"></div></div>
            <div class="form-row">
                <div class="form-group"><label>Montant sponsoring (DT) *</label><input type="number" name="montant_sponsoring" id="montant_sponsoring" step="0.01" value="<?= $sponsor['montant_sponsoring'] ?? '' ?>"><div id="montantError" class="error"></div></div>
                <div class="form-group"><label>Type sponsor</label>
                    <select name="type_sponsor">
                        <option value="">Sélectionner</option>
                        <option value="gold" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'gold') ? 'selected' : '' ?>>Gold</option>
                        <option value="silver" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'silver') ? 'selected' : '' ?>>Silver</option>
                        <option value="bronze" <?= (isset($sponsor['type_sponsor']) && $sponsor['type_sponsor'] == 'bronze') ? 'selected' : '' ?>>Bronze</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Statut</label>
                    <select name="statut">
                        <option value="en_attente" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                        <option value="confirme" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'confirme') ? 'selected' : '' ?>>Confirmé</option>
                        <option value="refuse" <?= (isset($sponsor['statut']) && $sponsor['statut'] == 'refuse') ? 'selected' : '' ?>>Refusé</option>
                    </select>
                </div>
                <div class="form-group"><label>Événement associé</label>
                    <select name="evenement_id">
                        <option value="">Aucun</option>
                        <?php foreach($events as $e): ?>
                        <option value="<?= $e['id'] ?>" <?= (isset($sponsor['evenement_id']) && $sponsor['evenement_id'] == $e['id']) ? 'selected' : '' ?>><?= htmlspecialchars($e['titre']) ?> - <?= date('d/m/Y', strtotime($e['date_evenement'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group"><label>Logo</label><input type="file" name="logo" accept="image/*" id="logoInput"><div class="preview-logo" id="logoPreview"><?php if(isset($sponsor['logo']) && $sponsor['logo']): ?><img src="../../../uploads/sponsors/<?= $sponsor['logo'] ?>" style="max-width:100px; border-radius:10px;"><?php endif; ?></div></div>
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
    if(file) { const reader = new FileReader(); reader.onload = function(e) { const img = document.createElement('img'); img.src = e.target.result; img.style.maxWidth = '100px'; img.style.borderRadius = '10px'; preview.appendChild(img); }; reader.readAsDataURL(file); }
});
function toggleDropdown() { document.getElementById("dropdownMenu").classList.toggle("show"); }
window.onclick = function(e) { if (!e.target.matches('.dropdown-btn') && !e.target.closest('.dropdown-btn')) { var d = document.getElementById("dropdownMenu"); if (d.classList.contains('show')) d.classList.remove('show'); } }
</script>
</body>
</html>