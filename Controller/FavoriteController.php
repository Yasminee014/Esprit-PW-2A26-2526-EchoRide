<?php

require_once __DIR__ . '/../Model/FavoriteModel.php';
require_once __DIR__ . '/../Config/Database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function resolveUserId(): int
{
    $sessionId = (int)($_SESSION['user_id'] ?? 0);
    if ($sessionId > 0) {
        return $sessionId;
    }

    $db = Database::getInstance();
    $row = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
    return $row ? (int)$row['id'] : 0;
}

try {
    $userId = resolveUserId();
    if ($userId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
        exit;
    }

    $model = new FavoriteModel();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $rows = $model->getByUserId($userId);
        $data = array_map(static function (array $r): array {
            return [
                'id_T' => (int)$r['trajet_id'],
                'point_depart' => $r['point_depart'] ?? '',
                'point_arrive' => $r['point_arrive'] ?? '',
                'prix_total' => (float)($r['prix_total'] ?? 0),
                'distance_total' => (float)($r['distance_total'] ?? 0),
                'savedAt' => $r['created_at'] ?? null,
            ];
        }, $rows);
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    $raw = file_get_contents('php://input');
    $payload = $raw ? json_decode($raw, true) : [];
    $trajetId = (int)($payload['trajet_id'] ?? $_GET['trajet_id'] ?? 0);
    if ($trajetId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'trajet_id invalide.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = $model->add($userId, $trajetId);
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $ok = $model->remove($userId, $trajetId);
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

