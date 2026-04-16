<?php
// Controller/EventController.php
namespace Controller;

use Model\Event;
use Model\Sponsor;

class EventController {
    private $eventModel;
    private $sponsorModel;
    private $errors = [];
    
    public function __construct() {
        $this->eventModel = new Event();
        $this->sponsorModel = new Sponsor();
    }
    
    // ========== FRONTOFFICE ==========
    
    // Page d'accueil
    public function accueil() {
        $upcomingEvents = $this->eventModel->getUpcoming();
        $sponsors = $this->sponsorModel->getActive();
        require_once __DIR__ . '/../View/FrontOffice/accueil.php';
    }
    
    // Liste publique des événements
    public function publicEvents() {
        $events = $this->eventModel->getAll();
        require_once __DIR__ . '/../View/FrontOffice/events.php';
    }
    
    // Détail d'un événement
    public function eventDetail($id) {
        if (!$id) {
            header('Location: index.php?action=events');
            exit();
        }
        
        $event = $this->eventModel->getWithSponsors($id);
        if (!$event) {
            header('Location: index.php?action=events');
            exit();
        }
        
        require_once __DIR__ . '/../View/FrontOffice/event-detail.php';
    }
    
    // ========== BACKOFFICE ==========
    
    // Dashboard admin
    public function dashboard() {
        $totalEvents = $this->eventModel->countAll();
        $upcomingEvents = $this->eventModel->countUpcoming();
        $totalSponsors = $this->sponsorModel->countAll();
        $totalSponsoring = $this->sponsorModel->getTotalMontant();
        $recentEvents = $this->eventModel->getUpcoming();
        
        require_once __DIR__ . '/../View/BackOffice/dashboard.php';
    }
    
    // Liste des événements (CRUD)
    public function index() {
        $events = $this->eventModel->getAll();
        require_once __DIR__ . '/../View/BackOffice/events/list.php';
    }
    
    // Ajouter un événement
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateEvent($_POST);
            
            if (empty($this->errors)) {
                if ($this->eventModel->add($_POST)) {
                    header('Location: index.php?action=admin_events&success=added');
                    exit();
                }
            }
        }
        $event = null;
        require_once __DIR__ . '/../View/BackOffice/events/form.php';
    }
    
    // Modifier un événement
    public function edit($id) {
        if (!$id) {
            header('Location: index.php?action=admin_events');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateEvent($_POST);
            
            if (empty($this->errors)) {
                if ($this->eventModel->update($id, $_POST)) {
                    header('Location: index.php?action=admin_events&success=updated');
                    exit();
                }
            }
        }
        
        $event = $this->eventModel->getById($id);
        require_once __DIR__ . '/../View/BackOffice/events/form.php';
    }
    
    // Supprimer un événement
    public function delete($id) {
        if ($id) {
            $this->eventModel->delete($id);
        }
        header('Location: index.php?action=admin_events&success=deleted');
        exit();
    }
    
    // Validation serveur
    private function validateEvent($data) {
        if (empty($data['titre']) || strlen($data['titre']) < 3) {
            $this->errors['titre'] = 'Le titre doit contenir au moins 3 caractères';
        }
        
        if (empty($data['type'])) {
            $this->errors['type'] = 'Le type est obligatoire';
        }
        
        if (empty($data['ville'])) {
            $this->errors['ville'] = 'La ville est obligatoire';
        }
        
        if (empty($data['date_evenement'])) {
            $this->errors['date_evenement'] = 'La date est obligatoire';
        } elseif ($data['date_evenement'] < date('Y-m-d H:i:s')) {
            $this->errors['date_evenement'] = 'La date ne peut pas être dans le passé';
        }
        
        if (empty($data['nb_places']) || $data['nb_places'] <= 0) {
            $this->errors['nb_places'] = 'Le nombre de places doit être supérieur à 0';
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
}
?>