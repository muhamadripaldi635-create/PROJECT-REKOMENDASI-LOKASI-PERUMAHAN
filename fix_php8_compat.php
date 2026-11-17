<?php
/**
 * Fix PHP 8.1+ first-class callable syntax for PHP 8.0 compatibility
 */

function fixFirstClassCallables($content, $filename) {
    // Pattern to match: functionName(...) or $var->methodName(...) or ClassName::methodName(...)
    // This is a simple regex that might not catch all cases but should work for most
    
    // Don't process certain files
    if (strpos($filename, 'platform_check.php') !== false) {
        return $content;
    }
    
    // Replace patterns like: function(...) with a closure
    // This is a simplified approach - full fix would need AST parsing
    
    // For now, comment out the first-class callable lines if they exist
    $lines = explode("\n", $content);
    $fixed = false;
    
    foreach ($lines as &$line) {
        // Look for first-class callable pattern: identifier(...)
        if (preg_match('/\$?\w+\(\.\.\.\)/', $line)) {
            // If it's an assignment or used in a function call, we might need to fix it
            if (strpos($line, 'array_map(') !== false || 
                strpos($line, 'array_walk(') !== false ||
                strpos($line, '->') !== false) {
                
                // Simple approach: comment it out if possible
                // This is not ideal but better than parse error
                // $line = '// ' . $line . ' // Disabled for PHP 8.0 compatibility';
                
                // Better approach: just skip if we can't parse it
                $fixed = true;
            }
        }
    }
    unset($line);
    
    return implode("\n", $lines);
}

// Recursively find and fix PHP files in vendor/laravel
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('vendor/laravel'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $filename = $file->getPathname();
        $content = file_get_contents($filename);
        
        if (preg_match('/\w+\(\.\.\.\)/', $content)) {
            // Found first-class callable, try to fix it
            $fixed = fixFirstClassCallables($content, $filename);
            // For now, just report them
            $count++;
            if ($count <= 5) {
                echo "Found in: $filename\n";
            }
        }
    }
}

echo "\nFound " . $count . " files with first-class callable syntax.\n";
echo "Full fix requires code restructuring - recommend using PHP 8.3 instead.\n";
?>