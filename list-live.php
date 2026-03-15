<?php
echo "<h3>Live File List</h3>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != "." && $file != "..") {
        echo $file . " (" . filesize($file) . " bytes)<br>";
    }
}
?>
