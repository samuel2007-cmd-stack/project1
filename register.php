<?php
session_start();
require_once 'settings.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $error = "Password must have one uppercase letter";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Password must have one number";
    } else {
        $conn = mysqli_connect($host, $user, $pwd, $sql_db);
        
        if (!$conn) {
            $error = "Database connection failed";
        } else {
            $username = mysqli_real_escape_string($conn, $username);
            
            // Check if username already exists
            $checkQuery = "SELECT username FROM managers WHERE username='$username'";
            $result = mysqli_query($conn, $checkQuery);
            
            if (mysqli_num_rows($result) > 0) {
                $error = "Username already exists";
            } else {
                // Hash password and insert
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insertQuery = "INSERT INTO managers (username, password, failed_attempts) VALUES ('$username', '$hashed_password', 0)";
                
                if (mysqli_query($conn, $insertQuery)) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed: " . mysqli_error($conn);
                }
            }
            
            mysqli_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Registration</title>
    <link rel="stylesheet" href="dynamic/dynamic.css">
</head>
<body>

<?php include 'header.inc'; ?>

<div class="login-container">
    <h1>Manager Registration</h1>
    
    <?php if ($error != ""): ?>
        <div class="error-message">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($success != ""): ?>
        <div class="success-message">
            <p><?php echo htmlspecialchars($success); ?></p>
            <p><a href="login.php">Go to Login</a></p>
        </div>
    <?php endif; ?>
    
    <form method="post" class="login-form">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <small>Must be 8+ characters with 1 uppercase letter and 1 number</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn">Register</button>
    </form>
    
    <p class="register-link">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php include 'footer.inc'; ?>

</body>
</html>