<?php
/**
 * Process EOI Form Submission
 * Validates data and inserts into database
 */

// Start session for error handling
session_start();

// Check if form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: apply.php");
    exit();
}

// Include database connection
require_once 'settings.php';

// Check if connection exists
if (!isset($conn) || !$conn) {
    die("Database connection failed. Please check your settings.php file.");
}

// Initialize error array
$errors = array();

// Sanitization function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate and sanitize Job Reference
if (empty($_POST["ref"])) {
    $errors[] = "Job reference number is required.";
} else {
    $job_ref = sanitize_input($_POST["ref"]);
    $valid_refs = array("Software Developer", "Network Administrator", "Cybersecurity", "Cloud Engineer");
    if (!in_array($job_ref, $valid_refs)) {
        $errors[] = "Invalid job reference number.";
    }
}

// Validate and sanitize First Name
if (empty($_POST["firstname"])) {
    $errors[] = "First name is required.";
} else {
    $firstname = sanitize_input($_POST["firstname"]);
    if (!preg_match("/^[a-zA-Z\s\-']{1,20}$/", $firstname)) {
        $errors[] = "First name must be maximum 20 alphabetic characters.";
    }
}

// Validate and sanitize Last Name
if (empty($_POST["lastname"])) {
    $errors[] = "Last name is required.";
} else {
    $lastname = sanitize_input($_POST["lastname"]);
    if (!preg_match("/^[a-zA-Z\s\-']{1,20}$/", $lastname)) {
        $errors[] = "Last name must be maximum 20 alphabetic characters.";
    }
}

// Validate Date of Birth
if (empty($_POST["dob"])) {
    $errors[] = "Date of birth is required.";
} else {
    $dob = sanitize_input($_POST["dob"]);
    // Validate date format and check if it's a valid date
    $date_parts = explode("-", $dob);
    if (count($date_parts) != 3 || !checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
        $errors[] = "Invalid date of birth format.";
    }
}

// Validate Gender
if (empty($_POST["gender"])) {
    $errors[] = "Gender is required.";
} else {
    $gender = sanitize_input($_POST["gender"]);
    if (!in_array($gender, array("male", "female"))) {
        $errors[] = "Invalid gender selection.";
    }
}

// Validate Unit Number
if (empty($_POST["unitnumber"])) {
    $errors[] = "Unit number is required.";
} else {
    $unit_number = sanitize_input($_POST["unitnumber"]);
    if (!preg_match("/^[0-9]{1,5}$/", $unit_number)) {
        $errors[] = "Unit number must be 1-5 digits.";
    }
}

// Validate Building Number
if (empty($_POST["buildingnumber"])) {
    $errors[] = "Building number is required.";
} else {
    $building_number = sanitize_input($_POST["buildingnumber"]);
    if (!preg_match("/^[0-9]{1,5}$/", $building_number)) {
        $errors[] = "Building number must be 1-5 digits.";
    }
}

// Validate Street Name (Street Address)
if (empty($_POST["streetname"])) {
    $errors[] = "Street name is required.";
} else {
    $street_address = sanitize_input($_POST["streetname"]);
    if (strlen($street_address) > 40) {
        $errors[] = "Street address must be maximum 40 characters.";
    }
}

// Validate Zone
if (empty($_POST["zone"])) {
    $errors[] = "Zone is required.";
} else {
    $zone = sanitize_input($_POST["zone"]);
    if (!preg_match("/^[0-9]{1,2}$/", $zone)) {
        $errors[] = "Zone must be a 1 or 2 digit number.";
    }
}

// Validate City
if (empty($_POST["city"])) {
    $errors[] = "City is required.";
} else {
    $city = sanitize_input($_POST["city"]);
    $valid_cities = array("Doha", "Al Wakra", "Al Khor", "Dukhan", "Al Shamal", "Mesaieed", "Ras Laffan");
    if (!in_array($city, $valid_cities)) {
        $errors[] = "Invalid city. Must be one of: Doha, Al Wakra, Al Khor, Dukhan, Al Shamal, Mesaieed, Ras Laffan.";
    }
}

// Validate Email
if (empty($_POST["email"])) {
    $errors[] = "Email address is required.";
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address format.";
    }
}

// Validate Phone Number
if (empty($_POST["phone"])) {
    $errors[] = "Phone number is required.";
} else {
    $phone = sanitize_input($_POST["phone"]);
    if (!preg_match("/^[0-9]{8,12}$/", $phone)) {
        $errors[] = "Phone number must be 8-12 digits.";
    }
}

// Validate Skills - at least one must be selected
$skill1 = isset($_POST["skill1"]) ? sanitize_input($_POST["skill1"]) : NULL;
$skill2 = isset($_POST["skill2"]) ? sanitize_input($_POST["skill2"]) : NULL;
$skill3 = isset($_POST["skill3"]) ? sanitize_input($_POST["skill3"]) : NULL;
$skill4 = isset($_POST["skill4"]) ? sanitize_input($_POST["skill4"]) : NULL;

if (!$skill1 && !$skill2 && !$skill3 && !$skill4) {
    $errors[] = "At least one technical skill must be selected.";
}

// Validate Other Skills
$other_skills = isset($_POST["otherskills"]) ? sanitize_input($_POST["otherskills"]) : "";

// If there are errors, display them
if (!empty($errors)) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Validation Errors</title>
        <link rel='stylesheet' href='styles/styles.css'>
        <link rel='stylesheet' href='styles/process_styles.css'>
    </head>
    <body>";
    
    include 'header.inc';
    
    echo "<div class='error-container'>
            <h1>Validation Errors</h1>
            <p>Please correct the following errors and try again:</p>
            <ul class='error-list'>";
    
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    
    echo "</ul>
            <a href='apply.php' class='back-button'>Go Back to Form</a>
          </div>";
    
    include 'footer.inc';
    
    echo "</body></html>";
    
    // Close database connection
    mysqli_close($conn);
    exit();
}

// Create table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS eoi (
    EOInumber INT(11) AUTO_INCREMENT PRIMARY KEY,
    job_reference VARCHAR(10) NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    last_name VARCHAR(20) NOT NULL,
    street_address VARCHAR(40) NOT NULL,
    suburb_town VARCHAR(40) NOT NULL,
    state VARCHAR(3) NOT NULL,
    postcode VARCHAR(4) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(12) NOT NULL,
    skill1 VARCHAR(50) NULL,
    skill2 VARCHAR(50) NULL,
    skill3 VARCHAR(50) NULL,
    skill4 VARCHAR(50) NULL,
    other_skills TEXT NULL,
    status ENUM('New', 'Current', 'Final') DEFAULT 'New' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $create_table_sql)) {
    die("Error creating table: " . mysqli_error($conn));
}

// Combine address components
$full_street_address = $unit_number . "/" . $building_number . " " . $street_address;

// Prepare and execute INSERT statement
$insert_sql = "INSERT INTO eoi (job_reference, first_name, last_name, 
                street_address, suburb_town, state, postcode, email, phone, 
                skill1, skill2, skill3, skill4, other_skills, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')";

$stmt = mysqli_prepare($conn, $insert_sql);

if ($stmt) {
    // Bind parameters - 14 parameters total (status is hardcoded as 'New')
    mysqli_stmt_bind_param($stmt, "ssssssssssssss", 
        $job_ref, 
        $firstname, 
        $lastname, 
        $full_street_address, 
        $city,
        $zone,
        $zone, // Using zone as postcode for Qatar
        $email, 
        $phone, 
        $skill1, 
        $skill2, 
        $skill3, 
        $skill4, 
        $other_skills
    );
    
    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Get the auto-generated EOInumber
        $eoi_number = mysqli_insert_id($conn);
        
        // Display success page
        echo "<!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Application Submitted Successfully</title>
            <link rel='stylesheet' href='styles/styles.css'>
            <link rel='stylesheet' href='styles/process_styles.css'>
        </head>
        <body>";
        
        include 'header.inc';
        
        echo "<div class='success-container'>
                <h1>✓ Application Submitted Successfully!</h1>
                <p class='success-message'>Thank you for your Expression of Interest.</p>
                <p class='success-message'>Your application has been received and recorded.</p>
                <div class='eoi-number'>EOI #" . $eoi_number . "</div>
                <p class='success-message'>Please save this reference number for your records.</p>
                <p class='success-message'>We will contact you at <strong>" . htmlspecialchars($email) . "</strong></p>
                <a href='index.php' class='home-button'>Return to Home Page</a>
              </div>";
        
        include 'footer.inc';
        
        echo "</body></html>";
        
    } else {
        echo "Error: " . mysqli_stmt_error($stmt);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}

// Close database connection
mysqli_close($conn);
?>