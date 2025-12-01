@echo off
echo =========================================
echo   Farmers Mall - Database Setup
echo =========================================
echo.

REM Run the PowerShell setup script
powershell -ExecutionPolicy Bypass -File "%~dp0setup.ps1"

echo.
echo Press any key to exit...
pause >nul
