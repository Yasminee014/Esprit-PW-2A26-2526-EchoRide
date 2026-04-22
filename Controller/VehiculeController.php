<?php

require_once __DIR__ . '/../Model/VehiculeModel.php';
require_once __DIR__ . '/../Model/ReservationModel.php';

class VehiculeController {

    private VehiculeModel    $model;
    private ReservationModel $resaModel;

    public function __construct() {
        $this->model     = new VehiculeModel();
        $this->resaModel = new ReservationModel();
    }

    /* ══════════════════════════════════════════
       BACKOFFICE — Admin
    ══════════════════════════════════════════ */

    public function adminShowCreateForm(): void {
        $db    = Database::getInstance();
        $users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();
        require __DIR__ . '/../View/backoffice/admin_ajouter_vehicule_view.php';
    }

    public function adminShowEditForm(): void {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: admin_vehicules.php');
            exit;
        }
        $vehicule = $this->model->getById($id);
        if (!$vehicule) {
            $_SESSION['errors'] = ['Véhicule introuvable.'];
            header('Location: admin_vehicules.php');
            exit;
        }
        $db    = Database::getInstance();
        $users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();
        require __DIR__ . '/../View/backoffice/admin_modifier_vehicule_view.php';
    }

    public function adminIndex(): void {
        $search     = trim($_GET['search'] ?? '');
        $vehicules  = $search ? $this->model->search($search) : $this->model->getAll();

        $stats = [
            'total'       => $this->model->countAll(),
            'disponibles' => $this->model->countByStatut('disponible'),
            'maintenance' => $this->model->countByStatut('en_maintenance'),
            'indisponibles' => $this->model->countByStatut('indisponible'),
        ];

        $db = Database::getInstance();
        $users = $db->query("SELECT id, nom, prenom FROM users ORDER BY nom, prenom")->fetchAll();

        require __DIR__ . '/../View/backoffice/admin_vehicules_view.php';
    }

    public function adminCreate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_vehicules.php');
            exit;
        }

        $data = $this->sanitize($_POST);

        // Si user_id est 0 ou vide, prendre le premier utilisateur disponible
        if (empty($data['user_id'])) {
            $db = Database::getInstance();
            $first = $db->query("SELECT id FROM users LIMIT 1")->fetch();
            $data['user_id'] = $first ? intval($first['id']) : 1;
        }

        $errors = $this->model->validate($data);

        if (!empty($errors)) {
            $_SESSION['errors']  = $errors;
            $_SESSION['old']     = $data;
            header('Location: admin_ajouter_vehicule.php');
            exit;
        }

        if ($this->model->create($data)) {
            $_SESSION['success'] = 'Véhicule ajouté avec succès.';
        } else {
            $_SESSION['errors']  = ["Erreur lors de l'ajout du véhicule."];
        }

        header('Location: admin_vehicules.php');
        exit;
    }

    public function adminUpdate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_vehicules.php');
            exit;
        }

        $id   = intval($_POST['id'] ?? 0);
        $data = $this->sanitize($_POST);
        $errors = $this->model->validate($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: admin_vehicules.php');
            exit;
        }

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = 'Véhicule modifié avec succès.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la modification.'];
        }

        header('Location: admin_vehicules.php');
        exit;
    }

    public function adminUpdateStatut(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_vehicules.php');
            exit;
        }

        $id     = intval($_POST['id'] ?? 0);
        $statut = $_POST['statut'] ?? '';
        $statuts_valides = ['disponible', 'indisponible', 'en_maintenance'];

        if (!in_array($statut, $statuts_valides)) {
            $_SESSION['errors'] = ['Statut invalide.'];
            header('Location: admin_vehicules.php');
            exit;
        }

        $this->model->updateStatut($id, $statut);
        header('Location: admin_vehicules.php');
        exit;
    }

    public function adminDelete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_vehicules.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Véhicule supprimé avec succès.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la suppression.'];
        }

        header('Location: admin_vehicules.php');
        exit;
    }

    /* ══════════════════════════════════════════
       FRONTOFFICE — Conducteur connecté
    ══════════════════════════════════════════ */

    public function mesVehicules(): void {
        $userId   = $_SESSION['user_id'] ?? 0;
        $vehicules = $this->model->getByUserId($userId);

        // Compteurs de réservations par véhicule
        $resaCounts = [];
        foreach ($vehicules as $v) {
            $resaCounts[$v['id']] = $this->resaModel->countByVehiculeId($v['id']);
        }

        require __DIR__ . '/../View/frontoffice/mes_vehicules_view.php';
    }

    public function foCreate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: mes_vehicules.php');
            exit;
        }

        // Gérer l'upload de la photo
        $photoName = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/uploads/vehicules/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            
            if (in_array(strtolower($extension), $allowedExtensions)) {
                $photoName = uniqid('vehicule_') . '.' . $extension;
                $uploadPath = $uploadDir . $photoName;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath);
            }
        }

        $data = [
            'user_id' => $_SESSION['user_id'] ?? 0,
            'marque' => trim($_POST['marque'] ?? ''),
            'modele' => trim($_POST['modele'] ?? ''),
            'immatriculation' => strtoupper(trim($_POST['immatriculation'] ?? '')),
            'couleur' => trim($_POST['couleur'] ?? ''),
            'capacite' => intval($_POST['capacite'] ?? 4),
            'climatisation' => isset($_POST['climatisation']) ? 1 : 0,
            'statut' => $_POST['statut'] ?? 'disponible',
            'photo' => $photoName
        ];

        $errors = $this->model->validate($data);
        
        if ($this->model->immatriculationExists($data['immatriculation'])) {
            $errors[] = "Cette immatriculation (" . htmlspecialchars($data['immatriculation']) . ") est déjà utilisée par un autre véhicule.";
        }
        
        if (empty($errors)) {
            if ($this->model->create($data)) {
                $_SESSION['success'] = 'Véhicule ajouté avec succès.';
            } else {
                $_SESSION['errors'] = ['Erreur lors de l\'ajout.'];
            }
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
        }

        header('Location: mes_vehicules.php');
        exit;
    }

    public function foUpdate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: mes_vehicules.php');
            exit;
        }

        $id      = intval($_POST['id'] ?? 0);
        $userId  = $_SESSION['user_id'] ?? 0;

        // Vérifier que le véhicule appartient bien au conducteur
        $vehicule = $this->model->getById($id);
        if (!$vehicule || $vehicule['user_id'] != $userId) {
            $_SESSION['errors'] = ['Accès refusé.'];
            header('Location: mes_vehicules.php');
            exit;
        }

        $data   = $this->sanitize($_POST);
        $errors = $this->model->validate($data);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: mes_vehicules.php');
            exit;
        }

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = 'Véhicule modifié avec succès.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la modification.'];
        }

        header('Location: mes_vehicules.php');
        exit;
    }

    public function foDelete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: mes_vehicules.php');
            exit;
        }

        $id     = intval($_POST['id'] ?? 0);
        $userId = $_SESSION['user_id'] ?? 0;

        $vehicule = $this->model->getById($id);
        if (!$vehicule || $vehicule['user_id'] != $userId) {
            $_SESSION['errors'] = ['Accès refusé.'];
            header('Location: mes_vehicules.php');
            exit;
        }

        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Véhicule supprimé.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la suppression.'];
        }

        header('Location: mes_vehicules.php');
        exit;
    }

    /* ─────────────────── UTILITAIRE ─────────────────── */
    private function sanitize(array $data): array {
        return [
            'user_id'          => intval($data['user_id'] ?? 0) ?: intval($_SESSION['user_id'] ?? 0),
            'marque'           => htmlspecialchars(trim($data['marque']          ?? ''), ENT_QUOTES, 'UTF-8'),
            'modele'           => htmlspecialchars(trim($data['modele']          ?? ''), ENT_QUOTES, 'UTF-8'),
            'immatriculation'  => strtoupper(trim($data['immatriculation']       ?? '')),
            'couleur'          => htmlspecialchars(trim($data['couleur']          ?? ''), ENT_QUOTES, 'UTF-8'),
            'capacite'         => intval($data['capacite']          ?? 4),
            'climatisation'    => isset($data['climatisation']) ? 1 : 0,
            'statut'           => $data['statut']                   ?? 'disponible',
        ];
    }
}
?>