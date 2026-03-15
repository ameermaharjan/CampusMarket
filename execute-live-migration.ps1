# CampusMarket Live Deployer
$user = 'if0_41393349'
$pass = 'pKBD8MHnMe'
$host_name = 'ftpupload.net'
$baseUrl = "ftp://$host_name/htdocs"
$creds = New-Object System.Net.NetworkCredential($user, $pass)

Write-Host "Starting Clean Sweep and Deployment..." -ForegroundColor Cyan

function Remove-FtpDirectoryContent($url) {
    $request = [System.Net.FtpWebRequest]::Create($url)
    $request.Credentials = $creds
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $response = $request.GetResponse()
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $files = $reader.ReadToEnd() -split "`r`n"
    $response.Close()

    foreach ($file in $files) {
        if ($file -ne "." -and $file -ne ".." -and $file -ne "" -and $file -ne "files for your website should be uploaded here!") {
            $itemUrl = "$url/$file"
            Write-Host "Deleting $file..."
            try {
                $delRequest = [System.Net.FtpWebRequest]::Create($itemUrl)
                $delRequest.Credentials = $creds
                $delRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
                $delResponse = $delRequest.GetResponse()
                $delResponse.Close()
            } catch {
                Write-Host "Skipping directory (or error): $file" -ForegroundColor Gray
            }
        }
    }
}

function Upload-File($localPath, $remoteName) {
    $remoteUrl = "$baseUrl/$remoteName"
    Write-Host "Uploading $remoteName..."
    $webClient = New-Object System.Net.WebClient
    $webClient.Credentials = $creds
    $webClient.UploadFile($remoteUrl, "STOR", $localPath)
}

# 1. Wipe Live Files
Remove-FtpDirectoryContent $baseUrl

# 2. Upload Everything in Migration Export
$exportFiles = Get-ChildItem "$exportDir"
foreach ($file in $exportFiles) {
    Upload-File $file.FullName $file.Name
}

Write-Host "`n✅ Remote Deployment Finished!" -ForegroundColor Green
