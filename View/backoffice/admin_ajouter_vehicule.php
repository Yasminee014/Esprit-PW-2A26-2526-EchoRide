<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// FORCER LE MODE ADMIN POUR TEST (À ENLEVER APRÈS)
$_SESSION['is_admin'] = true;
$_SESSION['user_id'] = 1;
// FIN TEST

require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/admin_ajouter_vehicule_view.php';
?>