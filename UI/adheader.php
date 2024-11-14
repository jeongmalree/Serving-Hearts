<?php
// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="/Serving Hearts/CSS/adheader.css">

<header class="main-header">
    <div class="logo-container">
        <img src="/Serving Hearts/WEB/images/logo.png" alt="Serving Hearts Logo" class="logo">
    </div>
    <div class="notification-container">
        <!-- Add the link to the message icon and conditionally apply active class -->
        <a href="http://localhost/Serving%20Hearts/NOTIFICATION_MODULE/send_notification.php" 
           class="message-link <?php echo ($current_page == 'send_notification.php') ? 'active' : ''; ?>">
            <i class="fa-regular fa-message"></i>
        </a>
        <i class="fa-regular fa-bell"></i>
    </div>
</header>
