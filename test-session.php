<?php
session_start();

echo "Session Data:\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "\n";
echo "Email: " . ($_SESSION['email'] ?? 'Not set') . "\n";
echo "Full Name: " . ($_SESSION['full_name'] ?? 'Not set') . "\n";
echo "Logged in: " . ($_SESSION['loggedin'] ?? 'Not set') . "\n";
