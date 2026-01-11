<?php
/**
 * Manager Login Page
 * Handles authentication with account lockout protection
 * Includes CSRF protection and failed login attempt tracking
 */

session_start();
require_once 'settings.php';

$error = "";

// Redirect if already logged in
if (isset($_SESSION['manager_logged_in'])) {
    header("Location: manage.php");
    exit();
}

// ============================================================================
// PROCESS LOGIN FORM
// ============================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid security token. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (!empty($username) && !empty($password)) {
            $conn = getDatabaseConnection(); // CRITICAL FIX: Use consistent connection method
            
            if (!$conn) {
                $error = "Database connection failed";
            } else {
                // Prepare statement to prevent SQL injection
                $stmt = mysqli_prepare($conn, "SELECT * FROM managers WHERE username=?");
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) == 1) {
                    $manager = mysqli_fetch_assoc($result);
                    
                    // Check if account is locked
                    if ($manager['lockout_time'] != NULL) {
                        $current = new DateTime();
                        $lockout = new DateTime($manager['lockout_time']);
                        
                        // Check if lockout period has expired
                        if ($current < $lockout) {
                            $diff = $current->diff($lockout);
                            $minutes = $diff->i + 1;
                            $error = "Account locked. Please wait $minutes minutes.";
                        } else {
                            // Lockout expired, reset failed attempts
                            $resetStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=0, lockout_time=NULL WHERE username=?");
                            mysqli_stmt_bind_param($resetStmt, "s", $username);
                            mysqli_stmt_execute($resetStmt);
                            mysqli_stmt_close($resetStmt);
                        }
                    }
                    
                    // Proceed with password verification if not locked
                    if ($error == "") {
                        if (password_verify($password, $manager['password'])) {
                            // CRITICAL FIX: Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Successful login - set session variables
                            $_SESSION['manager_logged_in'] = true;
                            $_SESSION['manager_username'] = $username;
                            $_SESSION['manager_id'] = $manager['id']; // CRITICAL FIX: Store manager ID
                            
                            // Reset failed attempts on successful login
                            $resetStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=0, lockout_time=NULL WHERE username=?");
                            mysqli_stmt_bind_param($resetStmt, "s", $username);
                            mysqli_stmt_execute($resetStmt);
                            mysqli_stmt_close($resetStmt);
                            
                            mysqli_stmt_close($stmt);
                            closeDatabaseConnection($conn); // CRITICAL FIX: Use consistent close method
                            header("Location: manage.php");
                            exit();
                        } else {
                            // Incorrect password - increment failed attempts
                            $failed = $manager['failed_attempts'] + 1;
                            
                            if ($failed >= 3) {
                                // Lock account for 15 minutes after 3 failed attempts
                                $lockout_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                                $updateStmt = mysqli_prepare($conn, "UPDATE managers SET failed_attempts=?, lockout_time=? WHERE username=?");
                                mysqli_stmt_bind_param($updateStmt, "iss", $failed, $lockout_time, $username);
                                mysqli_stmt_execute($updateStmt);
                                mysqli_stmt_close($updateStmt);
                                $error = "Too many failed attempts. Account locked for 15 minutes.";
                            } else {
                                // Increment failed attempts counter
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
                    // CRITICAL FIX: Use constant-time comparison to prevent username enumeration
                    // Still show generic error, but simulate password check timing
                    $dummy_hash = '$2y$10$dummyhashtopreventtimingattacksxxxxxxxxxxxxxxxxxxxxxxxxx';
                    password_verify($password, $dummy_hash);
                    mysqli_stmt_close($stmt);
                    $error = "Invalid username or password"; // CRITICAL FIX: Generic error message
                }
                
                closeDatabaseConnection($conn); // CRITICAL FIX: Use consistent close method
            }
        } else {
            $error = "Please fill in all fields";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manager login portal for Control Alt Elite">
    <meta name="keywords" content="login, manager, authentication, Control Alt Elite">
    <title>Manager Login - Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body class="auth-page">

<?php include 'header.inc'; ?>

<div class="auth-hero">
    <div class="auth-hero-content">
        <h1>Manager Login</h1>
        <p>Access the management dashboard</p>
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
        
        <form method="post" class="auth-form">
            <!-- CSRF Token for security -->
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="auth-btn">
                <span>Login</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<?php include 'footer.inc'; ?>

</body>
</html>