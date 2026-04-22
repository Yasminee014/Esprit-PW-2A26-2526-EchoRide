<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins','Segoe UI',sans-serif;
            background:#0A1628;
            min-height:100vh; color:#fff;
            display:flex; align-items:center; justify-content:center;
        }
        .login-wrapper { width:100%; max-width:450px; padding:2rem; }
        .logo { text-align:center; margin-bottom:2rem; }
        .logo i { font-size:50px; color:#1976D2; display:block; margin-bottom:1rem; }
        .logo h1 { font-size:2rem; background:linear-gradient(135deg,#1976D2,#61B3FA); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .logo p { color:#A7A9AC; font-size:0.9rem; margin-top:0.3rem; }
        .admin-badge { display:inline-block; background:rgba(255,165,0,0.2); border:1px solid rgba(255,165,0,0.5); color:#ffa500; padding:0.3rem 0.8rem; border-radius:20px; font-size:0.8rem; margin-top:0.5rem; }
        .card { background:rgba(13,31,58,0.95); border-radius:24px; padding:2.5rem; border:1px solid rgba(25,118,210,0.3); box-shadow:0 20px 60px rgba(0,0,0,0.4); }
        .card h2 { color:#1976D2; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; }
        .form-group { margin-bottom:1.3rem; }
        .form-group label { display:block; margin-bottom:0.5rem; color:#61B3FA; font-size:0.9rem; }
        .form-group label i { margin-right:8px; color:#1976D2; }
        .form-group input { width:100%; padding:0.9rem 1rem; border-radius:12px; border:1px solid rgba(25,118,210,0.3); background:rgba(10,22,40,0.8); color:white; font-size:0.95rem; transition:all 0.3s; }
        .form-group input:focus { outline:none; border-color:#1976D2; box-shadow:0 0 10px rgba(25,118,210,0.2); }
        .form-group input.error-field { border-color:#ff4444; }
        .error-msg { color:#ff6b6b; font-size:0.8rem; margin-top:4px; display:block; }
        .alert { padding:0.8rem 1.2rem; border-radius:12px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px; font-size:0.9rem; }
        .alert-error { background:rgba(255,68,68,0.15); border:1px solid rgba(255,68,68,0.4); color:#ff6b6b; }
        .btn-submit { background:linear-gradient(135deg,#1976D2,#1976D2); color:white; padding:0.9rem 1.5rem; border:none; border-radius:30px; cursor:pointer; font-weight:600; width:100%; font-size:1rem; transition:all 0.3s; margin-top:0.5rem; }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(25,118,210,0.3); }
        .back-link { text-align:center; margin-top:1.5rem; }
        .back-link a { color:#A7A9AC; text-decoration:none; font-size:0.85rem; transition:color 0.3s; }
        .back-link a:hover { color:#1976D2; }
    </style>
</head>
<body>
<?php
$errors  = $_SESSION['admin_login_errors']    ?? [];
$oldInput= $_SESSION['admin_login_old_input'] ?? [];
unset($_SESSION['admin_login_errors'], $_SESSION['admin_login_old_input']);
?>

<div class="login-wrapper">
    <div class="logo">
        <i class="fas fa-leaf" style="font-size:50px;color:#61B3FA;display:block;margin:0 auto 0.5rem;text-align:center;"></i>
        <h1>Eco<span style="color:white">Ride</span></h1>
        <p>Panneau d'Administration</p>
        <span class="admin-badge"><i class="fas fa-shield-alt"></i> Accès Restreint</span>
    </div>

    <div class="card">
        <h2><i class="fas fa-lock"></i> Connexion Admin</h2>

        <?php if (!empty($errors['global'])): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['global']) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>controllers/AdminController.php?action=login" novalidate>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email administrateur *</label>
                <input type="text" name="email"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                       placeholder="admin@ecoride.fr"
                       class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label><i class="fas fa-key"></i> Mot de passe *</label>
                <input type="password" name="password"
                       placeholder="••••••••"
                       class="<?= isset($errors['password']) ? 'error-field' : '' ?>">
                <?php if (isset($errors['password'])): ?>
                    <span class="error-msg"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i> Accéder au Panneau
            </button>
        </form>
    </div>

    <div class="back-link">
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=index">
            <i class="fas fa-arrow-left"></i> Retour au site
        </a>
    </div>
</div>
<script src="<?= BASE_URL ?>views/backoffice/js/admin_login.validation.js"></script>
</body>
</html>