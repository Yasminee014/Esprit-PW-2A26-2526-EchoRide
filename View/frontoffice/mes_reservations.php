<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Controller/ReservationController.php';
$controller = new ReservationController();

$action = $_POST['action'] ?? '';

match ($action) {
    'annuler'   => $controller->foAnnuler(),
    'supprimer' => $controller->foSupprimer(),
    'modifier'  => $controller->foModifier(),
    default     => $controller->mesReservations(),
};
?>
