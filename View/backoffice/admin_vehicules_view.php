<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';

$vehiculeModel = new VehiculeModel();
$db = Database::getInstance();

$id = intval($_GET['id'] ?? 0);
$vehicule = $vehiculeModel->getById($id);

if (!$vehicule) {
    $_SESSION['errors'] = ['Véhicule introuvable.'];
    header('Location: admin.php');
    exit;
}

$users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer l'upload de la photo
    $photoName = $vehicule['photo']; // Conserver l'ancienne photo par défaut
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/uploads/vehicules/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array(strtolower($extension), $allowedExtensions)) {
            $photoName = uniqid('vehicule_') . '.' . $extension;
            $uploadPath = $uploadDir . $photoName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath);
        }
    }
    
    $data = [
        'user_id' => intval($_POST['user_id'] ?? $vehicule['user_id']),
        'marque' => trim($_POST['marque'] ?? ''),
        'modele' => trim($_POST['modele'] ?? ''),
        'immatriculation' => strtoupper(trim($_POST['immatriculation'] ?? '')),
        'couleur' => trim($_POST['couleur'] ?? ''),
        'capacite' => intval($_POST['capacite'] ?? 4),
        'climatisation' => isset($_POST['climatisation']) ? 1 : 0,
        'statut' => $_POST['statut'] ?? 'disponible',
        'photo' => $photoName
    ];
    
    $errors = $vehiculeModel->validate($data);
    
    // Vérifier si l'immatriculation existe déjà (sauf pour ce véhicule)
    if ($vehiculeModel->immatriculationExists($data['immatriculation'], $id)) {
        $errors[] = "Cette immatriculation (" . htmlspecialchars($data['immatriculation']) . ") est déjà utilisée par un autre véhicule.";
    }
    
    if (empty($errors)) {
        if ($vehiculeModel->update($id, $data)) {
            $_SESSION['success'] = 'Véhicule modifié avec succès.';
            // ✅ CORRECTION : Redirection vers admin.php (pas admin_vehicules.php)
            header('Location: admin.php');
            exit;
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
    }
    
    $_SESSION['errors'] = $errors;
    header('Location: admin_modifier_vehicule.php?id=' . $id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un véhicule - EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{--blue:#1976D2;--blue-light:#61B3FA;--dark:#0A1628;--dark2:#0D1F3A;--dark3:#0F3B6E;--grey:#A7A9AC;--green:#27ae60;--red:#e74c3c;}
        body{font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,var(--dark),var(--dark2));color:#fff;min-height:100vh;}
        .wrap{display:flex;min-height:100vh;}
        .sidebar{width:260px;background:linear-gradient(180deg,var(--blue),var(--dark3));padding:1.5rem 1rem;position:fixed;height:100vh;overflow-y:auto;}
        .logo{text-align:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--blue-light);}
        .logo i{font-size:40px;color:var(--blue-light);display:block;margin-bottom:6px;}
        .logo h2{background:linear-gradient(135deg,#fff,var(--blue-light));-webkit-background-clip:text;background-clip:text;color:transparent;font-size:1.35rem;}
        .logo p{color:var(--grey);font-size:.72rem;}
        .nav-section{color:var(--grey);font-size:.68rem;padding:.7rem 1rem .25rem;}
        nav ul{list-style:none;}
        nav ul li{margin-bottom:.25rem;}
        nav ul li a{display:flex;align-items:center;gap:11px;padding:.72rem 1rem;color:#fff;text-decoration:none;border-radius:10px;transition:all .25s;font-size:.88rem;}
        nav ul li a i{width:18px;color:var(--blue-light);}
        nav ul li a:hover,nav ul li a.active{background:rgba(255,255,255,.15);border-left:3px solid var(--blue-light);}
        .sidebar-sep{border:none;border-top:1px solid rgba(97,179,250,.2);margin:.75rem 0;}
        .main{flex:1;margin-left:260px;padding:1.6rem;}
        .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.6rem;padding-bottom:1rem;border-bottom:1px solid rgba(97,179,250,.2);}
        .topbar h1{font-size:1.5rem;display:flex;align-items:center;gap:9px;}
        .topbar h1 i{color:var(--blue-light);}
        .pill{background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);padding:.4rem .9rem;border-radius:20px;font-size:.8rem;display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:#fff;}
        .pill:hover{background:rgba(25,118,210,.3);border-color:#61b3fa;}
        .container{max-width:700px;margin:0 auto;background:rgba(255,255,255,.08);border-radius:20px;padding:2rem;border:1px solid rgba(97,179,250,.3);}
        .form-group{margin-bottom:1.2rem;}
        label{display:block;margin-bottom:0.5rem;color:#61B3FA;font-size:0.85rem;font-weight:600;}
        input,select{width:100%;padding:0.8rem;background:rgba(255,255,255,.08);border:1px solid rgba(97,179,250,.3);border-radius:10px;color:#fff;font-size:0.9rem;outline:none;}
        input:focus,select:focus{border-color:#61B3FA;}
        input[type="file"]{padding:0.5rem;}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
        .checkbox-group{display:flex;align-items:center;gap:0.8rem;background:rgba(255,255,255,.08);padding:0.8rem;border-radius:10px;border:1px solid rgba(97,179,250,.3);margin:1rem 0;}
        .checkbox-group input{width:auto;}
        .form-actions{display:flex;gap:1rem;margin-top:2rem;justify-content:flex-end;}
        .btn-primary{background:linear-gradient(135deg,#1976D2,#61B3FA);color:#fff;border:none;padding:0.8rem 2rem;border-radius:10px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s;}
        .btn-primary:hover{transform:translateY(-2px);}
        .btn-secondary{background:rgba(255,255,255,.1);color:#A7A9AC;border:1px solid rgba(255,255,255,.2);padding:0.8rem 2rem;border-radius:10px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s;}
        .btn-secondary:hover{background:rgba(231,76,60,.2);color:#e74c3c;}
        .alert{padding:1rem;border-radius:10px;margin-bottom:1.5rem;}
        .alert-error{background:rgba(231,76,60,.15);border:1px solid rgba(231,76,60,.4);color:#e74c3c;}
        .alert-success{background:rgba(39,174,96,.15);border:1px solid rgba(39,174,96,.4);color:#27ae60;}
        .image-preview{margin-top:10px;}
        .image-preview img{max-width:200px;border-radius:10px;border:2px solid #61B3FA;}
        small{color:#A7A9AC;font-size:0.75rem;display:block;margin-top:5px;}
        .current-photo{margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid rgba(97,179,250,.2);}
        .current-photo img{width:100px;height:75px;object-fit:cover;border-radius:8px;border:1px solid #61B3FA;}
    </style>
</head>
<body>
<div class="wrap">

<aside class="sidebar">
  <div class="logo"><i class="fas fa-leaf"></i><h2>EcoRide</h2><p>Administration</p></div>
  <nav>
    <div class="nav-section">Gestion</div>
    <ul>
      <li><a href="admin.php" class="active"><i class="fas fa-car"></i> Véhicules</a></li>
    </ul>
    <hr class="sidebar-sep">
    <ul><li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li></ul>
  </nav>
</aside>

<main class="main">

<div class="topbar">
  <h1><i class="fas fa-edit"></i> Modifier un véhicule</h1>
  <a href="admin.php" class="pill"><i class="fas fa-arrow-left"></i> Retour à l'administration</a>
  <span class="pill"><i class="fas fa-shield-alt"></i> Admin</span>
</div>

<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php unset($_SESSION['success']); endif;
if (!empty($_SESSION['errors'])): ?>
  <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php foreach($_SESSION['errors'] as $e) echo htmlspecialchars($e) . ' '; unset($_SESSION['errors']); ?></div>
<?php endif; ?>

<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group">
                <label>Marque</label>
                <input type="text" name="marque" value="<?= htmlspecialchars($vehicule['marque']) ?>" required>
            </div>
            <div class="form-group">
                <label>Modèle</label>
                <input type="text" name="modele" value="<?= htmlspecialchars($vehicule['modele']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Immatriculation</label>
                <input type="text" name="immatriculation" placeholder="AB-123-CD" value="<?= htmlspecialchars($vehicule['immatriculation']) ?>" required>
            </div>
            <div class="form-group">
                <label>Couleur</label>
                <input type="text" name="couleur" value="<?= htmlspecialchars($vehicule['couleur'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Places (1-9)</label>
                <input type="number" name="capacite" min="1" max="9" value="<?= $vehicule['capacite'] ?>" required>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="disponible" <?= $vehicule['statut'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                    <option value="indisponible" <?= $vehicule['statut'] === 'indisponible' ? 'selected' : '' ?>>Indisponible</option>
                    <option value="en_maintenance" <?= $vehicule['statut'] === 'en_maintenance' ? 'selected' : '' ?>>En maintenance</option>
                </select>
            </div>
        </div>

        <!-- Photo actuelle -->
        <?php if (!empty($vehicule['photo'])): ?>
        <div class="current-photo">
            <label>Photo actuelle</label>
            <div><img src="/ecoride/assets/uploads/vehicules/<?= $vehicule['photo'] ?>" alt="Photo actuelle"></div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label>Nouvelle photo (optionnel)</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg">
            <small>Laissez vide pour conserver l'image actuelle</small>
            <div id="photoPreviewContainer" style="display: none; margin-top: 10px;">
                <img id="photoPreview" src="#" alt="Aperçu" style="max-width: 200px; border-radius: 10px; border: 2px solid #61B3FA;">
            </div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" name="climatisation" id="climatisation" <?= $vehicule['climatisation'] ? 'checked' : '' ?>>
            <label for="climatisation"><i class="fas fa-snowflake"></i> Climatisation</label>
        </div>

        <div class="form-actions">
            <a href="admin.php" class="btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

</main>
</div>

<script>
document.getElementById('photo')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('photoPreview');
            const container = document.getElementById('photoPreviewContainer');
            preview.src = event.target.result;
            container.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
document.querySelectorAll('.alert').forEach(a => {
    setTimeout(()=>{ a.style.transition='opacity 0.5s'; a.style.opacity='0'; },4000);
    setTimeout(()=>a.remove(),4600);
});
</script>
</body>
</html>