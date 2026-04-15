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
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_reclamation') {
            $this->create($userId);
        } elseif ($action === 'delete_reclamation') {
            $this->delete($userId);
        }
    }

    private function create(int $userId): void {
        $data = [
            'utilisateur_id' => $userId,
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'categorie' => trim($_POST['categorie'] ?? ''),
            'priorite' => trim($_POST['priorite'] ?? ''),
        ];
        
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $data;
            $_SESSION['err'] = 'Veuillez corriger les erreurs.';
            return;
        }
        
        if ($this->model->create($data)) {
            $_SESSION['msg'] = 'Réclamation soumise avec succès.';
        } else {
            $_SESSION['err'] = 'Erreur lors de la soumission.';
        }
    }
    
    private function delete(int $userId): void {
        $id = (int)($_POST['id'] ?? 0);
        
        $reclamation = $this->model->getById($id);
        
        if (!$reclamation) {
            $_SESSION['err'] = 'Réclamation introuvable.';
        } elseif ($reclamation['utilisateur_id'] != $userId) {
            $_SESSION['err'] = 'Vous ne pouvez pas supprimer cette réclamation.';
        } else {
            if ($this->model->delete($id)) {
                $_SESSION['msg'] = 'Réclamation supprimée avec succès.';
            } else {
                $_SESSION['err'] = 'Erreur lors de la suppression.';
            }
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