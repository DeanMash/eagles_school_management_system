@echo off
echo ====================================
echo EAGLES GITHUB SETUP
echo ====================================
echo.

REM Step 1: Configure Git
echo Setting Git identity...
git config --global user.email "dchipembe13@gmail.com"
git config --global user.name "DeanMash"

REM Step 2: Update .gitignore without removing project-specific protections
echo Updating .gitignore...
(
echo # Laravel Default
echo /node_modules
echo /vendor
echo .env
echo .env.*
echo !.env.example
echo .env.backup
echo storage/logs/*
echo storage/framework/sessions/*
echo storage/framework/views/*
echo storage/framework/cache/*
echo /public/hot
echo /public/storage
echo.
echo # Development files
echo DEBUG_TIMETABLE.md
echo FOUNDATION_CHECK_REPORT.md
echo FOUNDATION_CHECK_STATUS.md
echo PROGRESS_UPDATE.md
echo QUICK_TEST_START.md
echo REMAINING_WORK.md
echo TESTING_EXECUTION.md
echo TESTING_GUIDE.md
echo TIMETABLE_GUIDE.md
echo _ide_helper.php
echo .idea/
echo composer.phar
echo php
) >> .gitignore

REM Step 3: Rename README
if exist "readme.md" ren readme.md README.md

REM Step 4: Add files
echo Adding files to Git...
git add .
git diff --cached --name-only | findstr /R /I /X /C:"\.env" /C:"\.env\..*" | findstr /V /I /X /C:".env.example" >nul
if not errorlevel 1 (
    echo ERROR: A private environment file is staged. No commit was created.
    git diff --cached --name-only | findstr /R /I /X /C:"\.env" /C:"\.env\..*" | findstr /V /I /X /C:".env.example"
    exit /b 1
)

REM Step 5: Commit
echo Creating commit...
git commit -m "Initial commit: Eagles School Management System"

echo.
echo ====================================
echo ✅ LOCAL GIT SETUP COMPLETE!
echo ====================================
echo.