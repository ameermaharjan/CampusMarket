<?php 
$files = ["debug_docs.php", "phpinfo.php", "debug_403.php", "cleanup.php", "repair_theme.php", "debug_login.php", "set-pending.php"];
foreach ($files as $f) {
    if (file_exists($f)) { unlink($f); echo "Deleted: $f\n"; }
}
unlink(__FILE__);
?>
