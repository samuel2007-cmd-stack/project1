<?php
/**
 * Navigation Include File
 * Displays the main navigation menu with active page highlighting
 * and conditional manager access links
 * 
 * Part of COS10026 Web Technology Project Part 2
 * Control Alt Elite - Group Project
 */
// Get the current page filename for active state highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav aria-label="Main Navigation">
  <ul class="nav-menu">
    <li>
      <a href="index.php" <?= ($current_page == 'index.php') ? 'class="active"' : '' ?>>
        Home
      </a>
    </li>
    <li>
      <a href="jobs.php" <?= ($current_page == 'jobs.php') ? 'class="active"' : '' ?>>
        Jobs
      </a>
    </li>
    <li>
      <a href="apply.php" <?= ($current_page == 'apply.php') ? 'class="active"' : '' ?>>
        Apply
      </a>
    </li>
    <li>
      <a href="about.php" <?= ($current_page == 'about.php') ? 'class="active"' : '' ?>>
        About
      </a>
    </li>
    <li>
      <a href="enhancements.php" <?= ($current_page == 'enhancements.php') ? 'class="active"' : '' ?>>
        Enhancements
      </a>
    </li>
    
    <?php if(isset($_SESSION['manager_logged_in']) && $_SESSION['manager_logged_in'] === true): ?>
      <li>
        <a href="manage.php" <?= ($current_page == 'manage.php') ? 'class="active"' : '' ?>>
          Manage
        </a>
      </li>
      <li>
        <a href="logout.php" class="logout-btn">
          Logout
        </a>
      </li>
    <?php else: ?>
      <li>
        <a href="login.php" <?= ($current_page == 'login.php') ? 'class="active"' : '' ?>>
          Login
        </a>
      </li>
    <?php endif; ?>
    
    <li>
      <a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@controlaltelite.com&su=Inquiry%20from%20Control%20Alt%20Elite%20Website&body=Dear%20Control%20Alt%20Elite%20Team%2C%0A%0AI%20am%20writing%20to%20inquire%20about%20your%20services.%0A%0AThank%20you." 
         target="_blank" 
         title="Email us via Gmail">
        Email Us
      </a>
    </li>
  </ul>
</nav>