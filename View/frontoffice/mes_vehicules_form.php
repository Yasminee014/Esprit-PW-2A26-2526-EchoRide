<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isEditMode = $isEditMode ?? false;
$vehicule = $vehicule ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un véhicule - EcoRide</title>
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
        .navbar {
            background: linear-gradient(90deg, #1976D2, #0F3B6E);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-radius: 15px;
        }
        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }
        .navbar .logo i { color: #61B3FA; }
        .navbar nav a {
            color: #fff;
            text-decoration: none;
            padding: .5rem 1.2rem;
            border-radius: 25px;
            font-size: .88rem;
            background: rgba(255,255,255,.08);
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            transition: all 0.3s;
        }
        input:focus, select:focus {
            border-color: #61B3FA;
            background: rgba(97,179,250,.1);
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
            transition: all 0.3s;
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
            transition: all 0.3s;
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
        .field-success{
            border-color:#27ae60 !important;
            background:rgba(39,174,96,.1) !important;
        }
        .global-error{
            background:rgba(231,76,60,.15);
            border:1px solid #e74c3c;
            color:#e74c3c;
            padding:1rem;
            border-radius:10px;
            margin-bottom:1rem;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="../index.php" class="logo"><i class="fas fa-leaf"></i> EcoRide</a>
    <nav>
        <a href="vehicules_disponibles.php"><i class="fas fa-car"></i> Covoiturages</a>
        <a href="mes_reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
        <a href="mes_vehicules.php"><i class="fas fa-key"></i> Mes véhicules</a>
        <a href="mon_historique.php"><i class="fas fa-history"></i> Mon historique</a>
        <a href="../backoffice/admin.php"><i class="fas fa-shield-alt"></i> Admin</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
    </nav>
</nav>

<div class="container">
    <a href="mes_vehicules.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à mes véhicules</a>

    <h1><i class="fas fa-<?= $isEditMode ? 'edit' : 'plus-circle' ?>"></i> <?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un véhicule</h1>
    <div class="subtitle"><?= $isEditMode ? 'Modifiez les informations' : 'Remplissez le formulaire' ?> ci-dessous</div>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php foreach($_SESSION['errors'] as $e): ?>
                <?= htmlspecialchars($e) ?><br>
            <?php endforeach; unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="mes_vehicules.php" id="vehiculeForm" novalidate>
        <input type="hidden" name="action" value="<?= $isEditMode ? 'update' : 'create' ?>">
        <?php if ($isEditMode && $vehicule): ?>
            <input type="hidden" name="id" value="<?= $vehicule['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Marque <span class="required">*</span></label>
                <input type="text" name="marque" id="marque" value="<?= $isEditMode ? htmlspecialchars($vehicule['marque'] ?? '') : '' ?>">
                <span class="field-error" id="marqueError"></span>
            </div>
            <div class="form-group">
                <label>Modèle <span class="required">*</span></label>
                <input type="text" name="modele" id="modele" value="<?= $isEditMode ? htmlspecialchars($vehicule['modele'] ?? '') : '' ?>">
                <span class="field-error" id="modeleError"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Immatriculation <span class="required">*</span></label>
                <input type="text" name="immatriculation" id="immatriculation" placeholder="AB-123-CD" value="<?= $isEditMode ? htmlspecialchars($vehicule['immatriculation'] ?? '') : '' ?>">
                <span class="field-error" id="immatriculationError"></span>
            </div>
            <div class="form-group">
                <label>Couleur</label>
                <input type="text" name="couleur" id="couleur" placeholder="Rouge" value="<?= $isEditMode ? htmlspecialchars($vehicule['couleur'] ?? '') : '' ?>">
                <span class="field-error" id="couleurError"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Places (1-9) <span class="required">*</span></label>
                <input type="number" name="capacite" id="capacite" min="1" max="9" value="<?= $isEditMode ? ($vehicule['capacite'] ?? 4) : 4 ?>">
                <span class="field-error" id="capaciteError"></span>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut" id="statut">
                    <option value="disponible" <?= ($isEditMode && ($vehicule['statut'] ?? '') == 'disponible') ? 'selected' : '' ?>>Disponible</option>
                    <option value="indisponible" <?= ($isEditMode && ($vehicule['statut'] ?? '') == 'indisponible') ? 'selected' : '' ?>>Indisponible</option>
                    <option value="en_maintenance" <?= ($isEditMode && ($vehicule['statut'] ?? '') == 'en_maintenance') ? 'selected' : '' ?>>En maintenance</option>
                </select>
                <span class="field-error" id="statutError"></span>
            </div>
        </div>

        <div class="checkbox-group">
            <input type="checkbox" name="climatisation" id="climatisation" value="1" <?= ($isEditMode && ($vehicule['climatisation'] ?? 0)) ? 'checked' : '' ?>>
            <label for="climatisation"><i class="fas fa-snowflake"></i> Climatisation</label>
        </div>

        <div class="form-actions">
            <a href="mes_vehicules.php" class="btn-secondary"><i class="fas fa-times"></i> Annuler</a>
            <button type="submit" class="btn-primary" id="submitBtn"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
// ═══════════════════════════════════════════════════════════
//  CONTRÔLE DE SAISIE COMPLET - Formulaire véhicule
//  Aucune validation HTML5 n'est utilisée
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vehiculeForm');
    if (!form) return;

    // Supprimer tous les attributs de validation HTML5
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.removeAttribute('required');
        input.removeAttribute('pattern');
    });

    // Références des champs
    const marqueInput = document.getElementById('marque');
    const modeleInput = document.getElementById('modele');
    const immatInput = document.getElementById('immatriculation');
    const couleurInput = document.getElementById('couleur');
    const capaciteInput = document.getElementById('capacite');
    const statutInput = document.getElementById('statut');
    const submitBtn = document.getElementById('submitBtn');

    // Regex pour l'immatriculation
    const IMMAT_REGEX = /^[A-Z]{2}-\d{3}-[A-Z]{2}$/;

    // Fonction pour afficher une erreur
    function showError(input, message) {
        if (!input) return;
        const errorSpan = document.getElementById(input.id + 'Error');
        if (errorSpan) {
            errorSpan.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
        }
        input.style.borderColor = '#e74c3c';
        input.style.backgroundColor = 'rgba(231,76,60,.1)';
        input.classList.remove('valid');
        input.classList.add('invalid');
    }

    // Fonction pour effacer l'erreur
    function clearError(input) {
        if (!input) return;
        const errorSpan = document.getElementById(input.id + 'Error');
        if (errorSpan) {
            errorSpan.innerHTML = '';
        }
        input.style.borderColor = '';
        input.style.backgroundColor = '';
        input.classList.remove('invalid');
    }

    // Fonction pour marquer un champ comme valide
    function showValid(input) {
        if (!input) return;
        input.style.borderColor = '#27ae60';
        input.style.backgroundColor = 'rgba(39,174,96,.1)';
        input.classList.add('valid');
        input.classList.remove('invalid');
    }

    // Validation Marque
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
        showValid(marqueInput);
        return true;
    }

    // Validation Modèle
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
        showValid(modeleInput);
        return true;
    }

    // Validation Immatriculation
    function validateImmatriculation() {
        let value = immatInput?.value.trim().toUpperCase() || '';
        
        if (!value) {
            showError(immatInput, "L'immatriculation est obligatoire.");
            return false;
        }
        if (!IMMAT_REGEX.test(value)) {
            showError(immatInput, "Format invalide. Exemple: AB-123-CD");
            return false;
        }
        immatInput.value = value;
        clearError(immatInput);
        showValid(immatInput);
        return true;
    }

    // Validation Couleur (optionnelle)
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
        if (value !== '') showValid(couleurInput);
        return true;
    }

    // Validation Capacité
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
        showValid(capaciteInput);
        return true;
    }

    // Validation Statut
    function validateStatut() {
        const value = statutInput?.value;
        const validStatuts = ['disponible', 'indisponible', 'en_maintenance'];
        
        if (!validStatuts.includes(value)) {
            showError(statutInput, 'Statut invalide.');
            return false;
        }
        clearError(statutInput);
        showValid(statutInput);
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
            
            // Afficher un message d'erreur global
            let errorMsg = '❌ Veuillez corriger les erreurs suivantes :\n';
            if (!isMarqueValid) errorMsg += '- La marque est invalide\n';
            if (!isModeleValid) errorMsg += '- Le modèle est invalide\n';
            if (!isImmatValid) errorMsg += '- L\'immatriculation est invalide\n';
            if (!isCouleurValid) errorMsg += '- La couleur est invalide\n';
            if (!isCapaciteValid) errorMsg += '- La capacité est invalide (1-9 places)\n';
            
            alert(errorMsg);
            
            // Scroll vers le premier champ en erreur
            const firstError = document.querySelector('.invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        } else {
            // Désactiver le bouton pour éviter double soumission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
        }
    });

    // Validation en temps réel (au fur et à mesure de la saisie)
    if (marqueInput) {
        marqueInput.addEventListener('input', validateMarque);
        marqueInput.addEventListener('blur', validateMarque);
    }
    
    if (modeleInput) {
        modeleInput.addEventListener('input', validateModele);
        modeleInput.addEventListener('blur', validateModele);
    }
    
    if (immatInput) {
        immatInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            validateImmatriculation();
        });
        immatInput.addEventListener('blur', validateImmatriculation);
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

    // Formatage automatique de l'immatriculation à la sortie du champ
    if (immatInput) {
        immatInput.addEventListener('blur', function() {
            let val = this.value.toUpperCase();
            val = val.replace(/[^A-Z0-9]/g, '');
            if (val.length === 7) {
                val = val.substring(0,2) + '-' + val.substring(2,5) + '-' + val.substring(5,7);
                this.value = val;
                validateImmatriculation();
            }
        });
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
</body>
</html>