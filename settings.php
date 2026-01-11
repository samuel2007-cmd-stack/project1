<?php
/**
 * Database Configuration and Utility Functions
 * Control Alt Elite - Job Application System
 * 
 * This file contains:
 * - Database connection settings
 * - Connection management functions
 * - Auto-initialization of database and tables
 * - Input sanitization functions
 * - Validation functions for form data
 * - CSRF protection functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
// AUTO-INITIALIZATION FUNCTION
// ============================================================================

/**
 * Initialize database and tables automatically if they don't exist
 * This runs once per session to avoid repeated checks
 */
function initializeDatabase() {
    global $host, $user, $pwd, $sql_db;
    
    // Only check once per session
    if (isset($_SESSION['db_initialized'])) {
        return true;
    }
    
    $conn = @mysqli_connect($host, $user, $pwd);
    
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        return false;
    }
    
    // Create database if it doesn't exist
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS `$sql_db` 
                      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    if (!mysqli_query($conn, $create_db_sql)) {
        error_log("Database creation failed: " . mysqli_error($conn));
        mysqli_close($conn);
        return false;
    }
    
    // Select database
    if (!mysqli_select_db($conn, $sql_db)) {
        error_log("Database selection failed: " . mysqli_error($conn));
        mysqli_close($conn);
        return false;
    }
    
    // Create EOI table
    $eoi_table_sql = "CREATE TABLE IF NOT EXISTS `eoi` (
        `EOInumber` INT(11) AUTO_INCREMENT PRIMARY KEY,
        `job_reference` VARCHAR(10) NOT NULL,
        `first_name` VARCHAR(20) NOT NULL,
        `last_name` VARCHAR(20) NOT NULL,
        `gender` ENUM('male', 'female', 'other') NULL,
        `dob` VARCHAR(10) NULL,
        `street_address` VARCHAR(40) NOT NULL,
        `suburb_town` VARCHAR(40) NOT NULL,
        `state` VARCHAR(40) NOT NULL,
        `zone` VARCHAR(3) NULL,
        `postcode` VARCHAR(4) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(12) NOT NULL,
        `skill1` VARCHAR(50) NULL,
        `skill2` VARCHAR(50) NULL,
        `skill3` VARCHAR(50) NULL,
        `skill4` VARCHAR(50) NULL,
        `other_skills` TEXT NULL,
        `status` ENUM('New', 'Current', 'Final', 'Accepted') DEFAULT 'New' NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_job_reference` (`job_reference`),
        INDEX `idx_status` (`status`),
        INDEX `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($conn, $eoi_table_sql);
    
    // Create managers table
    $managers_table_sql = "CREATE TABLE IF NOT EXISTS `managers` (
        `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `failed_attempts` INT(11) DEFAULT 0,
        `locked_until` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_login` DATETIME NULL,
        INDEX `idx_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($conn, $managers_table_sql);
    
    // Create jobs table
    $jobs_table_sql = "CREATE TABLE IF NOT EXISTS `jobs` (
        `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
        `job_reference` VARCHAR(10) NOT NULL UNIQUE,
        `title` VARCHAR(100) NOT NULL,
        `summary` TEXT NOT NULL,
        `salary` VARCHAR(50) NOT NULL,
        `job_type` VARCHAR(50) NOT NULL,
        `location` VARCHAR(100) NOT NULL,
        `reports_to` VARCHAR(100) NOT NULL,
        `responsibilities` TEXT NOT NULL,
        `required_skills` TEXT NOT NULL,
        `preferred_skills` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_job_reference` (`job_reference`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($conn, $jobs_table_sql);
    
    // Insert default jobs if table is empty
    $check_jobs = mysqli_query($conn, "SELECT COUNT(*) as count FROM jobs");
    if ($check_jobs) {
        $row = mysqli_fetch_assoc($check_jobs);
        if ($row['count'] == 0) {
            $default_jobs = array(
                array('SWD93', 'Software Developer', 'Join our dynamic team as a Software Developer to design, develop, and maintain cutting-edge software solutions.', '$60,000 - $90,000', 'Full-time', 'Doha, Qatar', 'IT Manager', 'Write clean, scalable code|Collaborate with cross-functional teams|Debug and troubleshoot software issues|Participate in code reviews|Develop and maintain technical documentation', 'Proficiency in at least one programming language (Java, Python, C++, etc.)|Understanding of software development lifecycle|Problem-solving and analytical skills|Bachelor\'s degree in Computer Science or related field', 'Experience with Agile/Scrum methodologies|Knowledge of cloud platforms (AWS, Azure, GCP)|Familiarity with version control systems (Git)|Experience with CI/CD pipelines'),
                array('NAD88', 'Network Administrator', 'Manage and maintain our organization\'s network infrastructure to ensure optimal performance and security.', '$55,000 - $80,000', 'Full-time', 'Doha, Qatar', 'IT Director', 'Monitor network performance and troubleshoot issues|Configure and maintain network hardware and software|Implement security measures and protocols|Manage user accounts and permissions|Document network configurations and changes', 'Knowledge of networking protocols (TCP/IP, DNS, DHCP)|Experience with routers, switches, and firewalls|Understanding of network security principles|Strong troubleshooting skills', 'Cisco CCNA or equivalent certification|Experience with network monitoring tools|Knowledge of virtualization technologies|Scripting skills (Python, PowerShell)'),
                array('CSA71', 'Cybersecurity Analyst', 'Protect our organization\'s digital assets by identifying vulnerabilities and implementing security measures.', '$70,000 - $100,000', 'Full-time', 'Doha, Qatar', 'Security Manager', 'Monitor security systems for threats and anomalies|Conduct vulnerability assessments and penetration testing|Respond to security incidents|Implement security policies and procedures|Stay updated on latest security threats and trends', 'Knowledge of security frameworks (NIST, ISO 27001)|Experience with security tools (SIEM, IDS/IPS)|Understanding of threat intelligence|Incident response experience', 'Security certifications (CISSP, CEH, CompTIA Security+)|Experience with cloud security|Knowledge of compliance requirements|Forensics experience'),
                array('CEN54', 'Cloud Engineer', 'Design and implement cloud infrastructure solutions to support our organization\'s digital transformation.', '$75,000 - $110,000', 'Full-time', 'Doha, Qatar', 'Cloud Architecture Lead', 'Design and deploy cloud infrastructure|Automate cloud operations and deployments|Monitor cloud resources and optimize costs|Implement cloud security best practices|Collaborate with development teams', 'Experience with cloud platforms (AWS, Azure, or GCP)|Knowledge of infrastructure as code (Terraform, CloudFormation)|Understanding of containerization (Docker, Kubernetes)|Strong scripting skills', 'Cloud certifications (AWS Certified, Azure Administrator)|DevOps experience|Knowledge of microservices architecture|Experience with CI/CD pipelines')
            );
            
            foreach ($default_jobs as $job) {
                $stmt = mysqli_prepare($conn, "INSERT INTO jobs (job_reference, title, summary, salary, job_type, location, reports_to, responsibilities, required_skills, preferred_skills) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssssssss", $job[0], $job[1], $job[2], $job[3], $job[4], $job[5], $job[6], $job[7], $job[8], $job[9]);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    
    // Create default admin if no managers exist
    $check_managers = mysqli_query($conn, "SELECT COUNT(*) as count FROM managers");
    if ($check_managers) {
        $row = mysqli_fetch_assoc($check_managers);
        if ($row['count'] == 0) {
            $default_username = "admin";
            $default_password = "Admin123";
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
            
            $stmt = mysqli_prepare($conn, "INSERT INTO managers (username, password, failed_attempts) VALUES (?, ?, 0)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $default_username, $hashed_password);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    mysqli_close($conn);
    
    // Mark as initialized for this session
    $_SESSION['db_initialized'] = true;
    
    return true;
}

// Auto-initialize database
initializeDatabase();

// ============================================================================
// DATABASE CONNECTION FUNCTIONS
// ============================================================================

/**
 * Establishes connection to MySQL database
 * Creates database if it doesn't exist
 * Sets character encoding to UTF-8
 * 
 * @return mysqli|null Database connection object or null on failure
 */
function getDatabaseConnection() {
    global $host, $user, $pwd, $sql_db;
    
    // Suppress connection errors with @ to handle them gracefully
    $conn = @mysqli_connect($host, $user, $pwd);
    
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        return null;
    }
    
    // Check if database exists, create if not
    if (!@mysqli_select_db($conn, $sql_db)) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$sql_db` 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (mysqli_query($conn, $sql)) {
            mysqli_select_db($conn, $sql_db);
        } else {
            error_log("Database creation failed: " . mysqli_error($conn));
            mysqli_close($conn);
            return null;
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
    if ($conn && $conn instanceof mysqli) {
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
// DO NOT INITIALIZE GLOBAL CONNECTION
// Each page should call getDatabaseConnection() when needed
// ============================================================================
?>