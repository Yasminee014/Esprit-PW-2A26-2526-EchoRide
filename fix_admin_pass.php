<?php
require_once __DIR__ . '/config.php';
$pdo = Database::getInstance();

$hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE admins SET password = :pwd WHERE id = 1");
$stmt->execute([':pwd' => $hash]);

$r = $pdo->query("SELECT password FROM admins WHERE id=1")->fetch();
$ok = password_verify('Admin@1234', $r['password']);
echo $ok ? "✅ Mot de passe admin mis à jour : Admin@1234\n" : "❌ Erreur\n";
