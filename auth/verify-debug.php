<?php
// FILE: verify-debug.php - Session verification debugger
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$verification_code = $_SESSION['verification_code'] ?? null;
$code_email = $_SESSION['code_email'] ?? null;
$code_expires = $_SESSION['code_expires'] ?? null;

$current_time = time();
$expires_in = $code_expires ? max(0, $code_expires - $current_time) : 0;
$is_expired = $code_expires ? ($code_expires < $current_time) : false;

// JSON response if requested
if ($_GET['format'] === 'json' || $_SERVER['HTTP_ACCEPT'] === 'application/json') {
    header('Content-Type: application/json');
    echo json_encode([
        'verification_code' => $verification_code,
        'code_email' => $code_email,
        'expires_in' => $expires_in,
        'is_expired' => $is_expired,
        'status_message' => !$verification_code ? '❌ No code' : ($is_expired ? "⏰ Expired" : "✅ Valid"),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verification Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto p-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-green-600 mb-4">🔍 Verification Debug</h1>
            
            <!-- Cards -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="p-4 rounded-lg border-l-4 <?php echo $verification_code ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'; ?>">
                    <p class="text-xs text-gray-600">Code</p>
                    <p class="text-2xl font-bold <?php echo $verification_code ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $verification_code ?: '❌'; ?>
                    </p>
                </div>

                <div class="p-4 rounded-lg border-l-4 <?php echo $code_email ? 'bg-blue-50 border-blue-500' : 'bg-red-50 border-red-500'; ?>">
                    <p class="text-xs text-gray-600">Email</p>
                    <p class="text-sm font-bold <?php echo $code_email ? 'text-blue-600 break-all' : 'text-red-600'; ?>">
                        <?php echo $code_email ?: '❌'; ?>
                    </p>
                </div>

                <div class="p-4 rounded-lg border-l-4 <?php echo !$is_expired && $verification_code ? 'bg-green-50 border-green-500' : 'bg-yellow-50 border-yellow-500'; ?>">
                    <p class="text-xs text-gray-600">Status</p>
                    <p class="text-lg font-bold <?php echo !$is_expired && $verification_code ? 'text-green-600' : 'text-yellow-600'; ?>">
                        <?php echo !$verification_code ? '❌ None' : ($is_expired ? '⏰ Expired' : '✅ Valid'); ?>
                    </p>
                </div>

                <div class="p-4 rounded-lg border-l-4 border-purple-500 bg-purple-50">
                    <p class="text-xs text-gray-600">Expires In</p>
                    <p class="text-lg font-bold text-purple-600">
                        <?php echo !$code_expires ? '❌' : ($is_expired ? '⏰' : $expires_in . 's'); ?>
                    </p>
                </div>
            </div>

            <!-- Raw Data -->
            <div class="mb-8 p-4 bg-gray-100 rounded-lg">
                <h2 class="font-bold mb-2">Session Data</h2>
                <pre class="bg-white p-3 rounded text-xs overflow-x-auto"><code><?php
                    echo "verification_code: " . json_encode($verification_code) . "\n";
                    echo "code_email: " . json_encode($code_email) . "\n";
                    echo "code_expires: " . json_encode($code_expires) . "\n";
                    echo "expires_in: " . json_encode($expires_in) . " seconds\n";
                    echo "is_expired: " . json_encode($is_expired);
                ?></code></pre>
            </div>

            <!-- Logs -->
            <div class="p-4 bg-gray-100 rounded-lg">
                <h2 class="font-bold mb-2">Recent Logs</h2>
                <pre class="bg-white p-3 rounded text-xs overflow-x-auto max-h-64 overflow-y-auto"><code><?php
                    $log_file = __DIR__ . '/../debug_email.log';
                    if (file_exists($log_file)) {
                        $lines = file($log_file);
                        echo implode('', array_slice($lines, -10));
                    } else {
                        echo 'No logs yet';
                    }
                ?></code></pre>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex gap-2">
                <button onclick="location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">🔄 Refresh</button>
                <a href="register.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 inline-block">← Back to Registration</a>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh every 2 seconds
        setTimeout(() => location.reload(), 2000);
    </script>
</body>
</html>
