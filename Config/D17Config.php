<?php
/**
 * Configuration D17 (Poste Tunisienne)
 * En mode test, utiliser les numéros et codes ci-dessous.
 */
return [
    'test_mode'     => true,
    'merchant_id'   => 'ECORIDE_MERCHANT',
    'api_key'       => 'd17_test_api_key_ecoride',
    // Numéros test qui réussissent toujours (mode test)
    'test_phones'   => ['20000000', '90000000', '50000000', '55000000'],
    // Codes secrets test valides avec n'importe quel numéro test
    'test_codes'    => ['1234', '0000', '1111'],
    // Numéros test qui échouent (pour simuler un rejet)
    'fail_phones'   => ['20000001'],
];
