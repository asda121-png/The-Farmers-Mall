<?php
// Start session
session_start();

// Destroy all session data
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear localStorage cart data on logout
        localStorage.removeItem('cart');
        // Redirect to public index page
        window.location.href = '../public/index.php';
    </script>
</body>
</html>
