<?php

/**
 * D17Service — Simulation du gateway D17 (Poste Tunisienne)
 * En mode test, des numéros et codes prédéfinis permettent de simuler
 * les cas de succès et d'échec sans appel réseau réel.
 */
class D17Service
{
    private array  $cfg;
    private bool   $testMode;

    public function __construct()
    {
        $this->cfg      = require __DIR__ . '/../Config/D17Config.php';
        $this->testMode = (bool)($this->cfg['test_mode'] ?? true);
    }

    /**
     * Crée un "checkout" D17 et retourne un token de session.
     * En production, cet appel initierait une requête vers l'API D17.
     */
    public function createCheckout(int $reservationId, float $montantTND,
                                   string $successUrl, string $cancelUrl): array
    {
        $token = 'D17-' . $reservationId . '-' . bin2hex(random_bytes(8));

        // Stockage en session pour retrouver les infos lors du callback
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['d17_checkout'][$token] = [
            'reservation_id' => $reservationId,
            'montant'        => $montantTND,
            'success_url'    => $successUrl,
            'cancel_url'     => $cancelUrl,
            'created_at'     => time(),
        ];

        return [
            'token'       => $token,
            'test_mode'   => $this->testMode,
            'checkout_url'=> $successUrl, // remplacé par l'URL du checkout D17 simulé
        ];
    }

    /**
     * Vérifie un paiement D17 (numéro + code secret).
     * Retourne ['success' => bool, 'message' => string, 'reference' => string]
     */
    public function verifyPayment(string $token, string $phone, string $code): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $session = $_SESSION['d17_checkout'][$token] ?? null;

        if (!$session) {
            return ['success' => false, 'message' => 'Session D17 expirée ou invalide.'];
        }

        // Nettoyage du numéro (espaces / tirets)
        $phone = preg_replace('/\D/', '', $phone);
        $code  = trim($code);

        if ($this->testMode) {
            return $this->verifyTest($token, $phone, $code, $session);
        }

        // ── Mode production : appel API D17 réel ici ──
        // (nécessite un compte marchand Poste Tunisienne)
        return $this->verifyProduction($phone, $code, $session);
    }

    // ── Mode test ──────────────────────────────────────────────────────────

    private function verifyTest(string $token, string $phone, string $code,
                                array $session): array
    {
        $testPhones = $this->cfg['test_phones'] ?? [];
        $testCodes  = $this->cfg['test_codes']  ?? [];
        $failPhones = $this->cfg['fail_phones'] ?? [];

        if (in_array($phone, $failPhones, true)) {
            return [
                'success'   => false,
                'message'   => '[TEST] Numéro refusé — solde insuffisant simulé.',
                'reference' => '',
            ];
        }

        if (in_array($phone, $testPhones, true) && in_array($code, $testCodes, true)) {
            $ref = 'D17TEST-' . strtoupper(bin2hex(random_bytes(4)));
            unset($_SESSION['d17_checkout'][$token]);
            return [
                'success'   => true,
                'message'   => '[TEST] Paiement D17 validé.',
                'reference' => $ref,
            ];
        }

        return [
            'success'   => false,
            'message'   => '[TEST] Numéro ou code incorrect. Utilisez un numéro test : '
                         . implode(', ', $testPhones) . ' avec le code : '
                         . implode(' ou ', $testCodes),
            'reference' => '',
        ];
    }

    // ── Mode production (placeholder) ─────────────────────────────────────

    private function verifyProduction(string $phone, string $code, array $session): array
    {
        // TODO: remplacer par l'appel cURL vers l'API D17 Poste Tunisienne
        return ['success' => false, 'message' => 'Mode production non configuré.'];
    }
}
