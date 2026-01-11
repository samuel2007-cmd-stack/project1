<?php
/**
 * Navigation Include File
 * Dynamic navigation menu with authentication-based rendering
 */

// Check if manager is logged in
$is_manager = isset($_SESSION['manager_logged_in']) && $_SESSION['manager_logged_in'] === true;

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav>
    <ul class="nav-menu">
        <?php if ($is_manager): ?>
            <!-- Manager Navigation -->
            <li><a href="manage.php" class="<?php echo $current_page === 'manage.php' ? 'active' : ''; ?>">Manage</a></li>
            <li><a href="statistics.php" class="<?php echo $current_page === 'statistics.php' ? 'active' : ''; ?>">Statistics</a></li>
            <li><a href="logout.php" class="logout-btn">Logout</a></li>
        <?php else: ?>
            <!-- Public Navigation -->
            <li><a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a></li>
            <li><a href="jobs.php" class="<?php echo $current_page === 'jobs.php' ? 'active' : ''; ?>">Jobs</a></li>
            <li><a href="apply.php" class="<?php echo $current_page === 'apply.php' ? 'active' : ''; ?>">Apply</a></li>
            <li><a href="about.php" class="<?php echo $current_page === 'about.php' ? 'active' : ''; ?>">About</a></li>
            <li><a href="enhancements.php" class="<?php echo $current_page === 'enhancements.php' ? 'active' : ''; ?>">Enhancements</a></li>
            <li><a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@ctrlaltelite.co&su=Inquiry%20from%20Website&body=Hello%20Control%20Alt%20Elite%20Team,%0A%0AI%20would%20like%20to%20inquire%20about:%0A%0A" target="_blank" rel="noopener noreferrer">Mail Us</a></li>
            <li><a href="login.php" class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>