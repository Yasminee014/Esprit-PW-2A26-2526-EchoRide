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
    $data = [
        'user_id' => $_SESSION['user_id'] ?? 1,
        'marque' => trim($_POST['marque'] ?? ''),
        'modele' => trim($_POST['modele'] ?? ''),
        'immatriculation' => strtoupper(trim($_POST['immatriculation'] ?? '')),
        'couleur' => trim($_POST['couleur'] ?? ''),
        'capacite' => intval($_POST['capacite'] ?? 4),
        'climatisation' => isset($_POST['climatisation']) ? 1 : 0,
        'statut' => $_POST['statut'] ?? 'disponible'
    ];
    
    $errors = $vehiculeModel->validate($data);
    
    // Vérification de l'unicité de l'immatriculation
    if ($vehiculeModel->immatriculationExists($data['immatriculation'])) {
        $errors[] = "Cette immatriculation (" . htmlspecialchars($data['immatriculation']) . ") est déjà utilisée par un autre véhicule.";
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
        .alert-success{
            background:rgba(39,174,96,.15);
            border:1px solid rgba(39,174,96,.4);
            color:#27ae60;
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
        .field-success{
            border-color:#27ae60 !important;
            background:rgba(39,174,96,.1) !important;
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

    <form method="POST" id="vehiculeForm">
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
                <input type="text" name="capacite" id="capacite" value="<?= htmlspecialchars($_SESSION['old']['capacite'] ?? '4') ?>">
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
// ═══════════════════════════════════════════════════════════
//  CONTRÔLE DE SAISIE - Formulaire d'ajout de véhicule
//  Aucune validation HTML5 n'est utilisée
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vehiculeForm');
    if (!form) return;

    // Supprimer les attributs de validation HTML5
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.removeAttribute('required');
        input.removeAttribute('pattern');
        input.removeAttribute('min');
        input.removeAttribute('max');
    });

    // Références des champs
    const marqueInput = document.getElementById('marque');
    const modeleInput = document.getElementById('modele');
    const immatInput = document.getElementById('immatriculation');
    const couleurInput = document.getElementById('couleur');
    const capaciteInput = document.getElementById('capacite');
    const statutInput = document.getElementById('statut');

    // Fonctions d'affichage d'erreur
    function showError(input, message) {
        if (!input) return;
        const errorSpan = document.getElementById(input.id + 'Error');
        if (errorSpan) errorSpan.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
        input.style.borderColor = '#e74c3c';
        input.style.backgroundColor = 'rgba(231,76,60,.1)';
    }

    function clearError(input) {
        if (!input) return;
        const errorSpan = document.getElementById(input.id + 'Error');
        if (errorSpan) errorSpan.innerHTML = '';
        input.style.borderColor = '';
        input.style.backgroundColor = '';
    }

    // Validations individuelles
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
        if (value.length > 50) {
            showError(marqueInput, 'La marque ne doit pas dépasser 50 caractères.');
            return false;
        }
        if (!/^[a-zA-ZÀ-ÿ0-9\s\-]+$/.test(value)) {
            showError(marqueInput, 'Lettres, chiffres, espaces et tirets uniquement.');
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
        if (value.length > 50) {
            showError(modeleInput, 'Le modèle ne doit pas dépasser 50 caractères.');
            return false;
        }
        if (!/^[a-zA-ZÀ-ÿ0-9\s\-]+$/.test(value)) {
            showError(modeleInput, 'Lettres, chiffres, espaces et tirets uniquement.');
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

    function validateCouleur() {
        const value = couleurInput?.value.trim() || '';
        if (value !== '') {
            if (value.length > 30) {
                showError(couleurInput, 'La couleur ne doit pas dépasser 30 caractères.');
                return false;
            }
            if (!/^[a-zA-ZÀ-ÿ\s\-]+$/.test(value)) {
                showError(couleurInput, 'La couleur ne doit contenir que des lettres.');
                return false;
            }
        }
        clearError(couleurInput);
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

    function validateStatut() {
        const value = statutInput?.value;
        const validStatuts = ['disponible', 'indisponible', 'en_maintenance'];
        if (!validStatuts.includes(value)) {
            showError(statutInput, 'Statut invalide.');
            return false;
        }
        clearError(statutInput);
        return true;
    }

    // Validation globale avant soumission
    form.addEventListener('submit', function(e) {
        const isMarqueValid = validateMarque();
        const isModeleValid = validateModele();
        const isImmatValid = validateImmatriculation();
        const isCouleurValid = validateCouleur();
        const isCapaciteValid = validateCapacite();
        const isStatutValid = validateStatut();
        
        if (!isMarqueValid || !isModeleValid || !isImmatValid || !isCouleurValid || !isCapaciteValid || !isStatutValid) {
            e.preventDefault();
            const firstError = document.querySelector('.field-error:not(:empty)');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Validation en temps réel
    if (marqueInput) {
        marqueInput.addEventListener('input', validateMarque);
        marqueInput.addEventListener('blur', validateMarque);
    }
    if (modeleInput) {
        modeleInput.addEventListener('input', validateModele);
        modeleInput.addEventListener('blur', validateModele);
    }
    if (couleurInput) {
        couleurInput.addEventListener('input', validateCouleur);
        couleurInput.addEventListener('blur', validateCouleur);
    }
    if (capaciteInput) {
        capaciteInput.addEventListener('input', validateCapacite);
        capaciteInput.addEventListener('blur', validateCapacite);
    }
    if (statutInput) {
        statutInput.addEventListener('change', validateStatut);
    }
    
    // Auto-uppercase immatriculation
    if (immatInput) {
        immatInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validateImmatriculation();
        });
        immatInput.addEventListener('blur', validateImmatriculation);
    }
});

// Auto-dismiss des alertes
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