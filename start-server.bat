@echo off
echo.
echo ========================================
echo   Farmers Mall - Development Server
echo ========================================
echo.
echo Starting PHP server on localhost:8000...
echo.
echo Once started, open your browser to:
echo   http://localhost:8000/user/user-homepage.php
echo.
echo Press Ctrl+C to stop the server
echo.
echo ========================================
echo.

cd /d "%~dp0"
php -S localhost:8000

pause
