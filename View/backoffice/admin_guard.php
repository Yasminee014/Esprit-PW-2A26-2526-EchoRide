<?php
/**
 * Protection des pages admin.
 * À inclure en haut de chaque fichier backoffice.
 * Redirige vers le frontoffice si l'utilisateur n'est pas admin.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// ⚠️ POUR LE DÉVELOPPEMENT UNIQUEMENT - À SUPPRIMER EN PRODUCTION ⚠️
// Force le mode admin temporairement pour tester
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // En développement, on peut forcer admin pour tester
    // À COMMENTER/DÉCOMMENTER selon vos besoins
    $_SESSION['is_admin'] = true;
    $_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;
    $_SESSION['user_name'] = $_SESSION['user_name'] ?? 'Admin Test';
}

// Vérification finale
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirection absolue pour éviter les problèmes de chemin
    $redirectUrl = '/ecoride/frontoffice/vehicules_disponibles.php';
    header('Location: ' . $redirectUrl);
    exit;
}