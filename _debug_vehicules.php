<?php
require __DIR__ . '/config.php';
$db = Database::getInstance();

// Correction : réaffecter les IDs à 0 et fixer l'AUTO_INCREMENT
$db->exec("SET @next = (SELECT IFNULL(MAX(id),0)+1 FROM vehicules WHERE id > 0)");
$db->exec("UPDATE vehicules SET id = GREATEST(@next, 1) WHERE id = 0");
$db->exec("SET @ai = (SELECT IFNULL(MAX(id),0)+1 FROM vehicules)");
$db->exec("SET @sql = CONCAT('ALTER TABLE vehicules AUTO_INCREMENT = ', @ai)");
$db->exec("PREPARE stmt FROM @sql");
$db->exec("EXECUTE stmt");
$db->exec("DEALLOCATE PREPARE stmt");

echo "<h3>Correction effectuée — Vehicules en base</h3><pre>";
$r2 = $db->query('SELECT id, user_id, marque, modele, immatriculation FROM vehicules LIMIT 10');
foreach ($r2->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo implode(' | ', $row) . "\n";
}
echo "</pre><p style='color:green'>✅ AUTO_INCREMENT réinitialisé. IDs corrigés.</p>";
