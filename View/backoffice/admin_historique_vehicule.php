<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Force le mode admin pour test (à supprimer en production)
$_SESSION['is_admin'] = true;
$_SESSION['user_id'] = 1;

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/admin_historique_vehicule_view.php';
?>