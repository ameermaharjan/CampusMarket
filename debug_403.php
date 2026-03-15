<?php
echo "<h2>403 Debugger</h2>";

echo "<h3>.htaccess Content:</h3>";
if (file_exists('.htaccess')) {
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
} else {
    echo "❌ .htaccess not found.<br>";
}

echo "<h3>index.php First 5 Lines:</h3>";
if (file_exists('index.php')) {
    $lines = file('index.php');
    echo "<pre>";
    for($i=0; $i<min(5, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]);
    }
    echo "</pre>";
} else {
    echo "❌ index.php not found.<br>";
}

echo "<h3>Directory Permissions (htdocs):</h3>";
echo substr(sprintf('%o', fileperms('.')), -4);

echo "<br><br><b>Debug complete.</b>";
?>
