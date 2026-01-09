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
    <!-- Main public pages -->
    <li>
      <a href="index.php" <?php if($current_page == 'index.php') echo 'class="active"'; ?>>
        Home
      </a>
    </li>
    <li>
      <a href="jobs.php" <?php if($current_page == 'jobs.php') echo 'class="active"'; ?>>
        Job Descriptions
      </a>
    </li>
    <li>
      <a href="apply.php" <?php if($current_page == 'apply.php') echo 'class="active"'; ?>>
        Apply Now
      </a>
    </li>
    <li>
      <a href="about.php" <?php if($current_page == 'about.php') echo 'class="active"'; ?>>
        About Us
      </a>
    </li>
    <li>
      <a href="enhancements.php" <?php if($current_page == 'enhancements.php') echo 'class="active"'; ?>>
        Enhancements
      </a>
    </li>
    
    <!-- Conditional manager access links -->
    <?php if(isset($_SESSION['manager_logged_in']) && $_SESSION['manager_logged_in'] === true): ?>
      <li>
        <a href="manage.php" <?php if($current_page == 'manage.php') echo 'class="active"'; ?>>
          Manage EOI
        </a>
      </li>
      <li>
        <a href="logout.php">
          Logout
        </a>
      </li>
    <?php else: ?>
      <li>
        <a href="login.php" <?php if($current_page == 'login.php') echo 'class="active"'; ?>>
          Manager Login
        </a>
      </li>
    <?php endif; ?>
    
    <!-- Email contact link using mailto -->
    <li>
      <a href="mailto:info@controlaltelite.com?subject=Inquiry%20from%20Control%20Alt%20Elite%20Website&body=Dear%20Control%20Alt%20Elite%20Team%2C%0A%0AI%20am%20writing%20to%20inquire%20about...%0A%0AThank%20you." 
         target="_blank" 
         rel="noopener noreferrer">
        Email Us
      </a>
    </li>
  </ul>
</nav>