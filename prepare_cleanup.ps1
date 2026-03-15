$user = 'if0_41393349'
$pass = 'pKBD8MHnMe'
$host_name = 'ftpupload.net'

# Using a raw FTP command via a scratch script is complex, 
# so we'll use a PHP script to delete the files and then delete the script itself.
$localCleanup = 'c:\xampp\htdocs\campusmarket\server_cleanup.php'
$cleanupContent = '<?php 
$files = ["debug_docs.php", "phpinfo.php", "debug_403.php", "cleanup.php", "repair_theme.php", "debug_login.php", "set-pending.php"];
foreach ($files as $f) {
    if (file_exists($f)) { unlink($f); echo "Deleted: $f\n"; }
}
unlink(__FILE__);
?>'
Set-Content -Path $localCleanup -Value $cleanupContent

try {
    $webclient = New-Object System.Net.WebClient
    $webclient.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
    $webclient.UploadFile("ftp://$host_name/htdocs/server_cleanup.php", "STOR", $localCleanup)
    echo "CLEANUP SCRIPT UPLOADED"
} catch {
    echo "ERROR: $($_.Exception.Message)"
}
