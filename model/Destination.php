<?php
class Destination {

    private $conn;
    private $table = "destination";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── Lire toutes les destinations (existant, inchangé)
    public function read() {
        $query = "SELECT d.*, t.point_arrive
                  FROM destination d
                  LEFT JOIN trajet t ON d.trajet_id = t.id_T
                  ORDER BY d.id_des DESC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // ── Lire avec pagination + recherche + tri
    public function readWithPagination(
        string $search = '',
        string $sort   = 'id_des',
        string $order  = 'DESC',
        int    $page   = 1,
        int    $limit  = 10
    ) {
        $offset = ($page - 1) * $limit;

        $allowed = ['id_des', 'trajet_id', 'nom', 'distance', 'ordre', 'prix', 'point_arrive'];
        $sort    = in_array($sort, $allowed) ? $sort : 'id_des';
        $order   = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $sql    = "SELECT d.*, t.point_arrive
                   FROM destination d
                   LEFT JOIN trajet t ON d.trajet_id = t.id_T
                   WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql     .= " AND (d.nom LIKE :s1 OR t.point_arrive LIKE :s2)";
            $term     = "%$search%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
        }

        // point_arrive vient du JOIN donc préfixer correctement
        $sortCol = ($sort === 'point_arrive') ? "t.point_arrive" : "d.$sort";
        $sql .= " ORDER BY $sortCol $order";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── Compter le total pour la pagination
    public function countAll(string $search = ''): int {
        $sql    = "SELECT COUNT(*)
                   FROM destination d
                   LEFT JOIN trajet t ON d.trajet_id = t.id_T
                   WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql     .= " AND (d.nom LIKE :s1 OR t.point_arrive LIKE :s2)";
            $term     = "%$search%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ── Récupérer les arrêts d'un trajet (existant, inchangé)
    public function getByTrajet($trajet_id) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE trajet_id = :trajet_id
                  ORDER BY ordre ASC";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $trajet_id);
        $stmt->execute();
        return $stmt;
    }

    // ── Ajouter un arrêt intermédiaire (existant, inchangé)
    public function addArret($trajet_id, $nom, $ordre, $distance, $prix = 0) {
        $stmt = $this->conn->prepare(
            "INSERT INTO destination (trajet_id, nom, ordre, distance, prix)
             VALUES (:trajet_id, :nom, :ordre, :distance, :prix)"
        );
        $stmt->bindParam(':trajet_id', $trajet_id);
        $stmt->bindParam(':nom',       $nom);
        $stmt->bindParam(':ordre',     $ordre);
        $stmt->bindParam(':distance',  $distance);
        $stmt->bindParam(':prix',      $prix);
        return $stmt->execute();
    }

    // ── Ajouter une réservation (ordre = 999) (existant, inchangé)
    public function create($trajet_id, $descente, $distance = null, $prix = 0) {
        $query = "INSERT INTO destination (trajet_id, nom, ordre, distance, prix)
                  VALUES (:trajet_id, :nom, :ordre, :distance, :prix)";
        $stmt  = $this->conn->prepare($query);
        $ordre = 999;
        $stmt->bindParam(":trajet_id", $trajet_id);
        $stmt->bindParam(":nom",       $descente);
        $stmt->bindParam(":ordre",     $ordre);
        $stmt->bindParam(":distance",  $distance);
        $stmt->bindParam(":prix",      $prix);
        return $stmt->execute();
    }

    // ── Supprimer un arrêt par ID (existant, inchangé)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_des = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // ── Supprimer tous les arrêts d'un trajet (existant, inchangé)
    public function deleteByTrajet($trajet_id) {
        $query = "DELETE FROM " . $this->table . " WHERE trajet_id = :trajet_id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $trajet_id);
        return $stmt->execute();
    }
}
?>