<?php
/**
 * Header Include File
 * Displays the website header with logo and navigation
 * 
 * Part of COS10026 Web Technology Project Part 2
 * Control Alt Elite - Group Project
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header-box">
    <div class="header-flex">
        <div class="logo-container">
            <a href="index.php">
                <img src="images/logo.jpg" alt="Control Alt Elite Logo" class="logo">
            </a>
            <h1>Control Alt Elite</h1>
        </div>
        
        <?php include 'nav.inc'; ?>
    </div>
</header>