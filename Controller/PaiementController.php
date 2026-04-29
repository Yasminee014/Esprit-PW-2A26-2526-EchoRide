<?php

require_once __DIR__ . '/../Model/ReservationModel.php';
require_once __DIR__ . '/../Model/VehiculeModel.php';

class ReservationController {

    private ReservationModel $model;
    private VehiculeModel    $vehiculeModel;

    public function __construct() {
        $this->model         = new ReservationModel();
        $this->vehiculeModel = new VehiculeModel();
    }

    public function showReservationForm(): void {
        $vehiculeId = intval($_GET['vehicule_id'] ?? 0);
        if (!$vehiculeId) {
            header('Location: vehicules_disponibles.php');
            exit;
        }
        $vehicule = $this->vehiculeModel->getById($vehiculeId);
        if (!$vehicule) {
            header('Location: vehicules_disponibles.php');
            exit;
        }

        $reservationResult = $_SESSION['reservation_result'] ?? null;
        unset($_SESSION['reservation_result']);

        require __DIR__ . '/../View/frontoffice/reserver_vehicule_view.php';
    }

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
        
        // Vérifier les conflits
        if (method_exists($this->model, 'verifierConflits')) {
            if ($this->model->verifierConflits($vehiculeId, $dateDebut, $dateFin)) {
                $response['message'] = 'Ce véhicule est déjà réservé sur cette période.';
                return $response;
            }
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
        
        $prixUnitaire = $trajet['prix_total'] ?? $trajet['prix'] ?? 0;
        $prixTotal = $prixUnitaire * $nbPlaces;
        
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
            'note' => $_POST['note'] ?? null,
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

    // Version API pour les appels AJAX
    public function foCreateAPI(): array {
        return $this->foCreate();
    }
}
?>