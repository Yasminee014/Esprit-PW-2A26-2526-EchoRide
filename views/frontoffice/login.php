<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nettoyer les erreurs email des sessions
if (isset($_SESSION['errors']) && isset($_SESSION['errors']['email'])) {
    unset($_SESSION['errors']['email']);
}
if (isset($_SESSION['login_errors']) && isset($_SESSION['login_errors']['email'])) {
    unset($_SESSION['login_errors']['email']);
}

// Définir BASE_URL si non défini
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/projetG/');
}

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
    exit();
}

// Déterminer quel formulaire afficher
$showForm = $_GET['show'] ?? $_GET['action'] ?? 'showLogin';
if ($showForm === 'showRegister' || $showForm === 'register') {
    $showRegister = true;
    $showLogin = false;
    $showForm = 'showRegister';
} else {
    $showRegister = false;
    $showLogin = true;
    $showForm = 'showLogin';
}

// Récupérer et effacer les messages de session
$errors        = $_SESSION['errors']        ?? [];
$oldInput      = $_SESSION['old_input']     ?? [];
$loginErrors   = $_SESSION['login_errors']  ?? [];
$loginOld      = $_SESSION['login_old_input'] ?? [];
$success       = $_SESSION['success']       ?? '';

unset($_SESSION['errors'], $_SESSION['old_input'],
      $_SESSION['login_errors'], $_SESSION['login_old_input'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Connexion / Inscription</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            color: #F4F5F7;
        }
        .navbar {
            background: linear-gradient(90deg, #1565C0 0%, #0F3B6E 100%);
            padding: 0 2rem;
            height: 56px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(97,179,250,.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 400;
            color: #fff;
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
        }
        .nav-right, .nav-links {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            list-style: none;
            margin: 0; padding: 0;
        }
        .btn-nav, .nav-links li a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.38rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 500;
            color: #fff;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            transition: background 0.2s;
        }
        .btn-nav:hover, .nav-links li a:hover {
            background: rgba(255,255,255,.2);
        }
        .btn-logout {
            background: rgba(220,53,69,.25) !important;
            border-color: rgba(220,53,69,.4) !important;
            color: #ff8080 !important;
        }
        .btn-logout:hover {
            background: rgba(220,53,69,.4) !important;
        }
        .nav-links { display:flex; gap:2rem; list-style:none; }
        .nav-links a { 
            color:#fff; 
            text-decoration:none; 
            font-weight:500; 
            transition:color 0.3s;
        }
        .nav-links a:hover { color:#1976D2; }
        .hero {
            min-height: 40vh;
            display:flex; align-items:center; justify-content:center;
            text-align:center; padding:2rem;
        }
        .hero-content h1 { font-size:2.5rem; margin-bottom:1rem; }
        .hero-content .highlight { color:#1976D2; }
        .hero-content p { color:#61B3FA; font-size:1.1rem; margin-bottom:2rem; }
        .form-section { padding:3rem 2rem; background:rgba(10,22,40,0.5); }
        .form-container {
            max-width:550px;
            margin:0 auto;
        }
        .form-card {
            background:rgba(13,31,58,0.9);
            backdrop-filter:blur(10px);
            border-radius:24px; padding:2rem;
            border:1px solid rgba(25,118,210,0.3);
            transition:all 0.3s;
        }
        .form-card:hover { border-color:#1976D2; box-shadow:0 10px 30px rgba(25,118,210,0.1); }
        .form-card h2 { color:#1976D2; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:center; gap:10px; font-size:1.5rem; }
        .form-group { margin-bottom:1.2rem; }
        .form-group label { display:block; margin-bottom:0.5rem; color:#61B3FA; font-size:0.9rem; }
        .form-group label i { margin-right:8px; color:#1976D2; }
        .form-group input, .form-group select {
            width:100%; padding:0.8rem 1rem;
            border-radius:12px;
            border:1px solid rgba(25,118,210,0.3);
            background:rgba(10,22,40,0.8);
            color:white; transition:all 0.3s; font-size:0.95rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline:none; border-color:#1976D2;
            box-shadow:0 0 10px rgba(25,118,210,0.2);
        }
        .form-group input.error-field { border-color:#ff4444; }
        /* ── Password toggle eye ── */
        .password-wrapper { position:relative; }
        .password-wrapper input { padding-right:2.8rem; }
        .toggle-pwd {
            position:absolute; right:0.9rem; top:50%; transform:translateY(-50%);
            background:none; border:none; cursor:pointer;
            color:#A7A9AC; font-size:1rem; padding:0; line-height:1;
            transition:color 0.2s;
        }
        .toggle-pwd:hover { color:#1976D2; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .error-msg { color:#ff6b6b; font-size:0.8rem; margin-top:4px; display:block; }
        .error-msg i { margin-right:4px; }
        .alert {
            padding:0.8rem 1.2rem; border-radius:12px; margin-bottom:1.5rem;
            display:flex; align-items:center; gap:10px; font-size:0.9rem;
        }
        .alert-success { background:rgba(0,200,100,0.15); border:1px solid rgba(0,200,100,0.4); color:#4cff9a; }
        .alert-error   { background:rgba(255,68,68,0.15);  border:1px solid rgba(255,68,68,0.4);  color:#ff6b6b; }
        .btn-submit {
            background:linear-gradient(135deg,#1976D2,#1976D2);
            color:white; padding:0.8rem 1.5rem; border:none;
            border-radius:30px; cursor:pointer; font-weight:600;
            width:100%; font-size:1rem; transition:all 0.3s;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 5px 20px rgba(25,118,210,0.3); }
        .role-buttons { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 1.2rem; 
        }
        .role-btn {
            flex: 1;
            padding: 0.7rem;
            border-radius: 12px;
            border: 1px solid rgba(25,118,210,0.3);
            background: rgba(25,118,210,0.2);
            color: #1976D2;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            font-weight: 600;
        }
        .role-btn.active { 
            background: rgba(25,118,210,0.4); 
            border-color: #1976D2; 
            color: #1976D2; 
        }
        .forgot-link { color:#1976D2; text-decoration:none; font-size:0.85rem; }
        .forgot-link:hover { text-decoration:underline; }
        .switch-link { 
            color:#1976D2; 
            text-decoration:none; 
            font-weight: 500;
        }
        .switch-link:hover { text-decoration:underline; }
        .separator { text-align:center; margin:1rem 0; color:#A7A9AC; font-size:0.85rem; }
        .separator::before, .separator::after {
            content:''; display:inline-block; width:35%; height:1px;
            background:rgba(25,118,210,0.2); vertical-align:middle; margin:0 10px;
        }
        .features { padding:3rem 2rem; max-width:1200px; margin:0 auto; }
        .section-title { text-align:center; color:#1976D2; margin-bottom:2rem; font-size:1.8rem; }
        .features-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1.5rem; }
        .feature-card {
            background:rgba(13,31,58,0.8); border-radius:20px; padding:1.5rem;
            text-align:center; border:1px solid rgba(25,118,210,0.2); transition:all 0.3s;
        }
        .feature-card:hover { border-color:#1976D2; transform:translateY(-5px); }
        .feature-card i { font-size:2.5rem; color:#1976D2; margin-bottom:1rem; display:block; }
        .feature-card h3 { margin-bottom:0.5rem; }
        .feature-card p { color:#61B3FA; font-size:0.9rem; }
        footer { text-align:center; padding:2rem; border-top:1px solid rgba(25,118,210,0.2); color:#A7A9AC; }
        footer i { color:#1976D2; }
        
        /* Styles pour les boutons d'accès */
        .access-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 1rem 0;
        }
        .access-btn {
            flex: 1;
            text-align: center;
            padding: 0.7rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        .access-client {
            background: rgba(25,118,210,0.15);
            color: #1976D2;
            border: 1px solid rgba(25,118,210,0.3);
        }
        .access-client:hover {
            background: rgba(25,118,210,0.3);
            transform: translateY(-2px);
        }
        .access-admin {
            background: rgba(255,107,107,0.15);
            color: #ff6b6b;
            border: 1px solid rgba(255,107,107,0.3);
        }
        .access-admin:hover {
            background: rgba(255,107,107,0.3);
            transform: translateY(-2px);
        }
        .access-btn i {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .hero-content h1 { font-size: 1.8rem; }
            .access-links { flex-direction: column; }
        }
    </style>
    <!-- Google reCAPTCHA v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="logo" href="#"><i class="fas fa-leaf" style="font-size:1.5rem;color:#61B3FA;"></i> <span style="font-family:'Poppins',sans-serif;font-size:1.3rem;font-weight:400;color:#fff;letter-spacing:0.01em;">Eco<strong>Ride</strong></span></a>
    <ul class="nav-links">
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=showRegister"><i class="fas fa-user-plus"></i> S'inscrire</a></li>
        <li><a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm"><i class="fas fa-sign-in-alt"></i> Se connecter</a></li>
        <li><a href="#features"><i class="fas fa-star"></i> Fonctionnalités</a></li>
    </ul>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Bienvenue sur <span class="highlight">Eco Ride</span></h1>
        <p>La plateforme de covoiturage intelligente et écologique</p>
        <div style="display:flex;justify-content:center;margin-top:1.5rem;">
            <img src="<?= BASE_URL ?>uploads/photos/ecoride_logo.png" alt="EcoRide Logo"
                 style="width:220px;height:220px;object-fit:contain;filter:drop-shadow(0 4px 24px rgba(25,118,210,0.45));border-radius:16px;">
        </div>
    </div>
</section>

<!-- FORMULAIRES -->
<div class="form-section">
    <div class="form-container">

        <!-- ═══════════ INSCRIPTION ═══════════ -->
        <div class="form-card" id="registerForm" style="<?= $showForm === 'showRegister' ? 'display:block;' : 'display:none;' ?>">
            <h2><i class="fas fa-user-plus"></i> Créer un compte</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($errors['global'])): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['global']) ?></div>
            <?php endif; ?>

            <form method="POST" id="registerFormElement" action="<?= BASE_URL ?>controllers/UserController.php?action=register" novalidate>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Prénom *</label>
                        <input type="text" name="prenom"
                               value="<?= htmlspecialchars($oldInput['prenom'] ?? '') ?>"
                               placeholder="Votre prénom"
                               class="<?= isset($errors['prenom']) ? 'error-field' : '' ?>">
                        <?php if (isset($errors['prenom'])): ?>
                            <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($errors['prenom']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nom *</label>
                        <input type="text" name="nom"
                               value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>"
                               placeholder="Votre nom"
                               class="<?= isset($errors['nom']) ? 'error-field' : '' ?>">
                        <?php if (isset($errors['nom'])): ?>
                            <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($errors['nom']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="text" name="email" id="registerEmail"
                           value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                           placeholder="exemple@email.com">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Téléphone *</label>
                    <input type="text" name="telephone"
                           value="<?= htmlspecialchars($oldInput['telephone'] ?? '') ?>"
                           placeholder="Ex: 0612345678"
                           class="<?= isset($errors['telephone']) ? 'error-field' : '' ?>">
                        <?php if (isset($errors['telephone'])): ?>
                            <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($errors['telephone']) ?></span>
                        <?php endif; ?>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Je suis *</label>
                    <div class="role-buttons">
                        <div class="role-btn active">
                            <i class="fas fa-user"></i> Passager
                        </div>
                    </div>
                    <input type="hidden" id="regRole" name="role" value="passager">
                    <?php if (isset($errors['role'])): ?>
                        <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($errors['role']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mot de passe *</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="regPassword"
                                   placeholder="Min. 8 caractères"
                                   class="<?= isset($errors['password']) ? 'error-field' : '' ?>">
                            <button type="button" class="toggle-pwd" onclick="togglePassword('regPassword', this)" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirmer *</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="regConfirm"
                                   placeholder="Répéter le mot de passe"
                                   class="<?= isset($errors['confirm_password']) ? 'error-field' : '' ?>">
                            <button type="button" class="toggle-pwd" onclick="togglePassword('regConfirm', this)" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($errors['confirm_password']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <p style="color:#A7A9AC;font-size:0.78rem;margin-bottom:1rem;">
                    <i class="fas fa-info-circle" style="color:#1976D2"></i>
                    Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.
                </p>

                <button type="submit" class="btn-submit"><i class="fas fa-user-check"></i> S'inscrire</button>
            </form>
            
            <div class="separator">ou</div>
            <p style="text-align:center; color:#A7A9AC; font-size:0.85rem;">
                Déjà inscrit ? 
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm" class="switch-link">Se connecter</a>
            </p>
        </div>

<!-- ═══════════ CONNEXION ═══════════ -->
<div class="form-card" id="loginForm" style="<?= $showForm === 'showLogin' ? 'display:block;' : 'display:none;' ?>">
    <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($loginErrors['global'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($loginErrors['global']) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginFormElement" action="<?= BASE_URL ?>controllers/UserController.php?action=login" novalidate>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email *</label>
            <input type="text" name="email" id="loginEmail"
                   value="<?= htmlspecialchars($loginOld['email'] ?? '') ?>"
                   placeholder="exemple@email.com">
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Mot de passe *</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="loginPassword"
                       placeholder="••••••••"
                       class="<?= isset($loginErrors['password']) ? 'error-field' : '' ?>">
                <button type="button" class="toggle-pwd" onclick="togglePassword('loginPassword', this)" tabindex="-1">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <?php if (isset($loginErrors['password'])): ?>
                <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($loginErrors['password']) ?></span>
            <?php endif; ?>
        </div>

        <!-- ── Ligne : reCAPTCHA (gauche) + Mot de passe oublié (droite) ── -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; gap:0.5rem;">

            <!-- reCAPTCHA compact (data-size="compact") -->
            <div>
                <div class="g-recaptcha"
                     data-sitekey="6Leid74sAAAAAHcsWXv61YjJuUoQQY-UwkO-1FQo"
                     data-theme="dark"
                     data-size="compact">
                </div>
                <?php if (!empty($loginErrors['recaptcha'])): ?>
                    <span style="color:#ff6b6b;font-size:0.75rem;margin-top:0.3rem;display:block;">
                        <i class="fas fa-times-circle"></i> <?= htmlspecialchars($loginErrors['recaptcha']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Mot de passe oublié -->
            <a href="<?= BASE_URL ?>views/frontoffice/forgot_password.php" class="forgot-link" style="white-space:nowrap;">
                <i class="fas fa-question-circle"></i> Mot de passe oublié ?
            </a>
        </div>

        <button type="submit" class="btn-submit"><i class="fas fa-arrow-right"></i> Se connecter</button>
    </form>

    <div class="separator">ou</div>
    
    <!-- ═══ LIENS ACCÈS ADMIN SEULEMENT ═══ -->
    <div class="access-links">
        <a href="<?= BASE_URL ?>controllers/AdminController.php?action=showLogin" class="access-btn access-admin">
            <i class="fas fa-user-shield"></i> Accès Admin
        </a>
    </div>

    <div class="separator">ou</div>
    <p style="text-align:center; color:#A7A9AC; font-size:0.85rem;">
        Pas encore inscrit ? 
        <a href="<?= BASE_URL ?>controllers/UserController.php?action=showRegister" class="switch-link">Créer un compte</a>
    </p>
</div>
<!-- FEATURES -->
<div class="features" id="features">
    <h2 class="section-title"><i class="fas fa-star"></i> Notre Solution</h2>
    <div class="features-grid">
        <div class="feature-card">
            <i class="fas fa-brain"></i>
            <h3>Covoiturage Intelligent</h3>
            <p>Optimisation des trajets</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <h3>Pratique et Sécurisée</h3>
            <p>Plateforme web sécurisée</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-chart-line"></i>
            <h3>Plus économique</h3>
            <p>Réduction des coûts</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-mobile-alt"></i>
            <h3>Application simple</h3>
            <p>Interface intuitive</p>
        </div>
    </div>
</div>

<footer>
    <p><svg width="16" height="16" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="#61B3FA" opacity="0.9"/></svg> Eco Ride by Echo Group © 2025 - Covoiturage Intelligent et Écologique</p>
</footer>

<script>
function togglePassword(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
<script src="<?= BASE_URL ?>views/frontoffice/js/login.validation.js"></script>
</body>
</html>