<?php
require_once __DIR__ . '/../Model/ReclamationModel.php';

class ReclamationController {
    private $model;

    public function __construct() {
        $this->model = new ReclamationModel();
    }

    public function handleRequest() {
        $search = $_GET['search'] ?? '';
        $statut = $_GET['statut'] ?? '';
        $priorite = $_GET['priorite'] ?? '';
        $categorie = $_GET['categorie'] ?? '';
        
        $allReclamations = $this->model->getAll();
        $reclamations = array_filter($allReclamations, function($r) use ($search, $statut, $priorite, $categorie) {
            if ($statut && $r['statut'] !== $statut) return false;
            if ($priorite && $r['priorite'] !== $priorite) return false;
            if ($categorie && $r['categorie'] !== $categorie) return false;
            if ($search) {
                $s = strtolower($search);
                if (strpos(strtolower($r['titre']), $s) === false && 
                    strpos(strtolower($r['utilisateur_nom'] ?? ''), $s) === false) {
                    return false;
                }
            }
            return true;
        });
        
        $stats = $this->model->getStats();
        $msg = $_SESSION['msg'] ?? null;
        $err = $_SESSION['err'] ?? null;
        unset($_SESSION['msg'], $_SESSION['err']);
        
        include __DIR__ . '/../View/backoffice/admin_reclamations.php';
    }
    
    public function handlePost() {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create_reclamation':
                $data = [
                    'utilisateur_id' => $_POST['utilisateur_id'] ?? $_SESSION['user_id'] ?? 1,
                    'titre' => $_POST['titre'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'categorie' => $_POST['categorie'] ?? '',
                    'priorite' => $_POST['priorite'] ?? 'moyenne',
                    'statut' => $_POST['statut'] ?? 'en_attente',
                    'reponse_admin' => $_POST['reponse_admin'] ?? null
                ];
                if ($this->model->create($data)) {
                    $_SESSION['msg'] = 'Réclamation ajoutée.';
                } else {
                    $_SESSION['err'] = 'Erreur lors de l\'ajout.';
                }
                break;
                
            case 'reclamation_update':
                $id = $_POST['id'] ?? 0;
                $data = [
                    'titre' => $_POST['titre'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'categorie' => $_POST['categorie'] ?? '',
                    'priorite' => $_POST['priorite'] ?? 'moyenne',
                    'statut' => $_POST['statut'] ?? 'en_attente',
                    'reponse_admin' => $_POST['reponse_admin'] ?? null
                ];
                if ($this->model->update($id, $data)) {
                    $_SESSION['msg'] = 'Réclamation modifiée.';
                } else {
                    $_SESSION['err'] = 'Erreur lors de la modification.';
                }
                break;
                
            case 'reclamation_delete':
                $id = $_POST['id'] ?? 0;
                if ($this->model->delete($id)) {
                    $_SESSION['msg'] = 'Réclamation supprimée.';
                } else {
                    $_SESSION['err'] = 'Erreur lors de la suppression.';
                }
                break;
                
            case 'reclamation_statut':
                $id = $_POST['id'] ?? 0;
                $statut = $_POST['statut'] ?? '';
                if ($this->model->updateStatut($id, $statut)) {
                    $_SESSION['msg'] = 'Statut mis à jour.';
                }
                break;
        }
    }
}
?>