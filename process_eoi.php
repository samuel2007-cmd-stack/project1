<?php
/**
 * Process EOI Form Submission
 * Validates and stores job application data including DOB, Gender, and Zone
 * Includes CSRF protection and comprehensive validation
 */

// Prevent direct access - only allow POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    error_log("Non-POST request to process_eoi.php: " . $_SERVER["REQUEST_METHOD"]);
    header("Location: apply.php");
    exit();
}

require_once 'settings.php';

// ============================================================================
// CSRF VALIDATION
// ============================================================================

// Validate CSRF token first for security
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    die("Invalid security token. Please submit the form again.");
}

// Get database connection
$conn = getDatabaseConnection();

// Check database connection
if (!$conn) {
    error_log("Database connection failed in process_eoi.php");
    die("Database connection failed. Please try again later.");
}

// Initialize errors array
$errors = array();

// ============================================================================
// SANITIZE INPUT DATA
// ============================================================================

// Sanitize and retrieve form data
$job_ref = isset($_POST["ref"]) ? sanitizeInput($_POST["ref"]) : "";
$firstname = isset($_POST["firstname"]) ? sanitizeInput($_POST["firstname"]) : "";
$lastname = isset($_POST["lastname"]) ? sanitizeInput($_POST["lastname"]) : "";
$dob = isset($_POST["dob"]) ? sanitizeInput($_POST["dob"]) : "";
$gender = isset($_POST["gender"]) ? sanitizeInput($_POST["gender"]) : "";
$streetaddress = isset($_POST["streetaddress"]) ? sanitizeInput($_POST["streetaddress"]) : "";
$suburb = isset($_POST["suburb"]) ? sanitizeInput($_POST["suburb"]) : "";
$zone = isset($_POST["zone"]) ? sanitizeInput($_POST["zone"]) : "";
$postcode = isset($_POST["postcode"]) ? sanitizeInput($_POST["postcode"]) : "";
$city = isset($_POST["city"]) ? sanitizeInput($_POST["city"]) : "";
$email = isset($_POST["email"]) ? sanitizeInput($_POST["email"]) : "";
$phone = isset($_POST["phone"]) ? sanitizeInput($_POST["phone"]) : "";
$skill1 = isset($_POST["skill1"]) ? sanitizeInput($_POST["skill1"]) : NULL;
$skill2 = isset($_POST["skill2"]) ? sanitizeInput($_POST["skill2"]) : NULL;
$skill3 = isset($_POST["skill3"]) ? sanitizeInput($_POST["skill3"]) : NULL;
$skill4 = isset($_POST["skill4"]) ? sanitizeInput($_POST["skill4"]) : NULL;
$otherskills = isset($_POST["otherskills"]) ? sanitizeInput($_POST["otherskills"]) : "";

// ============================================================================
// VALIDATION SECTION
// ============================================================================

// Validate Job Reference Number
if (empty($job_ref)) {
    $errors[] = "Job reference number is required.";
} else {
    $valid_refs = array("SWD93", "NAD88", "CSA71", "CEN54");
    if (!in_array($job_ref, $valid_refs)) {
        $errors[] = "Invalid job reference number.";
    }
}

// Validate First Name
if (empty($firstname)) {
    $errors[] = "First name is required.";
} elseif (!validateName($firstname, 20)) {
    $errors[] = "First name must be maximum 20 alphabetic characters.";
}

// Validate Last Name
if (empty($lastname)) {
    $errors[] = "Last name is required.";
} elseif (!validateName($lastname, 20)) {
    $errors[] = "Last name must be maximum 20 alphabetic characters.";
}

// Validate Date of Birth
if (empty($dob)) {
    $errors[] = "Date of birth is required.";
} elseif (!validateDate($dob)) {
    $errors[] = "Invalid date of birth. Use dd/mm/yyyy format.";
} else {
    // Validate age (must be between 15 and 80)
    $age = calculateAge($dob);
    if ($age < 15 || $age > 80) {
        $errors[] = "Applicants must be between 15 and 80 years old.";
    }
}

// Validate Gender
if (empty($gender)) {
    $errors[] = "Gender is required.";
} elseif (!in_array($gender, array("male", "female", "other"))) {
    $errors[] = "Invalid gender selection.";
}

// Validate Street Address
if (empty($streetaddress)) {
    $errors[] = "Street address is required.";
} elseif (strlen($streetaddress) > 40) {
    $errors[] = "Street address must be maximum 40 characters.";
}

// Validate Suburb
if (empty($suburb)) {
    $errors[] = "Suburb/Town is required.";
} elseif (strlen($suburb) > 40) {
    $errors[] = "Suburb/Town must be maximum 40 characters.";
}

// Validate Zone
if (empty($zone)) {
    $errors[] = "Zone is required.";
} elseif (!preg_match('/^\d{2}$/', $zone)) {
    $errors[] = "Zone must be exactly 2 digits.";
}

// Validate Postcode
if (empty($postcode)) {
    $errors[] = "Postcode is required.";
} elseif (!preg_match('/^\d{4}$/', $postcode)) {
    $errors[] = "Postcode must be exactly 4 digits.";
}

// Validate City
if (empty($city)) {
    $errors[] = "City is required.";
}

// Validate Email
if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

// Validate Phone
if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match('/^\d{8}$/', $phone)) {
    $errors[] = "Phone number must be exactly 8 digits.";
}

// Validate Skills (at least one required)
if (empty($skill1) && empty($skill2) && empty($skill3) && empty($skill4)) {
    $errors[] = "Please select at least one technical skill.";
}

// Validate Other Skills (required if any checkbox is selected)
if (!empty($skill1) || !empty($skill2) || !empty($skill3) || !empty($skill4)) {
    if (empty($otherskills)) {
        $errors[] = "Please provide details in 'Other Skills' field when selecting technical skills.";
    }
}

// ============================================================================
// ERROR DISPLAY
// ============================================================================

if (!empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Application validation errors">
        <title>Validation Errors - Control Alt Elite</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>
    <body class="auth-page">
    
    <?php include 'header.inc'; ?>
    
    <div class="auth-hero">
        <div class="auth-hero-content">
            <h1>Validation Errors</h1>
            <p>Please correct the errors below</p>
        </div>
    </div>
    
    <div class="auth-container">
        <div class="auth-box">
            <div class="error-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <p class="error-list-title">Please correct the following errors:</p>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <a href="apply.php" class="auth-btn button-spacing">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Back to Application Form</span>
            </a>
        </div>
    </div>
    
    <?php include 'footer.inc'; ?>
    
    </body>
    </html>
    <?php
    closeDatabaseConnection($conn);
    exit();
}

// ============================================================================
// DATABASE OPERATIONS
// ============================================================================

// Create table if it doesn't exist (with DOB, Gender, and Zone columns)
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'eoi'");
if (!$table_check) {
    error_log("Table check error: " . mysqli_error($conn));
    die("Database error occurred. Please try again later.");
}

if (mysqli_num_rows($table_check) == 0) {
    $create_table_sql = "CREATE TABLE eoi (
        EOInumber INT(11) AUTO_INCREMENT PRIMARY KEY,
        job_reference VARCHAR(10) NOT NULL,
        first_name VARCHAR(20) NOT NULL,
        last_name VARCHAR(20) NOT NULL,
        gender ENUM('male', 'female', 'other') NULL,
        dob VARCHAR(10) NULL,
        street_address VARCHAR(40) NOT NULL,
        suburb_town VARCHAR(40) NOT NULL,
        state VARCHAR(40) NOT NULL,
        zone VARCHAR(3) NULL,
        postcode VARCHAR(4) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(12) NOT NULL,
        skill1 VARCHAR(50) NULL,
        skill2 VARCHAR(50) NULL,
        skill3 VARCHAR(50) NULL,
        skill4 VARCHAR(50) NULL,
        other_skills TEXT NULL,
        status ENUM('New', 'Current', 'Final', 'Accepted') DEFAULT 'New' NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($conn, $create_table_sql)) {
        error_log("Error creating table: " . mysqli_error($conn));
        die("An error occurred while setting up the database. Please contact support.");
    }
}

// Prepare SQL statement with parameterized query to prevent SQL injection
$insert_sql = "INSERT INTO eoi (job_reference, first_name, last_name, gender, dob,
                street_address, suburb_town, state, zone, postcode, email, phone, 
                skill1, skill2, skill3, skill4, other_skills, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')";

$stmt = mysqli_prepare($conn, $insert_sql);

if (!$stmt) {
    error_log("Database prepare error: " . mysqli_error($conn));
    die("An error occurred while processing your application. Please try again later.");
}

// Bind parameters to prevent SQL injection - INCLUDING GENDER, DOB, AND ZONE
mysqli_stmt_bind_param($stmt, "sssssssssssssssss", 
    $job_ref, 
    $firstname, 
    $lastname,
    $gender,        
    $dob,          
    $streetaddress, 
    $suburb, 
    $city,
    $zone,          // ADDED ZONE
    $postcode,
    $email, 
    $phone, 
    $skill1, 
    $skill2, 
    $skill3, 
    $skill4, 
    $otherskills
);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    // Get the auto-generated EOI number
    $eoi_number = mysqli_insert_id($conn);
    
    // ============================================================================
    // SUCCESS PAGE
    // ============================================================================
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Application successfully submitted">
        <title>Application Submitted - Control Alt Elite</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>
    <body class="auth-page">
    
    <?php include 'header.inc'; ?>
    
    <div class="auth-hero">
        <div class="auth-hero-content">
            <h1>Application Submitted!</h1>
            <p>Thank you for your interest</p>
        </div>
    </div>
    
    <div class="auth-container">
        <div class="auth-box">
            <div class="success-message">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <div>
                    <p class="success-title">Application Successfully Submitted!</p>
                    <p class="success-text">Your Expression of Interest has been received and recorded.</p>
                    <div class="eoi-reference-box">
                        <p class="eoi-reference-label">Your Reference Number:</p>
                        <p class="eoi-reference-number">EOI #<?php echo htmlspecialchars($eoi_number); ?></p>
                    </div>
                    <p class="success-text">Please save this reference number for your records.</p>
                    <p class="success-text">We will contact you at <strong><?php echo htmlspecialchars($email); ?></strong></p>
                </div>
            </div>
            
            <a href="index.php" class="auth-btn button-spacing">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>Return to Home</span>
            </a>
        </div>
    </div>
    
    <?php include 'footer.inc'; ?>
    
    </body>
    </html>
    <?php
} else {
    // Log error and show user-friendly message
    error_log("Database execution error: " . mysqli_stmt_error($stmt));
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Control Alt Elite</title>
        <link rel="stylesheet" href="styles/styles.css">
    </head>
    <body class="auth-page">
    
    <?php include 'header.inc'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <div class="error-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    <p>An error occurred while submitting your application. Please try again later.</p>
                </div>
            </div>
            <a href="apply.php" class="auth-btn">Back to Application Form</a>
        </div>
    </div>
    
    <?php include 'footer.inc'; ?>
    
    </body>
    </html>
    <?php
}

// Clean up
mysqli_stmt_close($stmt);
closeDatabaseConnection($conn);
?>