<?php
/**
 * Database Configuration File
 * Contains database connection settings
 */

// Database configuration
$host = "localhost";
$user = "root";
$password = "";  // Empty password for XAMPP default
$database = "ctrlaltelite";

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8 for proper encoding
mysqli_set_charset($conn, "utf8");
?>