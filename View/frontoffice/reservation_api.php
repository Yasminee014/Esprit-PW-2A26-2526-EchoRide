<?php
if (session_status() === PHP_SESSION_NONE) session_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
header('Content-Type: application/json');

ob_start();
require_once __DIR__ . '/../../Controller/ReservationController.php';

try {
    $controller = new ReservationController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = $controller->foCreate();
        if (!is_array($result)) {
            $result = ['success' => false, 'message' => 'Réponse API invalide.'];
        }
    } else {
        http_response_code(405);
        $result = ['success' => false, 'message' => 'Méthode non autorisée'];
    }
} catch (Throwable $e) {
    http_response_code(500);
    $result = ['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()];
}

$noise = ob_get_clean();
if (!empty($noise)) {
    error_log('reservation_api.php output noise: ' . $noise);
}

echo json_encode($result);