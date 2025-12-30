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
    
    <li><a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@ctrlaltelite.com.au&su=Inquiry%20from%20Control%20Alt%20Elite%20Website" target="_blank">Email Us</a></li>
  </ul>
</nav>