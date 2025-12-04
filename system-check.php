<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - The Farmers Mall</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2d3748;
            border-bottom: 3px solid #48bb78;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success {
            background-color: #c6f6d5;
            border-left: 4px solid #48bb78;
        }
        .error {
            background-color: #fed7d7;
            border-left: 4px solid #f56565;
        }
        .warning {
            background-color: #feebc8;
            border-left: 4px solid #ed8936;
        }
        .icon {
            font-size: 24px;
            font-weight: bold;
        }
        .info {
            background-color: #e6fffa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .fix-instructions {
            background-color: #fef5e7;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 2px solid #f39c12;
        }
        .fix-instructions h3 {
            color: #d68910;
            margin-top: 0;
        }
        .fix-instructions ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .fix-instructions li {
            margin: 8px 0;
            line-height: 1.6;
        }
        code {
            background-color: #f7fafc;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #c7254e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 System Requirements Check</h1>
        
        <?php
        $allPassed = true;
        
        // Check 1: PHP Version
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '7.4.0', '>=');
        ?>
        
        <div class="check-item <?php echo $phpOk ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $phpOk ? '✅' : '❌'; ?></span>
            <div>
                <strong>PHP Version:</strong> <?php echo $phpVersion; ?>
                <?php if (!$phpOk): ?>
                    <br><small>Required: PHP 7.4 or higher</small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
        // Check 2: cURL Extension
        $curlEnabled = function_exists('curl_init');
        if (!$curlEnabled) $allPassed = false;
        ?>
        
        <div class="check-item <?php echo $curlEnabled ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $curlEnabled ? '✅' : '❌'; ?></span>
            <div>
                <strong>cURL Extension:</strong> <?php echo $curlEnabled ? 'Enabled' : 'NOT Enabled (REQUIRED!)'; ?>
            </div>
        </div>
        
        <?php if (!$curlEnabled): ?>
        <div class="fix-instructions">
            <h3>🔧 How to Enable cURL in XAMPP:</h3>
            <ol>
                <li>Open <strong>XAMPP Control Panel</strong></li>
                <li>Click <strong>"Config"</strong> button next to Apache</li>
                <li>Select <strong>"PHP (php.ini)"</strong></li>
                <li>Press <strong>Ctrl+F</strong> and search for: <code>;extension=curl</code></li>
                <li>Remove the semicolon so it reads: <code>extension=curl</code></li>
                <li>Save the file (Ctrl+S)</li>
                <li>In XAMPP Control Panel, click <strong>Stop</strong> then <strong>Start</strong> for Apache</li>
                <li>Refresh this page</li>
            </ol>
            <p><strong>📖 Detailed guide:</strong> <a href="ENABLE_CURL.md" target="_blank">ENABLE_CURL.md</a></p>
        </div>
        <?php endif; ?>
        
        <?php
        // Check 3: OpenSSL Extension
        $sslEnabled = extension_loaded('openssl');
        ?>
        
        <div class="check-item <?php echo $sslEnabled ? 'success' : 'warning'; ?>">
            <span class="icon"><?php echo $sslEnabled ? '✅' : '⚠️'; ?></span>
            <div>
                <strong>OpenSSL Extension:</strong> <?php echo $sslEnabled ? 'Enabled' : 'Not Enabled'; ?>
                <?php if (!$sslEnabled): ?>
                    <br><small>Recommended for secure connections</small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
        // Check 4: .env file
        $envExists = file_exists(__DIR__ . '/config/.env');
        if (!$envExists) $allPassed = false;
        ?>
        
        <div class="check-item <?php echo $envExists ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $envExists ? '✅' : '❌'; ?></span>
            <div>
                <strong>.env Configuration File:</strong> <?php echo $envExists ? 'Exists' : 'Missing'; ?>
                <?php if (!$envExists): ?>
                    <br><small>Run setup.bat or copy config/.env.example to config/.env</small>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
        // Check 5: Database Connection (if .env exists and curl is enabled)
        if ($envExists && $curlEnabled) {
            try {
                require_once __DIR__ . '/config/supabase-api.php';
                $api = getSupabaseAPI();
                $result = $api->select('users', ['id' => '00000000-0000-0000-0000-000000000000']);
                $dbConnected = true;
            } catch (Exception $e) {
                $dbConnected = false;
                $dbError = $e->getMessage();
            }
        ?>
        
        <div class="check-item <?php echo $dbConnected ? 'success' : 'error'; ?>">
            <span class="icon"><?php echo $dbConnected ? '✅' : '❌'; ?></span>
            <div>
                <strong>Database Connection:</strong> <?php echo $dbConnected ? 'Working' : 'Failed'; ?>
                <?php if (!$dbConnected && isset($dbError)): ?>
                    <br><small><?php echo htmlspecialchars($dbError); ?></small>
                <?php endif; ?>
            </div>
        </div>
        <?php } ?>
        
        <hr style="margin: 30px 0;">
        
        <?php if ($allPassed && $curlEnabled): ?>
        <div class="check-item success">
            <span class="icon">🎉</span>
            <div>
                <strong>All checks passed! Your system is ready.</strong>
                <br><a href="auth/login.php">Go to Login Page →</a>
            </div>
        </div>
        <?php else: ?>
        <div class="check-item error">
            <span class="icon">⚠️</span>
            <div>
                <strong>Some issues need to be fixed before the application will work.</strong>
                <br><small>Please follow the instructions above.</small>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>ℹ️ Need Help?</strong>
            <ul>
                <li>Check <a href="TEAM_SETUP.md">TEAM_SETUP.md</a> for setup instructions</li>
                <li>Check <a href="ENABLE_CURL.md">ENABLE_CURL.md</a> for cURL troubleshooting</li>
                <li>Contact your project lead if issues persist</li>
            </ul>
        </div>
    </div>
</body>
</html>
