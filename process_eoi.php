<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: apply.php");
    exit();
}

require_once 'settings.php';

if (!$conn) {
    die("Database connection failed.");
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$errors = array();

$job_ref = sanitize_input($_POST["ref"] ?? '');
$valid_refs = array("Software Developer", "Network Administrator", "Cybersecurity", "Cloud Engineer");
if (!in_array($job_ref, $valid_refs)) $errors[] = "Invalid job reference selection.";

$firstname = sanitize_input($_POST["firstname"] ?? '');
if (!preg_match("/^[a-zA-Z\s\-']{1,20}$/", $firstname)) $errors[] = "First name must be alpha characters (max 20).";

$lastname = sanitize_input($_POST["lastname"] ?? '');
if (!preg_match("/^[a-zA-Z\s\-']{1,20}$/", $lastname)) $errors[] = "Last name must be alpha characters (max 20).";

$dob = sanitize_input($_POST["dob"] ?? '');
if (!empty($dob)) {
    $birthdate = new DateTime($dob);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;
    if ($age < 15 || $age > 80) $errors[] = "Applicants must be between 15 and 80 years old.";
} else {
    $errors[] = "Date of birth is required.";
}

$gender = sanitize_input($_POST["gender"] ?? '');
if (!in_array($gender, ["male", "female"])) $errors[] = "Please select a valid gender.";

$unit = sanitize_input($_POST["unitnumber"] ?? '');
$building = sanitize_input($_POST["buildingnumber"] ?? '');
$street = sanitize_input($_POST["streetname"] ?? '');
if (empty($unit) || empty($building) || empty($street)) $errors[] = "Full address details are required.";

$city = sanitize_input($_POST["city"] ?? '');
$zone = sanitize_input($_POST["zone"] ?? '');
if (empty($city) || empty($zone)) $errors[] = "City and Zone are required.";

$email = sanitize_input($_POST["email"] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email address is required.";

$phone = sanitize_input($_POST["phone"] ?? '');
if (!preg_match("/^[0-9]{8,12}$/", $phone)) $errors[] = "Phone number must be between 8 and 12 digits.";

$skill1 = isset($_POST["skill1"]) ? sanitize_input($_POST["skill1"]) : NULL;
$skill2 = isset($_POST["skill2"]) ? sanitize_input($_POST["skill2"]) : NULL;
$skill3 = isset($_POST["skill3"]) ? sanitize_input($_POST["skill3"]) : NULL;
$skill4 = isset($_POST["skill4"]) ? sanitize_input($_POST["skill4"]) : NULL;
$other_skills = sanitize_input($_POST["otherskills"] ?? "");

if (!$skill1 && !$skill2 && !$skill3 && !$skill4) $errors[] = "Please select at least one technical skill.";

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header("Location: apply.php"); 
    exit();
}

$full_address = "$unit/$building $street";
$status = "New";

$query = "INSERT INTO eoi (job_reference, first_name, last_name, street_address, suburb_town, state, postcode, email, phone, skill1, skill2, skill3, skill4, other_skills, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssssssssss", 
        $job_ref, $firstname, $lastname, $full_address, $city, $zone, $zone, 
        $email, $phone, $skill1, $skill2, $skill3, $skill4, $other_skills, $status
    );

    if (mysqli_stmt_execute($stmt)) {
        $eoi_id = mysqli_insert_id($conn);
        header("Location: post_apply.php?id=$eoi_id");
    } else {
        echo "Submission error. Please contact admin.";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>