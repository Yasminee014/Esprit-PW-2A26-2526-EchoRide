<?php

require_once __DIR__ . '/../Config/Database.php';

class FavoriteModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT f.id, f.user_id, f.trajet_id, f.created_at,
                   t.point_depart, t.point_arrive, t.prix_total, t.distance_total
            FROM favorites f
            INNER JOIN trajet t ON t.id_T = f.trajet_id
            WHERE f.user_id = :uid
            ORDER BY f.id DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $trajetId): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO favorites (user_id, trajet_id)
            VALUES (:uid, :tid)
        ");
        return $stmt->execute([':uid' => $userId, ':tid' => $trajetId]);
    }

    public function remove(int $userId, int $trajetId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM favorites
            WHERE user_id = :uid AND trajet_id = :tid
        ");
        return $stmt->execute([':uid' => $userId, ':tid' => $trajetId]);
    }
}

