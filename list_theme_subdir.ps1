$user = 'if0_41393349'
$pass = 'pKBD8MHnMe'
$host_name = 'ftpupload.net'
$url = "ftp://$host_name/htdocs/wp-content/themes/campusmarket/"

try {
    $request = [System.Net.FtpWebRequest]::Create($url)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $request.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
    $response = $request.GetResponse()
    $stream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($stream)
    $files = $reader.ReadToEnd()
    $response.Close()
    echo "FILES IN CAMPUSMARKET THEME:"
    echo $files
} catch {
    echo "ERROR: $($_.Exception.Message)"
}
