<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header-box">
  <div class="container header-flex">
    <div class="logo-container">
      <img src="images/logo.jpg" alt="Control Alt Elite Logo" class="logo">
      <h1>Control Alt Elite</h1>
    </div>
    
    <nav aria-label="Main Navigation">
      <ul class="nav-menu">
        <li><a href="index.php">Home</a></li>
        <li><a href="jobs.php">Job Descriptions</a></li>
        <li><a href="apply.php">Apply Now</a></li>
        <li><a href="about.php">About Us</a></li>
        <li><a href="enhancements.php">Enhancements</a></li>
        
        <?php if(isset($_SESSION['manager_logged_in'])): ?>
          <li><a href="manage.php">Manage EOI</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Manager Login</a></li>
        <?php endif; ?>
        
        <li><a href="mailto:info@ctrlaltelite.com.au">Email Us</a></li>
      </ul>
    </nav>
  </div>
</header>