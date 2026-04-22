<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: http://localhost/projetG/controllers/UserController.php?action=showLoginForm');
    exit();
}

// Définir BASE_URL
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/projetG/');
}

$profileErrors  = $_SESSION['profile_errors']    ?? [];
$profileOld     = $_SESSION['profile_old_input'] ?? [];
$profileSuccess = $_SESSION['profile_success']   ?? '';
$pwdErrors      = $_SESSION['pwd_errors']        ?? [];

unset($_SESSION['profile_errors'], $_SESSION['profile_old_input'],
      $_SESSION['profile_success'], $_SESSION['pwd_errors']);

$user = [
    'id' => $_SESSION['user_id'],
    'prenom' => $_SESSION['user_prenom'],
    'nom' => $_SESSION['user_nom'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_role'],
    'telephone' => $_SESSION['user_telephone'] ?? '',
    'photo' => $_SESSION['user_photo'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Mon Profil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Vos styles existants */
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins','Segoe UI',sans-serif; background:linear-gradient(135deg,#0A1628 0%,#0D1F3A 100%); min-height:100vh; color:#fff; }
        .navbar { background:linear-gradient(90deg,#1565C0 0%,#0F3B6E 100%); padding:0 2rem; height:56px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(97,179,250,.15); position:sticky; top:0; z-index:100; }
        .logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .logo i { font-size:1.2rem; color:#61B3FA; }
        .logo h1 { font-size:1.3rem; font-weight:400; color:#fff; background:none; -webkit-background-clip:unset; background-clip:unset; }
        .nav-right { display:flex; align-items:center; gap:1rem; }
        .btn-nav { display:inline-flex; align-items:center; gap:6px; padding:0.38rem 1rem; border-radius:20px; text-decoration:none; font-size:0.88rem; font-weight:500; color:#fff; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); transition:background 0.2s; }
        .btn-back { background:rgba(25,118,210,0.2); color:#1976D2; border:1px solid rgba(25,118,210,0.3); }
        .btn-logout { background:rgba(220,53,69,.25) !important; border-color:rgba(220,53,69,.4) !important; color:#ff8080 !important; }
        .btn-profile { background:rgba(255,255,255,.1); color:#fff; border:1px solid rgba(255,255,255,.18); }
        .btn-back:hover, .btn-profile:hover { background:rgba(25,118,210,0.35); }
        .btn-logout:hover { background:rgba(255,68,68,0.35); }
        .profile-container { max-width:900px; margin:3rem auto; padding:0 2rem; }
        .profile-header { text-align:center; margin-bottom:2.5rem; }
        .avatar { width:90px; height:90px; background:rgba(25,118,210,0.2); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; border:3px solid #1976D2; overflow:hidden; }
        .avatar i { font-size:2.5rem; color:#1976D2; }
        .avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
        .upload-photo-label { display:inline-flex; align-items:center; gap:6px; color:#1976D2; font-size:0.85rem; cursor:pointer; padding:0.35rem 0.9rem; border-radius:20px; border:1px solid rgba(25,118,210,0.35); background:rgba(25,118,210,0.1); transition:all 0.3s; margin-bottom:0.5rem; }
        .upload-photo-label:hover { background:rgba(25,118,210,0.25); }
        .upload-photo-label input { display:none; }
        .profile-header h2 { font-size:1.8rem; }
        .profile-header p { color:#61B3FA; }
        .role-badge { display:inline-block; background:rgba(25,118,210,0.2); border:1px solid #1976D2; color:#1976D2; padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; margin-top:0.5rem; }
        .profile-grid { display:grid; grid-template-columns:1fr 1fr; gap:2rem; }
        @media(max-width:768px) { .profile-grid { grid-template-columns:1fr; } }
        .card { background:rgba(13,31,58,0.9); border-radius:20px; padding:2rem; border:1px solid rgba(25,118,210,0.2); }
        .card h3 { color:#1976D2; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:1.2rem; }
        .form-group { margin-bottom:1.2rem; }
        .form-group label { display:block; margin-bottom:0.5rem; color:#61B3FA; font-size:0.9rem; }
        .form-group label i { margin-right:8px; color:#1976D2; }
        .form-group input { width:100%; padding:0.8rem 1rem; border-radius:12px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:0.95rem; }
        .form-group input:focus { outline:none; border-color:#1976D2; box-shadow:0 0 10px rgba(25,118,210,0.2); }
        .form-group input.error-field { border-color:#ff4444; }
        .error-msg { color:#ff6b6b; font-size:0.8rem; margin-top:4px; display:block; }
        .alert { padding:0.8rem 1.2rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:0.9rem; }
        .alert-success { background:rgba(0,200,100,0.15); border:1px solid rgba(0,200,100,0.4); color:#4cff9a; }
        .alert-error { background:rgba(255,68,68,0.15); border:1px solid rgba(255,68,68,0.4); color:#ff6b6b; }
        .btn-submit { background:linear-gradient(135deg,#1976D2,#1976D2); color:white; padding:0.8rem 1.5rem; border:none; border-radius:30px; cursor:pointer; font-weight:600; width:100%; font-size:1rem; transition:all 0.3s; margin-top:0.5rem; }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(25,118,210,0.3); }
        .info-text { color:#A7A9AC; font-size:0.78rem; margin-bottom:1rem; }
        .info-text i { color:#1976D2; }
        footer { text-align:center; padding:2rem; border-top:1px solid rgba(25,118,210,0.2); color:#A7A9AC; margin-top:4rem; }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="logo" href="#"><i class="fas fa-leaf" style="font-size:1.5rem;color:#61B3FA;"></i> <span style="font-family:'Poppins',sans-serif;font-size:1.3rem;font-weight:400;color:#fff;letter-spacing:0.01em;">Eco<strong>Ride</strong></span></a>
    <div class="nav-right">
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=dashboard" class="btn-nav btn-back">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=logout" class="btn-nav btn-logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</nav>

<div class="profile-container">
    <div class="profile-header">
        <div class="avatar">
            <?php if (!empty($user['photo'])): ?>
                <img src="<?= BASE_URL ?>uploads/photos/<?= htmlspecialchars($user['photo']) ?>" alt="Photo de profil">
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?>
        </div>
        <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=uploadPhoto" enctype="multipart/form-data">
            <label class="upload-photo-label">
                <i class="fas fa-camera"></i> Changer la photo
                <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit()">
            </label>
        </form>
        <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
        <span class="role-badge">
            <?php if ($user['role'] === 'conducteur'): ?>
                <i class="fas fa-car"></i> Conducteur
            <?php else: ?>
                <i class="fas fa-user"></i> Passager
            <?php endif; ?>
        </span>
    </div>

    <?php if ($profileSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($profileSuccess) ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- MODIFIER PROFIL -->
        <div class="card">
            <h3><i class="fas fa-user-edit"></i> Modifier mes informations</h3>
            <?php if (!empty($profileErrors['global'])): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($profileErrors['global']) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=updateProfile">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Prénom *</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($profileOld['prenom'] ?? $user['prenom']) ?>">
                    <?php if (isset($profileErrors['prenom'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['prenom']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom *</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($profileOld['nom'] ?? $user['nom']) ?>">
                    <?php if (isset($profileErrors['nom'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['nom']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($profileOld['email'] ?? $user['email']) ?>">
                    <?php if (isset($profileErrors['email'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['email']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Téléphone *</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($profileOld['telephone'] ?? $user['telephone']) ?>">
                    <?php if (isset($profileErrors['telephone'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($profileErrors['telephone']) ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Sauvegarder</button>
            </form>
        </div>

        <!-- CHANGER MOT DE PASSE -->
        <div class="card">
            <h3><i class="fas fa-key"></i> Changer le mot de passe</h3>
            <p class="info-text"><i class="fas fa-info-circle"></i> Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.</p>
            <?php if (!empty($pwdErrors['global'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($pwdErrors['global']) ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=changePassword">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Mot de passe actuel *</label>
                    <input type="password" name="current_password">
                    <?php if (isset($pwdErrors['current_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($pwdErrors['current_password']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Nouveau mot de passe *</label>
                    <input type="password" name="new_password">
                    <?php if (isset($pwdErrors['new_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($pwdErrors['new_password']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Confirmer le nouveau *</label>
                    <input type="password" name="confirm_password">
                    <?php if (isset($pwdErrors['confirm_password'])): ?>
                        <span class="error-msg"><?= htmlspecialchars($pwdErrors['confirm_password']) ?></span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-key"></i> Changer le mot de passe</button>
            </form>
        </div>
    </div>
</div>

<footer>
    <p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025</p>
</footer>
<script src="<?= BASE_URL ?>views/frontoffice/js/profile.validation.js"></script>
</body>
</html>