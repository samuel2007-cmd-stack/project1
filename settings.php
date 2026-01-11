<?php
/**
 * Database Configuration and Utility Functions
 * Control Alt Elite - Job Application System
 * 
 * This file contains:
 * - Database connection settings
 * - Connection management functions
 * - Input sanitization functions
 * - Validation functions for form data
 * - CSRF protection functions
 */

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

$host = "localhost";          // MySQL server hostname
$user = "root";               // Database username
$pwd = "";                    // Database password
$sql_db = "ctrlaltelite";     // Database name

// Set timezone for Qatar
date_default_timezone_set('Asia/Qatar');

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ============================================================================
// DATABASE CONNECTION FUNCTIONS
// ============================================================================

/**
 * Establishes connection to MySQL database
 * Creates database if it doesn't exist
 * Sets character encoding to UTF-8
 * 
 * @return mysqli|false Database connection object or false on failure
 */
function getDatabaseConnection() {
    global $host, $user, $pwd, $sql_db;
    
    // Suppress connection errors with @ to handle them gracefully
    $conn = @mysqli_connect($host, $user, $pwd);
    
    if (!$conn) {
        return false;
    }
    
    // Check if database exists, create if not
    if (!@mysqli_select_db($conn, $sql_db)) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$sql_db` 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (mysqli_query($conn, $sql)) {
            mysqli_select_db($conn, $sql_db);
        } else {
            return false;
        }
    }
    
    // Set character set to UTF-8 for proper encoding
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

/**
 * Safely closes database connection
 * 
 * @param mysqli $conn Database connection to close
 * @return void
 */
function closeDatabaseConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

// ============================================================================
// INPUT SANITIZATION & SECURITY
// ============================================================================

/**
 * Sanitizes user input to prevent XSS attacks
 * Removes whitespace, backslashes, and converts special characters
 * 
 * @param string $data User input to sanitize
 * @return string Sanitized data safe for output
 */
function sanitizeInput($data) {
    $data = trim($data);                                    // Remove leading/trailing whitespace
    $data = stripslashes($data);                           // Remove backslashes
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Convert special chars to HTML entities
    return $data;
}

/**
 * Generate CSRF token and store in session
 * Prevents Cross-Site Request Forgery attacks
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from form submission
 * Uses timing-safe comparison to prevent timing attacks
 * 
 * @param string $token Token to validate
 * @return bool True if token is valid
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validates email address format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validates phone number format
 * Accepts 8-12 digits (spaces are removed before validation)
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid phone format
 */
function validatePhone($phone) {
    $phone = str_replace(' ', '', $phone);  // Remove spaces
    return preg_match('/^\d{8,12}$/', $phone);
}

/**
 * Validates date in dd/mm/yyyy format
 * Checks format and ensures date is valid (e.g., not 31/02/2024)
 * 
 * @param string $date Date string to validate
 * @return bool True if valid date
 */
function validateDate($date) {
    // Check format matches dd/mm/yyyy
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
        return false;
    }
    
    $day = (int)$matches[1];
    $month = (int)$matches[2];
    $year = (int)$matches[3];
    
    // Use PHP's checkdate to validate the actual date
    return checkdate($month, $day, $year);
}

/**
 * Calculates age from date of birth
 * Date format: dd/mm/yyyy
 * 
 * @param string $dob Date of birth in dd/mm/yyyy format
 * @return int Age in years
 */
function calculateAge($dob) {
    $dob_parts = explode('/', $dob);
    if (count($dob_parts) != 3) {
        return 0;
    }
    
    // Convert to YYYY-MM-DD format for DateTime
    $birth_date = new DateTime($dob_parts[2] . '-' . $dob_parts[1] . '-' . $dob_parts[0]);
    $today = new DateTime();
    
    // Calculate difference and return years
    return $today->diff($birth_date)->y;
}

/**
 * Validates postcode format
 * Must be exactly 4 digits (Qatar format)
 * 
 * @param string $postcode Postcode to validate
 * @return bool True if valid postcode format
 */
function validatePostcode($postcode) {
    return preg_match('/^\d{4}$/', $postcode);
}

/**
 * Validates name format
 * Only letters, spaces, and hyphens allowed
 * 
 * @param string $name Name to validate
 * @param int $maxLength Maximum allowed length (default 20)
 * @return bool True if valid name format
 */
function validateName($name, $maxLength = 20) {
    if (strlen($name) > $maxLength) {
        return false;
    }
    // Allow letters, spaces, and hyphens only
    return preg_match('/^[a-zA-Z\s\-]+$/', $name);
}

/**
 * Validates address format
 * Allows letters, numbers, spaces, and common punctuation
 * 
 * @param string $address Address to validate
 * @param int $maxLength Maximum allowed length (default 40)
 * @return bool True if valid address format
 */
function validateAddress($address, $maxLength = 40) {
    if (strlen($address) > $maxLength) {
        return false;
    }
    // Allow alphanumeric, spaces, dots, commas, hyphens, and slashes
    return preg_match('/^[a-zA-Z0-9\s\.\,\-\/]+$/', $address);
}

/**
 * List of valid cities in Qatar
 */
$valid_cities = ['Doha', 'Al Wakra', 'Al Khor', 'Dukhan', 'Al Shamal', 'Mesaieed', 'Ras Laffan'];

/**
 * Validates city/state selection
 * Checks against predefined list of valid Qatar cities
 * 
 * @param string $city City name to validate
 * @return bool True if city is in valid list
 */
function validateCity($city) {
    global $valid_cities;
    return in_array($city, $valid_cities);
}

// ============================================================================
// INITIALIZE DATABASE CONNECTION
// ============================================================================

// Establish database connection for use throughout the application
$conn = getDatabaseConnection();

// Check if connection was successful
if (!$conn) {
    die("Connection failed: Unable to connect to database");
}
?>