<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($reservation) || !isset($vehicule)) {
    header('Location: tous_les_trajets.php');
    exit;
}

$montant = (float)($reservation['prix_total'] ?? 0);
$numResa = $reservation['id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir mode de paiement | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0A1628, #0D1F3A);
            color: #fff;
            min-height: 100vh;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 2rem; }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-header h1 { font-size: 2rem; color: #61B3FA; }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .method-card {
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            text-align: center;
        }
        .method-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.1);
        }
        .method-card.selected {
            border-color: #61B3FA;
            background: rgba(97,179,250,0.1);
        }
        .method-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .method-card.carte .method-icon { color: #0070ba; }
        .method-card.sur_place .method-icon { color: #27ae60; }
        .method-card.virement .method-icon { color: #f1c40f; }
        .method-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .method-desc {
            color: #A7A9AC;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        .btn-valider {
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        .btn-valider:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(25,118,210,0.4);
        }
        .resume-commande {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .error-message {
            display: none;
            margin-top: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 12px;
            background: rgba(231,76,60,0.15);
            color: #f8d7da;
            border: 1px solid rgba(231,76,60,0.35);
        }
        .error-message.show { display: block; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .payment-methods { grid-template-columns: 1fr; }
            .payment-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>

<div class="container">
    
    <div class="page-header">
        <h1><i class="fas fa-credit-card"></i> Choisissez votre mode de paiement</h1>
        <p>Sécurisé et adapté à vos besoins</p>
    </div>
    
    <div class="resume-commande">
        <div>
            <strong>Réservation #<?= $numResa ?></strong><br>
            <small><?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?></small><br>
            <?php if (!empty($trajet) && !empty($trajet['point_depart']) && !empty($trajet['point_arrive'])): ?>
                <small>Trajet : <?= htmlspecialchars($trajet['point_depart']) ?> → <?= htmlspecialchars($trajet['point_arrive']) ?></small><br>
            <?php endif; ?>
            <?php if (!empty($reservation['note'])): ?>
                <small>Destination choisie : <?= htmlspecialchars($reservation['note']) ?></small>
            <?php endif; ?>
        </div>
        <div style="font-size: 1.5rem; font-weight: bold; color: #61B3FA;">
            <?= number_format($montant, 2) ?> DT
        </div>
    </div>
    
    <form method="POST" action="/ecoride/View/frontoffice/paiement.php" id="paymentForm">
        <input type="hidden" name="action" value="traiter_paiement">
        <input type="hidden" name="reservation_id" value="<?= $numResa ?>">
        <input type="hidden" name="mode_paiement" id="modePaiement" value="sur_place">
        
        <div class="payment-methods">
            <div class="method-card carte" data-mode="carte" onclick="selectMethod('carte')">
                <div class="method-icon">
                    <i class="fab fa-cc-visa"></i> 
                    <i class="fab fa-cc-mastercard"></i>
                </div>
                <div class="method-title">💳 Carte bancaire</div>
                <div class="method-desc">Paiement sécurisé via Stripe (Visa, Mastercard)</div>
                <div style="font-size: 0.7rem; color: #27ae60;">🔒 Paiement en Dinar Tunisien (TND)</div>
            </div>
            
            <div class="method-card sur_place" data-mode="sur_place" onclick="selectMethod('sur_place')">
                <div class="method-icon"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="method-title">💵 Paiement sur place</div>
                <div class="method-desc">Vous payez en espèces à la remise du véhicule</div>
                <div style="font-size: 0.7rem; color: #27ae60;">✓ Sans frais supplémentaires</div>
            </div>
            
            <div class="method-card virement" data-mode="d17" onclick="selectMethod('d17')">
                <div class="method-icon"><i class="fas fa-university"></i></div>
                <div class="method-title">📱 D17</div>
                <div class="method-desc">Paiement mobile via D17</div>
                <div style="font-size: 0.7rem; color: #f1c40f;">✓ Confirmation immédiate</div>
            </div>
        </div>

        <button type="submit" class="btn-valider" id="submitBtn" disabled>
            <i class="fas fa-hand-holding-usd"></i> Sélectionnez un mode de paiement
        </button>
    </form>
    
</div>

<script>
function selectMethod(mode) {
    if (mode === 'carte' || mode === 'd17') {
        const reservationId = <?= $numResa ?>;
        window.location.href = '/ecoride/View/frontoffice/paiement.php?action=details_paiement&mode=' + mode + '&id=' + reservationId;
        return;
    }

    document.getElementById('modePaiement').value = mode;
    const cards = document.querySelectorAll('.method-card');
    cards.forEach(card => {
        card.classList.toggle('selected', card.dataset.mode === mode);
    });

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-hand-holding-usd"></i> Confirmer la réservation (paiement sur place)';
}
</script>

</body>
</html>