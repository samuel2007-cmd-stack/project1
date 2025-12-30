<?php
session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enhancements</title>
  <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<?php include 'header.inc'; ?>

<div class="enhancements-container">
  
  <h1>Enhancements</h1>
  
  <p>Here are the extra features we added to improve our website beyond the basic requirements.</p>
  
  <div class="enhancement-section">
    <h2>Enhancement 1: Manager Registration Page</h2>
    
    <p>We created a manager registration page with server side validation. This way managers have to make strong passwords.</p>
    
    <h3>What we did:</h3>
    <ul class="feature-list">
      <li>Created registration page (register.php)</li>
      <li>Username must be unique - checks database to make sure no duplicates</li>
      <li>Password rules enforced on server side</li>
      <li>Password must be at least 8 characters long</li>
      <li>Password must contain at least one uppercase letter</li>
      <li>Password must contain at least one number</li>
      <li>Passwords are hashed using password_hash() before storing</li>
      <li>Shows error messages if validation fails</li>
    </ul>
    
    <h3>Database table:</h3>
    <p>We made a managers table to store the login information:</p>
    <div class="code-example">
      CREATE TABLE managers (
        id INT AUTO_INCREMENT,
        username VARCHAR(50),
        password VARCHAR(225)
      );
    </div>
    
    <h3>Validation code:</h3>
    <p>In register.php we check the password requirements:</p>
    <pre>
      if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
      } elseif (!preg_match("/[A-Z]/", $password)) {
        $error = "Password must have one uppercase letter";
      } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Password must have one number";
      }
    </pre>
    
    <p>This makes sure all manager accounts have strong passwords.</p>
  </div>

  <div class="enhancement-section">
    <h2>Enhancement 2: Access Control with Login System</h2>
    
    <p>We control access to manage.php by checking username and password. Only logged in managers can see the management functions.</p>
    
    <h3>What we did:</h3>
    <ul class="feature-list">
      <li>Created login page (login.php) where managers enter credentials</li>
      <li>Checks username exists in database</li>
      <li>Uses password_verify() to check the hashed password</li>
      <li>Creates session variable when login is successful</li>
      <li>Session tracks who is logged in</li>
      <li>Logout button (logout.php) destroys the session</li>
    </ul>
    
    <h3>Access control:</h3>
    <p>At the top of manage.php we check if someone is logged in:</p>
    <pre>
      session_start();
      if (!isset($_SESSION['manager_logged_in'])) {
        header("Location: login.php");
        exit();
      }
    </pre>
    
    <p>If they're not logged in they get redirected to the login page. This means random people cant access the management page.</p>
  </div>

  <div class="enhancement-section">
    <h2>Enhancement 3: Login Attempt Lockout</h2>
    
    <p>We added a feature that disables website access for a user after three or more invalid login attempts. The account gets locked for 15 minutes.</p>
    
    <h3>How it works:</h3>
    <ul class="feature-list">
      <li>System counts wrong password attempts for each user</li>
      <li>After 3 failed attempts the account is locked</li>
      <li>Lockout lasts for 15 minutes</li>
      <li>Shows error message telling them to wait</li>
      <li>Displays how many minutes are left</li>
      <li>Counter resets to 0 after successful login</li>
      <li>After 15 minutes the lockout expires automatically</li>
    </ul>
    
    <h3>Database changes:</h3>
    <p>We added two columns to track this:</p>
    <div class="code-example">
      ALTER TABLE managers ADD failed_attempts INT DEFAULT 0;<br>
      ALTER TABLE managers ADD lockout_time DATETIME;
    </div>
    
    <h3>Implementation:</h3>
    <p>In login.php we check the lockout time first before checking password:</p>
    <pre>
    if ($lockout_time != NULL){
        $current = new Datetime();
        $lockout = new DateTime($lockout_time);
        if ($current < $lockout){
            error = "Account locked. Please wait.";
        }
    }
    
    <p>When password is wrong we increment the failed_attempts counter. If it reaches 3 we set the lockout_time to 15 minutes from now. This stops brute force attacks where hackers try to guess passwords.</p>
    </pre>

  <div class="enhancement-section">
    <h2>Files Created/Modified</h2>
    <ul class="feature-list">
      <li>register.php - new file for manager registration with validation</li>
      <li>login.php - new file for login with lockout checking</li>
      <li>logout.php - new file that destroys session</li>
      <li>manage.php - added session check at the top</li>
      <li>header.inc - shows login/logout links based on session</li>
      <li>styles.css - added styling for login and register forms</li>
    </ul>
    
    <h3>Testing:</h3>
    <p>We tested everything in Chrome and Firefox:</p>
    <ul class="feature-list">
      <li>Registration checks for duplicate usernames</li>
      <li>Weak passwords get rejected</li>
      <li>Login works with correct credentials</li>
      <li>Wrong password shows error message</li>
      <li>Lockout activates after 3 wrong attempts</li>
      <li>Lockout timer expires after 15 minutes</li>
      <li>Manage page redirects when not logged in</li>
    </ul>
    
    <p>Everything works properly. The enhancements make the website more secure and easier to use.</p>
  </div>

</div>

<?php include 'footer.inc'; ?>

</body>
</html>