<?php

require_once __DIR__ . '/../Model/ReservationModel.php';
require_once __DIR__ . '/../Model/VehiculeModel.php';
require_once __DIR__ . '/../Config/Database.php';

class ReservationController {

    private ReservationModel $model;
    private VehiculeModel    $vehiculeModel;

    public function __construct() {
        $this->model         = new ReservationModel();
        $this->vehiculeModel = new VehiculeModel();
    }

    /**
     * Affiche le formulaire de réservation
     */
    public function showReservationForm(): void {
        $vehiculeId = intval($_GET['vehicule_id'] ?? 0);
        $trajetId   = intval($_GET['trajet_id']   ?? 0);

        // Si vehicule_id absent, on le retrouve via trajet_id
        if (!$vehiculeId) {
            if ($trajetId) {
                $db = Database::getInstance();

                // 1. Essai par trajet_id (si la colonne existe)
                try {
                    $check = $db->query("SHOW COLUMNS FROM vehicules LIKE 'trajet_id'");
                    if ($check->fetch()) {
                        $stmt = $db->prepare("SELECT id FROM vehicules WHERE trajet_id = ? ORDER BY id DESC LIMIT 1");
                        $stmt->execute([$trajetId]);
                        $row = $stmt->fetch();
                        if ($row) {
                            $vehiculeId = intval($row['id']);
                        }
                    }
                } catch (Exception $e) {}

                // 2. Fallback : véhicule du conducteur du trajet
                if (!$vehiculeId) {
                    try {
                        $stmt = $db->prepare("SELECT v.id FROM vehicules v INNER JOIN trajet t ON v.user_id = t.id_u WHERE t.id_T = ? ORDER BY v.id DESC LIMIT 1");
                        $stmt->execute([$trajetId]);
                        $row = $stmt->fetch();
                        if ($row) {
                            $vehiculeId = intval($row['id']);
                        }
                    } catch (Exception $e) {}
                }

                // 3. Fallback global : premier véhicule disponible du système
                if (!$vehiculeId) {
                    try {
                        $db = Database::getInstance();
                        $stmt = $db->query("SELECT id FROM vehicules WHERE statut = 'disponible' ORDER BY id DESC LIMIT 1");
                        $row = $stmt->fetch();
                        if ($row) {
                            $vehiculeId = intval($row['id']);
                        }
                    } catch (Exception $e) {}
                }

                if ($vehiculeId) {
                    $qs = http_build_query(array_merge($_GET, ['vehicule_id' => $vehiculeId]));
                    header('Location: reserver_vehicule.php?' . $qs);
                    exit;
                }

                // Aucun véhicule trouvé — afficher une page d'erreur conviviale
                $_SESSION['reservation_error'] = 'Aucun véhicule disponible pour ce trajet. Veuillez réessayer ultérieurement.';
                header('Location: tous_les_trajets.php?error=no_vehicle');
                exit;
            }
            header('Location: tous_les_trajets.php');
            exit;
        }
        $vehicule = $this->vehiculeModel->getById($vehiculeId);
        if (!$vehicule) {
            $_SESSION['reservation_error'] = 'Le véhicule demandé est introuvable.';
            header('Location: tous_les_trajets.php?error=not_found');
            exit;
        }

        $reservationResult = $_SESSION['reservation_result'] ?? null;
        unset($_SESSION['reservation_result']);

        require __DIR__ . '/../View/frontoffice/reserver_vehicule_view.php';
    }

    /**
     * Crée une réservation (version standard)
     */
    public function foCreate(): array {
    $response = ['success' => false, 'message' => '', 'prix_total' => 0, 'reservation_id' => 0];
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Méthode non autorisée.';
        return $response;
    }

    $userId = intval($_SESSION['user_id'] ?? 0);
    if (!$userId) {
        $db = Database::getInstance();
        $first = $db->query("SELECT id FROM users LIMIT 1")->fetch();
        $userId = $first ? intval($first['id']) : 1;
    }
    
    $vehiculeId = intval($_POST['vehicule_id'] ?? 0);
    $trajetId = intval($_POST['trajet_id'] ?? 0);
    $nbPlaces = intval($_POST['nb_places'] ?? 1);
    $dateDebut = $_POST['date_debut'] ?? null;
    $dateFin = $_POST['date_fin'] ?? null;
    $heure = $_POST['heure'] ?? null;
    
    // Vérifications
    $vehicule = $this->vehiculeModel->getById($vehiculeId);
    if (!$vehicule || $vehicule['statut'] !== 'disponible') {
        $response['message'] = 'Ce véhicule n\'est plus disponible.';
        return $response;
    }
    
    if ($nbPlaces < 1 || $nbPlaces > $vehicule['capacite']) {
        $response['message'] = 'Nombre de places invalide. Maximum ' . $vehicule['capacite'] . ' places.';
        return $response;
    }
    
    if (!$dateDebut || !$dateFin) {
        $response['message'] = 'Les dates sont obligatoires.';
        return $response;
    }
    
    if (strtotime($dateFin) < strtotime($dateDebut)) {
        $response['message'] = 'La date de fin doit être postérieure à la date de début.';
        return $response;
    }
    
    // Récupérer le trajet
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM trajet WHERE id_T = :id");
    $stmt->execute([':id' => $trajetId]);
    $trajet = $stmt->fetch();
    
    if (!$trajet) {
        $response['message'] = 'Destination invalide.';
        return $response;
    }
    
    // Utiliser le prix de destination si fourni (via formulaire), sinon le prix du trajet
    $prixFormulaire = (float)($_POST['prix_trajet'] ?? 0);
    $prixUnitaire = $prixFormulaire > 0 ? $prixFormulaire : ($trajet['prix_total'] ?? $trajet['prix'] ?? 0);
    $nbJours = ceil((strtotime($dateFin) - strtotime($dateDebut)) / (60 * 60 * 24)) + 1;
    $prixTotal = $prixUnitaire * $nbJours;
    
    $destinationNom = trim((string)($_POST['destination_nom'] ?? ''));
    $note = $destinationNom !== '' ? $destinationNom : ($_POST['note'] ?? null);
    
    $data = [
        'vehicule_id' => $vehiculeId,
        'user_id' => $userId,
        'trajet_id' => $trajetId,
        'date_debut' => $dateDebut,
        'date_fin' => $dateFin,
        'heure' => $heure,
        'nb_places' => $nbPlaces,
        'prix_total' => $prixTotal,
        'statut' => 'en_attente',
        'note' => $note,
    ];
    
    $errors = $this->model->validate($data);
    if (!empty($errors)) {
        $response['message'] = implode(', ', $errors);
        return $response;
    }
    
    // Créer la réservation
    $reservationId = null;
    
    if (method_exists($this->model, 'createAndGetId')) {
        $reservationId = $this->model->createAndGetId($data);
    } else {
        if ($this->model->create($data)) {
            $db = Database::getInstance();
            $reservationId = (int)$db->lastInsertId();
        }
    }
    
    if ($reservationId) {
        $_SESSION['pending_reservation_id'] = $reservationId;
        
        $response['success'] = true;
        $response['message'] = 'Réservation créée !';
        $response['prix_total'] = $prixTotal;
        $response['reservation_id'] = $reservationId;
        return $response;
    } else {
        $response['message'] = 'Erreur lors de la réservation. Vérifiez que la table reservations existe.';
        return $response;
    }
}

    /**
     * Affiche l'historique de l'utilisateur connecté.
     */
    public function monHistorique(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: login.php');
            exit;
        }

        $vehicules = $this->vehiculeModel->getByUserId($userId);
        $reservations = $this->model->getMonHistoriqueReservations($userId);
        $historiqueGlobal = $this->model->getMonHistoriqueGlobal($userId);
        $stats = $this->model->statsMonHistorique($userId);

        require __DIR__ . '/../View/frontoffice/mon_historique_view.php';
    }

    /* ══════════════════════════════════════════
       BACKOFFICE — Admin réservations
    ══════════════════════════════════════════ */
    public function adminIndex(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $statut = trim((string)($_GET['statut'] ?? ''));
        if ($statut !== '' && in_array($statut, ['en_attente', 'confirmee', 'annulee'], true)) {
            $reservations = $this->model->filterByStatut($statut);
        } else {
            $reservations = $this->model->getAll();
        }

        $stats = [
            'total' => $this->model->countAll(),
            'en_attente' => $this->model->countByStatut('en_attente'),
            'confirmees' => $this->model->countByStatut('confirmee'),
            'annulees' => $this->model->countByStatut('annulee'),
        ];

        require __DIR__ . '/../View/backoffice/admin_reservations_view.php';
    }

    public function adminUpdateStatut(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_reservations.php');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $statut = trim((string)($_POST['statut'] ?? ''));
        $allowed = ['en_attente', 'confirmee', 'annulee'];

        if ($id <= 0 || !in_array($statut, $allowed, true)) {
            $_SESSION['errors'] = ['Données invalides pour la mise à jour du statut.'];
            header('Location: admin_reservations.php');
            exit;
        }

        if ($this->model->updateStatut($id, $statut)) {
            $_SESSION['success'] = 'Statut de réservation mis à jour.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la mise à jour du statut.'];
        }

        header('Location: admin_reservations.php');
        exit;
    }

    public function adminDelete(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_reservations.php');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['errors'] = ['ID réservation invalide.'];
            header('Location: admin_reservations.php');
            exit;
        }

        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Réservation supprimée avec succès.';
        } else {
            $_SESSION['errors'] = ['Erreur lors de la suppression de la réservation.'];
        }

        header('Location: admin_reservations.php');
        exit;
    }
}
?>