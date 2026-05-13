<?php
/**
 * fix_all_encoding.php
 * Fixes UTF-8 BOM and double/triple encoding issues in PHP files.
 * Run once via CLI: php fix_all_encoding.php
 */

function fixTripleEncoding($content) {
    // Remove UTF-8 BOM at start
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    // Remove double-encoded BOM (ï»¿) if present after the real BOM
    if (substr($content, 0, 6) === "\xC3\xAF\xC2\xBB\xC2\xBF") {
        $content = substr($content, 6);
    }
    // Two passes: each pass reverses one level of CP1252→UTF-8 mis-encoding
    $pass1 = mb_convert_encoding($content, 'CP1252', 'UTF-8');
    $result = mb_convert_encoding($pass1, 'CP1252', 'UTF-8');
    return $result;
}

function fixDoubleEncoding($content) {
    // Remove UTF-8 BOM at start
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    // One pass: reverses one level of CP1252→UTF-8 mis-encoding
    $result = mb_convert_encoding($content, 'CP1252', 'UTF-8');
    return $result;
}

function removeBomOnly($content) {
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    return $content;
}

function hasTripleEncoding($content) {
    // Triple-encoded é produces bytes: C3 83 C6 92 C3 82
    return strpos($content, "\xC3\x83\xC6\x92") !== false;
}

function hasDoubleEncoding($content) {
    // Double-encoded é produces bytes: C3 83 C2
    return strpos($content, "\xC3\x83\xC2") !== false;
}

function hasBom($content) {
    return substr($content, 0, 3) === "\xEF\xBB\xBF";
}

// Scan all PHP files in the project
$baseDir = __DIR__;
$dirs = [
    $baseDir . '/Controller',
    $baseDir . '/Model',
    $baseDir . '/View/backoffice',
    $baseDir . '/View/frontoffice',
    $baseDir . '/Config',
    $baseDir,
];

$fixed = [];
$skipped = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '/*.php');
    if (!$files) continue;
    
    foreach ($files as $file) {
        // Skip this fix script itself
        if (basename($file) === 'fix_all_encoding.php') continue;
        
        $content = file_get_contents($file);
        
        if (hasTripleEncoding($content)) {
            $fixed_content = fixTripleEncoding($content);
            file_put_contents($file, $fixed_content);
            $fixed[] = ['file' => $file, 'type' => 'triple-encoding'];
        } elseif (hasDoubleEncoding($content)) {
            $fixed_content = fixDoubleEncoding($content);
            file_put_contents($file, $fixed_content);
            $fixed[] = ['file' => $file, 'type' => 'double-encoding'];
        } elseif (hasBom($content)) {
            $fixed_content = removeBomOnly($content);
            file_put_contents($file, $fixed_content);
            $fixed[] = ['file' => $file, 'type' => 'bom-only'];
        } else {
            $skipped[] = $file;
        }
    }
}

echo "=== FIXED ===\n";
foreach ($fixed as $f) {
    echo "[{$f['type']}] " . str_replace($baseDir . '/', '', $f['file']) . "\n";
}
echo "\n=== SKIPPED (no issues) ===\n";
foreach ($skipped as $f) {
    echo str_replace($baseDir . '/', '', $f) . "\n";
}
echo "\nDone. Fixed " . count($fixed) . " file(s).\n";
