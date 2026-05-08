<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Model/ReservationModel.php';

header('Content-Type: application/json');

try {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        $db = Database::getInstance();
        $row = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
        $userId = $row ? (int)$row['id'] : 0;
    }

    if ($userId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit;
    }

    $model = new ReservationModel();
    $data = $model->getMonHistoriqueGlobal($userId);
    $stats = $model->statsMonHistorique($userId);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'stats' => $stats,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

