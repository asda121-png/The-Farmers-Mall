<?php
// Debug: Check what verify-email.php returns
session_start();

// Check .development file
$dev_file_exists = file_exists(__DIR__ . '/.development');
$dev_file_path = realpath(__DIR__ . '/.development');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verification Code Debug</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; border-radius: 8px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        textarea { width: 100%; height: 200px; border: 1px solid #ddd; padding: 10px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🔍 Verification System Debug</h1>

    <div class="card">
        <h2>File System Check</h2>
        <div class="status <?php echo $dev_file_exists ? 'success' : 'warning'; ?>">
            <strong>.development file:</strong> <?php echo $dev_file_exists ? '✅ EXISTS' : '❌ MISSING'; ?><br>
            <?php if ($dev_file_exists): ?>
                <strong>Path:</strong> <code><?php echo htmlspecialchars($dev_file_path); ?></code>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h2>Test API Call</h2>
        <p>Click the button below to send a test request to verify-email.php:</p>
        <button onclick="testAPI()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
            📤 Send Test Request
        </button>
        <div id="result" style="margin-top: 20px;"></div>
    </div>

    <script>
        async function testAPI() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div style="padding: 10px; background: #e7f3ff;">Testing...</div>';

            try {
                const response = await fetch('auth/verify-email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: 'debug@example.com' })
                });

                const data = await response.json();

                let html = '<div class="card">';
                html += '<h3>Response Received</h3>';
                html += '<textarea readonly>' + JSON.stringify(data, null, 2) + '</textarea>';
                html += '<br><br>';

                if (data.code) {
                    html += '<div class="status success">';
                    html += '✅ <strong>CODE RETURNED:</strong> <code style="font-size: 16px; font-weight: bold;">' + data.code + '</code>';
                    html += '</div>';
                } else {
                    html += '<div class="status warning">';
                    html += '⚠️ <strong>NO CODE IN RESPONSE</strong><br>';
                    html += 'This means either: (1) Email was sent successfully, (2) .development file is not being detected by server';
                    html += '</div>';
                }

                html += '</div>';
                resultDiv.innerHTML = html;
            } catch (error) {
                resultDiv.innerHTML = '<div class="status" style="background: #f8d7da; color: #721c24;">' +
                    '❌ Error: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>
