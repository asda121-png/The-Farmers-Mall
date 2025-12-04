<?php
// Test file to verify the email verification flow
session_start();

// Check if we have a code in session
$hasCode = isset($_SESSION['verification_code']);
$code = $_SESSION['verification_code'] ?? null;
$email = $_SESSION['code_email'] ?? null;
$expires = $_SESSION['code_expires'] ?? null;
$devFileExists = file_exists(__DIR__ . '/.development');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verification Flow Test</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; border-radius: 8px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .info { background: #d1ecf1; color: #0c5460; }
        .error { background: #f8d7da; color: #721c24; }
        input { padding: 8px; margin: 5px 0; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #0056b3; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>📧 Email Verification Flow Test</h1>
    
    <div class="card">
        <h2>System Status</h2>
        <div class="status <?php echo $devFileExists ? 'success' : 'error'; ?>">
            .development file: <strong><?php echo $devFileExists ? '✅ EXISTS' : '❌ MISSING'; ?></strong>
        </div>
        <div class="status info">
            Session has code: <strong><?php echo $hasCode ? '✅ YES' : '❌ NO'; ?></strong>
        </div>
        <?php if ($hasCode): ?>
            <div class="status success">
                Code in session: <code><?php echo htmlspecialchars($code); ?></code>
            </div>
            <div class="status info">
                Email in session: <code><?php echo htmlspecialchars($email); ?></code>
            </div>
            <div class="status info">
                Expires at: <strong><?php echo date('Y-m-d H:i:s', $expires); ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Test Verification Endpoint</h2>
        <p>Enter an email to trigger the verification code generation:</p>
        <input type="email" id="testEmail" placeholder="your-email@example.com" value="<?php echo htmlspecialchars($email ?: 'test@example.com'); ?>">
        <button onclick="testVerification()">📤 Send Verification Code</button>
        <div id="result"></div>
    </div>

    <div class="card">
        <h2>Test Code Matching</h2>
        <p>If you have a code in session, test it here:</p>
        <input type="text" id="codeInput" placeholder="Enter 4-6 digit code" maxlength="6">
        <button onclick="testCodeMatch()">✓ Check Code Match</button>
        <div id="matchResult"></div>
    </div>

    <script>
        async function testVerification() {
            const email = document.getElementById('testEmail').value;
            const resultDiv = document.getElementById('result');
            
            if (!email) {
                resultDiv.innerHTML = '<div class="status error">Please enter an email address</div>';
                return;
            }

            resultDiv.innerHTML = '<div class="status info">Sending...</div>';

            try {
                const response = await fetch('auth/verify-email.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email })
                });
                
                const data = await response.json();
                
                let html = `<div class="status ${data.success ? 'success' : 'error'}">
                    <strong>${data.success ? '✅ Success' : '❌ Error'}</strong><br>
                    Message: ${data.message}
                </div>`;
                
                if (data.code) {
                    html += `<div class="status success">
                        <strong>Code Returned (Dev Mode):</strong> <code>${data.code}</code>
                    </div>`;
                }
                
                resultDiv.innerHTML = html;
                
                // Reload to show session values
                setTimeout(() => location.reload(), 1000);
            } catch (error) {
                resultDiv.innerHTML = `<div class="status error">Error: ${error.message}</div>`;
            }
        }

        function testCodeMatch() {
            const code = document.getElementById('codeInput').value;
            const matchDiv = document.getElementById('matchResult');
            
            if (!code) {
                matchDiv.innerHTML = '<div class="status error">Please enter a code</div>';
                return;
            }

            const sessionCode = '<?php echo $code ?? ''; ?>';
            
            if (!sessionCode) {
                matchDiv.innerHTML = '<div class="status error">No code in session. Please send verification code first.</div>';
                return;
            }

            if (code === sessionCode) {
                matchDiv.innerHTML = `<div class="status success">
                    ✅ <strong>CODE MATCHES!</strong><br>
                    Entered: ${code}<br>
                    Session: ${sessionCode}
                </div>`;
            } else {
                matchDiv.innerHTML = `<div class="status error">
                    ❌ <strong>CODE DOES NOT MATCH</strong><br>
                    Entered: ${code}<br>
                    Session: ${sessionCode}
                </div>`;
            }
        }

        // Test on load
        window.addEventListener('load', () => {
            const code = '<?php echo $code ?? ''; ?>';
            if (code) {
                document.getElementById('codeInput').value = code;
                testCodeMatch();
            }
        });
    </script>
</body>
</html>
