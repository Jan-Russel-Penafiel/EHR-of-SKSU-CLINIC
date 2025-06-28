<?php
// Database configuration
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_name = getenv('DB_DATABASE') ?: 'ehrdb';
$db_user = getenv('DB_USERNAME') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_port = getenv('DB_PORT') ?: '3306';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default socket timeout
ini_set('default_socket_timeout', 60);

// Create connection with explicit port
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);
$success = mysqli_real_connect($conn, $db_host, $db_user, $db_pass, $db_name, $db_port);

// Check connection
if (!$success) {
    die("Connection failed: " . mysqli_connect_error() . " (Error code: " . mysqli_connect_errno() . ")");
}

// Set character set
mysqli_set_charset($conn, "utf8mb4");

// Return connection object
return $conn;
?> 