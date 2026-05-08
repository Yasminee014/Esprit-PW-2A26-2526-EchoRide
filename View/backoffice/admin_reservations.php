<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../../Controller/ReservationController.php';

$controller = new ReservationController();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
switch ($action) {
    case 'update_statut': $controller->adminUpdateStatut(); break;
    case 'delete':        $controller->adminDelete();       break;
    default:              $controller->adminIndex();         break;
}
