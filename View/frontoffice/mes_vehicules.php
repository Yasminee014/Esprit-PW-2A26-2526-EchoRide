<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
require_once __DIR__ . '/../../Controller/VehiculeController.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';

$controller = new VehiculeController();
$vehiculeModel = new VehiculeModel();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$editId = intval($_GET['id'] ?? 0);

// Traitement des formulaires POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'create': $controller->foCreate(); break;
        case 'update': $controller->foUpdate(); break;
        case 'delete': $controller->foDelete(); break;
    }
    exit;
}

// Affichage des pages
if ($action === 'add') {
    // Afficher formulaire d'ajout
    $isEditMode = false;
    require __DIR__ . '/mes_vehicules_form.php';
} 
elseif ($action === 'edit' && $editId > 0) {
    // Afficher formulaire de modification
    $vehicule = $vehiculeModel->getById($editId);
    if ($vehicule && $vehicule['user_id'] == ($_SESSION['user_id'] ?? 0)) {
        $isEditMode = true;
        require __DIR__ . '/mes_vehicules_form.php';
    } else {
        header('Location: mes_vehicules.php');
        exit;
    }
} 
else {
    // Afficher la liste des véhicules
    $controller->mesVehicules();
}
?>