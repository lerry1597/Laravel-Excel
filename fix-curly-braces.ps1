# PowerShell Script untuk mengganti curly braces dengan square brackets
# untuk kompatibilitas PHP 7.4+
#
# Usage: .\fix-curly-braces.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "PHP 7.4 Curly Braces Fix Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$srcDir = Join-Path $PSScriptRoot "src"

if (-not (Test-Path $srcDir)) {
    Write-Host "Error: src directory not found!" -ForegroundColor Red
    exit 1
}

$phpFiles = Get-ChildItem -Path $srcDir -Filter "*.php" -Recurse
$fixedFiles = @()
$totalReplacements = 0

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    $fileReplacements = 0
    
    # Pattern 1: $variable{number} -> $variable[number]
    $pattern1 = '\$([a-zA-Z_][a-zA-Z0-9_]*)\{(\d+)\}'
    $replacement1 = '$$$1[$$2]'
    $content = $content -replace $pattern1, $replacement1
    
    # Pattern 2: $variable{$var} -> $variable[$var]
    $pattern2 = '\$([a-zA-Z_][a-zA-Z0-9_]*)\{\$([a-zA-Z0-9_]+)\}'
    $replacement2 = '$$$1[$$$2]'
    $content = $content -replace $pattern2, $replacement2
    
    # Hitung jumlah perubahan dengan membandingkan sebelum dan sesudah
    if ($content -ne $originalContent) {
        # Hitung jumlah perubahan
        $changes = @($originalContent | Select-String -Pattern '\$\w+\{' -AllMatches).Matches.Count - 
                   @($content | Select-String -Pattern '\$\w+\{' -AllMatches).Matches.Count
        
        if ($changes -gt 0) {
            Set-Content -Path $file.FullName -Value $content -NoNewline
            $fileReplacements = $changes
            $totalReplacements += $changes
            
            $relativePath = $file.FullName.Replace($srcDir + "\", "")
            $fixedFiles += @{
                File = $relativePath
                Count = $fileReplacements
            }
            
            Write-Host "✓ Fixed $fileReplacements occurrence(s) in: $($file.Name)" -ForegroundColor Green
        }
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Files fixed: $($fixedFiles.Count)" -ForegroundColor White
Write-Host "Total replacements: $totalReplacements" -ForegroundColor White
Write-Host ""

if ($fixedFiles.Count -gt 0) {
    Write-Host "Modified files:" -ForegroundColor Yellow
    foreach ($file in $fixedFiles) {
        Write-Host "  - $($file.File) ($($file.Count) changes)" -ForegroundColor Gray
    }
} else {
    Write-Host "No curly braces found! ✓" -ForegroundColor Green
    Write-Host "Your code is already PHP 7.4+ compatible." -ForegroundColor Green
}

Write-Host ""
Write-Host "Done!" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Review the changes with: git diff" -ForegroundColor Gray
Write-Host "2. Test your code thoroughly" -ForegroundColor Gray
Write-Host "3. Commit the changes: git add . && git commit -m 'Fix PHP 7.4 curly braces deprecation'" -ForegroundColor Gray
Write-Host "4. Push to your fork: git push origin your-branch-name" -ForegroundColor Gray
