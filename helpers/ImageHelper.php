<?php
// ============================================================
// helpers/ImageHelper.php
// Gestion centralisée des images (logo, photos profil)
// Stockage en base de données pour persistance garantie
// ============================================================

class ImageHelper
{
    // ─── Récupérer le logo EcoRide depuis la BDD ──────────────
    public static function getLogoBase64(): string
    {
        static $cached = null;
        if ($cached !== null) return $cached;

        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'ecoride_logo_base64' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['setting_value'])) {
                $cached = 'data:image/png;base64,' . $row['setting_value'];
                return $cached;
            }
        } catch (Exception $e) {
            error_log('ImageHelper::getLogoBase64 - ' . $e->getMessage());
        }

        // Fallback : fichier physique
        $path = BASE_PATH . 'uploads/photos/photo.png';
        if (file_exists($path)) {
            $cached = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            return $cached;
        }

        return ''; // aucune image disponible
    }

    // ─── Récupérer la photo d'un utilisateur ──────────────────
    // Priorité : photo_data (BLOB BDD) > fichier physique > avatar par défaut
    public static function getUserPhoto(int $userId, string $photoFilename = ''): string
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT photo_data, photo_mime FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['photo_data'])) {
                $mime = $row['photo_mime'] ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($row['photo_data']);
            }
        } catch (Exception $e) {
            error_log('ImageHelper::getUserPhoto - ' . $e->getMessage());
        }

        // Fallback fichier physique
        if ($photoFilename) {
            $path = BASE_PATH . 'uploads/photos/' . $photoFilename;
            if (file_exists($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return ''; // pas de photo
    }

    // ─── Récupérer la photo d'un admin ────────────────────────
    public static function getAdminPhoto(int $adminId, string $photoFilename = ''): string
    {
        try {
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT photo_data, photo_mime FROM admins WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $adminId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['photo_data'])) {
                $mime = $row['photo_mime'] ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($row['photo_data']);
            }
        } catch (Exception $e) {
            error_log('ImageHelper::getAdminPhoto - ' . $e->getMessage());
        }

        // Fallback fichier physique
        if ($photoFilename) {
            $path = BASE_PATH . 'uploads/photos/' . $photoFilename;
            if (file_exists($path)) {
                $mime = mime_content_type($path) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return ''; // pas de photo
    }

    // ─── Sauvegarder photo user en BDD (BLOB) ─────────────────
    public static function saveUserPhotoDB(int $userId, string $tmpPath, string $mime): bool
    {
        try {
            $data = file_get_contents($tmpPath);
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE users SET photo_data = :data, photo_mime = :mime WHERE id = :id");
            return $stmt->execute([':data' => $data, ':mime' => $mime, ':id' => $userId]);
        } catch (Exception $e) {
            error_log('ImageHelper::saveUserPhotoDB - ' . $e->getMessage());
            return false;
        }
    }

    // ─── Sauvegarder photo admin en BDD (BLOB) ────────────────
    public static function saveAdminPhotoDB(int $adminId, string $tmpPath, string $mime): bool
    {
        try {
            $data = file_get_contents($tmpPath);
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE admins SET photo_data = :data, photo_mime = :mime WHERE id = :id");
            return $stmt->execute([':data' => $data, ':mime' => $mime, ':id' => $adminId]);
        } catch (Exception $e) {
            error_log('ImageHelper::saveAdminPhotoDB - ' . $e->getMessage());
            return false;
        }
    }

    // ─── Mettre à jour le logo EcoRide en BDD ─────────────────
    public static function saveLogoDB(string $tmpPath): bool
    {
        try {
            $b64  = base64_encode(file_get_contents($tmpPath));
            $db   = Database::getInstance()->getConnection();
            $stmt = $db->prepare(
                "INSERT INTO app_settings (setting_key, setting_value)
                 VALUES ('ecoride_logo_base64', :val)
                 ON DUPLICATE KEY UPDATE setting_value = :val2"
            );
            return $stmt->execute([':val' => $b64, ':val2' => $b64]);
        } catch (Exception $e) {
            error_log('ImageHelper::saveLogoDB - ' . $e->getMessage());
            return false;
        }
    }
}
