$user = 'if0_41393349'
$pass = 'pKBD8MHnMe'
$host_name = 'ftpupload.net'
$localFile = 'c:\xampp\htdocs\campusmarket\debug_docs.php'
$remoteFile = "ftp://$host_name/htdocs/debug_docs.php"

try {
    $webclient = New-Object System.Net.WebClient
    $webclient.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
    $webclient.UploadFile($remoteFile, "STOR", $localFile)
    echo "UPLOAD SUCCESSFUL"
} catch {
    echo "ERROR: $($_.Exception.Message)"
}
