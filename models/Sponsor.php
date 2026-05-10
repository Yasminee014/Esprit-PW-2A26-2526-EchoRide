<?php
// Model/Sponsor.php
namespace Model;

require_once __DIR__ . '/../config.php';

class Sponsor {
    private $db;
    
    public function __construct() {
        $this->db = \Database::getInstance()->getConnection();
    }
    
    // Récupérer tous les sponsors
    public function getAll() {
        $stmt = $this->db->query("
            SELECT s.*, e.titre as event_titre 
            FROM sponsors s 
            LEFT JOIN evenements e ON s.evenement_id = e.id 
            ORDER BY s.montant_sponsoring DESC
        ");
        return $stmt->fetchAll();
    }
    
    // Récupérer les sponsors confirmés
    public function getActive() {
        $stmt = $this->db->query("
            SELECT s.*, e.titre as event_titre 
            FROM sponsors s 
            LEFT JOIN evenements e ON s.evenement_id = e.id 
            WHERE s.statut = 'confirme'
            ORDER BY s.montant_sponsoring DESC
            LIMIT 6
        ");
        return $stmt->fetchAll();
    }
    
    // Récupérer les top sponsors pour le graphique
    public function getTopSponsors($limit = 5) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT nom_entreprise, montant_sponsoring, type_sponsor, logo FROM sponsors WHERE statut = 'confirme' ORDER BY montant_sponsoring DESC LIMIT $limit");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer un sponsor par ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sponsors WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Ajouter un sponsor avec logo
    public function add($data, $logoName = null) {
        $sql = "INSERT INTO sponsors (nom_entreprise, montant_sponsoring, type_sponsor, statut, evenement_id, logo) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nom_entreprise'],
            $data['montant_sponsoring'],
            $data['type_sponsor'],
            $data['statut'] ?? 'en_attente',
            !empty($data['evenement_id']) ? $data['evenement_id'] : null,
            $logoName
        ]);
    }
    
    // Modifier un sponsor avec logo
    public function update($id, $data, $logoName = null) {
        if($logoName) {
            $sql = "UPDATE sponsors SET nom_entreprise=?, montant_sponsoring=?, type_sponsor=?, statut=?, evenement_id=?, logo=? WHERE id=?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['nom_entreprise'], 
                $data['montant_sponsoring'], 
                $data['type_sponsor'],
                $data['statut'] ?? 'en_attente', 
                !empty($data['evenement_id']) ? $data['evenement_id'] : null,
                $logoName, 
                $id
            ]);
        } else {
            $sql = "UPDATE sponsors SET nom_entreprise=?, montant_sponsoring=?, type_sponsor=?, statut=?, evenement_id=? WHERE id=?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['nom_entreprise'], 
                $data['montant_sponsoring'], 
                $data['type_sponsor'],
                $data['statut'] ?? 'en_attente', 
                !empty($data['evenement_id']) ? $data['evenement_id'] : null, 
                $id
            ]);
        }
    }
    
    // Supprimer un sponsor
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM sponsors WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Compter tous les sponsors
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM sponsors");
        return $stmt->fetch()['total'];
    }
    
    // Total des montants de sponsoring
    public function getTotalMontant() {
        $stmt = $this->db->query("SELECT SUM(montant_sponsoring) as total FROM sponsors WHERE statut = 'confirme'");
        return $stmt->fetch()['total'] ?? 0;
    }
    
    // Récupérer tous les sponsors pour l'admin (avec recherche, tri, pagination)
    public function getAllAdmin($search = '', $sort = 'id', $order = 'DESC', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT s.*, e.titre as event_titre FROM sponsors s LEFT JOIN evenements e ON s.evenement_id = e.id WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (s.nom_entreprise LIKE ? OR s.type_sponsor LIKE ? OR s.statut LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $allowedSort = ['id', 'nom_entreprise', 'montant_sponsoring', 'type_sponsor', 'statut'];
        $sortColumn = in_array($sort, $allowedSort) ? "s.$sort" : 's.id';
        $orderDirection = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sortColumn $orderDirection";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Compter pour l'admin
    public function countAllAdmin($search = '') {
        $sql = "SELECT COUNT(*) as total FROM sponsors s WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (s.nom_entreprise LIKE ? OR s.type_sponsor LIKE ? OR s.statut LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    // Récupérer tous les sponsors avec pagination (pour le FrontOffice)
    public function getAllWithPagination($search = '', $sort = 'nom_entreprise', $order = 'ASC', $page = 1, $limit = 8) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT s.*, e.titre as event_titre 
                FROM sponsors s 
                LEFT JOIN evenements e ON s.evenement_id = e.id 
                WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (s.nom_entreprise LIKE ? OR s.type_sponsor LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm];
        }
        
        $allowedSort = ['nom_entreprise', 'montant_sponsoring', 'type_sponsor', 'statut'];
        $sortColumn = in_array($sort, $allowedSort) ? "s.$sort" : 's.nom_entreprise';
        $orderDirection = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sortColumn $orderDirection";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Compter le nombre total de sponsors pour la pagination
    public function countAllWithPagination($search = '') {
        $sql = "SELECT COUNT(*) as total FROM sponsors s WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (s.nom_entreprise LIKE ? OR s.type_sponsor LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    // Récupérer les sponsors d'un événement spécifique
    public function getByEventId($eventId) {
        $stmt = $this->db->prepare("SELECT * FROM sponsors WHERE evenement_id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
    
    // Récupérer l'événement associé à un sponsor
    public function getEventsBySponsorId($sponsorId) {
        $stmt = $this->db->prepare("SELECT e.* FROM evenements e JOIN sponsors s ON s.evenement_id = e.id WHERE s.id = ?");
        $stmt->execute([$sponsorId]);
        $event = $stmt->fetch();
        return $event ? [$event] : [];
    }
}
?>