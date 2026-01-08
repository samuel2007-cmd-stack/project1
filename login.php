<?php
session_start();

if (isset($_SESSION['manager_logged_in'])) {
    header("Location: manage.php");
    exit();
}

$error_message = "";
$success_message = "";

if (isset($_GET['msg']) && $_GET['msg'] == 'registered') {
    $success_message = "Account created successfully! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim(htmlspecialchars($_POST['username']));
    $password = trim($_POST['password']);

    $valid_user = "admin";
    $valid_pass_hash = password_hash("password123", PASSWORD_DEFAULT);

    if ($username === $valid_user && password_verify($password, $valid_pass_hash)) {
        session_regenerate_id(true);
        $_SESSION['manager_logged_in'] = true;
        $_SESSION['last_login'] = time();
        header("Location: manage.php");
        exit();
    } else {
        $error_message = "Invalid authentication credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Portal Login | Control Alt Elite</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<?php include 'header.inc'; ?>

<main class="manage-container">
    <section class="login-card">
        <h1>HR Manager Access</h1>

        <?php if ($success_message): ?>
            <div class="success-box" style="color: green; margin-bottom: 15px;">
                <p><?= htmlspecialchars($success_message) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-box" role="alert" style="color: red; margin-bottom: 15px;">
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="login-footer" style="margin-top: 20px; font-size: 0.9em;">
            <p>New manager? <a href="register.php">Register here</a></p>
        </div>
    </section>
</main>

<?php include 'footer.inc'; ?>

</body>
</html>