<?php
require_once __DIR__ . '/../Model/ReclamationModel.php';

class ReclamationController {
    private $model;

    public function __construct() {
        $this->model = new ReclamationModel();
    }

    // ── Gestion des POST admin ────────────────────────────────────
    public function handlePost(): void {
        $action = $_POST['action'] ?? '';

        switch ($action) {

            case 'update_statut':
            case 'reclamation_statut':
                $this->model->updateStatut((int)$_POST['id'], $_POST['statut']);
                break;

            // L'admin répond : on écrit dans la table `reponse`
            case 'repondre':
                $id      = (int)$_POST['id'];
                $contenu = trim($_POST['reponse_admin'] ?? '');

                if (strlen($contenu) >= 10) {
                    $this->model->upsertReponse($id, $contenu);

                    // Changer le statut si demandé
                    $newStatut = $_POST['new_statut'] ?? '';
                    if (!empty($newStatut)) {
                        $this->model->updateStatut($id, $newStatut);
                    }
                    $_SESSION['msg'] = 'Réponse envoyée avec succès.';
                } else {
                    $_SESSION['err'] = 'La réponse doit contenir au moins 10 caractères.';
                }
                break;

            case 'reclamation_update':
            case 'update':
                $this->model->update((int)$_POST['id'], [
                    'titre'       => $_POST['titre'],
                    'description' => $_POST['description'],
                    'categorie'   => $_POST['categorie'],
                    'priorite'    => $_POST['priorite'],
                    'statut'      => $_POST['statut'],
                ]);
                $_SESSION['msg'] = 'Réclamation mise à jour.';
                break;

            case 'reclamation_delete':
            case 'delete':
                // ON DELETE CASCADE supprime aussi la réponse liée
                $this->model->delete((int)$_POST['id']);
                $_SESSION['msg'] = 'Réclamation supprimée.';
                break;

            case 'create_reclamation':
                $data = [
                    'utilisateur_id' => (int)($_POST['utilisateur_id'] ?? 0),
                    'titre'          => trim($_POST['titre'] ?? ''),
                    'description'    => trim($_POST['description'] ?? ''),
                    'categorie'      => $_POST['categorie'] ?? 'autre',
                    'priorite'       => $_POST['priorite'] ?? 'moyenne',
                    'statut'         => $_POST['statut'] ?? 'en_attente',
                ];
                $this->model->create($data);
                $_SESSION['msg'] = 'Réclamation créée.';
                break;
        }

        // Redirection post-POST pour éviter double soumission
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/ecoride/View/frontoffice/index.php'));
        exit;
    }

    // ── Affichage de la page admin ────────────────────────────────
    public function handleRequest(): void {
        $search    = $_GET['search']    ?? '';
        $statut    = $_GET['statut']    ?? '';
        $priorite  = $_GET['priorite']  ?? '';
        $categorie = $_GET['categorie'] ?? '';

        $all = $this->model->getAll();

        $reclamations = array_filter($all, function ($r) use ($search, $statut, $priorite, $categorie) {
            if ($statut    && $r['statut']    !== $statut)    return false;
            if ($priorite  && $r['priorite']  !== $priorite)  return false;
            if ($categorie && $r['categorie'] !== $categorie) return false;
            if ($search) {
                $s = strtolower($search);
                if (strpos(strtolower($r['titre']), $s) === false) return false;
            }
            return true;
        });

        $stats = $this->model->getStats();
        $msg   = $_SESSION['msg'] ?? null;
        $err   = $_SESSION['err'] ?? null;
        unset($_SESSION['msg'], $_SESSION['err']);

        include __DIR__ . '/../View/backoffice/admin_reclamations.php';
    }
}
?>