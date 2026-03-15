<?php
// CampusMarket Theme Repair Tool
echo "<h2>Repairing Theme Structure...</h2>";

$themes_dir = 'wp-content/themes';
$files = scandir($themes_dir);

foreach ($files as $file) {
    if (strpos($file, '\\') !== false) {
        $full_path = $themes_dir . '/' . $file;
        $normalized = str_replace('\\', '/', $file);
        $correct_path = $themes_dir . '/' . $normalized;
        
        $parts = explode('/', $normalized);
        $current_dir = $themes_dir;
        
        // Ensure all parent directories exist
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $current_dir .= '/' . $parts[$i];
            if (!is_dir($current_dir)) {
                if (mkdir($current_dir, 0755, true)) {
                    echo "Created Dir: $current_dir<br>";
                }
            }
        }
        
        // If it's a directory-only entry (ends in / in normalized)
        if (is_dir($full_path) || substr($normalized, -1) == '/') {
            if (!is_dir($correct_path)) {
                mkdir($correct_path, 0755, true);
                echo "Fixed Dir: $file -> $correct_path<br>";
            }
            @unlink($full_path); // Remove the corrupted filename entry
            continue;
        }
        
        if (file_exists($full_path)) {
            if (rename($full_path, $correct_path)) {
                echo "Fixed File: $file -> $correct_path<br>";
            } else {
                echo "❌ Failed to rename: $file<br>";
            }
        }
    }
}

echo "<br><b>Repair complete.</b> Check your theme list now.";
?>
