<?php

require_once __DIR__ . '/../Config/Database.php';

class ReservationModel {

    private PDO $db;
    private ?array $reservationColumns = null;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function getReservationColumns(): array {
        if ($this->reservationColumns !== null) {
            return $this->reservationColumns;
        }

        $cols = $this->db->query("SHOW COLUMNS FROM reservations")->fetchAll(PDO::FETCH_COLUMN);
        $this->reservationColumns = is_array($cols) ? $cols : [];
        return $this->reservationColumns;
    }

    private function hasReservationColumn(string $column): bool {
        return in_array($column, $this->getReservationColumns(), true);
    }

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
            ORDER BY r.id DESC
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

    public function getMonHistoriqueGlobal(int $userId): array {
        $reservationCols = $this->db->query("SHOW COLUMNS FROM reservations")->fetchAll(PDO::FETCH_COLUMN);
        $has = static fn(string $col) => in_array($col, $reservationCols, true);

        $colDateReservation = $has('date_reservation') ? "r.date_reservation AS date_reservation" : "NULL AS date_reservation";
        $colDateDebut       = $has('date_debut')       ? "r.date_debut AS date_debut"           : "NULL AS date_debut";
        $colDateFin         = $has('date_fin')         ? "r.date_fin AS date_fin"               : "NULL AS date_fin";
        $colHeure           = $has('heure')            ? "r.heure AS heure"                     : "NULL AS heure";
        $colNbPlaces        = $has('nb_places')        ? "r.nb_places AS nb_places"             : "NULL AS nb_places";
        $colPrixTotal       = $has('prix_total')       ? "r.prix_total AS prix_total"           : "0 AS prix_total";
        $colStatut          = $has('statut')           ? "r.statut AS statut"                   : "'en_attente' AS statut";
        $joinTrajet         = $has('trajet_id')        ? "LEFT JOIN trajet t ON r.trajet_id = t.id_T" : "LEFT JOIN trajet t ON 1=0";
        $joinDestination    = $has('trajet_id')        ? "LEFT JOIN destination d ON d.trajet_id = t.id_T" : "LEFT JOIN destination d ON 1=0";
        $groupByDateRes     = $has('date_reservation') ? "r.date_reservation" : "NULL";
        $groupByDateDebut   = $has('date_debut')       ? "r.date_debut"       : "NULL";
        $groupByDateFin     = $has('date_fin')         ? "r.date_fin"         : "NULL";
        $groupByHeure       = $has('heure')            ? "r.heure"            : "NULL";
        $groupByNbPlaces    = $has('nb_places')        ? "r.nb_places"        : "NULL";
        $groupByPrixTotal   = $has('prix_total')       ? "r.prix_total"       : "NULL";
        $groupByStatut      = $has('statut')           ? "r.statut"           : "NULL";

        $stmt = $this->db->prepare("
            SELECT * FROM (
                SELECT
                r.id,
                {$colDateReservation},
                {$colDateDebut},
                {$colDateFin},
                {$colHeure},
                {$colNbPlaces},
                {$colPrixTotal},
                {$colStatut},
                v.id AS vehicule_id,
                v.marque,
                v.modele,
                v.immatriculation,
                v.capacite AS vehicule_capacite,
                v.climatisation,
                v.couleur,
                v.statut AS vehicule_statut,
                t.id_T AS trajet_id,
                t.point_depart,
                t.point_arrive,
                t.distance_total,
                GROUP_CONCAT(DISTINCT CASE WHEN d.ordre <> 999 THEN d.nom END ORDER BY d.ordre SEPARATOR ' | ') AS destinations,
                GROUP_CONCAT(DISTINCT CASE WHEN d.ordre = 999 THEN d.nom END ORDER BY d.id_des DESC SEPARATOR ' | ') AS arrets_reserves,
                MAX(CASE WHEN d.ordre = 999 THEN d.prix END) AS prix_arret_reserve,
                'reservation' AS type_ligne
                FROM reservations r
                LEFT JOIN vehicules v ON r.vehicule_id = v.id
                {$joinTrajet}
                {$joinDestination}
                WHERE r.user_id = :uid OR v.user_id = :uid2
                GROUP BY
                    r.id, {$groupByDateRes}, {$groupByDateDebut}, {$groupByDateFin}, {$groupByHeure}, {$groupByNbPlaces}, {$groupByPrixTotal}, {$groupByStatut},
                    v.id, v.marque, v.modele, v.immatriculation, v.capacite, v.climatisation, v.couleur, v.statut,
                    t.id_T, t.point_depart, t.point_arrive, t.distance_total

                UNION ALL

                SELECT
                    NULL AS id,
                    NULL AS date_reservation,
                    NULL AS date_debut,
                    NULL AS date_fin,
                    NULL AS heure,
                    NULL AS nb_places,
                    0 AS prix_total,
                    v.statut AS statut,
                    v.id AS vehicule_id,
                    v.marque,
                    v.modele,
                    v.immatriculation,
                    v.capacite AS vehicule_capacite,
                    v.climatisation,
                    v.couleur,
                    v.statut AS vehicule_statut,
                    NULL AS trajet_id,
                    NULL AS point_depart,
                    NULL AS point_arrive,
                    NULL AS distance_total,
                    NULL AS destinations,
                    NULL AS arrets_reserves,
                    NULL AS prix_arret_reserve,
                    'vehicule' AS type_ligne
                FROM vehicules v
                WHERE v.user_id = :uid3
                  AND NOT EXISTS (
                      SELECT 1
                      FROM reservations r2
                      WHERE r2.vehicule_id = v.id
                  )
            ) historique
            ORDER BY COALESCE(historique.date_reservation, historique.date_debut) DESC, historique.vehicule_id DESC
        ");
        $stmt->execute([':uid' => $userId, ':uid2' => $userId, ':uid3' => $userId]);
        return $stmt->fetchAll();
    }

    /* ─────────────────── HISTORIQUE ADMIN ─────────────────── */

    public function getHistoriqueAdmin(string $statut = '', string $dateDebut = '', string $dateFin = ''): array {
        $where = ["1=1"];
        $params = [];
        if ($statut)    { $where[] = 'r.statut = :statut';      $params[':statut'] = $statut; }
        if ($dateDebut) { $where[] = 'r.date_debut >= :debut';  $params[':debut']  = $dateDebut; }
        if ($dateFin)   { $where[] = 'r.date_fin <= :fin';      $params[':fin']    = $dateFin; }
        $sql = "
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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

    /* ─────────────────── HISTORIQUE CONDUCTEUR ─────────────────── */

    public function getHistoriqueConducteur(int $conducteurId): array {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   v.marque, v.modele, v.immatriculation,
                   u.nom AS passager_nom, u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            LEFT JOIN users u ON r.user_id = u.id
            WHERE v.user_id = :cid
              AND r.statut IN ('annulee','confirmee','en_attente')
            ORDER BY r.id DESC
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

    /* ─────────────────── GÉNÉRIQUE ─────────────────── */

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
        ORDER BY r.id DESC
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
            SELECT r.*,
                   u.nom AS passager_nom,
                   u.prenom AS passager_prenom
            FROM reservations r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.vehicule_id = :vehicule_id
            ORDER BY r.id DESC
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
        $fields = [
            'vehicule_id' => $data['vehicule_id'] ?? null,
            'user_id'     => $data['user_id'] ?? null,
            'trajet_id'   => $data['trajet_id'] ?? null,
            'date_debut'  => $data['date_debut'] ?? null,
            'date_fin'    => $data['date_fin'] ?? null,
            'heure'       => $data['heure'] ?? null,
            'nb_places'   => $data['nb_places'] ?? 1,
            'prix_total'  => $data['prix_total'] ?? 0,
            'statut'      => $data['statut'] ?? 'en_attente',
        ];

        $columns = [];
        $params = [];
        foreach ($fields as $column => $value) {
            if ($this->hasReservationColumn($column)) {
                $columns[] = $column;
                $params[":{$column}"] = $value;
            }
        }

        if (empty($columns)) {
            return false;
        }

        $placeholders = array_map(static fn(string $c) => ":{$c}", $columns);
        $sql = "INSERT INTO reservations (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
   /**
 * Crée une réservation et retourne l'ID
 */
public function createAndGetId(array $data): int|false {
    $fields = [
        'vehicule_id' => $data['vehicule_id'] ?? null,
        'user_id'     => $data['user_id'] ?? null,
        'trajet_id'   => $data['trajet_id'] ?? null,
        'date_debut'  => $data['date_debut'] ?? null,
        'date_fin'    => $data['date_fin'] ?? null,
        'heure'       => $data['heure'] ?? null,
        'nb_places'   => $data['nb_places'] ?? 1,
        'prix_total'  => $data['prix_total'] ?? 0,
        'statut'      => $data['statut'] ?? 'en_attente',
        'note'        => $data['note'] ?? null,
    ];

    $columns = [];
    $params = [];
    foreach ($fields as $column => $value) {
        if ($this->hasReservationColumn($column)) {
            $columns[] = $column;
            $params[":{$column}"] = $value;
        }
    }

    if (empty($columns)) {
        return false;
    }

    $placeholders = array_map(static fn(string $c) => ":{$c}", $columns);
    $sql = "INSERT INTO reservations (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $this->db->prepare($sql);
    $success = $stmt->execute($params);

    return $success ? (int)$this->db->lastInsertId() : false;
}

    /**
 * Vérifie les conflits de réservation
 */
public function verifierConflits(int $vehiculeId, string $dateDebut, string $dateFin): bool {
    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM reservations 
        WHERE vehicule_id = :vid 
        AND statut IN ('confirmee', 'en_attente')
        AND date_debut <= :fin 
        AND date_fin >= :debut
    ");
    $stmt->execute([
        ':vid' => $vehiculeId,
        ':debut' => $dateDebut,
        ':fin' => $dateFin
    ]);
    
    return $stmt->fetchColumn() > 0;
}

    /**
     * Récupère les réservations par statut
     */
    public function getByStatut(string $statut, int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, v.marque, v.modele, v.immatriculation
            FROM reservations r
            LEFT JOIN vehicules v ON r.vehicule_id = v.id
            WHERE r.user_id = :uid AND r.statut = :statut
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([':uid' => $userId, ':statut' => $statut]);
        return $stmt->fetchAll();
    }

    /* ─────────────────── MODIFIER ─────────────────── */

    public function updateStatut(int $id, string $statut): bool {
        $stmt = $this->db->prepare("UPDATE reservations SET statut = :s WHERE id = :id");
        return $stmt->execute([':s' => $statut, ':id' => $id]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE reservations SET
                vehicule_id = :vehicule_id,
                user_id     = :user_id,
                trajet_id   = :trajet_id,
                date_debut  = :date_debut,
                date_fin    = :date_fin,
                heure       = :heure,
                nb_places   = :nb_places,
                prix_total  = :prix_total,
                statut      = :statut,
                note        = :note
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'          => $id,
            ':vehicule_id' => $data['vehicule_id'],
            ':user_id'     => $data['user_id'],
            ':trajet_id'   => $data['trajet_id']  ?? null,
            ':date_debut'  => $data['date_debut']  ?? null,
            ':date_fin'    => $data['date_fin']    ?? null,
            ':heure'       => $data['heure']       ?? null,
            ':nb_places'   => $data['nb_places']   ?? 1,
            ':prix_total'  => $data['prix_total']  ?? 0,
            ':statut'      => $data['statut'],
            ':note'        => $data['note']        ?? null,
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

        if (!isset($data['nb_places']) || $data['nb_places'] < 1) {
            $errors[] = 'Le nombre de places doit être au moins 1.';
        }

        if (!empty($data['date_debut'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_debut']);
            if (!$date) {
                $errors[] = 'Format de date de début invalide.';
            }
        } else {
            $errors[] = 'La date de début est obligatoire.';
        }

        if (!empty($data['date_fin'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_fin']);
            if (!$date) {
                $errors[] = 'Format de date de fin invalide.';
            }
        } else {
            $errors[] = 'La date de fin est obligatoire.';
        }

        if (!empty($data['heure'])) {
            $time = DateTime::createFromFormat('H:i', $data['heure']);
            if (!$time) {
                $errors[] = 'Format d\'heure invalide.';
            }
        } else {
            $errors[] = 'L\'heure de prise en charge est obligatoire.';
        }

        $statuts_valides = ['en_attente', 'confirmee', 'annulee', 'attente_paiement'];
        if (!empty($data['statut']) && !in_array($data['statut'], $statuts_valides)) {
            $errors[] = 'Statut de réservation invalide.';
        }

        return $errors;
    }
}
?>