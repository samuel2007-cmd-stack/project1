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
    
    <?php include 'nav.inc'; ?>
    
  </div>
</header>