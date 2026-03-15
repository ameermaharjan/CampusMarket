<?php
// CampusMarket Theme Debugger
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Theme Structure Debugger</h2>";

$themes_dir = 'wp-content/themes';

if (is_dir($themes_dir)) {
    echo "✅ Themes directory found: $themes_dir<br>";
    $dirs = scandir($themes_dir);
    echo "<h3>Contents of $themes_dir:</h3><ul>";
    foreach ($dirs as $dir) {
        if ($dir == "." || $dir == "..") continue;
        echo "<li>📁 $dir";
        if (is_dir("$themes_dir/$dir")) {
            $sub = scandir("$themes_dir/$dir");
            echo "<ul>";
            foreach ($sub as $s) {
                 if ($s == "." || $s == "..") continue;
                 echo "<li>$s</li>";
            }
            echo "</ul>";
        }
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "❌ Themes directory NOT found!<br>";
}

echo "<br><b>Debug complete.</b>";
?>
