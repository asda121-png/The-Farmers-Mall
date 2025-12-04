<?php
// Simple test - call verify-email.php and show the response
header('Content-Type: text/html; charset=utf-8');

session_start();

// Make a direct call to verify-email.php
$email = 'test@example.com';

// Simulate the POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Set php://input data
$input_data = json_encode(['email' => $email]);

// Create a temp stream
$temp = tmpfile();
fwrite($temp, $input_data);
rewind($temp);

// Now test with actual curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/The-Farmers-Mall/auth/verify-email.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify-Email API Test</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .card { background: #f5f5f5; padding: 15px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        h2 { color: #333; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>🧪 Verify-Email Endpoint Test</h1>
    
    <div class="card">
        <h2>Request</h2>
        <strong>Endpoint:</strong> POST /auth/verify-email.php<br>
        <strong>Email:</strong> <?php echo htmlspecialchars($email); ?><br>
        <strong>HTTP Code:</strong> <?php echo $httpcode; ?>
    </div>

    <div class="card <?php echo ($httpcode === 200) ? 'success' : 'error'; ?>">
        <h2>Response</h2>
        <pre><?php echo htmlspecialchars($response); ?></pre>
    </div>

    <div class="card">
        <h2>Session Data After Call</h2>
        <pre><?php 
        // Make another call and check session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/The-Farmers-Mall/test-session-check.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $session_check = curl_exec($ch);
        curl_close($ch);
        echo htmlspecialchars($session_check);
        ?></pre>
    </div>
</body>
</html>
