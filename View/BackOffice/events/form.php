<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Model/Event.php';

use Model\Event;

$eventModel = new Event();
$event = null;
$error = '';

$uploadDir = __DIR__ . '/../../../uploads/events/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if(isset($_GET['id'])) {
    $event = $eventModel->getById($_GET['id']);
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageName = $event['image'] ?? 'default.jpg';
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $imageName = time() . '_' . uniqid() . '.' . $ext;
            $destination = $uploadDir . $imageName;
            move_uploaded_file($_FILES['image']['tmp_name'], $destination);
            
            if(isset($event['image']) && $event['image'] != 'default.jpg' && file_exists($uploadDir . $event['image'])) {
                unlink($uploadDir . $event['image']);
            }
        } else {
            $error = "Format d'image non autorisé (JPG, PNG, GIF, WEBP)";
        }
    }
    
    if(empty($error)) {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $eventModel->update($_POST['id'], $_POST, $imageName);
            header('Location: list.php?success=updated');
        } else {
            $eventModel->add($_POST, $imageName);
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
<title>Eco Ride - <?= isset($event) ? 'Modifier' : 'Ajouter' ?> événement</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../../style.css">
<style>
.btn-generate-ia {
    background: linear-gradient(135deg, #8E44AD, #9B59B6);
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    margin-top: 0.5rem;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-generate-ia:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(142, 68, 173, 0.4);
}
</style>
</head>
<body>

<nav class="navbar">
<div class="logo"><i class="fas fa-leaf"></i><h2>ECO RIDE - ADMIN</h2></div>
<ul class="nav-links">
<li><a href="../dashboard.php">Dashboard</a></li>
<li><a href="list.php">Événements</a></li>
<li><a href="../sponsors/list.php">Sponsors</a></li>
</ul>
</nav>

<div class="container">
<a href="list.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour</a>

<div class="card">
<div class="card-header">
<h2><i class="fas <?= isset($event) ? 'fa-edit' : 'fa-plus' ?>"></i> <?= isset($event) ? 'Modifier' : 'Ajouter' ?> un événement</h2>
</div>

<?php if($error): ?>
<div class="alert-error" style="background:rgba(220,53,69,0.2);color:#dc3545;padding:1rem;border-radius:10px;margin-bottom:1rem;"><?= $error ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" onsubmit="return validateEventForm()">
    <?php if(isset($event)): ?>
        <input type="hidden" name="id" value="<?= $event['id'] ?>">
    <?php endif; ?>
    
    <div class="form-group">
        <label>Titre *</label>
        <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($event['titre'] ?? '') ?>">
        <div id="titreError" class="error"></div>
    </div>
    
    <div class="form-group">
        <label>Type *</label>
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
    
    <div class="form-group">
        <label>Ville *</label>
        <input type="text" name="ville" id="ville" value="<?= htmlspecialchars($event['ville'] ?? '') ?>">
        <div id="villeError" class="error"></div>
    </div>
    
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" id="description" rows="4" placeholder="Description de l'événement..."><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
        <button type="button" id="btnGenerateIA" class="btn-generate-ia">
            <i class="fas fa-robot"></i> ✨ Générer description avec IA
        </button>
        <div id="iaLoading" style="display:none; margin-top:10px;">
            <i class="fas fa-spinner fa-spin"></i> Génération en cours...
        </div>
    </div>
    
    <div class="form-group">
        <label>Date et heure *</label>
        <input type="datetime-local" name="date_evenement" id="date_evenement" value="<?= isset($event['date_evenement']) ? date('Y-m-d\TH:i', strtotime($event['date_evenement'])) : '' ?>">
        <div id="dateError" class="error"></div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label>Nombre de places *</label>
            <input type="number" name="nb_places" id="nb_places" value="<?= $event['nb_places'] ?? '' ?>">
            <div id="placesError" class="error"></div>
        </div>
        
        <div class="form-group">
            <label>Statut</label>
            <select name="statut">
                <option value="ouvert" <?= (isset($event['statut']) && $event['statut'] == 'ouvert') ? 'selected' : '' ?>>Ouvert</option>
                <option value="complet" <?= (isset($event['statut']) && $event['statut'] == 'complet') ? 'selected' : '' ?>>Complet</option>
                <option value="annule" <?= (isset($event['statut']) && $event['statut'] == 'annule') ? 'selected' : '' ?>>Annulé</option>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label>Image</label>
        <input type="file" name="image" accept="image/*" id="imageInput">
        <div class="preview-image" id="imagePreview">
            <?php if(isset($event['image']) && $event['image'] != 'default.jpg'): ?>
                <img src="../../../uploads/events/<?= $event['image'] ?>" alt="Aperçu">
            <?php endif; ?>
        </div>
    </div>
    
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
    if(file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.maxWidth = '150px';
            img.style.borderRadius = '10px';
            preview.appendChild(img);
        }
        reader.readAsDataURL(file);
    }
});

// Génération de description avec IA
document.getElementById('btnGenerateIA').addEventListener('click', async function() {
    const titre = document.getElementById('titre').value;
    const ville = document.getElementById('ville').value;
    const type = document.getElementById('type').value;
    
    if(!titre) {
        alert('Veuillez d\'abord saisir le titre');
        document.getElementById('titre').focus();
        return;
    }
    
    if(!ville) {
        alert('Veuillez d\'abord saisir la ville');
        document.getElementById('ville').focus();
        return;
    }
    
    if(!type) {
        alert('Veuillez d\'abord sélectionner le type');
        document.getElementById('type').focus();
        return;
    }
    
    const btn = document.getElementById('btnGenerateIA');
    const loader = document.getElementById('iaLoading');
    btn.disabled = true;
    loader.style.display = 'block';
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération en cours...';
    
    try {
        const response = await fetch('../../../Controller/api_generate_description.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titre: titre, ville: ville, type: type })
        });
        
        const data = await response.json();
        
        if(data.description) {
            document.getElementById('description').value = data.description;
            if(data.fallback) {
                console.log('Mode fallback utilisé');
            }
        } else {
            alert('Erreur lors de la génération');
        }
    } catch(error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion à l\'API');
    } finally {
        btn.disabled = false;
        loader.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-robot"></i> ✨ Générer description avec IA';
    }
});
</script>

</body>
</html>