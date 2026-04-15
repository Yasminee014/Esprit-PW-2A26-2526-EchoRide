<?php
// Pas de texte ici
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/../../Controller/VehiculeController.php';

$controller = new VehiculeController();
$controller->adminIndex();
?>