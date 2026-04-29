<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un véhicule | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========== STYLES DU FORMULAIRE ========== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0A1628;
            color: #fff;
            transition: background 0.3s, color 0.3s;
        }
        
        body.light-mode {
            background: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .hero-small {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .hero-small h2 { font-size: 1.5rem; margin-bottom: 0.3rem; }
        .hero-small p { color: rgba(255,255,255,0.8); font-size: 0.85rem; }
        .hero-small-icon { font-size: 3rem; opacity: 0.4; }
        
        .form-container {
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 2rem;
        }
        
        body.light-mode .form-container {
            background: #fff;
            border: 1px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #A7A9AC;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            border: 1px solid rgba(97,179,250,0.3);
            background: rgba(0,0,0,0.3);
            color: #fff;
        }
        
        body.light-mode .form-group input,
        body.light-mode .form-group select {
            background: #f5f5f5;
            color: #333;
            border-color: #ccc;
        }
        
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-submit:hover { transform: translateY(-2px); }
        
        .btn-cancel {
            background: rgba(255,255,255,0.1);
            color: #fff;
            padding: 0.7rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        
        .btn-cancel:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .form-actions { flex-direction: column; }
            .btn-submit, .btn-cancel { text-align: center; justify-content: center; }
        }
    </style>
</head>
<body>

<!-- ========== LA BONNE NAVBAR (moderne) ========== -->
<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>
<!-- ============================================== -->

<main class="container">
    
    <div class="hero-small">
        <div class="hero-small-content">
            <h2><i class="fas fa-<?= $isEditMode ? 'edit' : 'plus' ?>"></i> <?= $isEditMode ? 'Modifier' : 'Ajouter' ?> un véhicule</h2>
            <p>Remplissez le formulaire ci-dessous</p>
        </div>
        <div class="hero-small-icon">
            <i class="fas fa-car"></i>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="mes_vehicules.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $isEditMode ? 'update' : 'create' ?>">
            <?php if ($isEditMode && isset($vehicule)): ?>
                <input type="hidden" name="id" value="<?= $vehicule['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Marque *</label>
                <input type="text" name="marque" value="<?= htmlspecialchars($vehicule['marque'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Modèle *</label>
                <input type="text" name="modele" value="<?= htmlspecialchars($vehicule['modele'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Immatriculation *</label>
                <input type="text" name="immatriculation" value="<?= htmlspecialchars($vehicule['immatriculation'] ?? '') ?>" required placeholder="ex: AB-123-CD">
            </div>
            
            <div class="form-group">
                <label>Couleur</label>
                <input type="text" name="couleur" value="<?= htmlspecialchars($vehicule['couleur'] ?? '') ?>" placeholder="ex: Rouge">
            </div>
            
            <div class="form-group">
                <label>Places (1-9) *</label>
                <input type="number" name="capacite" min="1" max="9" value="<?= $vehicule['capacite'] ?? '' ?>" required>
            </div>
            
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="disponible" <?= (isset($vehicule['statut']) && $vehicule['statut'] == 'disponible') ? 'selected' : '' ?>>Disponible</option>
                    <option value="indisponible" <?= (isset($vehicule['statut']) && $vehicule['statut'] == 'indisponible') ? 'selected' : '' ?>>Indisponible</option>
                    <option value="en_maintenance" <?= (isset($vehicule['statut']) && $vehicule['statut'] == 'en_maintenance') ? 'selected' : '' ?>>En maintenance</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="climatisation" value="1" <?= (isset($vehicule['climatisation']) && $vehicule['climatisation']) ? 'checked' : '' ?>> Climatisation
                </label>
            </div>
            
            <div class="form-group">
                <label>Photo du véhicule</label>
                <input type="file" name="photo" accept="image/*">
                <?php if (!empty($vehicule['photo'])): ?>
                    <small style="color: #A7A9AC;">Photo actuelle: <?= htmlspecialchars($vehicule['photo']) ?></small>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> <?= $isEditMode ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="mes_vehicules.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
    
</main>

<script>
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
}
</script>

</body>
</html>