@echo off
REM Restore Script for Version 1.0.0.3
REM This script restores the project to version 1.0.0.3

echo Starting restore to version 1.0.0.3...

REM Create temporary backup of current state
set timestamp=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set timestamp=%timestamp: =0%
set tempBackup=temp_backup_%timestamp%

echo Creating temporary backup of current state...
if exist "%tempBackup%" rmdir /s /q "%tempBackup%"
xcopy . "%tempBackup%" /E /I /H /Y /EXCLUDE:exclude.txt

echo Restoring files from backup_v1.0.0.3...

REM Copy files from backup to current directory
xcopy "backup_v1.0.0.3\*" . /E /I /H /Y

echo Restore completed successfully!
echo Temporary backup saved as: %tempBackup%
echo You can delete the temporary backup if everything is working correctly.

pause
