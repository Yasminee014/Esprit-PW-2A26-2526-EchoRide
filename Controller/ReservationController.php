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

    /* ══════════════════════════════════════════
       BACKOFFICE — Admin
    ══════════════════════════════════════════ */

    public function adminIndex(): void {
        $filtre      = $_GET['statut'] ?? '';
        $reservations = $filtre
            ? $this->model->filterByStatut($filtre)
            : $this->model->getAll();

        $stats = [
            'total'      => $this->model->countAll(),
            'confirmees' => $this->model->countByStatut('confirmee'),
            'annulees'   => $this->model->countByStatut('annulee'),
            'en_attente' => $this->model->countByStatut('en_attente'),
        ];

        require __DIR__ . '/../View/backoffice/admin_reservations_view.php';
    }

    public function adminUpdateStatut(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_reservations.php');
            exit;
        }

        $id     = intval($_POST['id']     ?? 0);
        $statut = $_POST['statut']        ?? '';
        $valides = ['en_attente', 'confirmee', 'annulee'];

        if (!in_array($statut, $valides)) {
            $_SESSION['errors'] = ['Statut invalide.'];
            header('Location: admin_reservations.php');
            exit;
        }

        $this->model->updateStatut($id, $statut);
        $_SESSION['success'] = 'Statut mis à jour.';
        header('Location: admin_reservations.php');
        exit;
    }

    public function adminDelete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: admin_reservations.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);

        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Réservation supprimée.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la suppression.'];
        }

        header('Location: admin_reservations.php');
        exit;
    }

    /* ══════════════════════════════════════════
       FRONTOFFICE — Passager connecté
    ══════════════════════════════════════════ */

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
        require __DIR__ . '/../View/frontoffice/reserver_vehicule_view.php';
    }

    public function vehiculesDisponibles(): void {
        $vehicules = $this->vehiculeModel->getDisponibles();
        require __DIR__ . '/../View/frontoffice/vehicules_disponibles_view.php';
    }

    public function mesReservations(): void {
        $userId       = $_SESSION['user_id'] ?? 0;
        $reservations = $this->model->getByUserId($userId);
        require __DIR__ . '/../View/frontoffice/mes_reservations_view.php';
    }

    public function foCreate(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: vehicules_disponibles.php');
            exit;
        }

        // Si pas de session, prendre le premier utilisateur
        $userId = intval($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            $db = Database::getInstance();
            $first = $db->query("SELECT id FROM users LIMIT 1")->fetch();
            $userId = $first ? intval($first['id']) : 1;
        }
        $vehiculeId = intval($_POST['vehicule_id'] ?? 0);

        // Vérifier que le véhicule est bien disponible
        $vehicule = $this->vehiculeModel->getById($vehiculeId);
        if (!$vehicule || $vehicule['statut'] !== 'disponible') {
            $_SESSION['errors'] = ['Ce véhicule n\'est plus disponible.'];
            header('Location: vehicules_disponibles.php');
            exit;
        }

        $data = [
            'vehicule_id'      => $vehiculeId,
            'user_id'          => $userId,
            'trajet_id'        => intval($_POST['trajet_id'] ?? 0) ?: null,
            'date_reservation' => $_POST['date_reservation'] ?? date('Y-m-d'),
            'statut'           => 'en_attente',
        ];

        $errors = $this->model->validate($data);
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: reserver_vehicule.php?vehicule_id=' . $vehiculeId);
            exit;
        }

        if ($this->model->create($data)) {
            $_SESSION['success'] = 'Réservation effectuée avec succès !';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la réservation.'];
        }

        header('Location: mes_reservations.php');
        exit;
    }

    public function foAnnuler(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: mes_reservations.php');
            exit;
        }

        $id     = intval($_POST['id'] ?? 0);
        $userId = $this->getCurrentUserId();

        $resa = $this->model->getById($id);
        if (!$resa || $resa['user_id'] != $userId) {
            $_SESSION['errors'] = ['Accès refusé.'];
            header('Location: mes_reservations.php');
            exit;
        }

        if ($resa['statut'] === 'confirmee') {
            $_SESSION['errors'] = ['Impossible d\'annuler une réservation déjà confirmée.'];
            header('Location: mes_reservations.php');
            exit;
        }

        if ($this->model->updateStatut($id, 'annulee')) {
            $_SESSION['success'] = 'Réservation annulée.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de l\'annulation.'];
        }

        header('Location: mes_reservations.php');
        exit;
    }

    public function foSupprimer(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: mes_reservations.php');
            exit;
        }

        $id     = intval($_POST['id'] ?? 0);
        $userId = $this->getCurrentUserId();

        $resa = $this->model->getById($id);
        if (!$resa || $resa['user_id'] != $userId) {
            $_SESSION['errors'] = ['Accès refusé.'];
            header('Location: mes_reservations.php');
            exit;
        }

        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Réservation supprimée.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la suppression.'];
        }

        header('Location: mes_reservations.php');
        exit;
    }

    public function foModifier(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: mes_reservations.php');
            exit;
        }

        $id     = intval($_POST['id'] ?? 0);
        $userId = $this->getCurrentUserId();

        $resa = $this->model->getById($id);
        if (!$resa || $resa['user_id'] != $userId) {
            $_SESSION['errors'] = ['Accès refusé.'];
            header('Location: mes_reservations.php');
            exit;
        }

        if ($resa['statut'] !== 'en_attente') {
            $_SESSION['errors'] = ['Seules les réservations en attente peuvent être modifiées.'];
            header('Location: mes_reservations.php');
            exit;
        }

        $newDate = $_POST['date_reservation'] ?? '';
        $date    = DateTime::createFromFormat('Y-m-d', $newDate);
        if (!$date || $date < new DateTime('today')) {
            $_SESSION['errors'] = ['Date invalide ou passée.'];
            header('Location: mes_reservations.php');
            exit;
        }

        $data = [
            'vehicule_id'      => $resa['vehicule_id'],
            'user_id'          => $resa['user_id'],
            'trajet_id'        => $resa['trajet_id'],
            'date_reservation' => $newDate,
            'statut'           => $resa['statut'],
        ];

        if ($this->model->update($id, $data)) {
            $_SESSION['success'] = 'Réservation modifiée avec succès.';
        } else {
            $_SESSION['errors']  = ['Erreur lors de la modification.'];
        }

        header('Location: mes_reservations.php');
        exit;
    }

    /* ── Historique Admin ────────────────────── */

    public function adminHistorique(): void {
        $statut    = $_GET['statut']    ?? '';
        $dateDebut = $_GET['date_debut'] ?? '';
        $dateFin   = $_GET['date_fin']   ?? '';

        $reservations = $this->model->getHistoriqueAdmin($statut, $dateDebut, $dateFin);
        $stats        = $this->model->statsHistoriqueAdmin();

        require __DIR__ . '/../View/backoffice/admin_historique_view.php';
    }

    /* ── Historique Conducteur ───────────────── */

    public function conducteurHistorique(): void {
        $conducteurId = $this->getCurrentUserId();
        $reservations = $this->model->getHistoriqueConducteur($conducteurId);
        $stats        = $this->model->statsHistoriqueConducteur($conducteurId);

        require __DIR__ . '/../View/frontoffice/conducteur_historique_view.php';
    }

    public function monHistorique(): void {
        $userId       = $this->getCurrentUserId();
        $vehicules    = $this->vehiculeModel->getByUserId($userId);
        $reservations = $this->model->getMonHistoriqueReservations($userId);
        $stats        = $this->model->statsMonHistorique($userId);

        require __DIR__ . '/../View/frontoffice/mon_historique_view.php';
    }

    /* ── Helpers ─────────────────────────────── */

    private function getCurrentUserId(): int {
        if (!empty($_SESSION['user_id'])) {
            return intval($_SESSION['user_id']);
        }
        // Fallback : premier utilisateur (dev / démo)
        $db   = Database::getInstance();
        $first = $db->query("SELECT id FROM users LIMIT 1")->fetch();
        return $first ? intval($first['id']) : 1;
    }
}