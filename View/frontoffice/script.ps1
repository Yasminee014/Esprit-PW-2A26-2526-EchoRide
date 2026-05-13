$file = 'c:\xampp1\htdocs\projetadmin\views\frontoffice\login.php'
$lines = Get-Content $file -Encoding UTF8
$newLines = @()
$i = 0
$skip = $false
while ($i -lt $lines.Length) {
    $line = $lines[$i]
    if ($line -match '<div class="features" id="how-it-works"') {
        $skip = $true
    }
    if (-not $skip) {
        $newLines += $line
    }
    if ($skip -and $line -match '</footer>') {
        $skip = $false
    }
    $i++
}
$newLines | Set-Content $file -Encoding UTF8
Write-Host "Done!"
