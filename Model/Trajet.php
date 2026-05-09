<?php
class Trajet {
    private $conn;
    private $table = "trajet";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ── Lire tous les trajets (existant, inchangé)
    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id_T DESC";
        $stmt  = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // ── Lire avec pagination + recherche + tri
    public function readWithPagination(
        string $search = '',
        string $sort   = 'id_T',
        string $order  = 'DESC',
        int    $page   = 1,
        int    $limit  = 10
    ) {
        $offset = ($page - 1) * $limit;

        $allowed = ['id_T', 'point_depart', 'point_arrive', 'prix_total', 'distance_total'];
        $sort    = in_array($sort, $allowed) ? $sort : 'id_T';
        $order   = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $sql    = "SELECT * FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql     .= " AND (point_depart LIKE :s1 OR point_arrive LIKE :s2)";
            $term     = "%$search%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
        }

        $sql .= " ORDER BY $sort $order";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // ── Compter le total pour la pagination
    public function countAll(string $search = ''): int {
        $sql    = "SELECT COUNT(*) FROM " . $this->table . " WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql     .= " AND (point_depart LIKE :s1 OR point_arrive LIKE :s2)";
            $term     = "%$search%";
            $params[':s1'] = $term;
            $params[':s2'] = $term;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // ── Ajouter un trajet (existant, inchangé)
    public function create($depart, $arrivee, $prix_total, $distance_total) {
        $query = "INSERT INTO " . $this->table . "
            (point_depart, point_arrive, prix_total, distance_total)
            VALUES (:depart, :arrivee, :prix_total, :distance_total)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":depart",         $depart);
        $stmt->bindParam(":arrivee",        $arrivee);
        $stmt->bindParam(":prix_total",     $prix_total);
        $stmt->bindParam(":distance_total", $distance_total);

        return $stmt->execute();
    }

    // ── Supprimer un trajet (existant, inchangé)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_T = :id";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>