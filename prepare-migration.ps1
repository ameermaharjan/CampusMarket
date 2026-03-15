# CampusMarket Migration Packager (V3 - Perfect Structure)
$sourceDir = "c:\xampp\htdocs\campusmarket"
$exportDir = "$sourceDir\migration_export"
$uploadsDir = "$sourceDir\wp-content\uploads"
$themesDir = "$sourceDir\wp-content\themes"
$pluginsDir = "$sourceDir\wp-content\plugins"

if (!(Test-Path $exportDir)) {
    New-Item -ItemType Directory -Path $exportDir
}

Write-Host "Packaging CampusMarket for live migration..." -ForegroundColor Cyan

function Create-Zip($zipName, $sourcePath, $relativePath) {
    Write-Host "Zipping $zipName..."
    $tempZipDir = "$exportDir\temp_$zipName"
    if (Test-Path $tempZipDir) { Remove-Item $tempZipDir -Recurse -Force }
    $destPath = Join-Path $tempZipDir $relativePath
    New-Item -ItemType Directory -Path (Split-Path $destPath) -Force | Out-Null
    Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
    Compress-Archive -Path "$tempZipDir\*" -DestinationPath "$exportDir\$zipName.zip" -Force
    Remove-Item $tempZipDir -Recurse -Force
}

# 1. Zip Theme
Create-Zip "theme" "$themesDir\campusmarket" "wp-content\themes\campusmarket"

# 2. Zip Plugins
Create-Zip "plugins" "$pluginsDir" "wp-content\plugins"

# 3. Zip Uploads in chunks
Write-Host "Preparing Uploads Parts..."
$tempDir1 = "$exportDir\temp_uploads1"
if (Test-Path $tempDir1) { Remove-Item $tempDir1 -Recurse -Force }
$destUploads = "$tempDir1\wp-content\uploads"
New-Item -ItemType Directory -Path $destUploads -Force | Out-Null

Copy-Item -Path "$uploadsDir" -Destination (Split-Path $destUploads) -Recurse -Force
Get-ChildItem -Path "$destUploads" -Recurse -Filter "134*" | Remove-Item -Force

Compress-Archive -Path "$tempDir1\*" -DestinationPath "$exportDir\uploads1.zip" -Force
Remove-Item $tempDir1 -Recurse -Force

$tempDir2 = "$exportDir\temp_uploads2"
if (Test-Path $tempDir2) { Remove-Item $tempDir2 -Recurse -Force }
$destUploads2 = "$tempDir2\wp-content\uploads\2026\03"
New-Item -ItemType Directory -Path $destUploads2 -Force | Out-Null
Get-ChildItem -Path "$uploadsDir\2026\03" -Filter "134*" | Copy-Item -Destination $destUploads2 -Force

Compress-Archive -Path "$tempDir2\*" -DestinationPath "$exportDir\uploads2.zip" -Force
Remove-Item $tempDir2 -Recurse -Force

# 4. Copy DB Exports & Scripts
Write-Host "Copying Database Exports & Scripts..."
Copy-Item "$sourceDir\marketcampus_export.sql" "$exportDir\db.sql" -Force
Copy-Item "$sourceDir\migrate-urls.php" "$exportDir\migrate-urls.php" -Force
Copy-Item "$sourceDir\finalize.php" "$exportDir\finalize.php" -Force
Copy-Item "$sourceDir\wipe-db.php" "$exportDir\wipe-db.php" -Force

Write-Host "`n✅ Migration assets packaged successfully!" -ForegroundColor Green
