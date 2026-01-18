#!/usr/bin/env php
<?php
/**
 * Script untuk mengganti curly braces syntax dengan square brackets
 * untuk kompatibilitas PHP 7.4+
 * 
 * Usage: php fix-curly-braces.php
 */

$srcDir = __DIR__ . '/src';

if (!is_dir($srcDir)) {
    echo "Error: src directory not found!\n";
    exit(1);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir)
);

$fixedFiles = [];
$totalReplacements = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getPathname();
        $content = file_get_contents($filepath);
        $originalContent = $content;
        
        // Pattern 1: $variable{number} -> $variable[number]
        $pattern1 = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\{(\d+)\}/';
        $replacement1 = '$${1}[${2}]';
        $content = preg_replace($pattern1, $replacement1, $content, -1, $count1);
        
        // Pattern 2: $variable{$var} -> $variable[$var]
        $pattern2 = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\{\$([a-zA-Z_][a-zA-Z0-9_]*)\}/';
        $replacement2 = '$${1}[$${2}]';
        $content = preg_replace($pattern2, $replacement2, $content, -1, $count2);
        
        // Pattern 3: $variable{expression} -> $variable[expression]
        // More complex pattern for expressions
        $pattern3 = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\{([^}]+)\}/';
        $content = preg_replace_callback($pattern3, function($matches) {
            // Avoid replacing things like \x{FEFF} (unicode escapes in strings)
            if (preg_match('/^[0-9A-F]{2,4}$/', $matches[2])) {
                return $matches[0]; // Keep unicode escapes
            }
            return '$' . $matches[1] . '[' . $matches[2] . ']';
        }, $content, -1, $count3);
        
        $totalCount = $count1 + $count2 + $count3;
        
        if ($totalCount > 0) {
            file_put_contents($filepath, $content);
            $fixedFiles[] = [
                'file' => str_replace($srcDir . DIRECTORY_SEPARATOR, '', $filepath),
                'count' => $totalCount
            ];
            $totalReplacements += $totalCount;
            echo "✓ Fixed $totalCount occurrence(s) in: " . basename($filepath) . "\n";
        }
    }
}

echo "\n";
echo "========================================\n";
echo "Summary:\n";
echo "========================================\n";
echo "Files fixed: " . count($fixedFiles) . "\n";
echo "Total replacements: $totalReplacements\n";
echo "\n";

if (count($fixedFiles) > 0) {
    echo "Modified files:\n";
    foreach ($fixedFiles as $file) {
        echo "  - {$file['file']} ({$file['count']} changes)\n";
    }
} else {
    echo "No curly braces found! ✓\n";
    echo "Your code is already PHP 7.4+ compatible.\n";
}

echo "\n";
echo "Done!\n";
