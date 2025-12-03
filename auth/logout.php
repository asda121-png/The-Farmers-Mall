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
        // Redirect to login page
        window.location.href = 'login.php?logout=success';
    </script>
</body>
</html>
