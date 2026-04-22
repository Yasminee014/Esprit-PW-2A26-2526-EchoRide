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
        .sidebar { width:260px; background:#0D1F3A; position:fixed; height:100vh; padding:1.5rem 1rem; overflow-y:auto; border-right:1px solid #1976D2; display:flex; flex-direction:column; }
        .sidebar-logo { display:flex; align-items:center; gap:10px; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:2px solid #1976D2; }
        .sidebar-logo i { font-size:28px; color:#1976D2; }
        .sidebar-logo h2 { font-size:1.3rem; }
        .sidebar-logo span { color:#1976D2; }
        .admin-info { background:rgba(25,118,210,0.1); border-radius:12px; padding:0.8rem; margin-bottom:1.5rem; text-align:center; border:1px solid rgba(25,118,210,0.2); }
        .admin-info i { font-size:1.5rem; color:#1976D2; display:block; margin-bottom:0.3rem; }
        .admin-info small { color:#A7A9AC; font-size:0.75rem; display:block; }
        .nav-section { color:#A7A9AC; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin:1rem 0 0.5rem 0.5rem; }
        .nav-item { display:flex; align-items:center; gap:12px; padding:0.75rem 1rem; color:#fff; text-decoration:none; border-radius:12px; margin-bottom:0.3rem; transition:all 0.3s; font-size:0.9rem; }
        .nav-item i { width:20px; color:#A7A9AC; }
        .nav-item:hover, .nav-item.active { background:rgba(25,118,210,0.2); color:#1976D2; }
        .nav-item:hover i, .nav-item.active i { color:#1976D2; }
        .sidebar-footer { margin-top:auto; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.1); }
        .logout-btn { display:flex; align-items:center; gap:10px; padding:0.75rem 1rem; color:#ff6b6b; text-decoration:none; border-radius:12px; transition:all 0.3s; font-size:0.9rem; }
        .logout-btn:hover { background:rgba(255,68,68,0.2); }
        .main-content { margin-left:260px; padding:2rem; flex:1; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,0.1); }
        .top-bar h1 { font-size:1.8rem; display:flex; align-items:center; gap:10px; }
        .top-bar h1 i { color:#1976D2; }
        .back-btn { background:rgba(25,118,210,0.2); color:#1976D2; padding:0.5rem 1.2rem; border-radius:20px; text-decoration:none; font-size:0.85rem; border:1px solid rgba(25,118,210,0.3); display:flex; align-items:center; gap:8px; transition:all 0.3s; }
        .back-btn:hover { background:rgba(25,118,210,0.35); }
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
    </style>
</head>
<body>
<?php
$errors   = $_SESSION['add_errors']    ?? [];
$oldInput = $_SESSION['add_old_input'] ?? [];
unset($_SESSION['add_errors'], $_SESSION['add_old_input']);
?>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <svg width="32" height="32" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 0 8px rgba(97,179,250,.45))"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="url(#lg_s)" opacity="0.95"/><path d="M22 38L22 12" stroke="rgba(255,255,255,0.3)" stroke-width="1.2" stroke-linecap="round"/><defs><linearGradient id="lg_s" x1="12" y1="4" x2="36" y2="38" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#61B3FA"/><stop offset="100%" stop-color="#1976D2"/></linearGradient></defs></svg>
        <h2>Eco<span>Ride</span></h2>
    </div>
    <div class="admin-info">
        <i class="fas fa-user-shield"></i>
        <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong>
        <small><?= htmlspecialchars($_SESSION['admin_email']) ?></small>
    </div>
    <span class="nav-section">Navigation</span>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=dashboard" class="nav-item">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=listUsers" class="nav-item">
        <i class="fas fa-users"></i> Utilisateurs
    </a>
    <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showAddUser" class="nav-item active">
        <i class="fas fa-user-plus"></i> Ajouter utilisateur
    </a>
    <span class="nav-section">Site</span>
    <a href="<?= BASE_URL ?>controllers/UserController.php?action=index" class="nav-item">
        <i class="fas fa-globe"></i> Voir le site
    </a>
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=logout" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-content">
    <div class="top-bar">
        <h1><i class="fas fa-user-plus"></i> Ajouter un Utilisateur</h1>
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=listUsers" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

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
<script src="<?= BASE_URL ?>views/backoffice/js/add_user.validation.js"></script>
</body>
</html>