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
        global $host, $user, $pwd, $sql_db;
        
        $conn = mysqli_connect($host, $user, $pwd, $sql_db);
        
        if (!$conn) {
            $error = "Database connection failed: " . mysqli_connect_error();
        } else {
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
    <meta name="description" content="Register as a manager for Control Alt Elite">
    <meta name="keywords" content="register, manager, sign up, Control Alt Elite">
    <title>Manager Registration - Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="auth-page">

<?php include 'header.inc'; ?>

<div class="auth-hero">
    <div class="auth-hero-content">
        <h1>Manager Registration</h1>
        <p>Create your manager account</p>
    </div>
</div>

<div class="auth-container">
    <div class="auth-box">
        
        <?php if ($error != ""): ?>
            <div class="error-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($success != ""): ?>
            <div class="success-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <div>
                    <p><?php echo htmlspecialchars($success); ?></p>
                    <p style="margin-top: 12px;"><a href="login.php" style="color: #059669; font-weight: 700; text-decoration: underline;">Go to Login →</a></p>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="post" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                
                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Contains at least 1 uppercase letter</li>
                        <li>Contains at least 1 number</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
            </div>
            
            <button type="submit" class="auth-btn">
                <span>Create Account</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<?php include 'footer.inc'; ?>

</body>
</html>