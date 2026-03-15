<?php
echo "<h2>Final Cleanup...</h2>";

// 1. Delete index2.html
if (file_exists('index2.html')) {
    if (unlink('index2.html')) {
        echo "✅ Deleted index2.html (conflicting file).<br>";
    } else {
        echo "❌ Failed to delete index2.html. Please delete it manually via File Manager.<br>";
    }
} else {
    echo "ℹ️ index2.html not found.<br>";
}

// 2. Create .htaccess
$htaccess_content = "# BEGIN WordPress\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase /\nRewriteRule ^index\.php$ - [L]\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule . /index.php [L]\n</IfModule>\n# END WordPress";

if (file_put_contents('.htaccess', $htaccess_content)) {
    echo "✅ .htaccess created successfully.<br>";
} else {
    echo "❌ Failed to create .htaccess. Please create it manually with WordPress default rules.<br>";
}

echo "<h3>Cleanup Complete.</h3> Try visiting your site now!";
?>
