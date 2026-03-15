<?php
// CampusMarket Login Debugger
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('wp-config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

echo "<h2>Login Debugger</h2>";

if ($conn->connect_error) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}
echo "✅ DB Connected Successfully.<br>";

// 1. Check Site URLs
$result = $conn->query("SELECT option_name, option_value FROM " . $table_prefix . "options WHERE option_name IN ('siteurl', 'home')");
while ($row = $result->fetch_assoc()) {
    echo "🔗 " . $row['option_name'] . ": " . $row['option_value'] . "<br>";
}

// 2. Check Users
$result = $conn->query("SELECT user_login, user_email FROM " . $table_prefix . "users");
echo "<h3>User List:</h3>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "👤 User: " . $row['user_login'] . " (" . $row['user_email'] . ")<br>";
    }
} else {
    echo "❌ No users found in database!<br>";
}

// 3. Check for .htaccess
if (file_exists('.htaccess')) {
    echo "✅ .htaccess found.<br>";
} else {
    echo "⚠️ .htaccess missing. Permalinks might not work.<br>";
}

echo "<br><br><b>Debug complete.</b>";
?>
