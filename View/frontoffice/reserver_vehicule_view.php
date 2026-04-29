<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';

$vehicule = $vehicule ?? null;
if (!$vehicule) {
    header('Location: vehicules_disponibles.php');
    exit;
}

$db = Database::getInstance();
$trajets = $db->query("
    SELECT t.*, u.nom as conducteur_nom, u.prenom as conducteur_prenom
    FROM trajet t
    LEFT JOIN users u ON t.id_u = u.id
    ORDER BY t.id_T DESC
")->fetchAll();

$capaciteMax = $vehicule['capacite'] ?? 4;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation sécurisée | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0A1628, #0D1F3A);
            color: #fff;
            min-height: 100vh;
        }
        
        .reservation-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(97,179,250,0.2);
            z-index: 1;
        }
        
        .step {
            text-align: center;
            z-index: 2;
            background: #0A1628;
            padding: 0 1rem;
        }
        
        .step .step-number {
            width: 60px;
            height: 60px;
            background: rgba(97,179,250,0.1);
            border: 2px solid rgba(97,179,250,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .step.active .step-number {
            background: #1976D2;
            border-color: #61B3FA;
            color: white;
        }
        
        .step.completed .step-number {
            background: #27ae60;
            border-color: #27ae60;
            color: white;
        }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 2rem;
        }
        
        .vehicle-card, .payment-card {
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(97,179,250,0.2);
        }
        
        .card-header {
            background: linear-gradient(135deg, #1976D2, #0F3B6E);
            padding: 1rem 1.5rem;
            font-weight: bold;
        }
        
        .vehicle-image {
            height: 200px;
            overflow: hidden;
        }
        
        .vehicle-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .vehicle-info {
            padding: 1.5rem;
        }
        
        .vehicle-info h2 {
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }
        
        .info-tag {
            display: inline-block;
            background: rgba(255,255,255,0.08);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-right: 0.5rem;
            margin-top: 0.5rem;
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
        
        input, select {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(97,179,250,0.3);
            border-radius: 12px;
            color: #fff;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            border-color: #61B3FA;
            background: rgba(97,179,250,0.1);
        }
        
        .prix-card {
            background: linear-gradient(135deg, #2e7d32, #4caf50);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            margin: 1rem 0;
        }
        
        .prix-card .prix-value {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .btn-paiement {
            width: 100%;
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            border: none;
            padding: 1rem;
            border-radius: 30px;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-paiement:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(25,118,210,0.4);
        }
        
        .btn-paiement:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            padding: 0.8rem;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            font-size: 0.8rem;
            flex-wrap: wrap;
        }
        
        .error-message {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.4);
            border-radius: 12px;
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
            .main-grid { grid-template-columns: 1fr; }
            .progress-steps { flex-direction: column; gap: 1rem; }
            .progress-steps::before { display: none; }
            .reservation-container { padding: 0 1rem; }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/navbar_moderne.php'; ?>

<div class="reservation-container">
    
    <!-- Étapes de réservation -->
    <div class="progress-steps">
        <div class="step completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
            <div>Choix véhicule</div>
        </div>
        <div class="step active">
            <div class="step-number">2</div>
            <div>Réservation</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div>Paiement</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div>Confirmation</div>
        </div>
    </div>
    
    <div class="main-grid">
        <!-- Colonne gauche : Infos véhicule -->
        <div class="vehicle-card">
            <div class="card-header">
                <i class="fas fa-car"></i> Votre véhicule
            </div>
            <div class="vehicle-image">
                <?php 
                $photoPath = '/ecoride/assets/uploads/vehicules/' . ($vehicule['photo'] ?? '');
                if (!empty($vehicule['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $photoPath)): ?>
                    <img src="<?= $photoPath ?>" alt="Véhicule">
                <?php else: ?>
                    <div style="height:100%; background:linear-gradient(135deg,#1976D2,#0F3B6E); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-car" style="font-size: 4rem; opacity:0.5;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="vehicle-info">
                <h2><?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?></h2>
                <p><i class="fas fa-user"></i> <?= htmlspecialchars(($vehicule['prenom'] ?? '') . ' ' . ($vehicule['nom'] ?? '')) ?></p>
                <div style="margin-top: 1rem;">
                    <span class="info-tag"><i class="fas fa-id-card"></i> <?= htmlspecialchars($vehicule['immatriculation']) ?></span>
                    <span class="info-tag"><i class="fas fa-users"></i> <?= $vehicule['capacite'] ?> places</span>
                    <span class="info-tag"><?= $vehicule['climatisation'] ? '<i class="fas fa-snowflake"></i> Clim' : '<i class="fas fa-sun"></i> Sans clim' ?></span>
                </div>
            </div>
        </div>
        
        <!-- Colonne droite : Formulaire -->
        <div class="payment-card">
            <div class="card-header">
                <i class="fas fa-file-signature"></i> Détails de la réservation
            </div>
            
            <div id="errorMessage" class="error-message"></div>
            
            <form id="reservationForm" style="padding: 1.5rem;">
                <input type="hidden" name="action" value="reserver">
                <input type="hidden" name="vehicule_id" value="<?= $vehicule['id'] ?>">
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Destination *</label>
                    <select name="trajet_id" id="trajetSelect" required>
                        <option value="">-- Sélectionnez une destination --</option>
                        <?php foreach ($trajets as $trajet): ?>
                        <option value="<?= $trajet['id_T'] ?>" data-prix="<?= $trajet['prix_total'] ?? $trajet['prix'] ?? 0 ?>">
                            🚩 <?= htmlspecialchars($trajet['point_depart'] ?? '?') ?> → 🏁 <?= htmlspecialchars($trajet['point_arrive'] ?? '?') ?> 
                            (<?= number_format($trajet['prix_total'] ?? $trajet['prix'] ?? 0, 2) ?> DT)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Nombre de places *</label>
                    <input type="number" name="nb_places" id="nbPlaces" min="1" max="<?= $capaciteMax ?>" value="1" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date de début *</label>
                    <input type="date" name="date_debut" id="dateDebut" min="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date de fin *</label>
                    <input type="date" name="date_fin" id="dateFin" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Heure de prise en charge *</label>
                    <input type="time" name="heure" id="heure" value="09:00" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> Note (optionnel)</label>
                    <input type="text" name="note" placeholder="Informations supplémentaires...">
                </div>
                
                <!-- Affichage du prix -->
                <div class="prix-card">
                    <div style="opacity:0.8;">Total à payer</div>
                    <div class="prix-value" id="prixDisplay">0.00 DT</div>
                    <div style="font-size:0.7rem;">TTC - Paiement sécurisé</div>
                </div>
                
                <!-- Bouton Continuer vers paiement -->
                <button type="submit" class="btn-paiement" id="submitBtn">
                    <i class="fas fa-credit-card"></i> Continuer vers paiement
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="security-badge">
                    <i class="fas fa-lock"></i>
                    <span>Paiement 100% sécurisé</span>
                    <i class="fas fa-hand-holding-usd"></i>
                    <i class="fas fa-university"></i>
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-paypal"></i>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mise à jour dynamique du prix
const trajetSelect = document.getElementById('trajetSelect');
const nbPlacesInput = document.getElementById('nbPlaces');
const prixDisplay = document.getElementById('prixDisplay');
let prixTrajetBase = 0;

function calculerPrixTotal() {
    const nbPlaces = parseInt(nbPlacesInput.value) || 1;
    if (prixTrajetBase <= 0) {
        prixDisplay.innerHTML = '0.00 DT';
        return;
    }
    const total = prixTrajetBase * nbPlaces;
    prixDisplay.innerHTML = total.toFixed(2) + ' DT';
}

if (trajetSelect) {
    trajetSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        prixTrajetBase = parseFloat(selectedOption.dataset.prix) || 0;
        calculerPrixTotal();
    });
}

if (nbPlacesInput) {
    nbPlacesInput.addEventListener('input', calculerPrixTotal);
}

// Validation date fin >= date début
const dateDebut = document.getElementById('dateDebut');
const dateFin = document.getElementById('dateFin');

if (dateDebut) {
    dateDebut.addEventListener('change', function() {
        if (dateFin) {
            dateFin.min = this.value;
            if (dateFin.value < this.value) {
                dateFin.value = this.value;
            }
        }
    });
}

// Afficher une erreur
function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
        errorDiv.classList.add('show');
        setTimeout(() => {
            errorDiv.classList.remove('show');
        }, 5000);
    }
}

// Soumission AJAX vers le contrôleur
const reservationForm = document.getElementById('reservationForm');
if (reservationForm) {
    reservationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!trajetSelect || !trajetSelect.value) {
            showError('Veuillez sélectionner une destination.');
            return;
        }
        
        if (!dateDebut || !dateDebut.value) {
            showError('Veuillez sélectionner une date de début.');
            return;
        }
        
        if (!dateFin || !dateFin.value) {
            showError('Veuillez sélectionner une date de fin.');
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
        
        const formData = new FormData(this);
        
        try {
            // ✅ URL CORRIGÉE - appelle l'API dédiée
            const response = await fetch('/ecoride/View/frontoffice/reservation_api.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Rediriger vers le choix du mode de paiement
                window.location.href = '/ecoride/View/frontoffice/choix_paiement.php?id=' + result.reservation_id;
            } else {
                showError(result.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-credit-card"></i> Continuer vers paiement <i class="fas fa-arrow-right"></i>';
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError('Erreur technique. Veuillez réessayer.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-credit-card"></i> Continuer vers paiement <i class="fas fa-arrow-right"></i>';
        }
    });
}

// Initialiser le prix si une destination est pré-sélectionnée
if (trajetSelect && trajetSelect.value) {
    const selectedOption = trajetSelect.options[trajetSelect.selectedIndex];
    prixTrajetBase = parseFloat(selectedOption.dataset.prix) || 0;
    calculerPrixTotal();
}

// Thème clair/sombre
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
}
</script>

</body>
</html>