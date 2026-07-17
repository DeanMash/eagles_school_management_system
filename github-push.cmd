@echo off
echo ====================================
echo EAGLES GITHUB PUSH - DeanMash
echo ====================================
echo.

echo [1/7] Configuring Git identity...
git config --global user.email "dchipembe13@gmail.com"
git config --global user.name "DeanMash"
echo ✅ Identity set: DeanMash <dchipembe13@gmail.com>

echo.
echo [2/7] Updating .gitignore...
echo # Laravel environment files >> .gitignore
echo .env >> .gitignore
echo .env.* >> .gitignore
echo !.env.example >> .gitignore
echo # Development files >> .gitignore
echo DEBUG_TIMETABLE.md >> .gitignore
echo FOUNDATION_CHECK_REPORT.md >> .gitignore
echo FOUNDATION_CHECK_STATUS.md >> .gitignore
echo PROGRESS_UPDATE.md >> .gitignore
echo QUICK_TEST_START.md >> .gitignore
echo REMAINING_WORK.md >> .gitignore
echo TESTING_EXECUTION.md >> .gitignore
echo TESTING_GUIDE.md >> .gitignore
echo TIMETABLE_GUIDE.md >> .gitignore
echo _ide_helper.php >> .gitignore
echo .idea/ >> .gitignore
echo composer.phar >> .gitignore
echo php >> .gitignore
echo ✅ .gitignore updated

echo.
echo [3/7] Renaming README...
if exist "readme.md" (
    ren readme.md README.md
    echo ✅ Renamed to README.md
)

echo.
echo [4/7] Adding files to Git...
git add .
echo ✅ Files added

echo.
echo [5/7] Creating commit...
git commit -m "Initial commit: Eagles School Management System by DeanMash"
echo ✅ Commit created

echo.
echo [6/7] Connecting to GitHub...
REM Choose repository name:
set /p REPO_NAME=Enter GitHub repository name (eagles-school-management or eagles_school_management_system): 
git remote add origin https://github.com/DeanMash/%REPO_NAME%.git
git branch -M main
echo ✅ Connected to GitHub

echo.
echo [7/7] Pushing to GitHub...
git push -u origin main
echo ✅ Code pushed to GitHub!

echo.
echo ====================================
echo 🎉 SUCCESS! Repository URL:
echo https://github.com/DeanMash/%REPO_NAME%
echo ====================================
pause