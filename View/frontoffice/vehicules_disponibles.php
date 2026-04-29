<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';

$vehiculeModel = new VehiculeModel();
$vehicules = $vehiculeModel->getDisponibles();

require __DIR__ . '/vehicules_disponibles_view.php';
?>