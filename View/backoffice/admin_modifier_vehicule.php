<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/admin_guard.php';
require_once __DIR__ . '/admin_modifier_vehicule_view.php';
?>