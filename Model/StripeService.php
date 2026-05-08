<?php
declare(strict_types=1);

/**
 * Service Stripe — appels API via cURL (sans Composer)
 * Docs : https://stripe.com/docs/api/checkout/sessions
 */
final class StripeService
{
    private string $secretKey;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Crée une session Stripe Checkout et retourne l'URL de paiement.
     *
     * @throws \RuntimeException si l'API Stripe retourne une erreur
     */
    public function createCheckoutSession(
        int    $reservationId,
        float  $montant,
        string $description,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'eur'
    ): array {
        // Stripe attend le montant en centimes
        $amountCents = (int) round($montant * 100);
        if ($amountCents < 50) {
            $amountCents = 50; // Montant minimum Stripe : 0,50 EUR
        }

        $params = [
            'payment_method_types[0]'                          => 'card',
            'line_items[0][price_data][currency]'              => $currency,
            'line_items[0][price_data][product_data][name]'    => $description,
            'line_items[0][price_data][unit_amount]'           => (string) $amountCents,
            'line_items[0][quantity]'                          => '1',
            'mode'                                             => 'payment',
            'success_url'                                      => $successUrl,
            'cancel_url'                                       => $cancelUrl,
            'metadata[reservation_id]'                         => (string) $reservationId,
        ];

        return $this->post('/checkout/sessions', $params);
    }

    /**
     * Récupère une session Stripe pour vérifier le statut du paiement.
     */
    public function retrieveSession(string $sessionId): array
    {
        return $this->get('/checkout/sessions/' . rawurlencode($sessionId));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Méthodes cURL internes
    // ──────────────────────────────────────────────────────────────────────

    private function post(string $endpoint, array $params): array
    {
        $ch = curl_init($this->apiBase . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_USERPWD        => $this->secretKey . ':',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        return $this->execute($ch);
    }

    private function get(string $endpoint): array
    {
        $ch = curl_init($this->apiBase . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET        => true,
            CURLOPT_USERPWD        => $this->secretKey . ':',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        return $this->execute($ch);
    }

    /**
     * @param resource $ch
     */
    private function execute($ch): array
    {
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('Stripe cURL error : ' . $curlError);
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Stripe : réponse invalide');
        }

        if ($httpCode >= 400) {
            $msg = $data['error']['message'] ?? ('Stripe erreur HTTP ' . $httpCode);
            throw new \RuntimeException($msg);
        }

        return $data;
    }
}
