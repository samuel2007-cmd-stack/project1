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
        <li><a href="index.php" <?= ($current_page == 'index.php') ? 'class="active"' : '' ?>>Home</a></li>
        <li><a href="jobs.php" <?= ($current_page == 'jobs.php') ? 'class="active"' : '' ?>>Jobs</a></li>
        <li><a href="apply.php" <?= ($current_page == 'apply.php') ? 'class="active"' : '' ?>>Apply</a></li>
        <li><a href="about.php" <?= ($current_page == 'about.php') ? 'class="active"' : '' ?>>About</a></li>
        <li><a href="enhancements.php" <?= ($current_page == 'enhancements.php') ? 'class="active"' : '' ?>>Enhancements</a></li>
        
        <?php if (isset($_SESSION['manager_logged_in'])): ?>
          <li><a href="manage.php" <?= ($current_page == 'manage.php') ? 'class="active"' : '' ?>>Manage</a></li>
          <li><a href="logout.php" class="logout-btn">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php" <?= ($current_page == 'login.php') ? 'class="active"' : '' ?>>Login</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>