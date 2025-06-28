<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "ehrdb";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF8
mysqli_set_charset($conn, "utf8");
?>
