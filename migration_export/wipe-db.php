<?php
// CampusMarket DB Wipe Tool - USE WITH CAUTION
$db_host = 'sql302.infinityfree.com';
$db_user = 'if0_41393349';
$db_pass = 'pKBD8MHnMe';
$db_name = 'if0_41393349_marketcampus';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Dropping all tables in $db_name...<br>";

$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    $table = $row[0];
    $conn->query("DROP TABLE $table");
    echo "Dropped table: $table<br>";
}

echo "<br><b>Database is now empty.</b> Please delete this file.";
$conn->close();
?>
