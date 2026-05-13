<?php
/**
 * auth_guard.php
 * Inclure ce fichier en haut de chaque page protégée.
 * Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /ecoride/View/frontoffice/login.php?show=showLogin');
    exit();
}
