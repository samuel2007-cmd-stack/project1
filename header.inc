<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="header-box">
  <div class="header-flex">
    <div class="logo-container">
      <a href="index.php">
        <img src="images/logo.jpg" alt="Control Alt Elite Logo" class="logo">
      </a>
      <h1>Control Alt Elite</h1>
    </div>
    
    <nav aria-label="Main Navigation">
      <ul class="nav-menu">
        <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Home</a></li>
        <li><a href="jobs.php" class="<?= ($current_page == 'jobs.php') ? 'active' : '' ?>">Job Descriptions</a></li>
        <li><a href="apply.php" class="<?= ($current_page == 'apply.php') ? 'active' : '' ?>">Apply Now</a></li>
        <li><a href="about.php" class="<?= ($current_page == 'about.php') ? 'active' : '' ?>">About Us</a></li>
        <li><a href="enhancements.php" class="<?= ($current_page == 'enhancements.php') ? 'active' : '' ?>">Enhancements</a></li>
        
        <?php if (isset($_SESSION['manager_logged_in'])): ?>
          <li><a href="manage.php" class="<?= ($current_page == 'manage.php') ? 'active' : '' ?>">Manage EOI</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php" class="<?= ($current_page == 'login.php') ? 'active' : '' ?>">Manager Login</a></li>
        <?php endif; ?>
        
        <li><a href="mailto:info@ctrlaltelite.com.au?subject=Inquiry">Email Us</a></li>
      </ul>
    </nav>
  </div>
</header>