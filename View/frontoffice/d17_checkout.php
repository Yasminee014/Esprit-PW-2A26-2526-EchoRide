<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Config/Database.php';

// Récupérer les paramètres
$token         = trim($_GET['token'] ?? '');
$reservationId = intval($_GET['reservation_id'] ?? 0);
$testMode      = ($_GET['test'] ?? '0') === '1';
$cancelUrl     = $_GET['cancel_url'] ?? '/ecoride/View/frontoffice/choix_paiement.php?id=' . $reservationId;
$d17Error      = trim($_GET['error'] ?? '');

if (!$token || !$reservationId) {
    header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
    exit;
}

// Récupérer montant depuis la session D17
$d17Session = $_SESSION['d17_checkout'][$token] ?? null;
$montant    = $d17Session ? (float)$d17Session['montant'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement D17 | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .d17-page {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        /* Header D17 brandé */
        .d17-header {
            background: linear-gradient(135deg, #E30613, #c0000f);
            padding: 1.5rem 2rem;
            text-align: center;
            color: #fff;
        }
        .d17-logo {
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: -1px;
            text-transform: uppercase;
        }
        .d17-logo span { color: #FFD700; }
        .d17-subtitle {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-top: 4px;
        }

        /* Corps */
        .d17-body { padding: 2rem; }

        .merchant-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #E30613;
        }
        .merchant-info .merchant-icon {
            font-size: 2rem;
            color: #E30613;
        }
        .merchant-name { font-weight: bold; font-size: 0.95rem; color: #333; }
        .merchant-amount { font-size: 1.3rem; font-weight: 900; color: #E30613; margin-top: 2px; }

        .form-group { margin-bottom: 1.2rem; }
        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #555;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 0.9rem;
        }
        input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
            background: #fafafa;
        }
        input:focus {
            border-color: #E30613;
            background: #fff;
        }

        .error-box {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            color: #c53030;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: none;
        }
        .error-box.show { display: flex; align-items: center; gap: 8px; }

        .btn-payer {
            width: 100%;
            background: linear-gradient(135deg, #E30613, #c0000f);
            color: #fff;
            border: none;
            padding: 1rem;
            border-radius: 30px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-payer:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(227,6,19,0.35); }
        .btn-payer:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .btn-annuler {
            width: 100%;
            margin-top: 0.8rem;
            background: transparent;
            border: 1px solid #ccc;
            padding: 0.7rem;
            border-radius: 30px;
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-annuler:hover { border-color: #E30613; color: #E30613; }

        .security-line {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.75rem;
            color: #999;
            margin-top: 1.2rem;
        }
        .security-line i { color: #27ae60; }

        /* Badge TEST */
        .test-badge {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: #fff;
            padding: 0.6rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-bottom: 1.2rem;
        }
        .test-badge strong { display: block; margin-bottom: 4px; }
        .test-badge code {
            background: rgba(255,255,255,0.25);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }

        /* Loader */
        .loader { display: none; }
        .loader.show {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.8rem;
        }
        .spinner {
            width: 18px; height: 18px;
            border: 3px solid #eee;
            border-top-color: #E30613;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="d17-page">

    <div class="d17-header">
        <div class="d17-logo">D<span>17</span></div>
        <div class="d17-subtitle">Paiement Mobile Sécurisé — Poste Tunisienne</div>
    </div>

    <div class="d17-body">

        <!-- Infos marchand -->
        <div class="merchant-info">
            <div class="merchant-icon"><i class="fas fa-leaf"></i></div>
            <div>
                <div class="merchant-name">EcoRide — Réservation #<?= $reservationId ?></div>
                <div class="merchant-amount"><?= number_format($montant, 2) ?> DT</div>
            </div>
        </div>

        <?php if ($testMode): ?>
        <div class="test-badge">
            <strong><i class="fas fa-flask"></i> Mode TEST activé</strong>
            Numéros test : <code>20000000</code>, <code>90000000</code>, <code>50000000</code><br>
            Codes secrets test : <code>1234</code>, <code>0000</code>, <code>1111</code>
        </div>
        <?php endif; ?>

        <div class="error-box<?= $d17Error !== '' ? ' show' : '' ?>" id="errorBox">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorMsg"><?= $d17Error !== '' ? htmlspecialchars($d17Error) : '' ?></span>
        </div>

        <form id="d17Form" method="POST"
              action="/ecoride/View/frontoffice/paiement.php?action=traiter_d17">

            <input type="hidden" name="d17_token"      value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="reservation_id" value="<?= $reservationId ?>">

            <div class="form-group">
                <label>Numéro de téléphone D17</label>
                <div class="input-wrap">
                    <i class="fas fa-mobile-alt"></i>
                    <input type="tel" name="d17_phone" id="d17_phone"
                           placeholder="Ex : 20 000 000"
                           maxlength="12" required autocomplete="tel">
                </div>
            </div>

            <div class="form-group">
                <label>Code secret D17</label>
                <div class="input-wrap">
                    <i class="fas fa-key"></i>
                    <input type="password" name="d17_code" id="d17_code"
                           placeholder="••••"
                           maxlength="8" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn-payer" id="btnPayer">
                <i class="fas fa-lock"></i>
                Payer <?= number_format($montant, 2) ?> DT
            </button>

            <div class="loader" id="loader">
                <div class="spinner"></div>
                Vérification en cours…
            </div>

        </form>

        <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn-annuler">
            <i class="fas fa-times"></i> Annuler
        </a>

        <div class="security-line">
            <i class="fas fa-shield-alt"></i>
            Paiement sécurisé — SSL 256 bits
        </div>

    </div>
</div>

<script>
document.getElementById('d17Form').addEventListener('submit', function(e) {
    const phone = document.getElementById('d17_phone').value.replace(/\D/g,'');
    const code  = document.getElementById('d17_code').value.trim();

    if (phone.length < 8) {
        e.preventDefault();
        showError('Numéro de téléphone invalide (minimum 8 chiffres).');
        return;
    }
    if (code.length < 4) {
        e.preventDefault();
        showError('Code secret invalide (minimum 4 chiffres).');
        return;
    }

    document.getElementById('btnPayer').disabled = true;
    document.getElementById('loader').classList.add('show');
});

function showError(msg) {
    const box = document.getElementById('errorBox');
    document.getElementById('errorMsg').textContent = msg;
    box.classList.add('show');
    setTimeout(() => box.classList.remove('show'), 6000);
}
</script>

</body>
</html>
