<?php
/**
 * Database Configuration File
 * Contains all database connection settings
 */

// Database connection parameters
$host = "localhost";           // Database host (usually localhost for XAMPP)
$database = "ctrlaltelite";  // Database name
$user = "root";                // Database username (default is 'root' for XAMPP)
$password = "";                // Database password (default is empty for XAMPP)

// Create database connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Set character encoding to UTF-8
mysqli_set_charset($conn, "utf8");

// Success message (comment out in production)
// echo "Connected successfully to database!";
?>