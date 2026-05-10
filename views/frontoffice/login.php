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
    define('BASE_URL', 'http://localhost/projetadmin/');
}

// Si l'utilisateur est déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'controllers/UserController.php?action=dashboard');
    exit();
}

// Déterminer quel formulaire afficher
$showForm = $_GET['show'] ?? $_GET['action'] ?? 'landing';
if ($showForm === 'showRegister' || $showForm === 'register') {
    $showRegister = true;
    $showLogin = false;
    $showForm = 'showRegister';
} elseif ($showForm === 'showLogin' || $showForm === 'login') {
    $showRegister = false;
    $showLogin = true;
    $showForm = 'showLogin';
} else {
    $showRegister = false;
    $showLogin = false;
    $showForm = 'landing';
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
        /* Bouton toggle thème */
        .theme-btn {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .theme-btn:hover {
            background: rgba(255,255,255,0.28);
            transform: rotate(15deg);
        }

        /* ── Google Translate widget */
        .goog-te-banner-frame,
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
            height: 0 !important;
        }
        body { top: 0 !important; position: relative !important; }
        #google_translate_element {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        .goog-te-gadget-simple {
            background: linear-gradient(135deg, #1976D2, #0F3B6E) !important;
            border: 1px solid rgba(97,179,250,0.3) !important;
            border-radius: 30px !important;
            padding: 6px 14px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            font-size: 13px !important;
            box-shadow: 0 4px 15px rgba(25,118,210,0.4) !important;
            cursor: pointer !important;
        }
        .goog-te-gadget-simple span,
        .goog-te-gadget-simple a,
        .goog-te-gadget-simple a span {
            color: white !important;
            font-family: inherit !important;
        }
        .goog-te-gadget-simple img,
        .goog-te-gadget-simple .goog-te-gadget-icon { display: none !important; }

        .hero {
            min-height: 40vh;
            display:flex; align-items:center; justify-content:center;
            text-align:left; padding:2rem;
            margin: 6rem auto 2rem auto;
            width: 95%;
            max-width: 1200px;
            border-radius: 20px;
            background-color: #0F3B6E;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .hero-content {
            width: 100%;
            max-width: 1200px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }
        .hero-text {
            flex: 1;
        }
        .hero-logo {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .hero-content h1 { font-size:2.5rem; margin-bottom:1rem; color: #fff; }
        .hero-content .highlight { color: #61B3FA; }
        .hero-content p { color: #E2E8F0; font-size:1.1rem; margin-bottom:2rem; }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: flex-start;
        }
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
        

    </style>
    <style>
        /* Light Mode Styles */
        body.light-mode {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            color: #263238;
        }
        body.light-mode .navbar {
            background: linear-gradient(90deg, #1565C0 0%, #0D47A1 100%);
        }
        body.light-mode .logo h1 {
            color: #fff;
        }
        body.light-mode .nav-links li a {
            color: #fff;
        }
        body.light-mode .nav-links li a:hover {
            background: rgba(255,255,255,.12);
        }
        body.light-mode .hero-content h1 {
            color: #263238;
        }
        body.light-mode .hero-content .highlight {
            color: #1976D2;
        }
        body.light-mode .hero-content p {
            color: #546E7A;
        }
        body.light-mode .form-section {
            background: rgba(255,255,255,0.1);
        }
        body.light-mode .form-card {
            background: rgba(255,255,255,0.95);
            border-color: rgba(25,118,210,0.3);
        }
        body.light-mode .form-card:hover {
            border-color: #1976D2;
            box-shadow: 0 10px 30px rgba(25,118,210,0.1);
        }
        body.light-mode .form-card h2 {
            color: #1976D2;
        }
        body.light-mode .form-group label {
            color: #546E7A;
        }
        body.light-mode .form-group label i {
            color: #1976D2;
        }
        body.light-mode .form-group input,
        body.light-mode .form-group select {
            background: rgba(255,255,255,0.9);
            border-color: rgba(25,118,210,0.3);
            color: #263238;
        }
        body.light-mode .form-group input:focus,
        body.light-mode .form-group select:focus {
            border-color: #1976D2;
            box-shadow: 0 0 10px rgba(25,118,210,0.2);
        }
        body.light-mode .form-group input.error-field {
            border-color: #ff4444;
        }
        body.light-mode .toggle-pwd {
            color: #546E7A;
        }
        body.light-mode .toggle-pwd:hover {
            color: #1976D2;
        }
        body.light-mode .error-msg {
            color: #C62828;
        }
        body.light-mode .alert-success {
            background: rgba(76,175,80,0.15);
            border-color: rgba(76,175,80,0.4);
            color: #2E7D32;
        }
        body.light-mode .alert-error {
            background: rgba(244,67,54,0.15);
            border-color: rgba(244,67,54,0.4);
            color: #C62828;
        }
        body.light-mode .btn-submit {
            background: linear-gradient(135deg, #1976D2, #1976D2);
            color: white;
        }
        body.light-mode .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(25,118,210,0.3);
        }
        body.light-mode .role-btn {
            background: rgba(25,118,210,0.1);
            border-color: rgba(25,118,210,0.3);
            color: #1976D2;
        }
        body.light-mode .role-btn.active {
            background: rgba(25,118,210,0.2);
            border-color: #1976D2;
            color: #1976D2;
        }
        body.light-mode .forgot-link,
        body.light-mode .switch-link {
            color: #1976D2;
        }
        body.light-mode .forgot-link:hover,
        body.light-mode .switch-link:hover {
            text-decoration: underline;
        }
        body.light-mode .separator {
            color: #546E7A;
        }
        body.light-mode .separator::before,
        body.light-mode .separator::after {
            background: rgba(25,118,210,0.2);
        }
        body.light-mode .section-title {
            color: #1976D2;
        }
        body.light-mode .feature-card {
            background: rgba(255,255,255,0.9);
            border-color: rgba(25,118,210,0.2);
        }
        body.light-mode .feature-card:hover {
            border-color: #1976D2;
            transform: translateY(-5px);
        }
        body.light-mode .feature-card i {
            color: #1976D2;
        }
        body.light-mode .feature-card h3 {
            color: #263238;
        }
        body.light-mode .feature-card p {
            color: #546E7A;
        }
        body.light-mode footer {
            border-top-color: rgba(25,118,210,0.2);
            color: #546E7A;
        }
        body.light-mode footer i {
            color: #1976D2;
        }
        body.light-mode .access-client {
            background: rgba(25,118,210,0.1);
            color: #1976D2;
            border-color: rgba(25,118,210,0.3);
        }
        body.light-mode .access-client:hover {
            background: rgba(25,118,210,0.2);
        }
        body.light-mode .access-admin {
            background: rgba(255,107,107,0.1);
            color: #C62828;
            border-color: rgba(255,107,107,0.3);
        }
        body.light-mode .access-admin:hover {
            background: rgba(255,107,107,0.2);
        }

        /* ── Bouton Google ── */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.07);
            color: #F4F5F7;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.25s;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .btn-google:hover {
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .google-icon { flex-shrink: 0; }
        body.light-mode .btn-google {
            background: #ffffff;
            border-color: #dadce0;
            color: #3c4043;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        body.light-mode .btn-google:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style>
    <!-- Google reCAPTCHA v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- face-api.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        /* ── Face verification overlay ─────────────────────────── */
        #faceModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        #faceModal.active { display: flex; }

        .face-card {
            background: rgba(13,31,58,0.97);
            border: 1px solid rgba(25,118,210,0.4);
            border-radius: 20px;
            padding: 2rem;
            max-width: 460px;
            width: 95%;
            text-align: center;
            color: #e0e0e0;
        }
        .face-card h3 { color: #1976D2; margin-bottom: 0.4rem; font-size: 1.2rem; }
        .face-card p  { font-size: 0.85rem; color: #aaa; margin-bottom: 1rem; }

        #cameraWrap {
            position: relative;
            display: inline-block;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid rgba(25,118,210,0.5);
            margin-bottom: 1rem;
        }
        #videoEl        { display: block; width: 320px; height: 240px; object-fit: cover; }
        #overlayCanvas  { position: absolute; top: 0; left: 0; width: 320px; height: 240px; pointer-events: none; }

        #faceStatus {
            min-height: 38px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }
        #faceStatus.ok   { color: #00e676; }
        #faceStatus.err  { color: #ff5252; }
        #faceStatus.info { color: #1976D2; }

        #scanProgress { width: 100%; height: 4px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; margin-bottom: 1rem; }
        #scanBar      { height: 100%; width: 0%; background: linear-gradient(90deg,#1976D2,#42A5F5); border-radius: 4px; transition: width 0.3s ease; }

        .face-actions { display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap; }

        #btnVerify {
            background: linear-gradient(135deg,#1976D2,#42A5F5);
            color: #fff;
            border: none;
            padding: 0.7rem 1.6rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        #btnVerify:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(25,118,210,0.4); }
        #btnVerify:disabled { opacity: 0.5; cursor: not-allowed; }

        #btnCancel {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 0.7rem 1.4rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        #btnCancel:hover { background: rgba(255,255,255,0.15); }

        .spinner-icon { animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body style="background:#0A1628; margin:0; padding:0;">

<?php include_once __DIR__ . '/navbar.php'; ?>

<style>
.auth-tabs { display:none; }
</style>

<div style="display:flex; justify-content:center; align-items:flex-start; min-height:calc(100vh - 80px); margin-top:80px; padding:2rem 1rem;">
<div style="width:100%; max-width:560px;">

  <!-- ════ FORMULAIRE CONNEXION ════ -->
<?php if ($showLogin): ?>
  <!-- ---- FORMULAIRE CONNEXION ---- -->
  <div class="form-card" id="loginFormCard">
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
          <input type="password" name="password" id="loginPassword" placeholder="••••••••"
                 class="<?= isset($loginErrors['password']) ? 'error-field' : '' ?>">
          <button type="button" class="toggle-pwd" onclick="togglePassword('loginPassword', this)" tabindex="-1">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <?php if (isset($loginErrors['password'])): ?>
          <span class="error-msg"><i class="fas fa-times-circle"></i><?= htmlspecialchars($loginErrors['password']) ?></span>
        <?php endif; ?>
      </div>

      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; gap:0.5rem;">
        <div>
          <div class="g-recaptcha" data-sitekey="6Leid74sAAAAAHcsWXv61YjJuUoQQY-UwkO-1FQo" data-theme="dark" data-size="compact"></div>
          <?php if (!empty($loginErrors['recaptcha'])): ?>
            <span style="color:#ff6b6b;font-size:0.75rem;margin-top:0.3rem;display:block;">
              <i class="fas fa-times-circle"></i> <?= htmlspecialchars($loginErrors['recaptcha']) ?>
            </span>
          <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?>views/frontoffice/forgot_password.php" class="forgot-link" style="white-space:nowrap;">
          <i class="fas fa-question-circle"></i> Mot de passe oublié ?
        </a>
      </div>

      <button type="submit" class="btn-submit"><i class="fas fa-arrow-right"></i> Se connecter</button>
    </form>

    <div class="separator">ou</div>

    <a href="<?= BASE_URL ?>controllers/google_auth.php" class="btn-google">
      <svg class="google-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        <path fill="none" d="M0 0h48v48H0z"/>
      </svg>
      Se connecter avec Google
    </a>

    <div class="separator">ou</div>
    <p style="text-align:center; color:#A7A9AC; font-size:0.85rem;">
      Pas encore inscrit ?
      <a href="<?= BASE_URL ?>controllers/UserController.php?action=showRegister" class="switch-link">Créer un compte</a>
    </p>
  </div>

</div>
</div>
<?php endif; ?>



<!-- FORMULAIRES -->
<?php if ($showRegister): ?>
<div class="form-section">
    <div class="form-container">

        <!-- ═══════════ INSCRIPTION ═══════════ -->
        <div class="form-card" id="registerForm">
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
</div>
</div>
<?php endif; ?>


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

<!-- Theme Toggle Script -->
<script>
/* ── Toggle mode sombre / clair ──────────────────────────── */
function toggleThemeLogin() {
    document.body.classList.toggle('light-mode');
    var isLight = document.body.classList.contains('light-mode');
    var icon = document.getElementById('loginThemeIcon');
    if (icon) {
        icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
    }
    localStorage.setItem('ecoride_theme', isLight ? 'light' : 'dark');
}
// Appliquer le thème sauvegardé au chargement
(function() {
    if (localStorage.getItem('ecoride_theme') === 'light') {
        document.body.classList.add('light-mode');
        var icon = document.getElementById('loginThemeIcon');
        if (icon) icon.className = 'fas fa-sun';
    }
})();
</script>
<script>
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'fr',
        includedLanguages: 'fr,en,ar,it,de,es',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>

<!-- ══════════════ FACE VERIFICATION MODAL ══════════════ -->
<div id="faceModal">
    <div class="face-card">
        <h3><i class="fas fa-camera"></i> Vérification de votre identité</h3>
        <p>Regardez la caméra pour confirmer que c'est bien vous</p>

        <div id="cameraWrap">
            <video id="videoEl" autoplay muted playsinline></video>
            <canvas id="overlayCanvas"></canvas>
        </div>

        <div id="scanProgress"><div id="scanBar"></div></div>

        <div id="faceStatus" class="info">
            <i class="fas fa-spinner spinner-icon"></i>
            Chargement des modèles IA...
        </div>

        <div class="face-actions">
            <button id="btnVerify" disabled>
                <i class="fas fa-check-circle"></i> Vérifier mon identité
            </button>
            <button id="btnCancel">
                <i class="fas fa-times"></i> Annuler
            </button>
        </div>
    </div>
</div>

<script>
// ════════════════════════════════════════════════════════
//  FACE VERIFICATION LOGIC — EcoRide
// ════════════════════════════════════════════════════════

const MODELS_URL  = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
const THRESHOLD   = 0.55; // 0.5 = strict, 0.6 = tolérant

let stream            = null;
let modelsLoaded      = false;
let pendingForm       = null;   // FormData à soumettre après vérification
let profileDescriptor = null;   // Float32Array depuis la photo de profil

// ── DOM refs ──────────────────────────────────────────
const faceModal     = document.getElementById('faceModal');
const videoEl       = document.getElementById('videoEl');
const overlayCanvas = document.getElementById('overlayCanvas');
const faceStatus    = document.getElementById('faceStatus');
const scanBar       = document.getElementById('scanBar');
const btnVerify     = document.getElementById('btnVerify');
const btnCancel     = document.getElementById('btnCancel');
const loginFormEl   = document.getElementById('loginFormElement');

// ── Chargement des modèles face-api.js ───────────────
async function loadModels() {
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL),
        ]);
        modelsLoaded = true;
        setStatus('info', '<i class="fas fa-video"></i> Modèles chargés. Activation de la caméra...');
    } catch(e) {
        setStatus('err', '<i class="fas fa-exclamation-triangle"></i> Erreur chargement modèles IA');
        console.error(e);
    }
}

// ── Helper statut ─────────────────────────────────────
function setStatus(type, html) {
    faceStatus.className = type;
    faceStatus.innerHTML = html;
}

// ── Démarrage caméra ──────────────────────────────────
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
        videoEl.srcObject = stream;
        await new Promise(r => videoEl.onloadedmetadata = r);
        setStatus('info', '<i class="fas fa-smile"></i> Positionnez votre visage dans le cadre');
        btnVerify.disabled = false;
        startLiveDetection();
    } catch(e) {
        setStatus('err', '<i class="fas fa-ban"></i> Accès caméra refusé. Veuillez autoriser l\'accès.');
    }
}

// ── Boucle de détection en direct ────────────────────
let detectionLoop = null;
function startLiveDetection() {
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
    detectionLoop = setInterval(async () => {
        const det = await faceapi.detectSingleFace(videoEl, opts);
        const ctx = overlayCanvas.getContext('2d');
        ctx.clearRect(0, 0, overlayCanvas.width, overlayCanvas.height);
        if (det) {
            const { x, y, width, height } = det.box;
            ctx.strokeStyle = '#1976D2';
            ctx.lineWidth   = 2;
            ctx.strokeRect(x, y, width, height);
        }
    }, 300);
}

// ── Récupérer le descripteur facial depuis la photo de profil ──
async function loadProfileDescriptor(email) {
    const res  = await fetch('<?= BASE_URL ?>controllers/UserController.php?action=getFaceImage', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    });
    const data = await res.json();
    if (!data.success || !data.image) return null;

    // Charger l'image depuis base64
    const img  = await faceapi.fetchImage('data:image/jpeg;base64,' + data.image);
    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
    const det  = await faceapi.detectSingleFace(img, opts)
                              .withFaceLandmarks(true)
                              .withFaceDescriptor();
    if (!det) return null;
    return det.descriptor;
}

// ── Capturer & comparer le visage ─────────────────────
async function verifyFace() {
    btnVerify.disabled = true;
    setStatus('info', '<i class="fas fa-spinner spinner-icon"></i> Analyse en cours...');
    scanBar.style.width = '30%';

    const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 });
    const det  = await faceapi.detectSingleFace(videoEl, opts)
                              .withFaceLandmarks(true)
                              .withFaceDescriptor();
    scanBar.style.width = '70%';

    if (!det) {
        setStatus('err', '<i class="fas fa-frown"></i> Aucun visage détecté. Rapprochez-vous.');
        btnVerify.disabled = false;
        scanBar.style.width = '0%';
        return;
    }

    if (!profileDescriptor) {
        // Pas de photo de profil → autoriser la connexion
        setStatus('ok', '<i class="fas fa-check-circle"></i> Pas de photo de profil. Connexion...');
        scanBar.style.width = '100%';
        setTimeout(submitPendingForm, 800);
        return;
    }

    const distance = faceapi.euclideanDistance(det.descriptor, profileDescriptor);
    scanBar.style.width = '100%';

    if (distance <= THRESHOLD) {
        setStatus('ok', '<i class="fas fa-check-circle"></i> Identité confirmée ! Connexion en cours...');
        setTimeout(submitPendingForm, 900);
    } else {
        setStatus('err', '<i class="fas fa-user-slash"></i> Ce n\'est pas vous ! Connexion refusée.');
        btnVerify.disabled = false;
        scanBar.style.width = '0%';
        setTimeout(() => {
            setStatus('info', '<i class="fas fa-redo"></i> Réessayez ou annulez.');
            btnVerify.disabled = false;
        }, 3000);
    }
}

// ── Soumettre le formulaire original ─────────────────
function submitPendingForm() {
    stopCamera();
    if (!pendingForm) return;

    const realForm = document.createElement('form');
    realForm.method = 'POST';
    realForm.action = '<?= BASE_URL ?>controllers/UserController.php?action=login';
    for (const [k, v] of pendingForm.entries()) {
        const inp   = document.createElement('input');
        inp.type    = 'hidden';
        inp.name    = k;
        inp.value   = v;
        realForm.appendChild(inp);
    }
    document.body.appendChild(realForm);
    realForm.submit();
}

// ── Arrêter caméra & fermer modal ─────────────────────
function stopCamera() {
    clearInterval(detectionLoop);
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    faceModal.classList.remove('active');
}

// ── Ouvrir le modal Face ID ───────────────────────────
async function openFaceModal(formData, email) {
    pendingForm       = formData;
    profileDescriptor = null;
    scanBar.style.width  = '0%';
    btnVerify.disabled   = true;
    faceModal.classList.add('active');
    setStatus('info', '<i class="fas fa-spinner spinner-icon"></i> Chargement des modèles IA...');

    if (!modelsLoaded) await loadModels();
    if (!modelsLoaded) return;

    setStatus('info', '<i class="fas fa-spinner spinner-icon"></i> Récupération de votre photo de profil...');
    try {
        profileDescriptor = await loadProfileDescriptor(email);
    } catch(e) {
        profileDescriptor = null;
    }

    await startCamera();
}

// ── Bouton Annuler ────────────────────────────────────
btnCancel.addEventListener('click', () => {
    stopCamera();
    pendingForm = null;
});

// ── Bouton Vérifier ───────────────────────────────────
btnVerify.addEventListener('click', verifyFace);

// ── Interception du formulaire de connexion ───────────
loginFormEl.addEventListener('submit', async function(e) {
    e.preventDefault();

    const email    = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value.trim();
    let valid = true;

    // Validation minimale côté client
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        valid = false;
    }
    if (!password) {
        valid = false;
    }
    if (!valid) {
        // Laisser la validation JS existante gérer l'affichage des erreurs
        this.submit();
        return;
    }

    const fd = new FormData(this);
    fd.set('email',    email);
    fd.set('password', password);

    // Ouvrir le modal de vérification faciale
    openFaceModal(fd, email);
});
</script>
</body>
</html>
