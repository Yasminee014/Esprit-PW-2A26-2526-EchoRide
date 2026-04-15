<?php
class Trajet {
    private $conn;
    private $table = "trajet";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les trajets
    public function read() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id_T DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Ajouter un trajet
    public function create($depart, $arrivee, $prix_total, $distance_total) {
        $query = "INSERT INTO " . $this->table . "
            (point_depart, point_arrive, prix_total, distance_total)
            VALUES (:depart, :arrivee, :prix_total, :distance_total)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":depart", $depart);
        $stmt->bindParam(":arrivee", $arrivee);
        $stmt->bindParam(":prix_total", $prix_total);
        $stmt->bindParam(":distance_total", $distance_total);
        
        return $stmt->execute();
    }

    // Supprimer un trajet
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_T = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>