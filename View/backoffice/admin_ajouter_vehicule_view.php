<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';

$vehiculeModel = new VehiculeModel();
$db = Database::getInstance();
$users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gérer l'upload de la photo
    $photoName = null;
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
        'user_id' => intval($_POST['user_id'] ?? 1),
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
    
    if ($vehiculeModel->immatriculationExists($data['immatriculation'])) {
        $errors[] = "Cette immatriculation (" . htmlspecialchars($data['immatriculation']) . ") est déjà utilisée.";
    }
    
    if (empty($errors)) {
        if ($vehiculeModel->create($data)) {
            $_SESSION['success'] = 'Véhicule ajouté avec succès.';
            header('Location: admin.php?tab=vehicules');
            exit;
        } else {
            $errors[] = "Erreur lors de l'ajout.";
        }
    }
    
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $data;
    header('Location: admin_ajouter_vehicule.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un véhicule - EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#0A1628,#0D1F3A);
            color:#fff;
            min-height:100vh;
            padding:2rem;
        }
        .container{
            max-width:700px;
            margin:0 auto;
            background:rgba(255,255,255,.08);
            border-radius:20px;
            padding:2rem;
            border:1px solid rgba(97,179,250,.3);
        }
        h1{
            font-size:1.8rem;
            margin-bottom:0.5rem;
            display:flex;
            align-items:center;
            gap:10px;
        }
        h1 i{color:#61B3FA;}
        .subtitle{
            color:#A7A9AC;
            margin-bottom:2rem;
            padding-bottom:1rem;
            border-bottom:1px solid rgba(97,179,250,.2);
        }
        .form-group{margin-bottom:1.2rem;}
        label{
            display:block;
            margin-bottom:0.5rem;
            color:#61B3FA;
            font-size:0.85rem;
            font-weight:600;
        }
        input,select{
            width:100%;
            padding:0.8rem;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(97,179,250,.3);
            border-radius:10px;
            color:#fff;
            font-size:0.9rem;
            outline:none;
            transition:all 0.3s;
        }
        input:focus,select:focus{border-color:#61B3FA;}
        input[type="file"]{
            padding:0.5rem;
            background:rgba(255,255,255,.08);
        }
        .form-row{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:1rem;
        }
        .checkbox-group{
            display:flex;
            align-items:center;
            gap:0.8rem;
            background:rgba(255,255,255,.08);
            padding:0.8rem;
            border-radius:10px;
            border:1px solid rgba(97,179,250,.3);
            margin:1rem 0;
        }
        .checkbox-group input{width:auto;}
        .form-actions{
            display:flex;
            gap:1rem;
            margin-top:2rem;
            justify-content:flex-end;
        }
        .btn-primary{
            background:linear-gradient(135deg,#1976D2,#61B3FA);
            color:#fff;
            border:none;
            padding:0.8rem 2rem;
            border-radius:10px;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            gap:8px;
            transition:all 0.3s;
        }
        .btn-primary:hover{transform:translateY(-2px);}
        .btn-secondary{
            background:rgba(255,255,255,.1);
            color:#A7A9AC;
            border:1px solid rgba(255,255,255,.2);
            padding:0.8rem 2rem;
            border-radius:10px;
            cursor:pointer;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            gap:8px;
            transition:all 0.3s;
        }
        .btn-secondary:hover{background:rgba(231,76,60,.2);color:#e74c3c;}
        .alert{
            padding:1rem;
            border-radius:10px;
            margin-bottom:1.5rem;
        }
        .alert-error{
            background:rgba(231,76,60,.15);
            border:1px solid rgba(231,76,60,.4);
            color:#e74c3c;
        }
        .required{color:#e74c3c;}
        .back-link{
            display:inline-block;
            margin-bottom:1rem;
            color:#61B3FA;
            text-decoration:none;
        }
        .field-error{
            color:#e74c3c;
            font-size:0.75rem;
            margin-top:0.25rem;
            display:block;
        }
        .image-preview {
            margin-top: 10px;
        }
        .image-preview img {
            max-width: 200px;
            border-radius: 10px;
            border: 2px solid #61B3FA;
        }
        small{
            color:#A7A9AC;
            font-size:0.75rem;
            display:block;
            margin-top:5px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="admin.php?tab=vehicules" class="back-link"><i class="fas fa-arrow-left"></i> Retour</a>

    <h1><i class="fas fa-plus-circle"></i> Ajouter un véhicule</h1>
    <div class="subtitle">Remplissez le formulaire ci-dessous</div>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php foreach($_SESSION['errors'] as $e): ?>
                <?= htmlspecialchars($e) ?><br>
            <?php endforeach; unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="vehiculeForm">
        <div class="form-row">
            <div class="form-group">
                <label>Marque <span class="required">*</span></label>
                <input type="text" name="marque" id="marque" value="<?= htmlspecialchars($_SESSION['old']['marque'] ?? '') ?>">
                <span class="field-error" id="marqueError"></span>
            </div>
            <div class="form-group">
                <label>Modèle <span class="required">*</span></label>
                <input type="text" name="modele" id="modele" value="<?= htmlspecialchars($_SESSION['old']['modele'] ?? '') ?>">
                <span class="field-error" id="modeleError"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Immatriculation <span class="required">*</span></label>
                <input type="text" name="immatriculation" id="immatriculation" placeholder="AB-123-CD" value="<?= htmlspecialchars($_SESSION['old']['immatriculation'] ?? '') ?>">
                <span class="field-error" id="immatriculationError"></span>
            </div>
            <div class="form-group">
                <label>Couleur</label>
                <input type="text" name="couleur" id="couleur" placeholder="Rouge" value="<?= htmlspecialchars($_SESSION['old']['couleur'] ?? '') ?>">
                <span class="field-error" id="couleurError"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Places (1-9) <span class="required">*</span></label>
                <input type="number" name="capacite" id="capacite" min="1" max="9" value="<?= htmlspecialchars($_SESSION['old']['capacite'] ?? '4') ?>">
                <span class="field-error" id="capaciteError"></span>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut" id="statut">
                    <option value="disponible" <?= (($_SESSION['old']['statut'] ?? 'disponible') == 'disponible') ? 'selected' : '' ?>>Disponible</option>
                    <option value="indisponible" <?= (($_SESSION['old']['statut'] ?? '') == 'indisponible') ? 'selected' : '' ?>>Indisponible</option>
                    <option value="en_maintenance" <?= (($_SESSION['old']['statut'] ?? '') == 'en_maintenance') ? 'selected' : '' ?>>En maintenance</option>
                </select>
                <span class="field-error" id="statutError"></span>
            </div>
        </div>

        <div class="form-group">
            <label>Photo du véhicule</label>
            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg">
            <small>Formats acceptés : JPG, PNG. Taille max : 5MB</small>
            <div id="photoPreviewContainer" style="display: none; margin-top: 10px;">
                <img id="photoPreview" src="#" alt="Aperçu" style="max-width: 200px; border-radius: 10px; border: 2px solid #61B3FA;">
            </div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" name="climatisation" id="climatisation" <?= !empty($_SESSION['old']['climatisation']) ? 'checked' : '' ?>>
            <label for="climatisation"><i class="fas fa-snowflake"></i> Climatisation</label>
        </div>

        <div class="form-actions">
            <a href="admin.php?tab=vehicules" class="btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
// Aperçu de la photo
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

// Validation JS
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vehiculeForm');
    if (!form) return;

    const marqueInput = document.getElementById('marque');
    const modeleInput = document.getElementById('modele');
    const immatInput = document.getElementById('immatriculation');
    const capaciteInput = document.getElementById('capacite');

    function showError(input, message) {
        if (!input) return;
        const errorSpan = document.getElementById(input.id + 'Error');
        if (errorSpan) errorSpan.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
        input.style.borderColor = '#e74c3c';
    }

    function clearError(input) {
        if (!input) return;
        const errorSpan = document.getElementById(input.id + 'Error');
        if (errorSpan) errorSpan.innerHTML = '';
        input.style.borderColor = '';
    }

    function validateMarque() {
        const value = marqueInput?.value.trim() || '';
        if (!value) {
            showError(marqueInput, 'La marque est obligatoire.');
            return false;
        }
        if (value.length < 2) {
            showError(marqueInput, 'La marque doit contenir au moins 2 caractères.');
            return false;
        }
        clearError(marqueInput);
        return true;
    }

    function validateModele() {
        const value = modeleInput?.value.trim() || '';
        if (!value) {
            showError(modeleInput, 'Le modèle est obligatoire.');
            return false;
        }
        if (value.length < 2) {
            showError(modeleInput, 'Le modèle doit contenir au moins 2 caractères.');
            return false;
        }
        clearError(modeleInput);
        return true;
    }

    function validateImmatriculation() {
        let value = immatInput?.value.trim().toUpperCase() || '';
        const immatRegex = /^[A-Z]{2}-\d{3}-[A-Z]{2}$/;
        if (!value) {
            showError(immatInput, "L'immatriculation est obligatoire.");
            return false;
        }
        if (!immatRegex.test(value)) {
            showError(immatInput, "Format invalide. Exemple: AB-123-CD");
            return false;
        }
        immatInput.value = value;
        clearError(immatInput);
        return true;
    }

    function validateCapacite() {
        const value = parseInt(capaciteInput?.value);
        if (isNaN(value)) {
            showError(capaciteInput, 'La capacité doit être un nombre.');
            return false;
        }
        if (value < 1) {
            showError(capaciteInput, 'La capacité doit être au moins 1 place.');
            return false;
        }
        if (value > 9) {
            showError(capaciteInput, 'La capacité ne peut pas dépasser 9 places.');
            return false;
        }
        clearError(capaciteInput);
        return true;
    }

    form.addEventListener('submit', function(e) {
        const isMarqueValid = validateMarque();
        const isModeleValid = validateModele();
        const isImmatValid = validateImmatriculation();
        const isCapaciteValid = validateCapacite();
        
        if (!isMarqueValid || !isModeleValid || !isImmatValid || !isCapaciteValid) {
            e.preventDefault();
        }
    });

    if (marqueInput) {
        marqueInput.addEventListener('input', validateMarque);
        marqueInput.addEventListener('blur', validateMarque);
    }
    if (modeleInput) {
        modeleInput.addEventListener('input', validateModele);
        modeleInput.addEventListener('blur', validateModele);
    }
    if (capaciteInput) {
        capaciteInput.addEventListener('input', validateCapacite);
        capaciteInput.addEventListener('blur', validateCapacite);
    }
    if (immatInput) {
        immatInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validateImmatriculation();
        });
        immatInput.addEventListener('blur', validateImmatriculation);
    }
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
    }, 4000);
    setTimeout(() => alert.remove(), 4500);
});
</script>

<?php unset($_SESSION['old']); ?>
</body>
</html>