<?php
/**
 * Fix PHP files by removing trailing whitespace and newlines after closing tags
 */

$files_to_fix = [
    'config/config.php',
    'config/database.php',
    'includes/auth.php'
];

echo "<h2>Fixing PHP Files</h2>";

foreach ($files_to_fix as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original_length = strlen($content);
        
        // Remove trailing whitespace and newlines after 
        $content = rtrim($content);
        
    
        if (substr($content, -2) === '?>') {
            
        } else {
            
            $content .= "\n";
        }
        
        $new_length = strlen($content);
        
        if ($original_length !== $new_length) {
            file_put_contents($file, $content);
            echo "<p style='color: green;'>✅ Fixed $file (removed " . ($original_length - $new_length) . " trailing characters)</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ $file is already clean</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ File not found: $file</p>";
    }
}

echo "<hr>";
echo "<h3>Alternative Solution: Remove Closing PHP Tags</h3>";
echo "<p>For API files, it's better to remove the closing ?> tag entirely to prevent this issue.</p>";

// Let's also create clean versions of the API files without closing tags
$api_files = [
    'api/categories.php',
    'api/transactions.php',
    'api/budgets.php',
    'api/dashboard.php',
    'api/users.php',
    'api/auth.php'
];

foreach ($api_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Remove closing PHP tag and any trailing whitespace
        $content = rtrim($content);
        if (substr($content, -2) === '?>') {
            $content = rtrim(substr($content, 0, -2));
            file_put_contents($file, $content);
            echo "<p style='color: green;'>✅ Removed closing tag from $file</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ $file doesn't have closing tag</p>";
        }
    }
}

echo "<hr>";
echo "<p><strong>Now test the API:</strong></p>";
echo "<p><a href='api/categories.php' target='_blank'>Test Categories API</a></p>";
echo "<p><a href='index.php'>Go to Dashboard</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
</style>
