<?php
/**
 * Update Status Handler
 * Changes application status (New, Current, Final)
 */

session_start();
require_once 'settings.php';

// Check authentication
if (!isset($_SESSION['manager_logged_in']) || $_SESSION['manager_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    die("Invalid security token.");
}

// Get form data
$eoi_number = isset($_POST['eoi_number']) ? intval($_POST['eoi_number']) : 0;
$new_status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';

// Validate status
$valid_statuses = array('New', 'Current', 'Final');
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['error_message'] = "Invalid status value.";
    header("Location: manage.php");
    exit();
}

if ($eoi_number <= 0) {
    $_SESSION['error_message'] = "Invalid EOI number.";
    header("Location: manage.php");
    exit();
}

// Update database
$conn = getDatabaseConnection();

$update_sql = "UPDATE eoi SET status = ? WHERE EOInumber = ?";
$stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($stmt, "si", $new_status, $eoi_number);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_message'] = "Status updated successfully to: " . $new_status;
} else {
    $_SESSION['error_message'] = "Failed to update status.";
}

mysqli_stmt_close($stmt);
closeDatabaseConnection($conn);

// Redirect back to manage page
header("Location: manage.php");
exit();
?>