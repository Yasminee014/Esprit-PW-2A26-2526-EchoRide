<?php
// Model/Event.php
namespace Model;

class Event {
    private $db;
    
    public function __construct() {
        $this->db = \Config::getConnexion();
    }
    
    // Récupérer tous les événements avec recherche, tri et pagination
    public function getAll($search = '', $sort = 'date_evenement', $order = 'ASC', $page = 1, $limit = 6) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM evenements WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (titre LIKE ? OR ville LIKE ? OR type LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $allowedSort = ['titre', 'ville', 'date_evenement', 'nb_places', 'statut'];
        $sortColumn = in_array($sort, $allowedSort) ? $sort : 'date_evenement';
        $orderDirection = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sortColumn $orderDirection";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Compter le nombre total d'événements
    public function countAll($search = '') {
        $sql = "SELECT COUNT(*) as total FROM evenements WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (titre LIKE ? OR ville LIKE ? OR type LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    // Récupérer les événements à venir
    public function getUpcoming($limit = 5) {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("SELECT * FROM evenements WHERE date_evenement >= NOW() AND statut = 'ouvert' ORDER BY date_evenement ASC LIMIT $limit");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Récupérer un événement par ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM evenements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Récupérer un événement avec ses sponsors
    public function getWithSponsors($id) {
        $stmt = $this->db->prepare("SELECT * FROM evenements WHERE id = ?");
        $stmt->execute([$id]);
        $event = $stmt->fetch();
        
        if ($event) {
            $stmt2 = $this->db->prepare("SELECT * FROM sponsors WHERE evenement_id = ? AND statut = 'confirme'");
            $stmt2->execute([$id]);
            $event['sponsors'] = $stmt2->fetchAll();
        }
        
        return $event;
    }
    
    // ✅ Ajouter un événement (AVEC description)
    public function add($data, $imageName = 'default.jpg') {
        $sql = "INSERT INTO evenements (titre, description, type, ville, date_evenement, nb_places, statut, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['titre'],
            $data['description'] ?? null,
            $data['type'],
            $data['ville'],
            $data['date_evenement'],
            $data['nb_places'],
            $data['statut'] ?? 'ouvert',
            $imageName
        ]);
    }
    
    // ✅ Modifier un événement (AVEC description)
    public function update($id, $data, $imageName = null) {
        if($imageName) {
            $sql = "UPDATE evenements SET titre=?, description=?, type=?, ville=?, date_evenement=?, nb_places=?, statut=?, image=? WHERE id=?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['titre'],
                $data['description'] ?? null,
                $data['type'],
                $data['ville'],
                $data['date_evenement'],
                $data['nb_places'],
                $data['statut'] ?? 'ouvert',
                $imageName,
                $id
            ]);
        } else {
            $sql = "UPDATE evenements SET titre=?, description=?, type=?, ville=?, date_evenement=?, nb_places=?, statut=? WHERE id=?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['titre'],
                $data['description'] ?? null,
                $data['type'],
                $data['ville'],
                $data['date_evenement'],
                $data['nb_places'],
                $data['statut'] ?? 'ouvert',
                $id
            ]);
        }
    }
    
    // Supprimer un événement
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM evenements WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Compter tous les événements
    public function countAllEvents() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM evenements");
        return $stmt->fetch()['total'];
    }
    
    // Compter les événements à venir
    public function countUpcomingEvents() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM evenements WHERE date_evenement >= NOW() AND statut = 'ouvert'");
        return $stmt->fetch()['total'];
    }
    
    // Récupérer les statistiques pour le graphique
    public function getStatsByMonth() {
        $stmt = $this->db->query("
            SELECT MONTH(date_evenement) as mois, COUNT(*) as total 
            FROM evenements 
            WHERE YEAR(date_evenement) = YEAR(NOW())
            GROUP BY MONTH(date_evenement)
            ORDER BY mois
        ");
        return $stmt->fetchAll();
    }
    
    // Pour l'admin (recherche, tri, pagination)
    public function getAllAdmin($search = '', $sort = 'id', $order = 'DESC', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM evenements WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (titre LIKE ? OR ville LIKE ? OR type LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $allowedSort = ['id', 'titre', 'type', 'ville', 'date_evenement', 'nb_places', 'statut'];
        $sortColumn = in_array($sort, $allowedSort) ? $sort : 'id';
        $orderDirection = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sortColumn $orderDirection";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Compter pour l'admin
    public function countAllAdmin($search = '') {
        $sql = "SELECT COUNT(*) as total FROM evenements WHERE 1=1";
        $params = [];
        
        if(!empty($search)) {
            $sql .= " AND (titre LIKE ? OR ville LIKE ? OR type LIKE ?)";
            $searchTerm = "%$search%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
}
?>