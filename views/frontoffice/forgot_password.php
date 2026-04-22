<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/projetG/');
}

$error = $_SESSION['forgot_error'] ?? '';
$success = $_SESSION['forgot_success'] ?? '';
unset($_SESSION['forgot_error'], $_SESSION['forgot_success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Ride - Mot de passe oublié</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628 0%, #0D1F3A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F4F5F7;
        }
        .container { max-width: 450px; width: 90%; margin: 2rem auto; }
        .card {
            background: rgba(13,31,58,0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid rgba(25,118,210,0.3);
        }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo i { font-size: 48px; color: #1976D2; }
        .logo h1 { font-size: 1.8rem; background: linear-gradient(135deg,#1976D2,#61B3FA); -webkit-background-clip: text; background-clip: text; color: transparent; }
        h2 { color: #1976D2; text-align: center; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #61B3FA; }
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(25,118,210,0.3);
            background: rgba(10,22,40,0.8);
            color: white;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #1976D2;
            box-shadow: 0 0 10px rgba(25,118,210,0.2);
        }
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(0,200,100,0.15); border: 1px solid rgba(0,200,100,0.4); color: #4cff9a; }
        .alert-error { background: rgba(255,68,68,0.15); border: 1px solid rgba(255,68,68,0.4); color: #ff6b6b; }
        .alert-info { background: rgba(25,118,210,0.15); border: 1px solid #1976D2; color: #1976D2; }
        .btn-submit {
            background: linear-gradient(135deg,#1976D2,#1976D2);
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(25,118,210,0.3);
        }
        .back-link { text-align: center; margin-top: 1.5rem; }
        .back-link a { color: #1976D2; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .separator { text-align: center; margin: 1rem 0; color: #A7A9AC; }
        .separator::before, .separator::after {
            content: ''; display: inline-block; width: 35%; height: 1px;
            background: rgba(25,118,210,0.2); vertical-align: middle; margin: 0 10px;
        }
        
        .step {
            transition: all 0.3s ease;
        }
        .step.hidden {
            display: none;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 2rem;
        }
        .step-dot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #A7A9AC;
        }
        .step-dot.active .dot {
            background: #1976D2;
            color: white;
        }
        .step-dot.completed .dot {
            background: #00ff88;
            color: white;
        }
        .step-label {
            font-size: 0.7rem;
            color: #A7A9AC;
        }
        .step-dot.active .step-label {
            color: #1976D2;
        }
        .step-dot.completed .step-label {
            color: #00ff88;
        }
        
        @media (max-width: 768px) {
            .step-indicator { gap: 1rem; }
            .dot { width: 30px; height: 30px; font-size: 0.8rem; }
            .step-label { font-size: 0.6rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <svg width="48" height="48" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 0 12px rgba(97,179,250,.5));display:block;margin:0 auto 0.4rem"><path d="M22 4C22 4 8 10 8 24C8 31.732 14.268 38 22 38C29.732 38 36 31.732 36 24C36 14 28 8 22 4Z" fill="url(#lg_l)" opacity="0.95"/><path d="M22 38L22 12" stroke="rgba(255,255,255,0.3)" stroke-width="1.2" stroke-linecap="round"/><defs><linearGradient id="lg_l" x1="12" y1="4" x2="36" y2="38" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#61B3FA"/><stop offset="100%" stop-color="#1976D2"/></linearGradient></defs></svg>
                <h1>Eco<span style="color:white">Ride</span></h1>
            </div>
            <h2><i class="fas fa-key"></i> Mot de passe oublié</h2>

            <div class="step-indicator">
                <div class="step-dot" id="step1Dot">
                    <div class="dot">1</div>
                    <span class="step-label">Email</span>
                </div>
                <div class="step-dot" id="step2Dot">
                    <div class="dot">2</div>
                    <span class="step-label">Code</span>
                </div>
                <div class="step-dot" id="step3Dot">
                    <div class="dot">3</div>
                    <span class="step-label">Nouveau mot de passe</span>
                </div>
            </div>

            <div id="alertContainer"></div>

            <!-- ÉTAPE 1 -->
            <div id="step1" class="step">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email *</label>
                    <input type="text" id="resetEmail" placeholder="exemple@email.com">
                </div>
                <button class="btn-submit" id="sendCodeBtn">
                    <i class="fas fa-paper-plane"></i> Envoyer le code
                </button>
            </div>

            <!-- ÉTAPE 2 -->
            <div id="step2" class="step hidden">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Code de vérification *</label>
                    <input type="text" id="verificationCode" placeholder="Entrez le code reçu">
                </div>
                <button class="btn-submit" id="verifyCodeBtn">
                    <i class="fas fa-check-circle"></i> Vérifier le code
                </button>
                <div class="separator">ou</div>
                <div style="text-align: center;">
                    <a href="#" id="resendCodeLink" style="color: #1976D2; text-decoration: none;">
                        <i class="fas fa-redo"></i> Renvoyer le code
                    </a>
                </div>
            </div>

            <!-- ÉTAPE 3 -->
            <div id="step3" class="step hidden">
                <form id="passwordForm" method="POST" action="<?= BASE_URL ?>controllers/UserController.php?action=resetPassword">
                    <input type="hidden" name="email" id="finalEmail">
                    <input type="hidden" name="code" id="finalCode">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Nouveau mot de passe *</label>
                        <input type="password" name="new_password" id="newPassword" placeholder="Min. 8 caractères" >
                        <small style="color: #A7A9AC; font-size: 0.7rem;">Doit contenir au moins 8 caractères, une majuscule et un chiffre</small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Confirmer le mot de passe *</label>
                        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Répéter le mot de passe" >
                    </div>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Réinitialiser le mot de passe
                    </button>
                </form>
            </div>

            <div class="separator">ou</div>
            <div class="back-link">
                <a href="<?= BASE_URL ?>controllers/UserController.php?action=showLoginForm">
                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </div>

    <script>
    window.FP_CONFIG = {
        sendResetUrl: "<?= BASE_URL ?>controllers/UserController.php?action=sendResetCode",
        resetEmail:   "<?= addslashes($_SESSION['reset_email'] ?? '') ?>",
        devCode:      "<?= addslashes($_SESSION['dev_code'] ?? '') ?>"
    };
    <?php
    unset($_SESSION['reset_email'], $_SESSION['dev_code']);
    ?>
    </script>
    <script src="<?= BASE_URL ?>views/frontoffice/js/forgot_password.validation.js"></script>
</body>
</html>