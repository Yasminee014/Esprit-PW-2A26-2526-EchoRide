<?php require_once __DIR__ . '/partials/partials.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Ajouter Utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:#0A1628; color:#fff; min-height:100vh; display:flex; }
        .main-content { margin-left:260px; padding:2rem; flex:1; }
        .form-card { background:rgba(13,31,58,0.9); border-radius:20px; padding:2rem; border:1px solid rgba(25,118,210,0.2); max-width:700px; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1.2rem; }
        .form-group { margin-bottom:1.3rem; }
        .form-group label { display:block; margin-bottom:0.5rem; color:#61B3FA; font-size:0.9rem; }
        .form-group label i { margin-right:8px; color:#1976D2; }
        .form-group input, .form-group select { width:100%; padding:0.85rem 1rem; border-radius:12px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:0.95rem; transition:all 0.3s; }
        .form-group input:focus, .form-group select:focus { outline:none; border-color:#1976D2; box-shadow:0 0 10px rgba(25,118,210,0.2); }
        .form-group input.error-field { border-color:#ff4444; }
        .form-group select option { background:#0A1628; }
        .error-msg { color:#ff6b6b; font-size:0.8rem; margin-top:4px; display:block; }
        .alert { padding:0.8rem 1.2rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:0.9rem; }
        .alert-error { background:rgba(255,68,68,0.15); border:1px solid rgba(255,68,68,0.4); color:#ff6b6b; }
        .form-actions { display:flex; gap:1rem; margin-top:1rem; }
        .btn-submit { background:linear-gradient(135deg,#1976D2,#1976D2); color:white; padding:0.85rem 2rem; border:none; border-radius:30px; cursor:pointer; font-weight:600; font-size:1rem; transition:all 0.3s; }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(25,118,210,0.3); }
        .btn-cancel { background:rgba(255,255,255,0.1); color:#61B3FA; padding:0.85rem 2rem; border:1px solid rgba(255,255,255,0.2); border-radius:30px; cursor:pointer; font-size:1rem; transition:all 0.3s; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
        .btn-cancel:hover { background:rgba(255,255,255,0.2); }
        .rules-box { background:rgba(25,118,210,0.05); border:1px solid rgba(25,118,210,0.2); border-radius:12px; padding:1rem; margin-bottom:1.5rem; }
        .rules-box p { color:#61B3FA; font-size:0.82rem; margin-bottom:0.3rem; }
        .rules-box i { color:#1976D2; margin-right:6px; }

        body.light-mode { background:linear-gradient(135deg,#EDF2F7 0%,#DBEAFE 100%) !important; color:#1A2844 !important; }
        body.light-mode .stat-card { background:rgba(255,255,255,.95) !important; }
        body.light-mode td { color:#1A2844 !important; }

    </style>
<?php render_nav_css(); ?>
<?php require_once __DIR__ . '/partials/partials.php'; ?>
</head>
<body>
<?php
$errors   = $_SESSION['add_errors']    ?? [];
$oldInput = $_SESSION['add_old_input'] ?? [];
unset($_SESSION['add_errors'], $_SESSION['add_old_input']);
?>

<?php require_once __DIR__ . '/partials/partials.php'; ?>
<?php sidebar_compact('add_user'); ?>

<!-- MAIN -->
<main class="main-content">
<?php navbar_compact('<a href="' . BASE_URL . 'controllers/AdminController.php?action=listUsers" class="back-btn" style="margin-left:0.5rem;"><i class="fas fa-arrow-left"></i> Retour</a>'); ?>

    <div class="form-card">

        <?php if (!empty($errors['global'])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <div class="rules-box">
            <p><i class="fas fa-info-circle"></i> Le mot de passe doit contenir au moins 8 caractères.</p>
            <p><i class="fas fa-info-circle"></i> Le mot de passe doit contenir au moins une majuscule.</p>
            <p><i class="fas fa-info-circle"></i> Le mot de passe doit contenir au moins un chiffre.</p>
            <p><i class="fas fa-info-circle"></i> Le numéro de téléphone doit contenir entre 8 et 15 chiffres.</p>
        </div>

        <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=addUser" novalidate>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Prénom *</label>
                    <input type="text" name="prenom"
                           value="<?= htmlspecialchars($oldInput['prenom'] ?? '') ?>"
                           placeholder="Prénom"
                           class="<?= isset($errors['prenom']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['prenom'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['prenom']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom *</label>
                    <input type="text" name="nom"
                           value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>"
                           placeholder="Nom"
                           class="<?= isset($errors['nom']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['nom'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['nom']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email *</label>
                <input type="text" name="email"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                       placeholder="email@exemple.com"
                       class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label><i class="fas fa-phone"></i> Téléphone *</label>
                <input type="text" name="telephone"
                       value="<?= htmlspecialchars($oldInput['telephone'] ?? '') ?>"
                       placeholder="Ex: 0612345678"
                       class="<?= isset($errors['telephone']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['telephone'])): ?>
                    <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['telephone']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Rôle *</label>
                    <select name="role" class="<?= isset($errors['role']) ? 'error-field' : '' ?>">
                        <option value="passager"   <?= (($oldInput['role'] ?? '') === 'passager')   ? 'selected' : '' ?>>Passager</option>
                        <option value="conducteur" <?= (($oldInput['role'] ?? '') === 'conducteur') ? 'selected' : '' ?>>Conducteur</option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['role']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-circle"></i> Statut *</label>
                    <select name="statut" class="<?= isset($errors['statut']) ? 'error-field' : '' ?>">
                        <option value="actif"   <?= (($oldInput['statut'] ?? 'actif') === 'actif')   ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= (($oldInput['statut'] ?? '') === 'inactif') ? 'selected' : '' ?>>Inactif</option>
                    </select>
                    <?php if (isset($errors['statut'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['statut']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe *</label>
                    <input type="password" name="password"
                           placeholder="Min. 8 car., 1 maj., 1 chiffre"
                           class="<?= isset($errors['password']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmer *</label>
                    <input type="password" name="confirm_password"
                           placeholder="Répéter"
                           class="<?= isset($errors['confirm_password']) ? 'error-field' : '' ?>">
                    <?php if (isset($errors['confirm_password'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['confirm_password']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="<?= BASE_URL ?>controllers/AdminController.php?action=listUsers" class="btn-cancel">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</main>
<script src="<?= BASE_URL ?>views/backoffice/js/add_user.validation.js">
    function toggleTheme() {
        document.body.classList.toggle('light-mode');
        const isLight = document.body.classList.contains('light-mode');
        document.querySelectorAll('.themeIcon').forEach(i => {
            i.className = isLight ? 'fas fa-sun themeIcon' : 'fas fa-moon themeIcon';
        });
        localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
    }
    (function() {
        if (localStorage.getItem('ecoride_theme') === 'light') {
            document.body.classList.add('light-mode');
            document.querySelectorAll('.themeIcon').forEach(i => { i.className = 'fas fa-sun themeIcon'; });
        }
    })();

    </script>
<?php require_once __DIR__ . '/ai_helper_widget.php'; ?>
</body>
</html>