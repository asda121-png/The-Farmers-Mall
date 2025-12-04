<?php
/**
 * Simple alternative: Copy existing product images or use CSS placeholders
 * This doesn't require GD library
 */

$productsDir = __DIR__ . '/../images/products';
if (!is_dir($productsDir)) {
    mkdir($productsDir, 0777, true);
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Product Images</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2E7D32; }
        .success { color: #2E7D32; background: #E8F5E9; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #1565C0; background: #E3F2FD; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; background: #2E7D32; color: white; padding: 12px 24px; border-radius: 5px; text-decoration: none; margin: 10px 5px; }
        .btn:hover { background: #1B5E20; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📦 Product Images Setup</h1>
        
        <div class='info'>
            <strong>Note:</strong> The GD library is not enabled on your PHP installation.
            Don't worry! We have two options:
        </div>
        
        <h2>Option 1: Use Placeholder Service (Recommended)</h2>
        <div class='success'>
            ✅ <strong>This is already configured!</strong><br>
            The populate-shops.php script has been updated to use a placeholder service that works without local images.
            Just click the button below to continue.
        </div>
        
        <h2>Option 2: Enable GD Library</h2>
        <p>If you want to use local images in the future:</p>
        <ol>
            <li>Open your php.ini file</li>
            <li>Find the line: <code>;extension=gd</code></li>
            <li>Remove the semicolon: <code>extension=gd</code></li>
            <li>Restart your web server (Apache/XAMPP)</li>
        </ol>
        
        <hr>
        <p><strong>Ready to proceed?</strong></p>
        <a href='populate-shops.php' class='btn'>→ Continue to Populate Shops</a>
        <a href='../user/user-homepage.php' class='btn' style='background: #666;'>← Back to Homepage</a>
    </div>
</body>
</html>";
?>
