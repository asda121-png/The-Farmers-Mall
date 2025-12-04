<?php
// FILE: check-config.php - Check system configuration

$format = $_GET['format'] ?? 'text';

$config = [
    'php_version' => phpversion(),
    'env_file' => file_exists(__DIR__ . '/.env'),
    'development_mode' => file_exists(__DIR__ . '/../.development'),
    'phpmailer' => file_exists(__DIR__ . '/../PHPMailer-master/src/PHPMailer.php'),
    'supabase_api' => file_exists(__DIR__ . '/supabase-api.php'),
    'mailer' => file_exists(__DIR__ . '/../includes/mailer.php'),
];

if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode($config);
} else {
    echo "PHP Version: " . $config['php_version'] . "\n";
    echo "ENV File: " . ($config['env_file'] ? 'YES' : 'NO') . "\n";
    echo "Dev Mode: " . ($config['development_mode'] ? 'YES' : 'NO') . "\n";
    echo "PHPMailer: " . ($config['phpmailer'] ? 'YES' : 'NO') . "\n";
    echo "Supabase API: " . ($config['supabase_api'] ? 'YES' : 'NO') . "\n";
    echo "Mailer: " . ($config['mailer'] ? 'YES' : 'NO') . "\n";
}
?>
