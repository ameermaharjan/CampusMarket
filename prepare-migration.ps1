# CampusMarket Migration Packager
# This script zips up the theme, plugins, and uploads for a "same to same" migration.

$sourceDir = "c:\xampp\htdocs\campusmarket"
$exportDir = "$sourceDir\migration_export"

if (!(Test-Path $exportDir)) {
    New-Item -ItemType Directory -Path $exportDir
}

Write-Host "Packaging CampusMarket for live migration..." -ForegroundColor Cyan

# 1. Zip Theme
Write-Host "Zipping Theme..."
Compress-Archive -Path "$sourceDir\wp-content\themes\campusmarket" -DestinationPath "$exportDir\theme.zip" -Force

# 2. Zip Plugins
Write-Host "Zipping Plugins..."
Compress-Archive -Path "$sourceDir\wp-content\plugins" -DestinationPath "$exportDir\plugins.zip" -Force

# 3. Zip Uploads
Write-Host "Zipping Uploads..."
Compress-Archive -Path "$sourceDir\wp-content\uploads" -DestinationPath "$exportDir\uploads.zip" -Force

# 4. Copy DB Exports & Scripts
Write-Host "Copying Database Exports & Scripts..."
Copy-Item "$sourceDir\marketcampus_export.sql" "$exportDir\db.sql" -Force
Copy-Item "$sourceDir\migrate-urls.php" "$exportDir\migrate-urls.php" -Force
Copy-Item "$sourceDir\finalize.php" "$exportDir\finalize.php" -Force
Copy-Item "$sourceDir\wipe-db.php" "$exportDir\wipe-db.php" -Force

Write-Host "`n✅ Migration assets packaged successfully in: $exportDir" -ForegroundColor Green
Write-Host "Please upload the contents of this folder to your live server root." -ForegroundColor Yellow
