<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../Controller/ReservationController.php';

$controller = new ReservationController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->foCreate();  // Appelle foCreate() au lieu de foCreateAPI()
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>