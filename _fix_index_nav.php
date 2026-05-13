<?php
$f = __DIR__ . '/View/frontoffice/index.php';
$content = file_get_contents($f);

$include = "<?php include_once __DIR__ . '/includes/navbar_moderne.php'; ?>";
$navEnd  = "</nav>";

$pos = strpos($content, $include);
if ($pos === false) { die("Include not found"); }

$afterInclude = $pos + strlen($include);
$navEndPos = strpos($content, $navEnd, $afterInclude);
if ($navEndPos === false) { die("</nav> not found after include"); }

$removeEnd = $navEndPos + strlen($navEnd);
$content = substr($content, 0, $afterInclude) . substr($content, $removeEnd);
file_put_contents($f, $content);
echo "Done. Removed " . ($removeEnd - $afterInclude) . " chars of old nav.";
