$user        = 'if0_41393349'
$pass        = 'pKBD8MHnMe'
$local_theme = 'c:\xampp\htdocs\campusmarket\wp-content\themes\campusmarket'
$remote_base = 'ftp://ftpupload.net/htdocs/wp-content/themes/campusmarket'

$webclient = New-Object System.Net.WebClient
$webclient.Credentials = New-Object System.Net.NetworkCredential($user, $pass)

function Upload-Dir($localDir, $remoteDir) {
    # Upload all files in this directory
    Get-ChildItem -Path $localDir -File | ForEach-Object {
        $localFile  = $_.FullName
        $remoteFile = "$remoteDir/$($_.Name)"
        Write-Host "  -> $remoteFile"
        try {
            $webclient.UploadFile($remoteFile, 'STOR', $localFile)
        } catch {
            Write-Host "     ERROR: $($_.Exception.Message)"
        }
    }
    # Recurse into subdirectories
    Get-ChildItem -Path $localDir -Directory | ForEach-Object {
        $sub = $_.Name
        Write-Host "[DIR] $remoteDir/$sub"
        Upload-Dir "$localDir\$sub" "$remoteDir/$sub"
    }
}

Write-Host "Starting full theme sync to live server..."
Upload-Dir $local_theme $remote_base
Write-Host "DONE - Full sync complete!"
