<?php
require_once __DIR__ . '/../Model/ReclamationModel.php';

/**
 * Controller : ReclamationController (BackOffice Admin)
 * Respecte le pattern MVC + POO.
 * Validation sans HTML5 (côté serveur PHP + côté client JS).
 */
class ReclamationController
{
    private ReclamationModel $model;

    public function __construct()
    {
        $this->model = new ReclamationModel();
    }

    /* ══════════════════════════════════════════
       Point d'entrée principal
    ══════════════════════════════════════════ */

    public function handleRequest(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $action = $_POST['action'] ?? $_GET['action'] ?? null;

        switch ($action) {
            case 'reclamation_statut':  $this->updateStatut();   break;
            case 'reclamation_reponse': $this->saveReponse();    break;
            case 'reclamation_delete':  $this->delete();         break;
            case 'reclamation_update':  $this->update();         break;
        }

        // Données pour la vue
        $search    = trim($_GET['search']    ?? '');
        $fStatut   = trim($_GET['statut']    ?? '');
        $fPriorite = trim($_GET['priorite']  ?? '');
        $fCategorie= trim($_GET['categorie'] ?? '');

        $reclamations = $this->model->getAll($search, $fStatut, $fPriorite, $fCategorie);
        $stats        = $this->model->countByStatut();
        $selected     = isset($_GET['id']) ? $this->model->getById((int)$_GET['id']) : null;

        $msg = $_SESSION['msg'] ?? null;
        $err = $_SESSION['err'] ?? null;
        unset($_SESSION['msg'], $_SESSION['err']);

        require __DIR__ . '/../View/backoffice/admin_reclamations.php';
    }

    /* ══════════════════════════════════════════
       ACTIONS
    ══════════════════════════════════════════ */

    private function updateStatut(): void
    {
        $id     = (int)($_POST['id'] ?? 0);
        $statut = trim($_POST['statut'] ?? '');
        $allowed = ['en_attente', 'en_cours', 'resolue', 'rejetee'];

        if ($id > 0 && in_array($statut, $allowed, true)) {
            $this->model->updateStatut($id, $statut);
            $_SESSION['msg'] = 'Statut mis à jour avec succès.';
        } else {
            $_SESSION['err'] = 'Données invalides.';
        }
        $this->redirect();
    }

    private function saveReponse(): void
    {
        $id      = (int)($_POST['id'] ?? 0);
        $reponse = trim($_POST['reponse_admin'] ?? '');
        $statut  = trim($_POST['statut'] ?? 'en_cours');

        $errors = [];
        if ($id <= 0)            $errors[] = 'ID invalide.';
        if (strlen($reponse) < 5) $errors[] = 'La réponse doit faire au moins 5 caractères.';

        if ($errors) {
            $_SESSION['err'] = implode(' ', $errors);
            $this->redirect();
        }

        $existing = $this->model->getById($id);
        if (!$existing) {
            $_SESSION['err'] = 'Réclamation introuvable.';
            $this->redirect();
        }

        $this->model->update($id, array_merge($existing, [
            'statut'       => $statut,
            'reponse_admin'=> $reponse,
        ]));
        $_SESSION['msg'] = 'Réponse enregistrée.';
        $this->redirect();
    }

    private function update(): void
    {
        $id   = (int)($_POST['id'] ?? 0);
        $data = $this->collectPost();
        $errors = $this->validateAdmin($data);

        if ($id <= 0) $errors[] = 'ID invalide.';

        if ($errors) {
            $_SESSION['err'] = implode(' | ', $errors);
            $this->redirect();
        }

        $this->model->update($id, $data);
        $_SESSION['msg'] = 'Réclamation modifiée.';
        $this->redirect();
    }

    private function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $_SESSION['msg'] = 'Réclamation supprimée.';
        } else {
            $_SESSION['err'] = 'ID invalide.';
        }
        $this->redirect();
    }

    /* ══════════════════════════════════════════
       Validation serveur (sans HTML5)
    ══════════════════════════════════════════ */

    private function validateAdmin(array $d): array
    {
        $errors = [];
        $allowed_cat  = ['technique', 'paiement', 'securite', 'autre'];
        $allowed_prio = ['faible', 'moyenne', 'elevee'];
        $allowed_stat = ['en_attente', 'en_cours', 'resolue', 'rejetee'];

        if (strlen($d['titre']) < 3)
            $errors[] = 'Le titre doit faire au moins 3 caractères.';
        if (strlen($d['description']) < 10)
            $errors[] = 'La description doit faire au moins 10 caractères.';
        if (!in_array($d['categorie'], $allowed_cat, true))
            $errors[] = 'Catégorie invalide.';
        if (!in_array($d['priorite'], $allowed_prio, true))
            $errors[] = 'Priorité invalide.';
        if (!in_array($d['statut'], $allowed_stat, true))
            $errors[] = 'Statut invalide.';

        return $errors;
    }

    private function collectPost(): array
    {
        return [
            'titre'        => trim($_POST['titre']        ?? ''),
            'description'  => trim($_POST['description']  ?? ''),
            'categorie'    => trim($_POST['categorie']    ?? ''),
            'priorite'     => trim($_POST['priorite']     ?? ''),
            'statut'       => trim($_POST['statut']       ?? 'en_attente'),
            'reponse_admin'=> trim($_POST['reponse_admin']?? ''),
            'utilisateur_id'=> (int)($_POST['utilisateur_id'] ?? 0),
        ];
    }

    private function redirect(): void
    {
        header('Location: admin_reclamations.php');
        exit;
    }
}
