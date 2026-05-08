<?php

require_once __DIR__ . '/../Config/Database.php';

class VehiculeModel {

    private PDO $db;
    private ?bool $hasTrajetIdColumn = null;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /* ─────────────────── LIRE ─────────────────── */

    public function getAll(): array {
        $joinTrajet = $this->supportsTrajetAssocie()
            ? ", t.point_depart AS trajet_depart, t.point_arrive AS trajet_arrive"
            : "";
        $leftJoinTrajet = $this->supportsTrajetAssocie()
            ? " LEFT JOIN trajet t ON v.trajet_id = t.id_T "
            : "";
        $stmt = $this->db->query("
            SELECT v.*, u.nom, u.prenom" . $joinTrajet . "
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            " . $leftJoinTrajet . "
            ORDER BY v.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $joinTrajet = $this->supportsTrajetAssocie()
            ? ", t.point_depart AS trajet_depart, t.point_arrive AS trajet_arrive"
            : "";
        $leftJoinTrajet = $this->supportsTrajetAssocie()
            ? " LEFT JOIN trajet t ON v.trajet_id = t.id_T "
            : "";
        $stmt = $this->db->prepare("
            SELECT v.*, u.nom, u.prenom" . $joinTrajet . "
            FROM vehicules v
            LEFT JOIN users u ON v.user_id = u.id
            " . $leftJoinTrajet . "
            WHERE v.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getByUserId(int $userId): array {
        $joinTrajet = $this->supportsTrajetAssocie()
            ? ", t.point_depart AS trajet_depart, t.point_arrive AS trajet_arrive"
            : "";
        $leftJoinTrajet = $this->supportsTrajetAssocie()
            ? " LEFT JOIN trajet t ON v.trajet_id = t.id_T "
            : "";
        $stmt = $this->db->prepare("
            SELECT v.*" . $joinTrajet . "
            FROM vehicules v
            " . $leftJoinTrajet . "
            WHERE v.user_id = :user_id
            ORDER BY v.id DESC
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

    public function countConfirmedReservations(int $vehiculeId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE vehicule_id = :vid AND statut = 'confirmee'");
        $stmt->execute([':vid' => $vehiculeId]);
        return (int) $stmt->fetchColumn();
    }

    public function calculateTrustScore(array $vehicule): array {
        $reservationCount = $this->countConfirmedReservations((int)$vehicule['id']);
        $baseScore = 20;

        $reservationScore = min(8, $reservationCount) * 6; // jusqu'à 48 points

        $statusScore = match($vehicule['statut'] ?? 'disponible') {
            'disponible' => 15,
            'indisponible' => 0,
            'en_maintenance' => -10,
            default => 5,
        };

        $optionsScore = 0;
        $optionsScore += !empty($vehicule['climatisation']) ? 8 : 0;
        $optionsScore += (!empty($vehicule['capacite']) && (int)$vehicule['capacite'] >= 5) ? 4 : 0;

        $photoScore = !empty($vehicule['photo']) ? 15 : -5;

        $score = $baseScore + $reservationScore + $statusScore + $optionsScore + $photoScore;
        $score = max(0, min(100, (int)round($score)));

        if ($score >= 90) {
            $label = 'Excellent';
            $class = 'excellent';
            $color = '#27ae60';
        } elseif ($score >= 70) {
            $label = 'Très bon';
            $class = 'high';
            $color = '#2ecc71';
        } elseif ($score >= 50) {
            $label = 'Moyen';
            $class = 'medium';
            $color = '#f1c40f';
        } else {
            $label = 'Faible';
            $class = 'low';
            $color = '#e74c3c';
        }

        return [
            'trust_score' => $score,
            'trust_label' => $label,
            'trust_class' => $class,
            'trust_color' => $color,
            'trusted_reservations' => $reservationCount,
        ];
    }

    public function augmentWithTrustScore(array $vehicule): array {
        return array_merge($vehicule, $this->calculateTrustScore($vehicule));
    }

    public function getDisponiblesWithScore(): array {
        $vehicules = $this->getDisponibles();
        return array_map([$this, 'augmentWithTrustScore'], $vehicules);
    }

    /* ─────────────────── CRÉER ─────────────────── */

    public function create(array $data): bool {
        $withTrajet = $this->supportsTrajetAssocie();
        $columns = "user_id, marque, modele, immatriculation, couleur, capacite, climatisation, statut, photo";
        $values = ":user_id, :marque, :modele, :immatriculation, :couleur, :capacite, :climatisation, :statut, :photo";
        if ($withTrajet) {
            $columns .= ", trajet_id";
            $values  .= ", :trajet_id";
        }
        $stmt = $this->db->prepare("
            INSERT INTO vehicules
                (" . $columns . ")
            VALUES
                (" . $values . ")
        ");
        $params = [
            ':user_id'         => $data['user_id'],
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':immatriculation' => $data['immatriculation'],
            ':couleur'         => $data['couleur']         ?? null,
            ':capacite'        => $data['capacite']        ?? 4,
            ':climatisation'   => $data['climatisation']   ?? 0,
            ':statut'          => $data['statut']          ?? 'disponible',
            ':photo'           => $data['photo']           ?? null
        ];
        if ($withTrajet) {
            $params[':trajet_id'] = $data['trajet_id'] ?? null;
        }
        return $stmt->execute($params);
    }

    /* ─────────────────── MODIFIER ─────────────────── */

    public function update(int $id, array $data): bool {
        $withTrajet = $this->supportsTrajetAssocie();
        $sql = "
            UPDATE vehicules SET
                marque          = :marque,
                modele          = :modele,
                immatriculation = :immatriculation,
                couleur         = :couleur,
                capacite        = :capacite,
                climatisation   = :climatisation,
                statut          = :statut,
                photo           = :photo";
        if ($withTrajet) {
            $sql .= ",
                trajet_id       = :trajet_id";
        }
        $sql .= "
            WHERE id = :id
        ";
        $stmt = $this->db->prepare($sql);
        $params = [
            ':id'              => $id,
            ':marque'          => $data['marque'],
            ':modele'          => $data['modele'],
            ':immatriculation' => $data['immatriculation'],
            ':couleur'         => $data['couleur']       ?? null,
            ':capacite'        => $data['capacite']      ?? 4,
            ':climatisation'   => $data['climatisation'] ?? 0,
            ':statut'          => $data['statut']        ?? 'disponible',
            ':photo'           => $data['photo']         ?? null
        ];
        if ($withTrajet) {
            $params[':trajet_id'] = $data['trajet_id'] ?? null;
        }
        return $stmt->execute($params);
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

    private function supportsTrajetAssocie(): bool {
        if ($this->hasTrajetIdColumn !== null) {
            return $this->hasTrajetIdColumn;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM vehicules LIKE 'trajet_id'");
        $exists = (bool)$stmt->fetch();

        // Migration defensive: si la colonne n'existe pas, on la crée pour
        // permettre l'association véhicule <-> trajet depuis le front.
        if (!$exists) {
            try {
                $this->db->exec("ALTER TABLE vehicules ADD COLUMN trajet_id INT NULL");
                $this->db->exec("ALTER TABLE vehicules ADD INDEX idx_vehicules_trajet_id (trajet_id)");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }

        if ($exists) {
            try {
                // Liaison rétroactive: pour les anciens véhicules sans trajet,
                // associer le dernier trajet du même utilisateur.
                $this->db->exec("
                    UPDATE vehicules v
                    INNER JOIN (
                        SELECT id_u, MAX(id_T) AS last_trajet_id
                        FROM trajet
                        GROUP BY id_u
                    ) t ON t.id_u = v.user_id
                    SET v.trajet_id = t.last_trajet_id
                    WHERE v.trajet_id IS NULL OR v.trajet_id = 0
                ");
            } catch (Throwable $e) {
                // Non bloquant
            }
        }

        $this->hasTrajetIdColumn = $exists;
        return $this->hasTrajetIdColumn;
    }
}
?>