<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Controller/ReservationController.php';
$controller = new ReservationController();

$action = $_POST['action'] ?? '';
if ($action === 'reserver') {
    $controller->foCreate();
} else {
    $controller->vehiculesDisponibles();
}
?>
