# Farmers Mall - Development Server Starter
# Double-click this file or run: .\start-server.ps1

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "   Farmers Mall - Development Server" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Starting PHP server on localhost:8000..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Once started, open your browser to:" -ForegroundColor Cyan
Write-Host "  http://localhost:8000/user/user-homepage.php" -ForegroundColor White
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Change to script directory
Set-Location $PSScriptRoot

# Start PHP server
php -S localhost:8000
