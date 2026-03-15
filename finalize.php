<?php
// CampusMarket Deployment Finalizer
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Set to 0 for unlimited execution time
ini_set('memory_limit', '512M'); // Increased memory limit

$db_host = 'sql302.infinityfree.com';
$db_user = 'if0_41393349';
$db_pass = 'pKBD8MHnMe';
$db_name = 'if0_41393349_marketcampus';
$table_prefix = 'wp_';

$old_url = 'http://localhost/campusmarket';
$new_url = 'http://campusmarket.lovestoblog.com';

echo "<h2>CampusMarket Finalizer</h2>";
echo "<p>Starting deployment finalization process...</p>";

// 1. Theme, Plugin, and Upload Extraction
$packages = [
    'theme.zip'   => 'wp-content/themes',
    'plugins.zip' => 'wp-content/plugins',
    'uploads.zip' => 'wp-content/uploads'
];

foreach ($packages as $zipFile => $destDir) {
    if (file_exists($zipFile)) {
        echo "Processing $zipFile...<br>";
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($destDir);
            $zip->close();
            
            // Handle nesting for theme specifically
            if ($zipFile === 'theme.zip') {
                $nested_path = $destDir . '/campusmarket/campusmarket';
                if (is_dir($nested_path)) {
                    echo "Fixing nested theme directory...<br>";
                    $temp_path = $destDir . '/campus_temp';
                    rename($nested_path, $temp_path);
                    exec("rm -rf " . escapeshellarg($destDir . '/campusmarket'));
                    rename($temp_path, $destDir . '/campusmarket');
                }
            }
            
            echo "✅ $zipFile extracted successfully.<br>";
        } else {
            echo "❌ Failed to open $zipFile<br>";
        }
    }
}

// 2. Database Import & Theme Activation
if (file_exists('db.sql')) {
    echo "Connecting to Database...<br>";
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        echo "❌ DB Connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ DB Connected. Importing SQL line-by-line...<br>";
        
        $handle = fopen('db.sql', 'r');
        $templine = '';
        $success_count = 0;
        $error_count = 0;

        if ($handle) {
            $is_first_line = true;
            while (($line = fgets($handle)) !== false) {
                if ($is_first_line) {
                    $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
                    $is_first_line = false;
                }
                if (substr(trim($line), 0, 2) == '--' || substr(trim($line), 0, 2) == '/*' || trim($line) == '') continue;
                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $query = str_replace($old_url, $new_url, $templine);
                    if (!$conn->query($query)) {
                        if ($conn->errno != 1062) $error_count++;
                    } else {
                        $success_count++;
                    }
                    $templine = '';
                }
            }
            fclose($handle);
            echo "✅ Database import finished. Queries: $success_count success, $error_count errors.<br>";
            
            // Activate Theme
            echo "Activating CampusMarket theme...<br>";
            $conn->query("UPDATE " . $table_prefix . "options SET option_value = 'campusmarket' WHERE option_name IN ('template', 'stylesheet')");
            echo "✅ Theme activated in database.<br>";
            
        } else {
            echo "❌ Failed to open db.sql for reading.<br>";
        }
        $conn->close();

        // 3. Update or Create wp-config.php
        $config_file = 'wp-config.php';
        $sample_file = 'wp-config-sample.php';
        
        if (!file_exists($config_file) && file_exists($sample_file)) {
            copy($sample_file, $config_file);
            echo "✅ Created wp-config.php from sample.<br>";
        }

        if (file_exists($config_file)) {
            echo "Configuring wp-config.php...<br>";
            $config = file_get_contents($config_file);
            $replacements = [
                "/define\(\s*'DB_NAME',\s*'.*'\s*\);/" => "define('DB_NAME', '$db_name');",
                "/define\(\s*'DB_USER',\s*'.*'\s*\);/" => "define('DB_USER', '$db_user');",
                "/define\(\s*'DB_PASSWORD',\s*'.*'\s*\);/" => "define('DB_PASSWORD', '$db_pass');",
                "/define\(\s*'DB_HOST',\s*'.*'\s*\);/" => "define('DB_HOST', '$db_host');"
            ];
            foreach ($replacements as $pattern => $replacement) {
                $config = preg_replace($pattern, $replacement, $config);
            }
            file_put_contents($config_file, $config);
            echo "✅ wp-config.php configured with live credentials.<br>";
        }
    }
}

echo "<br><b>Deployment Finished!</b> Please verify your site at <a href='/'>$new_url</a>";
?>
