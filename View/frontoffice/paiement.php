<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../../Controller/PaiementController.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$controller = new PaiementController();

switch ($action) {
    case 'details_paiement':
        $controller->detailsPaiement();
        break;
    case 'traiter_paiement':
        $controller->traiterPaiement();
        break;
    case 'traiter_d17':
        $controller->traiterD17();
        break;
    case 'upload_justificatif':
        $controller->uploadJustificatif();
        break;
    default:
        $controller->choixPaiement();
        break;
}
?>