<?php
$current_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    'index.php' => 'Home',
    'jobs.php' => 'Job Descriptions',
    'apply.php' => 'Apply Now',
    'about.php' => 'About Us',
    'enhancements.php' => 'Enhancements'
];
?>

<nav aria-label="Main Navigation" class="main-nav">
  <ul class="nav-menu">
    <?php foreach ($nav_items as $file => $label): ?>
      <li>
        <a href="<?= $file ?>" 
           class="<?= ($current_page == $file) ? 'active' : '' ?>" 
           <?= ($current_page == $file) ? 'aria-current="page"' : '' ?>>
          <?= $label ?>
        </a>
      </li>
    <?php endforeach; ?>

    <?php if (isset($_SESSION['manager_logged_in'])): ?>
      <li class="manager-nav-group">
        <a href="manage.php" 
           class="<?= ($current_page == 'manage.php') ? 'active' : '' ?>" 
           <?= ($current_page == 'manage.php') ? 'aria-current="page"' : '' ?>>
           Manage EOI
        </a>
      </li>
      <li><a href="logout.php" class="nav-logout">Logout</a></li>
    <?php else: ?>
      <li>
        <a href="login.php" 
           class="<?= ($current_page == 'login.php') ? 'active' : '' ?>"
           <?= ($current_page == 'login.php') ? 'aria-current="page"' : '' ?>>
           Manager Login
        </a>
      </li>
    <?php endif; ?>

    <li class="nav-contact">
      <a href="mailto:info@ctrlaltelite.com.au?subject=Inquiry from Website">Email Us</a>
    </li>
  </ul>
</nav>