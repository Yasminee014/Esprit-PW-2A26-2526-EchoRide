<?php
require_once __DIR__ . '/../Model/ReclamationModel.php';

/**
 * Controller : ReclamationFrontController (FrontOffice)
 * Permet à l'utilisateur connecté de déposer et suivre ses réclamations.
 */
class ReclamationFrontController
{
    private ReclamationModel $model;

    public function __construct()
    {
        $this->model = new ReclamationModel();
    }

    /* ══════════════════════════════════════════
       Point d'entrée
    ══════════════════════════════════════════ */

    public function handleRequest(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Sécurité : utilisateur connecté requis
        if (empty($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $action = $_POST['action'] ?? null;

        if ($action === 'create_reclamation') {
            $this->create($userId);
        }

        $reclamations = $this->model->getByUser($userId);
        $msg = $_SESSION['msg'] ?? null;
        $err = $_SESSION['err'] ?? null;
        $formData = $_SESSION['form_data'] ?? [];
        $formErrors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['msg'], $_SESSION['err'], $_SESSION['form_data'], $_SESSION['form_errors']);

        require __DIR__ . '/../View/frontoffice/mes_reclamations.php';
    }

    /* ══════════════════════════════════════════
       Créer une réclamation
    ══════════════════════════════════════════ */

    private function create(int $userId): void
    {
        $data = [
            'utilisateur_id' => $userId,
            'titre'          => trim($_POST['titre']       ?? ''),
            'description'    => trim($_POST['description'] ?? ''),
            'categorie'      => trim($_POST['categorie']   ?? ''),
            'priorite'       => trim($_POST['priorite']    ?? ''),
        ];

        $errors = $this->validateFront($data);

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data']   = $data;
            $_SESSION['err']         = 'Veuillez corriger les erreurs du formulaire.';
            $this->redirect();
        }

        $ok = $this->model->create($data);
        if ($ok) {
            $_SESSION['msg'] = 'Votre réclamation a bien été soumise. Nous vous répondrons dans les meilleurs délais.';
        } else {
            $_SESSION['err'] = 'Une erreur est survenue. Veuillez réessayer.';
        }
        $this->redirect();
    }

    /* ══════════════════════════════════════════
       Validation serveur — SANS HTML5
    ══════════════════════════════════════════ */

    private function validateFront(array $d): array
    {
        $errors = [];
        $allowed_cat  = ['technique', 'paiement', 'securite', 'autre'];
        $allowed_prio = ['faible', 'moyenne', 'elevee'];

        if (strlen($d['titre']) < 3 || strlen($d['titre']) > 150)
            $errors['titre'] = 'Le titre doit contenir entre 3 et 150 caractères.';

        if (strlen($d['description']) < 10 || strlen($d['description']) > 2000)
            $errors['description'] = 'La description doit contenir entre 10 et 2000 caractères.';

        if (!in_array($d['categorie'], $allowed_cat, true))
            $errors['categorie'] = 'Veuillez choisir une catégorie valide.';

        if (!in_array($d['priorite'], $allowed_prio, true))
            $errors['priorite'] = 'Veuillez choisir une priorité valide.';

        return $errors;
    }

    private function redirect(): void
    {
        header('Location: mes_reclamations.php');
        exit;
    }
}
