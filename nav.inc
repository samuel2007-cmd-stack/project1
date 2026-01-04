<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav aria-label="Main Navigation">
  <ul class="nav-menu">
    <li><a href="index.php" <?php if($current_page == 'index.php') echo 'class="active"'; ?>>Home</a></li>
    <li><a href="jobs.php" <?php if($current_page == 'jobs.php') echo 'class="active"'; ?>>Job Descriptions</a></li>
    <li><a href="apply.php" <?php if($current_page == 'apply.php') echo 'class="active"'; ?>>Apply Now</a></li>
    <li><a href="about.php" <?php if($current_page == 'about.php') echo 'class="active"'; ?>>About Us</a></li>
    <li><a href="enhancements.php" <?php if($current_page == 'enhancements.php') echo 'class="active"'; ?>>Enhancements</a></li>
    
    <?php if(isset($_SESSION['manager_logged_in'])): ?>
      <li><a href="manage.php" <?php if($current_page == 'manage.php') echo 'class="active"'; ?>>Manage EOI</a></li>
      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php" <?php if($current_page == 'login.php') echo 'class="active"'; ?>>Manager Login</a></li>
    <?php endif; ?>
    
    <li><a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@ctrlaltelite.com.au&su=Inquiry%20from%20Control%20Alt%20Elite%20Website" target="_blank">Email Us</a></li>
  </ul>
</nav>