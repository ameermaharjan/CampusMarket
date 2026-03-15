<?php
// CampusMarket Auto-Installer
echo "Starting WordPress Download...<br>";
$url = "https://wordpress.org/latest.zip";
$zipFile = "wp.zip";

if (file_put_contents($zipFile, file_get_contents($url))) {
    echo "Download Complete. Extracting...<br>";
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo('.');
        $zip->close();
        echo "Extraction Complete!<br>";
        
        // Move files from 'wordpress' folder to root
        $files = scandir('wordpress');
        foreach($files as $file) {
            if ($file != "." && $file != "..") {
                rename("wordpress/$file", "./$file");
            }
        }
        rmdir('wordpress');
        unlink($zipFile);
        echo "Installation ready at root. Please refresh your domain.";
    } else {
        echo "Failed to open zip.";
    }
} else {
    echo "Failed to download WordPress.";
}
?>
