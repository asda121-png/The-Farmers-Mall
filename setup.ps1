# Farmers Mall - Automated Setup Script
# This script will set up everything automatically for team members

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  Farmers Mall - Database Setup" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check if .env file exists
Write-Host "Step 1: Checking configuration file..." -ForegroundColor Yellow
if (Test-Path "config\.env") {
    Write-Host "  [OK] .env file already exists" -ForegroundColor Green
} else {
    Write-Host "  Creating .env file from template..." -ForegroundColor Yellow
    
    if (Test-Path "config\.env.example") {
        Copy-Item "config\.env.example" "config\.env"
        Write-Host "  [OK] .env file created successfully" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] .env.example not found!" -ForegroundColor Red
        Write-Host "  Please run 'git pull' first" -ForegroundColor Yellow
        exit 1
    }
}
Write-Host ""

# Step 2: Check PHP installation
Write-Host "Step 2: Checking PHP installation..." -ForegroundColor Yellow
try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Host "  [OK] PHP is installed: $phpVersion" -ForegroundColor Green
} catch {
    Write-Host "  [ERROR] PHP is not installed or not in PATH" -ForegroundColor Red
    Write-Host "  Please install PHP first: https://www.php.net/downloads" -ForegroundColor Yellow
    exit 1
}
Write-Host ""

# Step 3: Check CURL extension
Write-Host "Step 3: Checking PHP CURL extension..." -ForegroundColor Yellow
$curlEnabled = php -m 2>&1 | Select-String -Pattern "curl" -Quiet

if ($curlEnabled) {
    Write-Host "  [OK] CURL extension is enabled" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] CURL extension not enabled" -ForegroundColor Yellow
    Write-Host "  Attempting to enable CURL..." -ForegroundColor Yellow
    
    # Try to find php.ini
    $phpIniPath = (php --ini 2>&1 | Select-String -Pattern "Loaded Configuration File:" | ForEach-Object { $_ -replace ".*: ", "" }).Trim()
    
    if ($phpIniPath -and (Test-Path $phpIniPath)) {
        Write-Host "  Found php.ini at: $phpIniPath" -ForegroundColor Cyan
        
        # Create backup
        $backupPath = "$phpIniPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
        Copy-Item $phpIniPath $backupPath
        Write-Host "  Backup created: $backupPath" -ForegroundColor Cyan
        
        # Enable CURL
        $content = Get-Content $phpIniPath
        $content = $content -replace ';extension=curl', 'extension=curl'
        $content | Set-Content $phpIniPath
        
        # Check if we need cacert.pem
        if (!(Select-String -Path $phpIniPath -Pattern "curl.cainfo" -Quiet) -or (Select-String -Path $phpIniPath -Pattern ";curl.cainfo" -Quiet)) {
            $phpDir = Split-Path $phpIniPath -Parent
            $cacertPath = Join-Path $phpDir "cacert.pem"
            
            if (!(Test-Path $cacertPath)) {
                Write-Host "  Downloading SSL certificate bundle..." -ForegroundColor Yellow
                try {
                    Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile $cacertPath -UseBasicParsing
                    Write-Host "  [OK] Certificate bundle downloaded" -ForegroundColor Green
                } catch {
                    Write-Host "  [WARNING] Could not download certificate bundle" -ForegroundColor Yellow
                }
            }
            
            # Update php.ini with cacert path
            $content = Get-Content $phpIniPath
            $content = $content -replace ';curl.cainfo =', "curl.cainfo = `"$cacertPath`""
            $content | Set-Content $phpIniPath
        }
        
        Write-Host "  [OK] CURL extension enabled" -ForegroundColor Green
    } else {
        Write-Host "  [WARNING] Could not find php.ini automatically" -ForegroundColor Yellow
        Write-Host "  Please enable CURL extension manually" -ForegroundColor Yellow
    }
}
Write-Host ""

# Step 4: Check internet connection
Write-Host "Step 4: Checking internet connection..." -ForegroundColor Yellow
try {
    $testConnection = Test-Connection -ComputerName google.com -Count 1 -Quiet -ErrorAction SilentlyContinue
    if ($testConnection) {
        Write-Host "  [OK] Internet connection is working" -ForegroundColor Green
    } else {
        Write-Host "  [WARNING] Cannot reach internet" -ForegroundColor Yellow
        Write-Host "  Please check your network connection" -ForegroundColor Yellow
    }
} catch {
    Write-Host "  [WARNING] Could not test internet connection" -ForegroundColor Yellow
}
Write-Host ""

# Step 5: Test database connection
Write-Host "Step 5: Testing Supabase connection..." -ForegroundColor Yellow
Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan

try {
    php config/test-connection.php
    $testResult = $LASTEXITCODE
    
    Write-Host "=========================================" -ForegroundColor Cyan
    Write-Host ""
    
    if ($testResult -eq 0) {
        Write-Host "SUCCESS! You're all set up!" -ForegroundColor Green -BackgroundColor Black
        Write-Host ""
        Write-Host "Next steps:" -ForegroundColor Cyan
        Write-Host "  1. Start coding your features" -ForegroundColor White
        Write-Host "  2. Check TEAM_SETUP.md for code examples" -ForegroundColor White
        Write-Host "  3. Use 'php config/test-database.php' anytime to verify connection" -ForegroundColor White
    } else {
        Write-Host "SETUP INCOMPLETE - Please check errors above" -ForegroundColor Yellow -BackgroundColor Black
        Write-Host ""
        Write-Host "Common solutions:" -ForegroundColor Cyan
        Write-Host "  1. Make sure config/.env file exists" -ForegroundColor White
        Write-Host "  2. Check your internet connection" -ForegroundColor White
        Write-Host "  3. Try running this script again" -ForegroundColor White
        Write-Host "  4. Contact team if issues persist" -ForegroundColor White
    }
} catch {
    Write-Host "=========================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "[ERROR] Could not run database test" -ForegroundColor Red
    Write-Host "Error: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
