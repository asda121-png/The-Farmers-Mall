<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Flow Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-green-600 mb-2">🧪 Email Verification Tester</h1>
                <p class="text-gray-600 mb-8">Test the complete verification flow in real-time</p>

                <!-- Session Status -->
                <div class="mb-8 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                    <h2 class="font-bold text-blue-900 mb-4">📊 Current Session Status</h2>
                    <div id="sessionStatus" class="space-y-2 text-sm font-mono">
                        <p>Loading session data...</p>
                    </div>
                </div>

                <!-- Test Controls -->
                <div class="mb-8 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                    <h2 class="font-bold text-yellow-900 mb-4">🚀 Test Verification</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="testEmail" placeholder="test@example.com" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <button onclick="testSendCode()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            📧 Step 1: Send Verification Code
                        </button>

                        <div id="codeContainer" style="display: none;" class="p-4 bg-green-50 border border-green-200 rounded">
                            <p class="text-sm text-gray-700 mb-2">Verification code received:</p>
                            <div id="codeDisplay" class="text-3xl font-bold text-green-600 mb-4 text-center"></div>
                            <p class="text-xs text-gray-500 mb-4">Auto-populated below ↓</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Enter Code</label>
                            <input type="text" id="testCode" placeholder="123456" maxlength="6"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>

                        <button onclick="testValidateCode()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            ✅ Step 2: Validate Code
                        </button>
                    </div>
                </div>

                <!-- Debug Information -->
                <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <h2 class="font-bold text-red-900 mb-4">🔍 Debug Information</h2>
                    
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="font-bold text-gray-700 mb-2">Email Service Status:</p>
                            <div id="emailStatus" class="p-2 bg-white rounded border border-gray-200">
                                <p>Checking...</p>
                            </div>
                        </div>

                        <div>
                            <p class="font-bold text-gray-700 mb-2">Recent Debug Logs:</p>
                            <div id="debugLogs" class="p-2 bg-white rounded border border-gray-200 font-mono text-xs max-h-48 overflow-y-auto">
                                <p>Loading...</p>
                            </div>
                        </div>

                        <div>
                            <p class="font-bold text-gray-700 mb-2">Environment Check:</p>
                            <div id="envStatus" class="p-2 bg-white rounded border border-gray-200 text-xs">
                                <p>Checking...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div id="messages" class="space-y-2"></div>
            </div>
        </div>
    </div>

    <script>
        // Load session status on page load
        window.addEventListener('load', function() {
            refreshSessionStatus();
            checkEnvironment();
            checkEmailLogs();
            setInterval(refreshSessionStatus, 2000); // Refresh every 2 seconds
        });

        function refreshSessionStatus() {
            fetch('verify-debug.php?format=json')
                .then(r => r.json())
                .then(data => {
                    const html = `
                        <p><span class="text-gray-500">Verification Code:</span> <span class="font-bold">${data.verification_code || '❌ None'}</span></p>
                        <p><span class="text-gray-500">Registered Email:</span> <span class="font-bold">${data.code_email || '❌ None'}</span></p>
                        <p><span class="text-gray-500">Expires In:</span> <span class="font-bold">${data.expires_in || '❌ Expired'}</span></p>
                        <p><span class="text-gray-500">Status:</span> <span class="font-bold">${data.status_message}</span></p>
                    `;
                    document.getElementById('sessionStatus').innerHTML = html;
                })
                .catch(e => {
                    document.getElementById('sessionStatus').innerHTML = '<p class="text-red-600">⚠️ Could not load session (refresh page)</p>';
                });
        }

        function testSendCode() {
            const email = document.getElementById('testEmail').value.trim();

            if (!email) {
                showMessage('⚠️ Please enter an email address', 'warning');
                return;
            }

            showMessage('⏳ Sending verification code...', 'info');

            fetch('auth/verify-email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage('✅ ' + data.message, 'success');
                    
                    if (data.dev_code) {
                        document.getElementById('codeDisplay').textContent = data.dev_code;
                        document.getElementById('testCode').value = data.dev_code;
                        document.getElementById('codeContainer').style.display = 'block';
                    }
                    
                    // Auto-refresh session status
                    setTimeout(refreshSessionStatus, 500);
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
            })
            .catch(e => {
                showMessage('❌ Error: ' + e.message, 'error');
            });
        }

        function testValidateCode() {
            const email = document.getElementById('testEmail').value.trim();
            const code = document.getElementById('testCode').value.trim();

            if (!email || !code) {
                showMessage('⚠️ Please enter both email and code', 'warning');
                return;
            }

            showMessage('⏳ Validating code...', 'info');

            // This simulates what register.php does
            fetch('auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=validate_code&email=${encodeURIComponent(email)}&otp=${encodeURIComponent(code)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage('✅ Code is valid! Verification passed.', 'success');
                } else {
                    showMessage('❌ ' + data.message, 'error');
                }
                
                // Auto-refresh session status
                setTimeout(refreshSessionStatus, 500);
            })
            .catch(e => {
                showMessage('❌ Validation error: ' + e.message, 'error');
            });
        }

        function checkEmailLogs() {
            fetch('verify-debug.php?format=json')
                .then(r => r.json())
                .then(data => {
                    if (data.debug_logs) {
                        document.getElementById('debugLogs').innerHTML = 
                            '<pre class="whitespace-pre-wrap">' + data.debug_logs + '</pre>';
                    }
                })
                .catch(e => {
                    document.getElementById('debugLogs').innerHTML = '<p class="text-red-600">Could not load logs</p>';
                });
        }

        function checkEnvironment() {
            fetch('config/check-config.php?format=json')
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    html += `<p>✅ PHP Version: ${data.php_version}</p>`;
                    html += `<p>${data.env_file ? '✅' : '❌'} .env File: ${data.env_file ? 'Found' : 'Missing'}</p>`;
                    html += `<p>${data.development_mode ? '✅' : '❌'} Development Mode: ${data.development_mode ? 'Enabled' : 'Disabled'}</p>`;
                    html += `<p>${data.phpmailer ? '✅' : '❌'} PHPMailer: ${data.phpmailer ? 'Available' : 'Missing'}</p>`;
                    document.getElementById('envStatus').innerHTML = html;
                })
                .catch(e => {
                    document.getElementById('envStatus').innerHTML = '<p class="text-red-600">Could not check environment</p>';
                });
        }

        function showMessage(msg, type) {
            const div = document.createElement('div');
            div.className = `p-3 rounded mb-2 ${
                type === 'success' ? 'bg-green-100 text-green-800' :
                type === 'error' ? 'bg-red-100 text-red-800' :
                type === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                'bg-blue-100 text-blue-800'
            }`;
            div.textContent = msg;
            document.getElementById('messages').appendChild(div);
            
            setTimeout(() => div.remove(), 5000);
        }
    </script>
</body>
</html>
