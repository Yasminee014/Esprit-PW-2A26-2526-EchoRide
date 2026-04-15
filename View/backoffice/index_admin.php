<?php
// ════════════════════════════════════════════
// Point d'entrée BackOffice : admin_reclamations.php
// À placer dans : View/backoffice/admin_reclamations.php
// ════════════════════════════════════════════
session_start();

// Sécurité : vérification admin
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../Controller/ReclamationController.php';

$controller = new ReclamationController();
$controller->handleRequest();
