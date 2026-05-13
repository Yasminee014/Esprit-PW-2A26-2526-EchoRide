<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$montant = (float)($reservation['prix_total'] ?? 0);
$numResa = (int)($reservation['id'] ?? 0);
$isCarte = ($mode === 'carte');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isCarte ? 'Paiement par carte bancaire' : 'Paiement D17' ?> | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628, #0D1F3A);
            color: #fff;
            min-height: 100vh;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem;
        }
        .payment-card {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(97,179,250,0.2);
            border-radius: 20px;
            padding: 2rem;
        }
        .title {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
            color: #61B3FA;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            color: #A7A9AC;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(97,179,250,0.2);
        }
        .resume {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .amount {
            font-size: 1.4rem;
            font-weight: bold;
            color: #61B3FA;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #61B3FA;
            font-size: 0.85rem;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(97,179,250,0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
        }
        input:focus {
            border-color: #61B3FA;
            background: rgba(97,179,250,0.1);
        }
        input::placeholder {
            color: rgba(255,255,255,0.4);
        }
        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 0.8rem;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            font-size: 0.8rem;
            color: #A7A9AC;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn-payer {
            flex: 2;
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            border: none;
            padding: 0.9rem;
            border-radius: 30px;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-payer:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(25,118,210,0.4);
        }
        .btn-retour {
            flex: 1;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 0.9rem;
            border-radius: 30px;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-retour:hover {
            background: rgba(231,76,60,0.2);
            border-color: rgba(231,76,60,0.4);
            color: #e74c3c;
        }
        .card-icons {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            justify-content: center;
        }
        .card-icons i {
            font-size: 2rem;
            opacity: 0.6;
        }
        .error-message {
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.4);
            border-radius: 10px;
            padding: 0.8rem;
            margin-bottom: 1rem;
            color: #e74c3c;
            font-size: 0.85rem;
            display: none;
        }
        .error-message.show {
            display: block;
        }
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .row-2 { grid-template-columns: 1fr; }
            .btn-group { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <div class="payment-card">
        
        <div class="title">
            <i class="<?= $isCarte ? 'fab fa-cc-visa' : 'fas fa-mobile-alt' ?>"></i>
            <?= $isCarte ? 'Paiement par carte bancaire' : 'Paiement mobile D17' ?>
        </div>
        <div class="subtitle">
            <?= $isCarte ? 'Veuillez renseigner les informations de votre carte' : 'Veuillez renseigner vos informations D17' ?>
        </div>
        
        <div class="resume">
            <div>
                <strong>Réservation #<?= $numResa ?></strong><br>
                <small><?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele'] ?? 'Véhicule') ?></small><br>
                <?php if (!empty($trajet) && !empty($trajet['point_depart']) && !empty($trajet['point_arrive'])): ?>
                    <small>Trajet : <?= htmlspecialchars($trajet['point_depart']) ?> → <?= htmlspecialchars($trajet['point_arrive']) ?></small><br>
                <?php endif; ?>
                <?php if (!empty($reservation['note'])): ?>
                    <small>Destination choisie : <?= htmlspecialchars($reservation['note']) ?></small>
                <?php endif; ?>
            </div>
            <div class="amount"><?= number_format($montant, 2) ?> DT</div>
        </div>
        
        <div id="errorMessage" class="error-message"></div>
        
        <form method="POST" action="/ecoride/View/frontoffice/paiement.php" id="paymentDetailsForm">
            <input type="hidden" name="action" value="traiter_paiement">
            <input type="hidden" name="reservation_id" value="<?= $numResa ?>">
            <input type="hidden" name="mode_paiement" value="<?= htmlspecialchars($mode) ?>">
            
            <?php if ($isCarte): ?>
                <!-- Formulaire Carte Bancaire -->
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom du titulaire *</label>
                    <input type="text" name="carte_nom" id="carte_nom" placeholder="Ex: DUPONT" autocomplete="off" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Prénom du titulaire *</label>
                    <input type="text" name="carte_prenom" id="carte_prenom" placeholder="Ex: Jean" autocomplete="off" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-credit-card"></i> Numéro de carte *</label>
                    <input type="text" name="carte_numero" id="carte_numero" maxlength="19" placeholder="1234 5678 9012 3456" autocomplete="off" required>
                </div>
                
                <div class="row-2">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date d'expiration *</label>
                        <input type="text" name="carte_expiration" id="carte_expiration" maxlength="5" placeholder="MM/AA" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Code CVV *</label>
                        <input type="password" name="carte_cvv" id="carte_cvv" maxlength="4" placeholder="123" autocomplete="off" required>
                    </div>
                </div>
                
                <div class="card-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-discover"></i>
                </div>
                
            <?php else: ?>
                <!-- Formulaire D17 -->
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nom *</label>
                    <input type="text" name="d17_nom" id="d17_nom" placeholder="Votre nom" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Prénom *</label>
                    <input type="text" name="d17_prenom" id="d17_prenom" placeholder="Votre prénom" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Numéro de téléphone *</label>
                    <input type="tel" name="d17_telephone" id="d17_telephone" maxlength="15" placeholder="20 123 456" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Code D17 *</label>
                    <input type="password" name="d17_code" id="d17_code" maxlength="8" placeholder="Votre code secret" required>
                </div>
            <?php endif; ?>
            
            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <span>Paiement 100% sécurisé</span>
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <div class="btn-group">
                <a href="/ecoride/View/frontoffice/choix_paiement.php?id=<?= $numResa ?>" class="btn-retour">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <button type="submit" class="btn-payer" id="submitBtn">
                    <i class="fas fa-lock"></i> 
                    <?= $isCarte ? 'Payer ' . number_format($montant, 2) . ' DT' : 'Confirmer le paiement D17' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Formatage automatique du numéro de carte
const carteNumero = document.getElementById('carte_numero');
if (carteNumero) {
    carteNumero.addEventListener('input', function(e) {
        let value = this.value.replace(/\s/g, '');
        if (value.length > 16) value = value.slice(0, 16);
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += value[i];
        }
        this.value = formatted;
    });
}

// Formatage automatique de la date d'expiration
const carteExpiration = document.getElementById('carte_expiration');
if (carteExpiration) {
    carteExpiration.addEventListener('input', function(e) {
        let value = this.value.replace(/\//g, '');
        if (value.length > 4) value = value.slice(0, 4);
        if (value.length >= 3) {
            this.value = value.slice(0, 2) + '/' + value.slice(2);
        } else {
            this.value = value;
        }
    });
}

// Validation du formulaire
const form = document.getElementById('paymentDetailsForm');
const errorDiv = document.getElementById('errorMessage');

function showError(message) {
    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
    errorDiv.classList.add('show');
    setTimeout(() => {
        errorDiv.classList.remove('show');
    }, 5000);
}

if (form) {
    form.addEventListener('submit', function(e) {
        const mode = '<?= $mode ?>';
        
        if (mode === 'carte') {
            const nom = document.getElementById('carte_nom')?.value.trim();
            const prenom = document.getElementById('carte_prenom')?.value.trim();
            const numero = document.getElementById('carte_numero')?.value.replace(/\s/g, '');
            const expiration = document.getElementById('carte_expiration')?.value.trim();
            const cvv = document.getElementById('carte_cvv')?.value.trim();
            
            if (!nom) {
                e.preventDefault();
                showError('Veuillez saisir le nom du titulaire');
                return;
            }
            if (!prenom) {
                e.preventDefault();
                showError('Veuillez saisir le prénom du titulaire');
                return;
            }
            if (!numero || numero.length < 13 || numero.length > 19) {
                e.preventDefault();
                showError('Numéro de carte invalide (13 à 19 chiffres)');
                return;
            }
            if (!expiration || !/^\d{2}\/\d{2}$/.test(expiration)) {
                e.preventDefault();
                showError('Date d\'expiration invalide (format MM/AA)');
                return;
            }
            if (!cvv || cvv.length < 3 || cvv.length > 4) {
                e.preventDefault();
                showError('Code CVV invalide (3 ou 4 chiffres)');
                return;
            }
        }
        
        if (mode === 'd17') {
            const nom = document.getElementById('d17_nom')?.value.trim();
            const prenom = document.getElementById('d17_prenom')?.value.trim();
            const telephone = document.getElementById('d17_telephone')?.value.trim();
            const code = document.getElementById('d17_code')?.value.trim();
            
            if (!nom) {
                e.preventDefault();
                showError('Veuillez saisir votre nom');
                return;
            }
            if (!prenom) {
                e.preventDefault();
                showError('Veuillez saisir votre prénom');
                return;
            }
            if (!telephone || telephone.length < 8) {
                e.preventDefault();
                showError('Numéro de téléphone invalide');
                return;
            }
            if (!code || code.length < 4) {
                e.preventDefault();
                showError('Code D17 invalide (minimum 4 caractères)');
                return;
            }
        }
    });
}
</script>

</body>
</html>