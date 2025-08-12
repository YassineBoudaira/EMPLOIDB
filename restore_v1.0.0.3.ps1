# Restore Script for Version 1.0.0.3
# This script restores the project to version 1.0.0.3

Write-Host "Starting restore to version 1.0.0.3..." -ForegroundColor Green

# Create temporary backup of current state
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$tempBackup = "temp_backup_$timestamp"

Write-Host "Creating temporary backup of current state..." -ForegroundColor Yellow
if (Test-Path $tempBackup) {
    Remove-Item $tempBackup -Recurse -Force
}
Copy-Item . $tempBackup -Recurse -Force -Exclude ".git", "backup_v1.0.0.1", "backup_v1.0.0.2", "backup_v1.0.0.3", "temp_backup_*"

Write-Host "Restoring files from backup_v1.0.0.3..." -ForegroundColor Yellow

# Copy files from backup to current directory
Copy-Item "backup_v1.0.0.3\*" . -Recurse -Force -Exclude ".git"

Write-Host "Restore completed successfully!" -ForegroundColor Green
Write-Host "Temporary backup saved as: $tempBackup" -ForegroundColor Cyan
Write-Host "You can delete the temporary backup if everything is working correctly." -ForegroundColor Cyan

Read-Host "Press Enter to continue..."
