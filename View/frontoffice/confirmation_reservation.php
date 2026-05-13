<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../../Model/ReservationModel.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';

$reservationId = intval($_GET['id'] ?? 0);
$reservationModel = new ReservationModel();
$vehiculeModel = new VehiculeModel();

$reservation = $reservationModel->getById($reservationId);
if (!$reservation || $reservation['user_id'] != ($_SESSION['user_id'] ?? 0)) {
    header('Location: tous_les_trajets.php');
    exit;
}

$vehicule = $vehiculeModel->getById($reservation['vehicule_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation | EcoRide</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0A1628, #0D1F3A);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .confirmation-card {
            background: rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            text-align: center;
            border: 1px solid rgba(97,179,250,0.3);
        }
        .success-icon {
            font-size: 5rem;
            color: #27ae60;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #1976D2, #61B3FA);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>✅ Réservation confirmée !</h2>
        <p>Votre paiement a été accepté.</p>
        <div style="margin: 1rem 0; padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 12px;">
            <strong>Réservation #<?= $reservation['id'] ?></strong><br>
            <?= htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']) ?><br>
            <?= date('d/m/Y', strtotime($reservation['date_debut'])) ?> → <?= date('d/m/Y', strtotime($reservation['date_fin'])) ?>
        </div>
        <a href="tous_les_trajets.php" class="btn">Voir les covoiturages</a>
    </div>
</body>
</html>