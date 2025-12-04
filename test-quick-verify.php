<!DOCTYPE html>
<html>
<head>
    <title>🚀 Quick Email Verification Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .blink { animation: blink 1s infinite; }
        @keyframes blink { 0%, 50%, 100% { opacity: 1; } 25%, 75% { opacity: 0.5; } }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen p-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-t-lg shadow-lg p-8 border-b-4 border-green-600">
            <h1 class="text-4xl font-bold text-green-600 mb-2">🚀 Email Verification Live Test</h1>
            <p class="text-gray-600">Send a test code and verify it works end-to-end</p>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-b-lg shadow-lg p-8">
            <!-- Step 1: Send Code -->
            <div class="mb-8 p-6 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                <h2 class="text-xl font-bold text-blue-900 mb-4">📧 Step 1: Send Verification Code</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Email</label>
                        <input type="email" id="email" placeholder="test@example.com" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none">
                    </div>

                    <button onclick="sendCode()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                        <span id="sendBtn">📨 Send Code</span>
                        <span id="spinner" style="display: none;" class="blink">⏳</span>
                    </button>

                    <div id="sendResult" style="display: none;" class="p-4 rounded-lg">
                        <p id="sendMessage"></p>
                        <div id="codeBox" style="display: none;" class="mt-4 p-4 bg-green-100 rounded-lg border-2 border-green-500">
                            <p class="text-sm text-gray-700 mb-2">✅ Your Verification Code:</p>
                            <p id="codeDisplay" class="text-4xl font-bold text-green-600 text-center"></p>
                            <button onclick="copyCode()" class="w-full mt-3 bg-green-600 text-white py-2 rounded hover:bg-green-700">
                                📋 Copy Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Enter Code -->
            <div class="mb-8 p-6 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                <h2 class="text-xl font-bold text-purple-900 mb-4">✅ Step 2: Verify Code</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Enter Code (4-6 digits)</label>
                        <input type="text" id="code" placeholder="123456" maxlength="6"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:outline-none text-center text-3xl tracking-widest">
                    </div>

                    <button onclick="verifyCode()" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition">
                        🔍 Verify Code
                    </button>

                    <div id="verifyResult" style="display: none;" class="p-4 rounded-lg">
                        <p id="verifyMessage"></p>
                    </div>
                </div>
            </div>

            <!-- Debug Info -->
            <div class="p-6 bg-gray-50 rounded-lg border-l-4 border-gray-500">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🔍 Debug Information</h2>
                
                <div class="space-y-3 text-sm">
                    <div class="p-3 bg-white rounded border border-gray-200">
                        <span class="font-bold">Session Status:</span>
                        <span id="sessionStatus" class="ml-2">⏳ Loading...</span>
                    </div>

                    <div class="p-3 bg-white rounded border border-gray-200">
                        <span class="font-bold">Development Mode:</span>
                        <span id="devMode" class="ml-2">⏳ Checking...</span>
                    </div>

                    <div class="p-3 bg-white rounded border border-gray-200">
                        <span class="font-bold">PHP Version:</span>
                        <span id="phpVersion" class="ml-2">⏳ Checking...</span>
                    </div>

                    <div>
                        <span class="font-bold block mb-2">Recent Logs:</span>
                        <div id="logs" class="bg-white p-3 rounded border border-gray-200 text-xs font-mono max-h-48 overflow-y-auto">
                            <p>Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="mt-8 flex flex-wrap gap-2">
                <a href="auth/register.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium">
                    ← Back to Registration
                </a>
                <a href="auth/verify-debug.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                    🔍 Debug Dashboard
                </a>
                <button onclick="refreshAll()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 font-medium">
                    🔄 Refresh All
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 space-y-2 max-w-sm z-50"></div>

    <script>
        let currentCode = null;

        function showToast(msg, type = 'info') {
            const toast = document.createElement('div');
            const colors = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500'
            };
            toast.className = `${colors[type]} text-white p-4 rounded-lg shadow-lg`;
            toast.textContent = msg;
            document.getElementById('toastContainer').appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function sendCode() {
            const email = document.getElementById('email').value.trim();
            if (!email) {
                showToast('Please enter an email address', 'error');
                return;
            }

            document.getElementById('spinner').style.display = 'inline';
            document.getElementById('sendBtn').style.display = 'none';

            fetch('auth/verify-email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('sendBtn').style.display = 'inline';
                document.getElementById('sendResult').style.display = 'block';

                if (data.success) {
                    currentCode = data.dev_code;
                    document.getElementById('sendMessage').innerHTML = `<span class="text-green-600 font-bold">✅ ${data.message}</span>`;
                    
                    if (data.dev_code) {
                        document.getElementById('codeDisplay').textContent = data.dev_code;
                        document.getElementById('codeBox').style.display = 'block';
                        document.getElementById('code').value = data.dev_code;
                        showToast('Code generated! Auto-filled below.', 'success');
                    }
                } else {
                    document.getElementById('sendMessage').innerHTML = `<span class="text-red-600 font-bold">❌ ${data.message}</span>`;
                    showToast(data.message, 'error');
                }
                
                refreshDebug();
            })
            .catch(e => {
                document.getElementById('sendMessage').innerHTML = `<span class="text-red-600 font-bold">❌ Error: ${e.message}</span>`;
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('sendBtn').style.display = 'inline';
                showToast('Network error', 'error');
            });
        }

        function verifyCode() {
            const code = document.getElementById('code').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!code || !email) {
                showToast('Please enter both email and code', 'error');
                return;
            }

            fetch('auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=validate_otp&email=${encodeURIComponent(email)}&otp=${encodeURIComponent(code)}`
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('verifyResult').style.display = 'block';
                
                if (data.success || data.code_valid || data.message.includes('matches')) {
                    document.getElementById('verifyMessage').innerHTML = `<span class="text-green-600 font-bold">✅ ${data.message || 'Code is valid!'}</span>`;
                    showToast('Code verified successfully!', 'success');
                } else {
                    document.getElementById('verifyMessage').innerHTML = `<span class="text-red-600 font-bold">❌ ${data.message || 'Code verification failed'}</span>`;
                    showToast(data.message || 'Code verification failed', 'error');
                }
                
                refreshDebug();
            })
            .catch(e => {
                document.getElementById('verifyMessage').innerHTML = `<span class="text-red-600 font-bold">❌ Error: ${e.message}</span>`;
                showToast('Network error', 'error');
            });
        }

        function copyCode() {
            if (currentCode) {
                navigator.clipboard.writeText(currentCode);
                showToast('Code copied to clipboard!', 'success');
            }
        }

        function refreshDebug() {
            fetch('auth/verify-debug.php?format=json')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('sessionStatus').innerHTML = 
                        (data.verification_code ? '✅ Code in session' : '❌ No code in session') + 
                        (data.verification_code ? ` (${data.expires_in}s left)` : '');
                })
                .catch(e => console.log('Debug fetch error:', e));
        }

        function refreshAll() {
            refreshDebug();
            showToast('Debug info refreshed', 'info');
        }

        // Load initial debug info
        window.addEventListener('load', function() {
            fetch('config/check-config.php?format=json')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('devMode').textContent = 
                        data.development_mode ? '✅ Enabled' : '❌ Disabled';
                    document.getElementById('phpVersion').textContent = data.php_version;
                })
                .catch(e => {
                    document.getElementById('devMode').textContent = '⚠️ Could not check';
                });

            refreshDebug();

            // Refresh logs every 2 seconds
            setInterval(() => {
                fetch('auth/verify-debug.php?format=json')
                    .then(r => r.json())
                    .then(data => {
                        if (data.verification_code) {
                            document.getElementById('sessionStatus').innerHTML = 
                                `✅ Code "${data.verification_code}" (${data.expires_in}s left)`;
                        }
                    });
            }, 2000);
        });
    </script>
</body>
</html>
