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
        // Make sure settings variables are available
        global $host, $user, $pwd, $sql_db;
        
        $conn = mysqli_connect($host, $user, $pwd, $sql_db);
        
        if (!$conn) {
            $error = "Database connection failed: " . mysqli_connect_error();
        } else {
            // Check if username already exists using prepared statement
            $checkStmt = mysqli_prepare($conn, "SELECT username FROM managers WHERE username=?");
            
            if (!$checkStmt) {
                $error = "Database error: " . mysqli_error($conn);
            } else {
                mysqli_stmt_bind_param($checkStmt, "s", $username);
                mysqli_stmt_execute($checkStmt);
                $result = mysqli_stmt_get_result($checkStmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $error = "Username already exists";
                    mysqli_stmt_close($checkStmt);
                } else {
                    mysqli_stmt_close($checkStmt);
                    
                    // Hash password and insert using prepared statement
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insertStmt = mysqli_prepare($conn, "INSERT INTO managers (username, password, failed_attempts) VALUES (?, ?, 0)");
                    
                    if (!$insertStmt) {
                        $error = "Database error: " . mysqli_error($conn);
                    } else {
                        mysqli_stmt_bind_param($insertStmt, "ss", $username, $hashed_password);
                        
                        if (mysqli_stmt_execute($insertStmt)) {
                            $success = "Registration successful! You can now login.";
                        } else {
                            $error = "Registration failed: " . mysqli_stmt_error($insertStmt);
                        }
                        
                        mysqli_stmt_close($insertStmt);
                    }
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
    <link rel="stylesheet" href="styles/styles.css">
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