<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$vehiculeId = (int)($_GET['vehicule_id'] ?? 0);
$from = $_GET['from'] ?? '';

// Garder exactement les mêmes fonctionnalités que user.php
// tout en restant sur l'URL tous_les_trajets.php.
$_GET['tab'] = 'tous-trajets';
if ($vehiculeId > 0) {
    $_GET['vehicule_id'] = $vehiculeId;
}
if ($from !== '') {
    $_GET['from'] = $from;
}

require __DIR__ . '/user.php';
exit;
