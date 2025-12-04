<?php
// Direct test of verify-email.php
session_start();

// Test with a specific email
$_REQUEST = [];
$input = json_encode(['email' => 'test@example.com']);

// Temporarily override php://input
stream_wrapper_unregister('php');
stream_wrapper_register('php', 'PHPTestStreamWrapper');

class PHPTestStreamWrapper {
    public static $data = '';
    public $position = 0;
    
    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->position = 0;
        return true;
    }
    
    public function stream_read($count) {
        $ret = substr(PHPTestStreamWrapper::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    
    public function stream_eof() {
        return $this->position >= strlen(PHPTestStreamWrapper::$data);
    }
}

PHPTestStreamWrapper::$data = $input;

// Include the verify-email.php
ob_start();
include 'auth/verify-email.php';
$response = ob_get_clean();

echo '<pre>';
echo 'Raw Response:' . "\n";
echo htmlspecialchars($response) . "\n\n";

$data = json_decode($response, true);
echo 'Parsed Response:' . "\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

echo 'Session Data:' . "\n";
echo json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n\n";

echo '.development file exists: ' . (file_exists('.development') ? 'YES' : 'NO') . "\n";
echo '.development file path: ' . realpath('.development') . "\n";

?></pre>
