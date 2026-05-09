<?php
/**
 * matching_api.php — Point d'entrée REST du moteur IA
 *
 * GET  ?depart=Tunis&arrivee=Sousse&rayon=60&places=1
 *      → JSON avec trajets matchés et scorés
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/MatchingIA.php';

(new MatchingIA())->handleApiRequest();
?>
