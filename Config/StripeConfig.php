<?php
/**
 * Configuration Stripe (Test Mode)
 *
 * Carte de test Stripe : 4242 4242 4242 4242 | exp: 12/34 | CVV: 123
 *
 * Taux de conversion : 1 EUR = ~3.30 TND (majà manuellement si besoin)
 */

return [
    'secret_key'      => $_ENV['STRIPE_SECRET_KEY']      ?? 'sk_test_VOTRE_CLE_SECRETE_ICI',
    'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? 'pk_test_VOTRE_CLE_PUBLIQUE_ICI',
    'currency'        => 'eur',
    // Taux : 1 EUR = X TND
    'tnd_to_eur_rate' => 3.30,
];
