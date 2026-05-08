<?php

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Model/PaiementModel.php';
require_once __DIR__ . '/../Model/ReservationModel.php';
require_once __DIR__ . '/../Model/VehiculeModel.php';
require_once __DIR__ . '/../Model/StripeService.php';
require_once __DIR__ . '/../Model/D17Service.php';

class PaiementController {

    private PaiementModel $paiementModel;
    private ReservationModel $reservationModel;
    private VehiculeModel $vehiculeModel;

    public function __construct() {
        $this->paiementModel = new PaiementModel();
        $this->reservationModel = new ReservationModel();
        $this->vehiculeModel = new VehiculeModel();
    }

    public function choixPaiement(): void {
        [$reservation, $vehicule, $trajet] = $this->chargerReservationEtVehicule();

        $infosBancaires = [
            'titulaire' => 'EcoRide SARL',
            'banque' => 'STB',
            'iban' => 'TN59 1234 5678 9012 3456 7890',
            'bic' => 'STBKTNTT',
        ];

        require __DIR__ . '/../View/frontoffice/choix_paiement_view.php';
    }

    public function detailsPaiement(): void {
        [$reservation, $vehicule, $trajet] = $this->chargerReservationEtVehicule();

        $mode = trim((string)($_GET['mode'] ?? ''));
        if (!in_array($mode, ['carte', 'd17'], true)) {
            header('Location: /ecoride/View/frontoffice/choix_paiement.php?id=' . (int)$reservation['id']);
            exit;
        }

        // Pour le paiement par carte, rediriger vers Stripe Checkout
        if ($mode === 'carte') {
            $this->redirigerVersStripe((int)$reservation['id'], $reservation);
            return;
        }

        // Pour D17, rediriger vers le checkout D17
        if ($mode === 'd17') {
            $this->redirigerVersD17((int)$reservation['id'], $reservation);
            return;
        }

        require __DIR__ . '/../View/frontoffice/paiement_details_view.php';
    }

    private function redirigerVersStripe(int $reservationId, array $reservation): void {
        $cfg       = require __DIR__ . '/../Config/StripeConfig.php';
        $secretKey = $cfg['secret_key'] ?? '';

        if (strpos($secretKey, 'REMPLACEZ') !== false || $secretKey === '') {
            require __DIR__ . '/../View/frontoffice/stripe_config_error.php';
            exit;
        }

        $montant = (float)($reservation['prix_total'] ?? 1);
        if ($montant <= 0) $montant = 1.0;

        $description = 'Réservation EcoRide #' . $reservationId;
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $successUrl = $baseUrl . '/ecoride/View/frontoffice/paiement_success.php'
            . '?session_id={CHECKOUT_SESSION_ID}&reservation_id=' . $reservationId;
        $cancelUrl  = $baseUrl . '/ecoride/View/frontoffice/paiement_cancel.php'
            . '?reservation_id=' . $reservationId;

        try {
            $stripe  = new StripeService($secretKey);
            // Conversion TND → EUR avant envoi à Stripe
            $tauxTndEur  = (float)($cfg['tnd_to_eur_rate'] ?? 3.30);
            $montantEur  = round($montant / $tauxTndEur, 2);
            if ($montantEur < 0.50) $montantEur = 0.50; // minimum Stripe
            $descriptionAvecDevise = $description . ' (' . number_format($montant, 2) . ' DT)';
            $session = $stripe->createCheckoutSession(
                $reservationId, $montantEur, $descriptionAvecDevise, $successUrl, $cancelUrl, 'eur'
            );
            $this->paiementModel->enregistrerPaiement($reservationId, 'carte', $montant);

            header('Location: ' . $session['url']);
            exit;
        } catch (\RuntimeException $e) {
            error_log('[Stripe] createCheckoutSession error: ' . $e->getMessage());
            header('Location: /ecoride/View/frontoffice/paiement_cancel.php?reservation_id=' . $reservationId . '&stripe_error=1');
            exit;
        }
    }

    public function traiterPaiement(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $reservationId = intval($_POST['reservation_id'] ?? 0);
        $mode = trim($_POST['mode_paiement'] ?? '');
        $modesAutorises = ['carte', 'sur_place', 'd17', 'virement'];

        if (!$reservationId || !in_array($mode, $modesAutorises, true)) {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        if ($mode === 'carte' && !$this->validerDonneesCarte($_POST)) {
            header('Location: /ecoride/View/frontoffice/paiement.php?action=details_paiement&mode=carte&id=' . $reservationId);
            exit;
        }

        if ($mode === 'd17' && !$this->validerDonneesD17($_POST)) {
            header('Location: /ecoride/View/frontoffice/paiement.php?action=details_paiement&mode=d17&id=' . $reservationId);
            exit;
        }

        $reservation = $this->reservationModel->getById($reservationId);
        if (!$reservation) {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $montant = (float)($reservation['prix_total'] ?? 0);
        if ($montant <= 0) {
            $montant = 1.0;
        }

        // Compatibilite DB: la colonne enum peut encore contenir "virement".
        $modeEnregistre = ($mode === 'd17') ? 'virement' : $mode;
        $paiementId = $this->paiementModel->enregistrerPaiement($reservationId, $modeEnregistre, $montant);
        if (!$paiementId) {
            header('Location: /ecoride/View/frontoffice/paiement_cancel.php?reservation_id=' . $reservationId);
            exit;
        }

        if ($mode === 'carte') {
            $ref = 'PAY-' . $reservationId . '-' . time();
            $this->paiementModel->validerPaiement((int)$paiementId, $ref);
            $this->reservationModel->updateStatut($reservationId, 'confirmee');
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php?reservation_success=1');
            exit;
        }

        if ($mode === 'sur_place' || $mode === 'd17') {
            $this->reservationModel->updateStatut($reservationId, 'confirmee');
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php?reservation_success=1');
            exit;
        }

        // Fallback
        $this->reservationModel->updateStatut($reservationId, 'confirmee');
        header('Location: /ecoride/View/frontoffice/tous_les_trajets.php?reservation_success=1');
        exit;
    }

    public function uploadJustificatif(): void {
        $reservationId = intval($_POST['reservation_id'] ?? 0);
        header('Location: /ecoride/View/frontoffice/paiement_success.php?reservation_id=' . $reservationId);
        exit;
    }

    public function paiementSuccess(): void {
        $reservationId = intval($_GET['reservation_id'] ?? 0);
        if (!$reservationId) {
            $reservationId = intval($_SESSION['pending_reservation_id'] ?? 0);
        }

        // Vérification Stripe si session_id présent
        $sessionId = trim((string)($_GET['session_id'] ?? ''));
        if ($sessionId !== '' && $reservationId > 0) {
            $this->confirmerPaiementStripe($sessionId, $reservationId);
            return;
        }

        header('Location: /ecoride/View/frontoffice/confirmation_reservation.php?id=' . $reservationId);
        exit;
    }

    private function confirmerPaiementStripe(string $sessionId, int $reservationId): void {
        $cfg       = require __DIR__ . '/../Config/StripeConfig.php';
        $secretKey = $cfg['secret_key'] ?? '';

        try {
            $stripe  = new StripeService($secretKey);
            $session = $stripe->retrieveSession($sessionId);

            if (($session['payment_status'] ?? '') === 'paid') {
                $paiement = $this->paiementModel->getPaiementByReservation($reservationId);
                if ($paiement && !empty($paiement['id'])) {
                    $this->paiementModel->validerPaiement((int)$paiement['id'], $sessionId);
                }
                $this->reservationModel->updateStatut($reservationId, 'confirmee');
                header('Location: /ecoride/View/frontoffice/confirmation_reservation.php?id=' . $reservationId);
                exit;
            }

            header('Location: /ecoride/View/frontoffice/paiement_cancel.php?reservation_id=' . $reservationId);
            exit;
        } catch (\RuntimeException $e) {
            error_log('[Stripe] retrieveSession error: ' . $e->getMessage());
            header('Location: /ecoride/View/frontoffice/paiement_cancel.php?reservation_id=' . $reservationId);
            exit;
        }
    }

    public function paiementCancel(): void {
        $reservationId = intval($_GET['reservation_id'] ?? 0);
        if ($reservationId > 0) {
            $lastPaiement = $this->paiementModel->getPaiementByReservation($reservationId);
            if ($lastPaiement && !empty($lastPaiement['id'])) {
                $this->paiementModel->annulerPaiement((int)$lastPaiement['id']);
            }
        }
        header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
        exit;
    }

    private function validerDonneesCarte(array $data): bool {
        $nom = trim($data['carte_nom'] ?? '');
        $prenom = trim($data['carte_prenom'] ?? '');
        $numero = preg_replace('/\D+/', '', (string)($data['carte_numero'] ?? ''));
        $expiration = trim($data['carte_expiration'] ?? '');
        $cvv = preg_replace('/\D+/', '', (string)($data['carte_cvv'] ?? ''));

        if ($nom === '' || $prenom === '') {
            return false;
        }
        if (strlen($numero) < 13 || strlen($numero) > 19) {
            return false;
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiration)) {
            return false;
        }
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            return false;
        }
        return true;
    }

    private function validerDonneesD17(array $data): bool {
        $nom = trim($data['d17_nom'] ?? '');
        $prenom = trim($data['d17_prenom'] ?? '');
        $telephone = preg_replace('/\D+/', '', (string)($data['d17_telephone'] ?? ''));
        $code = preg_replace('/\D+/', '', (string)($data['d17_code'] ?? ''));

        if ($nom === '' || $prenom === '') {
            return false;
        }
        if (strlen($telephone) < 8 || strlen($telephone) > 15) {
            return false;
        }
        if (strlen($code) < 4 || strlen($code) > 8) {
            return false;
        }
        return true;
    }

    private function redirigerVersD17(int $reservationId, array $reservation): void
    {
        $cfg      = require __DIR__ . '/../Config/D17Config.php';
        $testMode = (bool)($cfg['test_mode'] ?? true);

        $montant = (float)($reservation['prix_total'] ?? 1);
        if ($montant <= 0) $montant = 1.0;

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $successUrl = $baseUrl . '/ecoride/View/frontoffice/paiement_success.php'
            . '?reservation_id=' . $reservationId . '&mode=d17';
        $cancelUrl  = $baseUrl . '/ecoride/View/frontoffice/paiement_cancel.php'
            . '?reservation_id=' . $reservationId;

        $d17     = new D17Service();
        $session = $d17->createCheckout($reservationId, $montant, $successUrl, $cancelUrl);
        $token   = $session['token'];

        $checkoutUrl = $baseUrl . '/ecoride/View/frontoffice/d17_checkout.php'
            . '?token='          . urlencode($token)
            . '&reservation_id=' . $reservationId
            . '&cancel_url='     . urlencode($cancelUrl)
            . ($testMode ? '&test=1' : '');

        header('Location: ' . $checkoutUrl);
        exit;
    }

    public function traiterD17(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $reservationId = intval($_POST['reservation_id'] ?? 0);
        $token         = trim($_POST['d17_token'] ?? '');
        $phone         = trim($_POST['d17_phone']  ?? '');
        $code          = trim($_POST['d17_code']   ?? '');

        if (!$reservationId || !$token) {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $d17    = new D17Service();
        $result = $d17->verifyPayment($token, $phone, $code);

        if (!$result['success']) {
            // Retour au checkout avec message d'erreur en session
            $_SESSION['d17_error'] = $result['message'];
            $reservation = $this->reservationModel->getById($reservationId);
            $montant     = (float)($reservation['prix_total'] ?? 0);
            $baseUrl     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                         . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $cancelUrl   = $baseUrl . '/ecoride/View/frontoffice/paiement_cancel.php'
                         . '?reservation_id=' . $reservationId;

            // Recréer un nouveau token pour retenter
            $d17new  = new D17Service();
            $session = $d17new->createCheckout(
                $reservationId,
                $montant,
                $baseUrl . '/ecoride/View/frontoffice/paiement_success.php?reservation_id=' . $reservationId . '&mode=d17',
                $cancelUrl
            );
            $cfg = require __DIR__ . '/../Config/D17Config.php';
            header('Location: /ecoride/View/frontoffice/d17_checkout.php'
                . '?token='          . urlencode($session['token'])
                . '&reservation_id=' . $reservationId
                . '&cancel_url='     . urlencode($cancelUrl)
                . '&error='          . urlencode($result['message'])
                . ($cfg['test_mode'] ? '&test=1' : ''));
            exit;
        }

        // Paiement validé
        $reservation = $this->reservationModel->getById($reservationId);
        $montant     = (float)($reservation['prix_total'] ?? 1);

        $paiementId  = $this->paiementModel->enregistrerPaiement($reservationId, 'virement', $montant);
        if ($paiementId) {
            $this->paiementModel->validerPaiement((int)$paiementId, $result['reference']);
        }
        $this->reservationModel->updateStatut($reservationId, 'confirmee');

        header('Location: /ecoride/View/frontoffice/confirmation_reservation.php?id=' . $reservationId . '&mode=d17');
        exit;
    }

    private function chargerReservationEtVehicule(): array {
        $reservationId = intval($_GET['id'] ?? $_GET['reservation_id'] ?? ($_POST['reservation_id'] ?? ($_SESSION['pending_reservation_id'] ?? 0)));
        if (!$reservationId) {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $reservation = $this->reservationModel->getById($reservationId);
        if (!$reservation) {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $vehiculeId = intval($reservation['vehicule_id'] ?? 0);
        $vehicule = $vehiculeId ? $this->vehiculeModel->getById($vehiculeId) : false;
        if (!$vehicule) {
            header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
            exit;
        }

        $trajet = null;
        $trajetId = intval($reservation['trajet_id'] ?? 0);
        if ($trajetId > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT point_depart, point_arrive FROM trajet WHERE id_T = :id');
            $stmt->execute([':id' => $trajetId]);
            $trajet = $stmt->fetch();
        }

        return [$reservation, $vehicule, $trajet];
    }
}
?>