# Restore Script for Version 1.0.0.1
# This script will restore your project to version 1.0.0.1 from the backup

Write-Host "Starting restoration to version 1.0.0.1..." -ForegroundColor Green

# Check if backup exists
if (-not (Test-Path "backup_v1.0.0.1")) {
    Write-Host "ERROR: Backup folder 'backup_v1.0.0.1' not found!" -ForegroundColor Red
    exit 1
}

# Create a temporary backup of current state (in case you want to revert)
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$currentBackup = "backup_current_$timestamp"

Write-Host "Creating backup of current state as '$currentBackup'..." -ForegroundColor Yellow

# Copy current files to temporary backup (excluding backup folders)
Get-ChildItem -Path "." -Exclude "backup_*", ".git" | Copy-Item -Destination $currentBackup -Recurse -Force

Write-Host "Current state backed up successfully." -ForegroundColor Green

# Remove current files (excluding backup folders and .git)
Write-Host "Removing current files..." -ForegroundColor Yellow
Get-ChildItem -Path "." -Exclude "backup_*", ".git" | Remove-Item -Recurse -Force

# Restore from backup
Write-Host "Restoring files from backup_v1.0.0.1..." -ForegroundColor Yellow
Copy-Item -Path "backup_v1.0.0.1\*" -Destination "." -Recurse -Force

Write-Host "Restoration completed successfully!" -ForegroundColor Green
Write-Host "Your project has been restored to version 1.0.0.1" -ForegroundColor Green
Write-Host "If you need to revert this restoration, use the backup in: $currentBackup" -ForegroundColor Cyan
