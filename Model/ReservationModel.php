<?php

require_once __DIR__ . '/../Config/Database.php';

class ReservationModel {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /* ─────────────────── LIRE ─────────────────── */

    /* ─────────────────── MON HISTORIQUE (user connecté) ─────────────────── */

    public function getMonHistoriqueReservations(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   v.user_id AS vehicule_owner_id
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            WHERE r.user_id = :uid OR v.user_id = :uid2
            GROUP BY r.id
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([':uid' => $userId, ':uid2' => $userId]);
        return $stmt->fetchAll();
    }

    public function statsMonHistorique(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT
                SUM(r.statut = 'confirmee')  AS confirmees,
                SUM(r.statut = 'annulee')    AS annulees,
                SUM(r.statut = 'en_attente') AS en_attente
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            WHERE r.user_id = :uid OR v.user_id = :uid2
        ");
        $stmt->execute([':uid' => $userId, ':uid2' => $userId]);
        return $stmt->fetch() ?: ['confirmees'=>0,'annulees'=>0,'en_attente'=>0];
    }

    /* ─────────────────── HISTORIQUE ─────────────────── */

    public function getHistoriqueAdmin(string $statut = '', string $dateDebut = '', string $dateFin = ''): array {
        $where = ["1=1"];
        $params = [];
        if ($statut) { $where[] = 'r.statut = :statut'; $params[':statut'] = $statut; }
        if ($dateDebut) { $where[] = 'r.date_reservation >= :debut'; $params[':debut'] = $dateDebut; }
        if ($dateFin)   { $where[] = 'r.date_reservation <= :fin';   $params[':fin']   = $dateFin; }
        $sql = "
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.date_reservation DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getHistoriqueConducteur(int $conducteurId): array {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE v.user_id = :cid
              AND (r.date_reservation < CURDATE() OR r.statut IN ('annulee','confirmee'))
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([':cid' => $conducteurId]);
        return $stmt->fetchAll();
    }

    public function statsHistoriqueConducteur(int $conducteurId): array {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*)                        AS total,
                SUM(r.statut = 'confirmee')     AS confirmees,
                SUM(r.statut = 'annulee')       AS annulees,
                COUNT(DISTINCT r.vehicule_id)   AS vehicules_actifs
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            WHERE v.user_id = :cid
        ");
        $stmt->execute([':cid' => $conducteurId]);
        return $stmt->fetch() ?: ['total'=>0,'confirmees'=>0,'annulees'=>0,'vehicules_actifs'=>0];
    }

    public function statsHistoriqueAdmin(): array {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)                    AS total,
                SUM(statut='confirmee')     AS confirmees,
                SUM(statut='annulee')       AS annulees,
                SUM(statut='en_attente')    AS en_attente,
                COUNT(DISTINCT user_id)     AS passagers,
                COUNT(DISTINCT vehicule_id) AS vehicules
            FROM reservations
        ");
        return $stmt->fetch() ?: [];
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            ORDER BY r.id DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getByUserId(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, v.marque, v.modele, v.immatriculation
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            WHERE r.user_id = :user_id
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function countByVehiculeId(int $vehiculeId): array {
        $stmt = $this->db->prepare("
            SELECT
                SUM(statut = 'en_attente') AS en_attente,
                SUM(statut = 'confirmee')  AS confirmee,
                SUM(statut = 'annulee')    AS annulee,
                COUNT(*)                   AS total
            FROM reservations
            WHERE vehicule_id = :vid
        ");
        $stmt->execute([':vid' => $vehiculeId]);
        return $stmt->fetch() ?: ['en_attente'=>0,'confirmee'=>0,'annulee'=>0,'total'=>0];
    }

    public function getByVehiculeId(int $vehiculeId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.vehicule_id = :vehicule_id
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([':vehicule_id' => $vehiculeId]);
        return $stmt->fetchAll();
    }

    public function filterByStatut(string $statut): array {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.statut = :statut
            ORDER BY r.id DESC
        ");
        $stmt->execute([':statut' => $statut]);
        return $stmt->fetchAll();
    }

    /* ─────────────────── STATS ─────────────────── */

    public function countAll(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
    }

    public function countByStatut(string $statut): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reservations WHERE statut = :s");
        $stmt->execute([':s' => $statut]);
        return (int) $stmt->fetchColumn();
    }

    /* ─────────────────── CRÉER ─────────────────── */

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO reservations
                (vehicule_id, user_id, trajet_id, date_reservation, statut)
            VALUES
                (:vehicule_id, :user_id, :trajet_id, :date_reservation, :statut)
        ");
        return $stmt->execute([
            ':vehicule_id'      => $data['vehicule_id'],
            ':user_id'          => $data['user_id'],
            ':trajet_id'        => $data['trajet_id']        ?? null,
            ':date_reservation' => $data['date_reservation'] ?? date('Y-m-d H:i:s'),
            ':statut'           => $data['statut']           ?? 'en_attente',
        ]);
    }

    /* ─────────────────── MODIFIER ─────────────────── */

    public function updateStatut(int $id, string $statut): bool {
        $stmt = $this->db->prepare("UPDATE reservations SET statut = :s WHERE id = :id");
        return $stmt->execute([':s' => $statut, ':id' => $id]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE reservations SET
                vehicule_id      = :vehicule_id,
                user_id          = :user_id,
                trajet_id        = :trajet_id,
                date_reservation = :date_reservation,
                statut           = :statut
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'               => $id,
            ':vehicule_id'      => $data['vehicule_id'],
            ':user_id'          => $data['user_id'],
            ':trajet_id'        => $data['trajet_id']        ?? null,
            ':date_reservation' => $data['date_reservation'],
            ':statut'           => $data['statut'],
        ]);
    }

    /* ─────────────────── SUPPRIMER ─────────────────── */

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM reservations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /* ─────────────────── VALIDATION ─────────────────── */

    public function validate(array $data): array {
        $errors = [];

        if (empty($data['vehicule_id']) || !is_numeric($data['vehicule_id'])) {
            $errors[] = 'Véhicule invalide.';
        }

        if (empty($data['user_id']) || !is_numeric($data['user_id'])) {
            $errors[] = 'Utilisateur invalide.';
        }

        if (!empty($data['date_reservation'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_reservation']);
            if (!$date) {
                $errors[] = 'Format de date invalide.';
            }
        } else {
            $errors[] = 'La date de réservation est obligatoire.';
        }

        $statuts_valides = ['en_attente', 'confirmee', 'annulee'];
        if (!empty($data['statut']) && !in_array($data['statut'], $statuts_valides)) {
            $errors[] = 'Statut de réservation invalide.';
        }

        return $errors;
    }
}