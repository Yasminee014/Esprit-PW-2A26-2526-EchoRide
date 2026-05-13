<?php
// ============================================================
// helpers/logo_tag.php
// Génère le tag <img> du logo EcoRide depuis la BDD
// Usage: <?php include BASE_PATH . 'helpers/logo_tag.php'; ?>
//        puis: echo ecoride_logo_tag('width:60px');
// ============================================================
if (!function_exists('ecoride_logo_tag')) {
    function ecoride_logo_tag(string $style = 'width:60px;height:60px;object-fit:contain;', string $class = '', string $alt = 'EcoRide Logo'): string
    {
        // Priorité 1 : fichier physique (le plus rapide)
        $physPath = BASE_PATH . 'uploads/photos/photo.png';
        if (file_exists($physPath)) {
            $src = BASE_URL . 'uploads/photos/photo.png';
            return sprintf('<img src="%s" alt="%s" style="%s" class="%s">', htmlspecialchars($src), htmlspecialchars($alt), htmlspecialchars($style), htmlspecialchars($class));
        }

        // Priorité 2 : BDD via serve_image.php
        $serveSrc = BASE_URL . 'serve_image.php?type=logo';
        return sprintf('<img src="%s" alt="%s" style="%s" class="%s">', htmlspecialchars($serveSrc), htmlspecialchars($alt), htmlspecialchars($style), htmlspecialchars($class));
    }
}
