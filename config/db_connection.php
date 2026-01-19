<?php
// Database credentials
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'crop_management';

// Create connection (procedural style for consistency)
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

// Check connection
if (!$conn) {
  die("Connection Failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Set timezone
date_default_timezone_set('UTC');

// Define BASE_URL
define('BASE_URL', '/'); // Change to '/your-folder/' if needed
