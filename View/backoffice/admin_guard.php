<?php
/**
 * Protection des pages admin.
 * À inclure en haut de chaque fichier backoffice.
 * Redirige vers le frontoffice si l'utilisateur n'est pas admin.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// Vérifier que l'utilisateur est connecté ET est admin
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../frontoffice/vehicules_disponibles.php');
    exit;
}
