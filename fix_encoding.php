<?php
// Fixes triple-encoded UTF-8 (é ? ?) in PHP view files
$files = [
    __DIR__ . '/View/backoffice/admin_dashboard.php',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // First pass: undo one level of latin-1/utf-8 confusion
    $pass1 = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    // The fix: encode current UTF-8 string as latin-1, then re-read as UTF-8 (twice)
    $fixed = utf8_encode(utf8_decode(utf8_encode(utf8_decode($content))));
    // Actually let's do it properly
    // é are UTF-8 bytes of ? (C3 83) + � (C2 A9)
    // Reading as latin-1 gives: 0xC3 0x83 0xC2 0xA9
    // Reading THOSE as latin-1 again: step 1 decode utf8->latin1, step 2 decode again
    $step1 = mb_convert_encoding($content, 'ISO-8859-1', 'UTF-8');
    $step2 = mb_convert_encoding($step1, 'ISO-8859-1', 'UTF-8');
    $result = mb_convert_encoding($step2, 'UTF-8', 'ISO-8859-1');
    
    file_put_contents($file, $result);
    echo "Fixed: $file\n";
}
echo "Done.\n";
