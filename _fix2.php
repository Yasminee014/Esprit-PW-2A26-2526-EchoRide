<?php
// Fix index.php - remove old inline nav block
$f = dirname(__FILE__) . '/View/frontoffice/index.php';
$content = file_get_contents($f);

// The include line we want to keep
$include = "<?php include_once __DIR__ . '/includes/navbar_moderne.php'; ?>";
// The real content we want after it
$keepAfter = "\n\n<div class=\"hero-wrapper\">";

$pos = strpos($content, $include);
if ($pos === false) { die("ERROR: include not found"); }

$afterInclude = $pos + strlen($include);

// Find </nav> after the include
$navEndTag = "</nav>";
$navEndPos = strpos($content, $navEndTag, $afterInclude);
if ($navEndPos === false) { die("ERROR: nav end not found"); }

$navEndFull = $navEndPos + strlen($navEndTag);

// Find the REAL hero-wrapper after </nav>
$heroPos = strpos($content, "<div class=\"hero-wrapper\">", $navEndFull);
if ($heroPos === false) { die("ERROR: hero-wrapper not found"); }

// Build new content: keep everything before and after the old nav block
$newContent = substr($content, 0, $afterInclude) . $keepAfter . substr($content, $heroPos + strlen("<div class=\"hero-wrapper\">"));
file_put_contents($f, $newContent);
echo "Done! Removed " . ($heroPos - $afterInclude) . " chars of old nav.";
