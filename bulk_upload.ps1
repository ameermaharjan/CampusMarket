$user = 'if0_41393349'
$pass = 'pKBD8MHnMe'
$host_name = 'ftpupload.net'
$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($user, $pass)

$files = @(
    @{ local = 'c:\xampp\htdocs\campusmarket\campusmarket_theme.zip'; remote = "ftp://$host_name/htdocs/theme.zip" },
    @{ local = 'c:\xampp\htdocs\campusmarket\marketcampus_export_utf8.sql'; remote = "ftp://$host_name/htdocs/db.sql" }
)

foreach ($item in $files) {
    echo "Uploading $($item.local)..."
    try {
        $webclient.UploadFile($item.remote, "STOR", $item.local)
        echo "UPLOADED: $($item.remote)"
    } catch {
        echo "ERROR: $($_.Exception.Message)"
    }
}
