$user = 'if0_41393349'
$pass = 'pKBD8MHnMe'
$host_name = 'ftpupload.net'
$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($user, $pass)

$files = @(
    @{ local = 'c:\xampp\htdocs\campusmarket\wp-content\themes\campusmarket\page-templates\page-verify.php'; remote = 'ftp://ftpupload.net/htdocs/wp-content/themes/campusmarket/page-templates/page-verify.php' },
    @{ local = 'c:\xampp\htdocs\campusmarket\wp-content\themes\campusmarket\page-templates\page-admin-panel.php'; remote = 'ftp://ftpupload.net/htdocs/wp-content/themes/campusmarket/page-templates/page-admin-panel.php' },
    @{ local = 'c:\xampp\htdocs\campusmarket\wp-content\themes\campusmarket\inc\ajax-handlers.php'; remote = 'ftp://ftpupload.net/htdocs/wp-content/themes/campusmarket/inc/ajax-handlers.php' },
    @{ local = 'c:\xampp\htdocs\campusmarket\wp-content\themes\campusmarket\assets\js\dashboard.js'; remote = 'ftp://ftpupload.net/htdocs/wp-content/themes/campusmarket/assets/js/dashboard.js' }
)

foreach ($file in $files) {
    Write-Output "Uploading: $($file.local)"
    try {
        $webclient.UploadFile($file.remote, "STOR", $file.local)
        Write-Output "SUCCESS"
    } catch {
        Write-Output "ERROR: $($_.Exception.Message)"
    }
}
