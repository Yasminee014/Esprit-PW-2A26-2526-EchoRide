<?php

class Destination {
    
    private $conn;
    private $table = "destination";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Récupérer toutes les réservations
   public function read() {
    $query = "SELECT d.*, t.point_arrive 
              FROM destination d 
              LEFT JOIN trajet t ON d.trajet_id = t.id_T 
              ORDER BY d.id_des DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt;
}
    
    // Récupérer les arrêts d'un trajet
    public function getByTrajet($trajet_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE trajet_id = :trajet_id 
                  ORDER BY ordre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $trajet_id);
        $stmt->execute();
        return $stmt;
    }
    
    // Ajouter un arrêt (pour la création du trajet)
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
    
    // Ajouter une réservation (point de descente choisi par l'utilisateur)
    public function create($trajet_id, $descente, $distance = null, $prix = 0) {
    $query = "INSERT INTO destination (trajet_id, nom, ordre, distance, prix)
              VALUES (:trajet_id, :nom, :ordre, :distance, :prix)";
    $stmt = $this->conn->prepare($query);
    $ordre = 999;
    $stmt->bindParam(":trajet_id", $trajet_id);
    $stmt->bindParam(":nom",       $descente);
    $stmt->bindParam(":ordre",     $ordre);
    $stmt->bindParam(":distance",  $distance);
    $stmt->bindParam(":prix",      $prix);
    return $stmt->execute();
}
    
    // Supprimer les arrêts d'un trajet
    public function deleteByTrajet($trajet_id) {
        $query = "DELETE FROM " . $this->table . " WHERE trajet_id = :trajet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $trajet_id);
        return $stmt->execute();
    }
}

?>