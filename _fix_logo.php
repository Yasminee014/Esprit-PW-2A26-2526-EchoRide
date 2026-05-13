<?php
$file = __DIR__ . '/index.php';
$content = file_get_contents($file);

// Replace base64 image with actual file
$result = preg_replace(
    '/data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+/',
    '<?= BASE_URL ?>assets/images/photo.png',
    $content
);

if ($result !== $content) {
    file_put_contents($file, $result);
    echo "Logo remplacé avec succès!";
} else {
    echo "Pattern non trouvé - cherchons...";
    $pos = strpos($content, 'data:image/png;base64,');
    echo " Position: " . ($pos !== false ? $pos : 'NOT FOUND');
}
