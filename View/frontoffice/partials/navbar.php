<?php
/**
 * partials/navbar.php
 * Navbar publique : affiche profil si connecté, sinon Se connecter / S'inscrire
 * Délègue vers navbar_moderne.php si connecté, navbar.php si non connecté
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 3) . '/config.php';
}

if (isset($_SESSION['user_id'])) {
    include_once __DIR__ . '/../includes/navbar_moderne.php';
} else {
    include_once __DIR__ . '/../navbar.php';
}
