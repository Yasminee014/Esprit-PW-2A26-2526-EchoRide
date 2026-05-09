<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Location: /ecoride/View/frontoffice/tous_les_trajets.php');
exit;
?>
