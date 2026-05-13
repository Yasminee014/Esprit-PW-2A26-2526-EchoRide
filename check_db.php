<?php
require_once __DIR__ . '/config.php';
$pdo = Database::getInstance();

// Vérifier admin
$r = $pdo->query("SELECT id, nom, email, password FROM admins LIMIT 3")->fetchAll();
foreach ($r as $admin) {
    $ok = password_verify('Admin@1234', $admin['password']);
    echo "Admin [{$admin['id']}] {$admin['nom']} ({$admin['email']}) — password 'Admin@1234': " . ($ok ? "✅ OK" : "❌ INCORRECT") . "\n";
}

// Stats
$tables = ['admins','users','trajet','vehicules','reservations','evenements','sponsors','reclamations','app_settings','paiements'];
echo "\n--- TABLES ---\n";
foreach ($tables as $t) {
    try {
        $n = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: $n lignes\n";
    } catch (Exception $e) {
        echo "$t: ❌ MANQUANTE\n";
    }
}

// Rôles users
echo "\n--- ROLES USERS ---\n";
$roles = $pdo->query("SELECT role, COUNT(*) as nb FROM users GROUP BY role")->fetchAll();
foreach ($roles as $row) echo ($row['role'] ?: '(vide)') . ": {$row['nb']}\n";
