<?php
// ============================================================
// serve_image.php - Sert les images depuis la BDD si absentes
// Usage: serve_image.php?type=logo|user|admin&id=X&file=xxx.jpg
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers/ImageHelper.php';

$type = $_GET['type'] ?? 'logo';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$file = $_GET['file'] ?? '';

switch ($type) {
    // ── Logo EcoRide ──────────────────────────────────────────
    case 'logo':
        // Essayer d'abord le fichier physique
        $physPath = BASE_PATH . 'uploads/photos/photo.png';
        if (file_exists($physPath)) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=86400');
            readfile($physPath);
            exit;
        }
        // Sinon depuis la BDD
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'ecoride_logo_base64' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['setting_value'])) {
                $data = base64_decode($row['setting_value']);
                header('Content-Type: image/png');
                header('Cache-Control: public, max-age=86400');
                echo $data;
                exit;
            }
        } catch (Exception $e) {
            error_log('serve_image logo: ' . $e->getMessage());
        }
        http_response_code(404);
        exit;

    // ── Photo utilisateur ────────────────────────────────────
    case 'user':
        if ($id <= 0) { http_response_code(404); exit; }
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT photo_data, photo_mime FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['photo_data'])) {
                $mime = $row['photo_mime'] ?: 'image/jpeg';
                header('Content-Type: ' . $mime);
                header('Cache-Control: public, max-age=3600');
                echo $row['photo_data'];
                exit;
            }
        } catch (Exception $e) {
            error_log('serve_image user: ' . $e->getMessage());
        }
        // Fallback fichier
        if ($file) {
            $path = BASE_PATH . 'uploads/photos/' . basename($file);
            if (file_exists($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                header('Content-Type: ' . $mime);
                readfile($path);
                exit;
            }
        }
        http_response_code(404);
        exit;

    // ── Photo admin ──────────────────────────────────────────
    case 'admin':
        if ($id <= 0) { http_response_code(404); exit; }
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare("SELECT photo_data, photo_mime FROM admins WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['photo_data'])) {
                $mime = $row['photo_mime'] ?: 'image/jpeg';
                header('Content-Type: ' . $mime);
                header('Cache-Control: public, max-age=3600');
                echo $row['photo_data'];
                exit;
            }
        } catch (Exception $e) {
            error_log('serve_image admin: ' . $e->getMessage());
        }
        // Fallback fichier
        if ($file) {
            $path = BASE_PATH . 'uploads/photos/' . basename($file);
            if (file_exists($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                header('Content-Type: ' . $mime);
                readfile($path);
                exit;
            }
        }
        http_response_code(404);
        exit;

    default:
        http_response_code(400);
        exit;
}
