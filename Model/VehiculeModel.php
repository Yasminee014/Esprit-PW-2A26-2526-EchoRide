<?php

require_once __DIR__ . '/../Config/Database.php';

class VehiculeModel {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /* ─────────────────── LIRE ─────────────────── */

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT v.*, u.nom, u.prenom
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            ORDER BY v.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT v.*, u.nom, u.prenom
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            WHERE v.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getByUserId(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM vehicules
            WHERE user_id = :user_id
            ORDER BY id DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getDisponibles(): array {
        $stmt = $this->db->query("
            SELECT v.*, u.nom, u.prenom
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            WHERE v.statut = 'disponible'
            ORDER BY v.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function search(string $term): array {
        $like = '%' . $term . '%';
        $stmt = $this->db->prepare("
            SELECT v.*, u.nom, u.prenom
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            WHERE v.marque LIKE :marque 
               OR v.modele LIKE :modele 
               OR v.immatriculation LIKE :immat
            ORDER BY v.id DESC
        ");
        $stmt->execute([
            ':marque' => $like,
            ':modele' => $like,
            ':immat' => $like
        ]);
        return $stmt->fetchAll();
    }

    public function immatriculationExists(string $immatriculation, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM vehicules WHERE immatriculation = :immat";
        $params = [':immat' => $immatriculation];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /* ─────────────────── STATS ─────────────────── */

    public function countAll(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM vehicules")->fetchColumn();
    }

    public function countByStatut(string $statut): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vehicules WHERE statut = :s");
        $stmt->execute([':s' => $statut]);
        return (int) $stmt->fetchColumn();
    }

    /* ─────────────────── CRÉER ─────────────────── */

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO vehicules
                (user_id, marque, modele, immatriculation, couleur, capacite, climatisation, statut, photo)
            VALUES
                (:user_id, :marque, :modele, :immatriculation, :couleur, :capacite, :climatisation, :statut, :photo)
        ");
        return $stmt->execute([
            ':user_id'         => $data['user_id'],
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':immatriculation' => $data['immatriculation'],
            ':couleur'         => $data['couleur']         ?? null,
            ':capacite'        => $data['capacite']        ?? 4,
            ':climatisation'   => $data['climatisation']   ?? 0,
            ':statut'          => $data['statut']          ?? 'disponible',
            ':photo'           => $data['photo']           ?? null
        ]);
    }

    /* ─────────────────── MODIFIER ─────────────────── */

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE vehicules SET
                marque          = :marque,
                modele          = :modele,
                immatriculation = :immatriculation,
                couleur         = :couleur,
                capacite        = :capacite,
                climatisation   = :climatisation,
                statut          = :statut,
                photo           = :photo
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'              => $id,
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':immatriculation' => $data['immatriculation'],
            ':couleur'         => $data['couleur']       ?? null,
            ':capacite'        => $data['capacite']      ?? 4,
            ':climatisation'   => $data['climatisation'] ?? 0,
            ':statut'          => $data['statut']        ?? 'disponible',
            ':photo'           => $data['photo']         ?? null
        ]);
    }

    public function updateStatut(int $id, string $statut): bool {
        $stmt = $this->db->prepare("UPDATE vehicules SET statut = :s WHERE id = :id");
        return $stmt->execute([':s' => $statut, ':id' => $id]);
    }

    /* ─────────────────── SUPPRIMER ─────────────────── */

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM vehicules WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /* ─────────────────── VALIDATION ─────────────────── */

    public function validate(array $data): array {
        $errors = [];

        if (empty(trim($data['marque'] ?? ''))) {
            $errors[] = 'La marque est obligatoire.';
        } elseif (strlen($data['marque']) > 50) {
            $errors[] = 'La marque ne doit pas dépasser 50 caractères.';
        }

        if (empty(trim($data['modele'] ?? ''))) {
            $errors[] = 'Le modèle est obligatoire.';
        } elseif (strlen($data['modele']) > 50) {
            $errors[] = 'Le modèle ne doit pas dépasser 50 caractères.';
        }

        if (empty(trim($data['immatriculation'] ?? ''))) {
            $errors[] = "L'immatriculation est obligatoire.";
        } elseif (!preg_match('/^[A-Z]{2}-\d{3}-[A-Z]{2}$/', strtoupper($data['immatriculation']))) {
            $errors[] = "Format d'immatriculation invalide (ex: AB-123-CD).";
        }

        $capacite = intval($data['capacite'] ?? 0);
        if ($capacite < 1 || $capacite > 9) {
            $errors[] = 'La capacité doit être entre 1 et 9 places.';
        }

        $statuts_valides = ['disponible', 'indisponible', 'en_maintenance'];
        if (!in_array($data['statut'] ?? '', $statuts_valides)) {
            $errors[] = 'Statut invalide.';
        }

        return $errors;
    }
}
?>