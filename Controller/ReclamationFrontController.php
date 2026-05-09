<?php
require_once __DIR__ . '/../Model/ReclamationModel.php';

class ReclamationFrontController
{
    private $model;

    public function __construct() {
        $this->model = new ReclamationModel();
    }

    public function handleRequest(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1;
        }
        
        $userId = (int)$_SESSION['user_id'];
        
        if (isset($_GET['action']) && $_GET['action'] === 'getUserReclamations') {
            header('Content-Type: application/json');
            $reclamations = $this->model->getByUserId($userId);
            echo json_encode($reclamations);
            return;
        }
        
        $reclamations = $this->model->getByUserId($userId);
        $msg = $_SESSION['msg'] ?? null;
        $err = $_SESSION['err'] ?? null;
        $formData = $_SESSION['form_data'] ?? [];
        $formErrors = $_SESSION['form_errors'] ?? [];
        
        unset($_SESSION['msg'], $_SESSION['err'], $_SESSION['form_data'], $_SESSION['form_errors']);
        
        include __DIR__ . '/../View/frontoffice/mes_reclamations.php';
    }
    
    public function handlePost(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (empty($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1;
        }
        
        $userId = (int)$_SESSION['user_id'];
        
        // Set JSON header for AJAX responses
        if (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false ||
            !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        if ($action === 'create_reclamation') {
            $this->create($userId);
        } elseif ($action === 'delete_reclamation') {
            $this->delete($userId);
        } elseif ($action === 'update_reclamation') {
            $this->update($userId);
        } elseif ($action === 'getUserReclamations') {
            header('Content-Type: application/json');
            $reclamations = $this->model->getByUserId($userId);
            echo json_encode($reclamations);
            return;
        }
    }

    private function create(int $userId): void {
        $data = [
            'utilisateur_id' => $userId,
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'categorie' => trim($_POST['categorie'] ?? ''),
            'priorite' => trim($_POST['priorite'] ?? 'moyenne'),
            'statut' => 'en_attente'
        ];
        
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        if ($this->model->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Réclamation soumise avec succès.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la soumission.']);
        }
    }
    
    private function update(int $userId): void {
        $id = (int)($_POST['id'] ?? 0);
        
        $reclamation = $this->model->getById($id);
        
        if (!$reclamation) {
            echo json_encode(['success' => false, 'error' => 'Réclamation introuvable.']);
            return;
        }
        if ($reclamation['utilisateur_id'] != $userId) {
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas modifier cette réclamation.']);
            return;
        }
        
        $data = [
            'titre' => trim($_POST['titre'] ?? $reclamation['titre']),
            'description' => trim($_POST['description'] ?? $reclamation['description']),
            'categorie' => trim($_POST['categorie'] ?? $reclamation['categorie']),
            'priorite' => trim($_POST['priorite'] ?? $reclamation['priorite']),
            'statut' => $reclamation['statut'],
            'reponse_admin' => $reclamation['reponse_admin']
        ];
        
        if ($this->model->update($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Réclamation modifiée.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la modification.']);
        }
    }
    
    private function delete(int $userId): void {
        $id = (int)($_POST['id'] ?? 0);
        
        $reclamation = $this->model->getById($id);
        
        if (!$reclamation) {
            echo json_encode(['success' => false, 'error' => 'Réclamation introuvable.']);
            return;
        }
        if ($reclamation['utilisateur_id'] != $userId) {
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas supprimer cette réclamation.']);
            return;
        }
        
        if ($this->model->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Réclamation supprimée avec succès.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression.']);
        }
    }
    
    private function validate(array $d): array {
        $errors = [];
        $allowedCat = ['technique', 'paiement', 'securite', 'autre'];
        $allowedPrio = ['faible', 'moyenne', 'elevee'];
        
        if (strlen($d['titre']) < 3) $errors['titre'] = 'Titre (min 3 caractères)';
        if (strlen($d['description']) < 10) $errors['description'] = 'Description (min 10 caractères)';
        if (!in_array($d['categorie'], $allowedCat)) $errors['categorie'] = 'Catégorie invalide';
        if (!in_array($d['priorite'], $allowedPrio)) $errors['priorite'] = 'Priorité invalide';
        
        return $errors;
    }
}
?>