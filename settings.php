<?php

$host = "localhost";
$user = "root";
$pwd = "";
$sql_db = "ctrlaltelite";

date_default_timezone_set('Asia/Qatar');

ini_set('display_errors', 1);
error_reporting(E_ALL);

function getDatabaseConnection() {
    global $host, $user, $pwd, $sql_db;
    
    $conn = @mysqli_connect($host, $user, $pwd);
    
    if (!$conn) {
        return false;
    }
    
    if (!@mysqli_select_db($conn, $sql_db)) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$sql_db` 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (mysqli_query($conn, $sql)) {
            mysqli_select_db($conn, $sql_db);
        } else {
            return false;
        }
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

function closeDatabaseConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{8}$/', $phone);
}

function validateDate($date) {
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return false;
    }
    
    $day = (int)$matches[1];
    $month = (int)$matches[2];
    $year = (int)$matches[3];
    
    return checkdate($month, $day, $year);
}

function validatePostcode($postcode) {
    return preg_match('/^[0-9]{2}$/', $postcode);
}

function validateName($name, $maxLength = 20) {
    return preg_match('/^[a-zA-Z\s]{1,' . $maxLength . '}$/', $name);
}

function validateAddress($address, $maxLength = 40) {
    return strlen($address) > 0 && strlen($address) <= $maxLength;
}

$valid_cities = [
    'Doha',
    'Al Wakra',
    'Al Khor',
    'Dukhan',
    'Al Shamal',
    'Mesaieed',
    'Ras Laffan'
];

function validateCity($city) {
    global $valid_cities;
    return in_array($city, $valid_cities);
}

$conn = getDatabaseConnection();

if (!$conn) {
    die("Connection failed: Unable to connect to database");
}
?>