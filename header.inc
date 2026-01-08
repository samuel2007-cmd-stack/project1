<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="main-header" role="banner">
  <div class="header-container">
    
    <div class="brand-identity">
      <a href="index.php" class="logo-link">
        <img src="images/logo.jpg" alt="Control Alt Elite Logo" class="site-logo">
      </a>
      <div class="site-title">
        <h1>Control Alt Elite</h1>
      </div>
    </div>

    <div class="navigation-wrapper">
      <?php include 'nav.inc'; ?>
    </div>

  </div>
</header>