<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth_guard.php';

require_once __DIR__ . '/../../Controller/ReservationController.php';

$controller = new ReservationController();

// Pour les requêtes AJAX (appel API)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    $result = $controller->foCreate();
    echo json_encode($result);
    exit;
}

// Pour les requêtes normales (affichage du formulaire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->foCreate();
    $_SESSION['reservation_result'] = $result;
    $vehiculeId = intval($_POST['vehicule_id'] ?? 0);
    header('Location: reserver_vehicule.php?vehicule_id=' . $vehiculeId);
    exit;
} else {
    $controller->showReservationForm();
}
?>