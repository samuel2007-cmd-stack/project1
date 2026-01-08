<?php
session_start();

if (isset($_SESSION['manager_logged_in'])) {
    header("Location: manage.php");
    exit();
}

require_once 'settings.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $error = "Password does not meet security requirements.";
    } else {
        $conn = mysqli_connect($host, $user, $pwd, $sql_db);
        
        if (!$conn) {
            $error = "System error. Please try again later.";
        } else {
            $checkStmt = mysqli_prepare($conn, "SELECT username FROM managers WHERE username=?");
            mysqli_stmt_bind_param($checkStmt, "s", $username);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $error = "Username is already taken.";
                mysqli_stmt_close($checkStmt);
            } else {
                mysqli_stmt_close($checkStmt);
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = mysqli_prepare($conn, "INSERT INTO managers (username, password) VALUES (?, ?)");
                mysqli_stmt_bind_param($insertStmt, "ss", $username, $hashed_password);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    header("Location: login.php?msg=registered");
                    exit();
                } else {
                    $error = "An unexpected error occurred during registration.";
                }
                mysqli_stmt_close($insertStmt);
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
    <title>Manager Registration | Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<?php include 'header.inc'; ?>

<main class="manage-container">
    <section class="login-card">
        <h1>Manager Registration</h1>
        
        <?php if ($error): ?>
            <div class="error-box" role="alert" style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <p><?= htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="post" novalidate>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <small style="display: block; color: #666; margin-top: 5px;">Must be 8+ characters, include 1 uppercase letter and 1 number.</small>
            </div>
            
            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn">Register Manager</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.9em;">Already have an account? <a href="login.php">Login here</a></p>
    </section>
</main>

<?php include 'footer.inc'; ?>

</body>
</html>