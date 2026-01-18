#!/usr/bin/env php
<?php
/**
 * Script untuk fix curly braces di PHPExcel vendor
 * untuk kompatibilitas PHP 7.4+
 * 
 * Usage: php fix-phpexcel-curly-braces.php
 * 
 * Script ini akan:
 * 1. Scan semua file PHP di vendor/phpoffice/phpexcel
 * 2. Replace $var{index} dengan $var[index]
 * 3. Skip unicode escapes seperti \x{FEFF}
 */

echo "\n";
echo "========================================\n";
echo "PHPExcel PHP 7.4 Curly Braces Fix\n";
echo "========================================\n";
echo "\n";

// Cek apakah di root Laravel project atau di Laravel-Excel
$possiblePaths = [
    __DIR__ . '/vendor/phpoffice/phpexcel/Classes/PHPExcel',  // Root Laravel
    __DIR__ . '/../../../phpoffice/phpexcel/Classes/PHPExcel', // Dari vendor/maatwebsite/excel
];

$vendorDir = null;
foreach ($possiblePaths as $path) {
    if (is_dir($path)) {
        $vendorDir = $path;
        break;
    }
}

if (!$vendorDir) {
    echo "❌ Error: PHPExcel tidak ditemukan!\n";
    echo "\n";
    echo "Pastikan Anda sudah menjalankan:\n";
    echo "  composer install\n";
    echo "\n";
    echo "Dan script ini dijalankan dari:\n";
    echo "  - Root project Laravel, ATAU\n";
    echo "  - vendor/maatwebsite/excel/\n";
    echo "\n";
    exit(1);
}

echo "📂 PHPExcel directory: $vendorDir\n";
echo "🔍 Scanning files...\n";
echo "\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($vendorDir),
    RecursiveIteratorIterator::SELF_FIRST
);

$totalFiles = 0;
$totalReplacements = 0;
$fixedFiles = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getPathname();
        $content = file_get_contents($filepath);
        $original = $content;
        $fileChanges = 0;
        
        // Pattern 1: $var{0} atau $var{123}
        $pattern1 = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\{(\d+)\}/';
        $content = preg_replace($pattern1, '$${1}[${2}]', $content, -1, $count1);
        $fileChanges += $count1;
        
        // Pattern 2: $var{$i} atau $var{$index}
        $pattern2 = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\{\$([a-zA-Z_][a-zA-Z0-9_]*)\}/';
        $content = preg_replace($pattern2, '$${1}[$${2}]', $content, -1, $count2);
        $fileChanges += $count2;
        
        // Pattern 3: Complex expressions, tapi skip unicode escapes
        $content = preg_replace_callback(
            '/\$([a-zA-Z_][a-zA-Z0-9_]*)\{([^}]+)\}/',
            function($matches) use (&$fileChanges) {
                // Skip unicode escapes seperti \x{FEFF}, \x{200B}
                if (preg_match('/^[0-9A-Fa-f]{2,4}$/', trim($matches[2]))) {
                    return $matches[0]; // Keep as is
                }
                
                // Skip jika bukan variable/array access
                if (strpos($matches[0], '\\x{') !== false || 
                    strpos($matches[0], '\\u{') !== false) {
                    return $matches[0]; // Keep unicode escapes
                }
                
                $fileChanges++;
                return '$' . $matches[1] . '[' . $matches[2] . ']';
            },
            $content
        );
        
        if ($content !== $original) {
            // Backup original file
            $backupFile = $filepath . '.bak';
            if (!file_exists($backupFile)) {
                copy($filepath, $backupFile);
            }
            
            file_put_contents($filepath, $content);
            $totalFiles++;
            $totalReplacements += $fileChanges;
            
            $relativePath = str_replace($vendorDir . DIRECTORY_SEPARATOR, '', $filepath);
            $fixedFiles[] = [
                'file' => $relativePath,
                'changes' => $fileChanges
            ];
            
            echo "✓ " . basename($filepath) . " ($fileChanges changes)\n";
        }
    }
}

echo "\n";
echo "========================================\n";
echo "Summary\n";
echo "========================================\n";
echo "Files fixed: $totalFiles\n";
echo "Total replacements: $totalReplacements\n";
echo "\n";

if ($totalFiles > 0) {
    echo "✅ PHPExcel has been fixed for PHP 7.4 compatibility!\n";
    echo "\n";
    echo "Backup files created with .bak extension\n";
    echo "If something goes wrong, you can restore from backups\n";
    echo "\n";
    echo "Modified files:\n";
    foreach ($fixedFiles as $file) {
        echo "  - {$file['file']} ({$file['changes']} changes)\n";
    }
    echo "\n";
    echo "Next steps:\n";
    echo "1. Test your Excel export/import functionality\n";
    echo "2. If everything works, you can delete .bak files:\n";
    echo "   find vendor/phpoffice/phpexcel -name '*.bak' -delete\n";
    echo "3. Consider upgrading to Laravel-Excel 3.1 for long-term support\n";
} else {
    echo "✅ No curly braces found in PHPExcel!\n";
    echo "PHPExcel is already PHP 7.4 compatible.\n";
}

echo "\n";
echo "Done!\n";
echo "\n";
