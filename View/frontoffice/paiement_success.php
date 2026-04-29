<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../Controller/PaiementController.php';

$controller = new PaiementController();
$controller->paiementSuccess();
?>