@echo off
echo Starting restoration to version 1.0.0.2...

REM Check if backup exists
if not exist "backup_v1.0.0.2" (
    echo ERROR: Backup folder 'backup_v1.0.0.2' not found!
    pause
    exit /b 1
)

REM Create timestamp for current backup
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "timestamp=%dt:~0,8%_%dt:~8,6%"
set "currentBackup=backup_current_%timestamp%"

echo Creating backup of current state as '%currentBackup%'...

REM Create exclude file for xcopy
echo backup_* > exclude.txt
echo .git >> exclude.txt

REM Create backup of current state
xcopy . "%currentBackup%" /E /I /H /Y /EXCLUDE:exclude.txt >nul 2>&1

echo Removing current files...

REM Remove current files (excluding backup folders and .git)
for /d %%i in (*) do (
    if not "%%i"=="backup_v1.0.0.2" if not "%%i"=="%currentBackup%" if not "%%i"==".git" (
        rmdir /s /q "%%i" >nul 2>&1
    )
)
for %%i in (*) do (
    if not "%%i"=="backup_v1.0.0.2" if not "%%i"=="%currentBackup%" if not "%%i"==".git" (
        del "%%i" >nul 2>&1
    )
)

echo Restoring files from backup_v1.0.0.2...

REM Restore files from backup
xcopy "backup_v1.0.0.2\*" . /E /I /H /Y >nul 2>&1

REM Clean up exclude file
del exclude.txt >nul 2>&1

echo Restoration completed successfully!
echo Current state backed up as: %currentBackup%
pause
