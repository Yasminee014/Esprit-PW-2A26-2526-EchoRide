<?php
echo "=== VÉRIFICATION DES CHEMINS ===<br><br>";

echo "1. Chemin actuel : " . __DIR__ . "<br>";
echo "2. Chemin vers Controller : " . __DIR__ . '/../../Controller/ReservationController.php' . "<br><br>";

$controllerPath = __DIR__ . '/../../Controller/ReservationController.php';

if (file_exists($controllerPath)) {
    echo "✅ Le fichier ReservationController.php EXISTE<br>";
    
    // Vérifier le contenu
    $content = file_get_contents($controllerPath);
    if (strpos($content, 'class ReservationController') !== false) {
        echo "✅ La classe ReservationController est définie dans le fichier<br>";
    } else {
        echo "❌ La classe ReservationController n'est PAS définie dans le fichier<br>";
    }
    
    // Inclure et vérifier
    require_once $controllerPath;
    
    if (class_exists('ReservationController')) {
        echo "✅ La classe ReservationController est chargée avec succès !<br>";
    } else {
        echo "❌ La classe ReservationController n'existe pas après inclusion<br>";
    }
} else {
    echo "❌ Le fichier ReservationController.php N'EXISTE PAS<br>";
    echo "   Créez-le dans : " . dirname($controllerPath) . "<br>";
}

echo "<br><br>=== STRUCTURE DES DOSSIERS ===<br>";
echo "Contenu de /Controller : <br>";
$controllerDir = __DIR__ . '/../../Controller';
if (is_dir($controllerDir)) {
    $files = scandir($controllerDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "  - " . $file . "<br>";
        }
    }
} else {
    echo "  ❌ Le dossier Controller n'existe pas à : " . $controllerDir . "<br>";
}
?>