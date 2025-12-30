<?php
session_start();
require_once 'settings.php';

$error = "";

// Redirect if already logged in
if (isset($_SESSION['manager_logged_in'])) {
    header("Location: manage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $conn = mysqli_connect($host, $user, $pwd, $sql_db);
        
        if (!$conn) {
            $error = "Database connection failed";
        } else {
            // Use prepared statement for security
            $stmt = mysqli_prepare($conn, "SELECT * FROM managers WHERE username=?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $manager = mysqli_fetch_assoc($result);
                
                // Check lockout
                if ($manager['lockout_time'] != NULL) {
                    $current = new DateTime();
                    $lockout = new DateTime($manager['lockout_time']);
                    
                    if ($current < $lockout) {
                        $diff = $current->diff($lockout);
                        $minutes = $diff->i + 1;
                        $error = "Account locked. Please wait $minutes minutes.";
                    } else {
                        // Lockout expired, reset it
                        $resetStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=0, lockout_time=NULL WHERE username=?");
                        mysqli_stmt_bind_param($resetStmt, "s", $username);
                        mysqli_stmt_execute($resetStmt);
                        mysqli_stmt_close($resetStmt);
                    }
                }
                
                // Verify password if not locked
                if ($error == "") {
                    if (password_verify($password, $manager['password'])) {
                        // Successful login
                        $_SESSION['manager_logged_in'] = true;
                        $_SESSION['manager_username'] = $username;
                        
                        // Reset failed attempts
                        $resetStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=0, lockout_time=NULL WHERE username=?");
                        mysqli_stmt_bind_param($resetStmt, "s", $username);
                        mysqli_stmt_execute($resetStmt);
                        mysqli_stmt_close($resetStmt);
                        
                        mysqli_stmt_close($stmt);
                        mysqli_close($conn);
                        header("Location: manage.php");
                        exit();
                    } else {
                        // Wrong password - increment failed attempts
                        $failed = $manager['failed_attempts'] + 1;
                        
                        if ($failed >= 3) {
                            // Lock account for 15 minutes
                            $lockout_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            $updateStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=?, lockout_time=? WHERE username=?");
                            mysqli_stmt_bind_param($updateStmt, "iss", $failed, $lockout_time, $username);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                            $error = "Too many failed attempts. Account locked for 15 minutes.";
                        } else {
                            $updateStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=? WHERE username=?");
                            mysqli_stmt_bind_param($updateStmt, "is", $failed, $username);
                            mysqli_stmt_execute($updateStmt);
                            mysqli_stmt_close($updateStmt);
                            $remaining = 3 - $failed;
                            $error = "Incorrect password. $remaining attempts remaining.";
                        }
                    }
                }
                
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);
                $error = "Username not found";
            }
            
            mysqli_close($conn);
        }
    } else {
        $error = "Please fill in all fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<?php include 'header.inc'; ?>

<div class="login-container">
    <h1>Manager Login</h1>
    
    <?php if ($error != ""): ?>
        <div class="error-message">
            <p><?php echo htmlspecialchars($error); ?></p>
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
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php include 'footer.inc'; ?>

</body>
</html>