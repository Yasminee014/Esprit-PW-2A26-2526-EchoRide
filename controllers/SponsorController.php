<?php
// controllers/SponsorController.php
namespace Controllers;

use Model\Sponsor;
use Model\Event;

class SponsorController {
    private $sponsorModel;
    private $eventModel;
    private $errors = [];
    
    public function __construct() {
        $this->sponsorModel = new Sponsor();
        $this->eventModel = new Event();
    }
    
    // Liste des sponsors (CRUD)
    public function index() {
        $sponsors = $this->sponsorModel->getAll();
        require_once __DIR__ . '/../views/backoffice/sponsors/list.php';
    }
    
    // Ajouter un sponsor
    public function add() {
        $events = $this->eventModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateSponsor($_POST);
            
            if (empty($this->errors)) {
                if ($this->sponsorModel->add($_POST)) {
                    header('Location: index.php?action=admin_sponsors&success=added');
                    exit();
                }
            }
        }
        $sponsor = null;
        require_once __DIR__ . '/../views/backoffice/sponsors/form.php';
    }
    
    // Modifier un sponsor
    public function edit($id) {
        if (!$id) {
            header('Location: index.php?action=admin_sponsors');
            exit();
        }
        
        $events = $this->eventModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateSponsor($_POST);
            
            if (empty($this->errors)) {
                if ($this->sponsorModel->update($id, $_POST)) {
                    header('Location: index.php?action=admin_sponsors&success=updated');
                    exit();
                }
            }
        }
        
        $sponsor = $this->sponsorModel->getById($id);
        require_once __DIR__ . '/../views/backoffice/sponsors/form.php';
    }
    
    // Supprimer un sponsor
    public function delete($id) {
        if ($id) {
            $this->sponsorModel->delete($id);
        }
        header('Location: index.php?action=admin_sponsors&success=deleted');
        exit();
    }
    
    // Validation serveur
    private function validateSponsor($data) {
        if (empty($data['nom_entreprise']) || strlen($data['nom_entreprise']) < 2) {
            $this->errors['nom_entreprise'] = 'Le nom doit contenir au moins 2 caractères';
        }
        
        if (empty($data['montant_sponsoring']) || $data['montant_sponsoring'] <= 0) {
            $this->errors['montant_sponsoring'] = 'Le montant doit être supérieur à 0';
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
    // Récupérer les sponsors d'un événement spécifique
public function getByEventId($eventId) {
    $stmt = $this->db->prepare("SELECT * FROM sponsors WHERE evenement_id = ? AND statut = 'confirme'");
    $stmt->execute([$eventId]);
    return $stmt->fetchAll();
}
}
?>