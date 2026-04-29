<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/PaiementController.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

$controller = new PaiementController();

switch ($action) {
    case 'traiter_paiement':
        $controller->traiterPaiement();
        break;
    case 'upload_justificatif':
        $controller->uploadJustificatif();
        break;
    default:
        $controller->choixPaiement();
        break;
}
?>