<?php
require_once 'Database.php';

class ReclamationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getPDO();
    }

    public function create($data) {
        $sql = "INSERT INTO reclamations (utilisateur_id, titre, description, categorie, priorite, statut, reponse_admin) 
                VALUES (:utilisateur_id, :titre, :description, :categorie, :priorite, :statut, :reponse_admin)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':utilisateur_id' => $data['utilisateur_id'],
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':categorie' => $data['categorie'],
            ':priorite' => $data['priorite'],
            ':statut' => $data['statut'] ?? 'en_attente',
            ':reponse_admin' => $data['reponse_admin'] ?? null
        ]);
    }

    public function getAll() {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.email as utilisateur_email 
                FROM reclamations r
                LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                ORDER BY r.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserId($userId) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.email as utilisateur_email 
                FROM reclamations r
                LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                WHERE r.utilisateur_id = :user_id
                ORDER BY r.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT r.*, u.nom as utilisateur_nom, u.email as utilisateur_email 
                FROM reclamations r
                LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
                WHERE r.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $sql = "UPDATE reclamations SET 
                    titre = :titre,
                    description = :description,
                    categorie = :categorie,
                    priorite = :priorite,
                    statut = :statut,
                    reponse_admin = :reponse_admin
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':titre' => $data['titre'],
            ':description' => $data['description'],
            ':categorie' => $data['categorie'],
            ':priorite' => $data['priorite'],
            ':statut' => $data['statut'],
            ':reponse_admin' => $data['reponse_admin'] ?? null
        ]);
    }

    public function updateStatut($id, $statut) {
        $sql = "UPDATE reclamations SET statut = :statut WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':statut' => $statut]);
    }

    public function delete($id) {
        $sql = "DELETE FROM reclamations WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(statut = 'en_attente') as en_attente,
                    SUM(statut = 'en_cours') as en_cours,
                    SUM(statut = 'resolue') as resolue,
                    SUM(statut = 'rejetee') as rejetee,
                    SUM(priorite = 'elevee') as priorite_elevee
                FROM reclamations";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>