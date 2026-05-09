<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../../Controller/VehiculeController.php';
require_once __DIR__ . '/../../Model/VehiculeModel.php';
require_once __DIR__ . '/../../Config/Database.php';

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
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id_T, point_depart, point_arrive FROM trajet WHERE id_u = :uid ORDER BY id_T DESC");
    $stmt->execute([':uid' => ($_SESSION['user_id'] ?? 0)]);
    $mesTrajets = $stmt->fetchAll();
    if (empty($mesTrajets)) {
        // Fallback pour anciens trajets crees sans id_u
        $mesTrajets = $db->query("SELECT id_T, point_depart, point_arrive FROM trajet ORDER BY id_T DESC")->fetchAll();
    }
    require __DIR__ . '/mes_vehicules_form.php';
} 
elseif ($action === 'edit' && $editId > 0) {
    // Afficher formulaire de modification
    $vehicule = $vehiculeModel->getById($editId);
    if ($vehicule && $vehicule['user_id'] == ($_SESSION['user_id'] ?? 0)) {
        $isEditMode = true;
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id_T, point_depart, point_arrive FROM trajet WHERE id_u = :uid ORDER BY id_T DESC");
        $stmt->execute([':uid' => ($_SESSION['user_id'] ?? 0)]);
        $mesTrajets = $stmt->fetchAll();
        if (empty($mesTrajets)) {
            // Fallback pour anciens trajets crees sans id_u
            $mesTrajets = $db->query("SELECT id_T, point_depart, point_arrive FROM trajet ORDER BY id_T DESC")->fetchAll();
        }
        require __DIR__ . '/mes_vehicules_form.php';
    } else {
        header('Location: mes_vehicules.php');
        exit;
    }
} 
elseif ($action === 'details' && $editId > 0) {
    // 🚗 PAGE DÉTAILS COMPLÈTE AVEC HEADER MODERNE
    $vehicule = $vehiculeModel->getById($editId);
    if ($vehicule && $vehicule['user_id'] == ($_SESSION['user_id'] ?? 0)) {
        require __DIR__ . '/mes_vehicules_details.php';
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