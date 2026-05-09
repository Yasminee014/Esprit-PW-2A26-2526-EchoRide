<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Config/Database.php';

$vehicule = $vehicule ?? null;
if (!$vehicule) {
    header('Location: tous_les_trajets.php');
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
$selectedTrajetId = (int)($_GET['trajet_id'] ?? 0);
$destinationPrix = (float)($_GET['destination_prix'] ?? 0);
$selectedDestinationName = trim((string)($_GET['destination_nom'] ?? ''));
$selectedTrajet = null;
foreach ($trajets as $t) {
    if ((int)($t['id_T'] ?? 0) === $selectedTrajetId) {
        $selectedTrajet = $t;
        break;
    }
}
$selectedTrajetPrix = (float)($selectedTrajet['prix_total'] ?? $selectedTrajet['prix'] ?? 0);
$prixUtilise = $destinationPrix > 0 ? $destinationPrix : $selectedTrajetPrix;
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
        
        /* Validation styles */
        input.v-ok {
            border-color: #27ae60;
            background: rgba(39,174,96,0.1);
        }
        input.v-error {
            border-color: #e74c3c;
            background: rgba(231,76,60,0.1);
        }
        .error-msg {
            display: none;
            font-size: 0.7rem;
            color: #e74c3c;
            margin-top: 0.3rem;
            padding-left: 0.5rem;
        }
        .error-msg.show {
            display: block;
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

        .trajet-selector {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(97,179,250,0.25);
            border-radius: 12px;
            padding: 0.9rem;
        }
        .trajet-btn {
            width: 100%;
            background: rgba(25,118,210,0.25);
            border: 1px solid rgba(97,179,250,0.45);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.25s;
        }
        .trajet-btn:hover { background: rgba(25,118,210,0.4); }
        .trajet-selected {
            margin-top: 0.8rem;
            font-size: 0.82rem;
            color: #d8e6f8;
            line-height: 1.45;
        }
        
        .btn-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .btn-cancel-reservation {
            flex: 1;
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.4);
            padding: 1rem;
            border-radius: 30px;
            color: #e74c3c;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        .btn-cancel-reservation:hover {
            background: rgba(231,76,60,0.25);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .main-grid { grid-template-columns: 1fr; }
            .progress-steps { flex-direction: column; gap: 1rem; }
            .progress-steps::before { display: none; }
            .reservation-container { padding: 0 1rem; }
            .btn-actions { flex-direction: column; }
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
            <div>Choix trajets</div>
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
                <i class="fas fa-car"></i> Véhicule réservée
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
                <input type="hidden" name="trajet_id" id="trajetId" value="<?= $selectedTrajetId ?>">
                <input type="hidden" name="prix_trajet" id="prixTrajetInput" value="<?= $prixUtilise ?>">
                <input type="hidden" name="destination_nom" value="<?= htmlspecialchars($selectedDestinationName) ?>">
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Destination *</label>
                    <div class="trajet-selector">
                        <?php if (!$selectedTrajet): ?>
                            <a class="trajet-btn" href="tous_les_trajets.php?from=reserver_vehicule&vehicule_id=<?= (int)$vehicule['id'] ?>">
                                <i class="fas fa-route"></i> Tous les trajets
                            </a>
                        <?php endif; ?>
                        <div class="trajet-selected" id="trajetSelectedText">
                            <?php if ($selectedTrajet): ?>
                                <?php if ($selectedDestinationName !== ''): ?>
                                    Destination sélectionnée : <?= htmlspecialchars($selectedDestinationName) ?>
                                    (<?= number_format($prixUtilise, 2) ?> DT)
                                <?php else: ?>
                                    Trajet sélectionné: <?= htmlspecialchars($selectedTrajet['point_depart'] ?? '?') ?> -> <?= htmlspecialchars($selectedTrajet['point_arrive'] ?? '?') ?>
                                    (<?= number_format($prixUtilise, 2) ?> DT)
                                <?php endif; ?>
                            <?php else: ?>
                                Aucun trajet sélectionné. Cliquez sur "Tous les trajets" pour choisir.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Nombre de places *</label>
                    <input type="number" name="nb_places" id="nbPlaces" min="1" max="<?= $capaciteMax ?>" value="1" required>
                    <div class="error-msg" id="error-nbPlaces">Le nombre de places doit être entre 1 et <?= $capaciteMax ?></div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date de début *</label>
                    <input type="date" name="date_debut" id="dateDebut" min="<?= date('Y-m-d') ?>" required>
                    <div class="error-msg" id="error-dateDebut">Veuillez sélectionner une date de début valide</div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Date de fin *</label>
                    <input type="date" name="date_fin" id="dateFin" required>
                    <div class="error-msg" id="error-dateFin">La date de fin doit être postérieure à la date de début</div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Heure de prise en charge *</label>
                    <input type="time" name="heure" id="heure" value="09:00" required>
                    <div class="error-msg" id="error-heure">Veuillez sélectionner une heure valide</div>
                </div>
                
                <!-- Affichage du prix -->
                <div class="prix-card">
                    <div style="opacity:0.8;">Total à payer</div>
                    <div class="prix-value" id="prixDisplay"><?= number_format($prixUtilise, 2) ?> DT</div>
                    <div style="font-size:0.7rem;">TTC - Paiement sécurisé</div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="btn-actions">
                    <a href="tous_les_trajets.php" class="btn-cancel-reservation">
                        <i class="fas fa-times-circle"></i> Annuler
                    </a>
                    <button type="submit" class="btn-paiement" id="submitBtn">
                        <i class="fas fa-credit-card"></i> Continuer vers paiement
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                
                <div class="security-badge">
                    <i class="fas fa-lock"></i>
                    <span>Paiement 100% sécurisé</span>
                    <i class="fas fa-hand-holding-usd"></i>
                    <i class="fas fa-university"></i>
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════
// VALIDATION EN TEMPS RÉEL
// ═══════════════════════════════════════════════════════════

const capaciteMax = <?= (int)$capaciteMax ?>;
const prixTrajetBase = parseFloat('<?= $prixUtilise ?>');

// Références aux éléments
const nbPlacesInput = document.getElementById('nbPlaces');
const dateDebut = document.getElementById('dateDebut');
const dateFin = document.getElementById('dateFin');
const heureInput = document.getElementById('heure');
const prixDisplay = document.getElementById('prixDisplay');
const submitBtn = document.getElementById('submitBtn');
const reservationForm = document.getElementById('reservationForm');

// Références aux messages d'erreur
const errorNbPlaces = document.getElementById('error-nbPlaces');
const errorDateDebut = document.getElementById('error-dateDebut');
const errorDateFin = document.getElementById('error-dateFin');
const errorHeure = document.getElementById('error-heure');

// ═══════════════════════════════════════════════════════════
// FONCTIONS DE VALIDATION
// ═══════════════════════════════════════════════════════════

function validateNbPlaces() {
    const value = parseInt(nbPlacesInput.value, 10);
    let isValid = true;
    let message = '';
    
    if (isNaN(value)) {
        isValid = false;
        message = 'Veuillez entrer un nombre valide';
    } else if (value < 1) {
        isValid = false;
        message = 'Le nombre de places doit être au moins 1';
    } else if (value > capaciteMax) {
        isValid = false;
        message = 'Le nombre de places ne peut pas dépasser ' + capaciteMax;
    }
    
    if (!isValid) {
        nbPlacesInput.classList.add('v-error');
        nbPlacesInput.classList.remove('v-ok');
        errorNbPlaces.textContent = message;
        errorNbPlaces.classList.add('show');
    } else {
        nbPlacesInput.classList.remove('v-error');
        nbPlacesInput.classList.add('v-ok');
        errorNbPlaces.classList.remove('show');
    }
    
    return isValid;
}

function validateDateDebut() {
    const value = dateDebut.value;
    let isValid = true;
    
    if (!value) {
        isValid = false;
        errorDateDebut.textContent = 'Veuillez sélectionner une date de début';
        errorDateDebut.classList.add('show');
    } else {
        const selectedDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            isValid = false;
            errorDateDebut.textContent = 'La date ne peut pas être dans le passé';
            errorDateDebut.classList.add('show');
        } else {
            errorDateDebut.classList.remove('show');
        }
    }
    
    if (!isValid) {
        dateDebut.classList.add('v-error');
        dateDebut.classList.remove('v-ok');
    } else {
        dateDebut.classList.remove('v-error');
        dateDebut.classList.add('v-ok');
    }
    
    // Revalider dateFin car elle dépend de dateDebut
    if (dateFin.value) {
        validateDateFin();
    }
    calculerPrixTotal();
    
    return isValid;
}

function validateDateFin() {
    const value = dateFin.value;
    let isValid = true;
    
    if (!value) {
        isValid = false;
        errorDateFin.textContent = 'Veuillez sélectionner une date de fin';
        errorDateFin.classList.add('show');
    } else {
        const debut = dateDebut.value ? new Date(dateDebut.value) : null;
        const fin = new Date(value);
        
        if (debut && fin < debut) {
            isValid = false;
            errorDateFin.textContent = 'La date de fin doit être postérieure à la date de début';
            errorDateFin.classList.add('show');
        } else {
            errorDateFin.classList.remove('show');
        }
    }
    
    if (!isValid) {
        dateFin.classList.add('v-error');
        dateFin.classList.remove('v-ok');
    } else {
        dateFin.classList.remove('v-error');
        dateFin.classList.add('v-ok');
    }
    
    calculerPrixTotal();
    return isValid;
}

function validateHeure() {
    const value = heureInput.value;
    let isValid = true;
    
    if (!value) {
        isValid = false;
        errorHeure.textContent = 'Veuillez sélectionner une heure';
        errorHeure.classList.add('show');
    } else if (!/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(value)) {
        isValid = false;
        errorHeure.textContent = 'Format d\'heure invalide';
        errorHeure.classList.add('show');
    } else {
        errorHeure.classList.remove('show');
    }
    
    if (!isValid) {
        heureInput.classList.add('v-error');
        heureInput.classList.remove('v-ok');
    } else {
        heureInput.classList.remove('v-error');
        heureInput.classList.add('v-ok');
    }
    
    return isValid;
}

function validateTrajet() {
    const trajetId = document.getElementById('trajetId');
    if (!trajetId || !trajetId.value) {
        return false;
    }
    return true;
}

// ═══════════════════════════════════════════════════════════
// CALCUL DU PRIX TOTAL
// ═══════════════════════════════════════════════════════════

function parseDate(value) {
    if (!value) return null;
    const date = new Date(value);
    return isNaN(date.getTime()) ? null : date;
}

function calculerPrixTotal() {
    const start = dateDebut?.valueAsDate ?? parseDate(dateDebut?.value);
    const end = dateFin?.valueAsDate ?? parseDate(dateFin?.value);
    
    if (!start || !end || prixTrajetBase <= 0 || end < start) {
        prixDisplay.innerHTML = '0.00 DT';
        return;
    }
    
    const diffTime = end - start;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
    const total = prixTrajetBase * diffDays;
    prixDisplay.innerHTML = total.toFixed(2) + ' DT';
}

// ═══════════════════════════════════════════════════════════
// VALIDATION GLOBALE
// ═══════════════════════════════════════════════════════════

function validateAllFields() {
    const isNbPlacesValid = validateNbPlaces();
    const isDateDebutValid = validateDateDebut();
    const isDateFinValid = validateDateFin();
    const isHeureValid = validateHeure();
    const isTrajetValid = validateTrajet();
    
    const allValid = isNbPlacesValid && isDateDebutValid && isDateFinValid && isHeureValid && isTrajetValid;
    
    // Le bouton reste toujours actif, la validation bloque à la soumission
    return allValid;
}

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

// ═══════════════════════════════════════════════════════════
// INITIALISATION DES ÉCOUTEURS
// ═══════════════════════════════════════════════════════════

// Validation en temps réel
nbPlacesInput?.addEventListener('input', () => {
    validateNbPlaces();
    calculerPrixTotal();
});
nbPlacesInput?.addEventListener('blur', validateNbPlaces);

dateDebut?.addEventListener('change', () => {
    validateDateDebut();
    if (dateFin) {
        dateFin.min = dateDebut.value;
        if (dateFin.value && dateFin.value < dateDebut.value) {
            dateFin.value = dateDebut.value;
        }
        validateDateFin();
    }
});
dateDebut?.addEventListener('blur', validateDateDebut);

dateFin?.addEventListener('change', validateDateFin);
dateFin?.addEventListener('blur', validateDateFin);

heureInput?.addEventListener('input', validateHeure);
heureInput?.addEventListener('blur', validateHeure);

// ═══════════════════════════════════════════════════════════
// SOUMISSION DU FORMULAIRE
// ═══════════════════════════════════════════════════════════

if (reservationForm) {
    reservationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Valider tous les champs avant soumission
        const isNbPlacesValid = validateNbPlaces();
        const isDateDebutValid = validateDateDebut();
        const isDateFinValid = validateDateFin();
        const isHeureValid = validateHeure();
        const isTrajetValid = validateTrajet();
        
        if (!isTrajetValid) {
            showError('Veuillez sélectionner un trajet en cliquant sur "Tous les trajets"');
            return;
        }
        
        if (!isNbPlacesValid || !isDateDebutValid || !isDateFinValid || !isHeureValid) {
            showError('Veuillez corriger les erreurs dans le formulaire');
            
            // Faire défiler jusqu'au premier champ en erreur
            const firstError = document.querySelector('.v-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
            return;
        }
        
        // Désactiver le bouton pendant la soumission
        const submitBtnEl = document.getElementById('submitBtn');
        submitBtnEl.disabled = true;
        submitBtnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours...';
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('reservation_api.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = 'choix_paiement.php?id=' + result.reservation_id;
            } else {
                showError(result.message);
                submitBtnEl.disabled = false;
                submitBtnEl.innerHTML = '<i class="fas fa-credit-card"></i> Continuer vers paiement <i class="fas fa-arrow-right"></i>';
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError('Erreur technique. Veuillez réessayer.');
            submitBtnEl.disabled = false;
            submitBtnEl.innerHTML = '<i class="fas fa-credit-card"></i> Continuer vers paiement <i class="fas fa-arrow-right"></i>';
        }
    });
}

// Initialiser les contraintes de dates
if (dateDebut) {
    dateDebut.min = new Date().toISOString().split('T')[0];
    dateDebut.addEventListener('change', function() {
        if (dateFin) {
            dateFin.min = this.value;
            if (dateFin.value && dateFin.value < this.value) {
                dateFin.value = this.value;
                validateDateFin();
            }
        }
    });
}

// Valider au chargement
setTimeout(() => {
    validateAllFields();
    calculerPrixTotal();
}, 100);

// Thème clair/sombre
if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode');
}
</script>

</body>
</html>