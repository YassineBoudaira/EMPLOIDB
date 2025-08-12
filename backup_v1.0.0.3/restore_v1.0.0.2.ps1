# Restore Script for Version 1.0.0.2
# This script will restore the project to version 1.0.0.2

Write-Host "Starting restoration to version 1.0.0.2..."

# Check if backup exists
if (-not (Test-Path "backup_v1.0.0.2")) {
    Write-Host "ERROR: Backup folder 'backup_v1.0.0.2' not found!" -ForegroundColor Red
    exit 1
}

# Create timestamp for current backup
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$currentBackup = "backup_current_$timestamp"

Write-Host "Creating backup of current state as '$currentBackup'..."

# Create backup of current state (excluding existing backup folders)
Get-ChildItem -Path "." -Exclude "backup_*", ".git" | Copy-Item -Destination $currentBackup -Recurse -Force

Write-Host "Removing current files..."

# Remove current files (excluding backup folders and .git)
Get-ChildItem -Path "." -Exclude "backup_*", ".git" | Remove-Item -Recurse -Force

Write-Host "Restoring files from backup_v1.0.0.2..."

# Restore files from backup
Copy-Item -Path "backup_v1.0.0.2\*" -Destination "." -Recurse -Force

Write-Host "Restoration completed successfully!" -ForegroundColor Green
Write-Host "Current state backed up as: $currentBackup" -ForegroundColor Yellow
