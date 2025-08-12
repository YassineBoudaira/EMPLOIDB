@echo off
echo Starting restoration to version 1.0.0.1...
echo.

REM Check if backup exists
if not exist "backup_v1.0.0.1" (
    echo ERROR: Backup folder 'backup_v1.0.0.1' not found!
    pause
    exit /b 1
)

REM Create timestamp for current backup
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "timestamp=%dt:~0,8%_%dt:~8,6%"
set "currentBackup=backup_current_%timestamp%"

echo Creating backup of current state as '%currentBackup%'...

REM Create current backup
xcopy . "%currentBackup%" /E /I /H /Y /EXCLUDE:exclude.txt >nul 2>&1

echo Current state backed up successfully.
echo.

echo Removing current files...
REM Remove current files (excluding backup folders)
for /d %%i in (*) do (
    if not "%%i"=="backup_v1.0.0.1" if not "%%i"=="%currentBackup%" if not "%%i"==".git" (
        rmdir /s /q "%%i" >nul 2>&1
    )
)
for %%i in (*) do (
    if not "%%i"=="backup_v1.0.0.1" if not "%%i"=="%currentBackup%" if not "%%i"==".git" (
        del "%%i" >nul 2>&1
    )
)

echo Restoring files from backup_v1.0.0.1...
xcopy "backup_v1.0.0.1\*" . /E /I /H /Y >nul 2>&1

echo.
echo Restoration completed successfully!
echo Your project has been restored to version 1.0.0.1
echo If you need to revert this restoration, use the backup in: %currentBackup%
echo.
pause
