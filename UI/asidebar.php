<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/Serving Hearts/USER-VERIFICATION/config/db.php');
include $_SERVER['DOCUMENT_ROOT'] . '/Serving Hearts/UI/adheader.php';
?>

<link rel="stylesheet" href="/Serving Hearts/CSS/adsidebar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<nav class="adsidebar">
    <div class="admin-icon">
        <a href="#"><i class="fas fa-user-circle profile-icon"></i></a>
        <span class="admin-fullname"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?></span>
        <span class="admin-unique-number"><?php echo isset($_SESSION['unique_number']) ? htmlspecialchars($_SESSION['unique_number']) : ''; ?></span>
    </div>

    <div class="divider"></div>

    <ul class="nav-links">
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home nav-icon"></i>
            <a href="../USER-VERIFICATION/admin_dashboard.php">Dashboard</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'map.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-plus nav-icon"></i>
            <a href="../EVENT_MODULE/maps/map.php">Event Menu</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'donation.php' || basename($_SERVER['PHP_SELF']) == 'manage_donation.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-box nav-icon"></i>
            <a href="../ADMIN_MODULE/donation.php">Blood Inventory</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'request.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-magnifying-glass nav-icon"></i>
            <a href="../ADMIN_MODULE/request.php">Review Request</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'handover.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-copy nav-icon"></i>
            <a href="../ADMIN_MODULE/handover.php">Hand Over Request</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'attendance.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user-pen nav-icon"></i>
            <a href="../ADMIN_MODULE/attendance.php">Attendance</a>
        </li>
    </ul>

    <div class="divider"></div>

   <!-- Logout Link -->
   <div class="exit">
        <i class="fas fa-sign-out-alt nav-icon"></i>
        <a href="javascript:void(0);" onclick="showLogoutModal()">Logout</a>
    </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal" id="logoutModal">
    <div class="modal-content">
        <h2>Logout Confirmation</h2>
        <p>Are you sure you want to log out?</p>
        <div class="modal-actions">
            <button class="confirm-btn" onclick="confirmLogout()">Yes, Logout</button>
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
        </div>
    </div>
</div>


<script>
    // Show the logout modal
    function showLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
    }

    // Close the logout modal
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Confirm logout and redirect to the logout URL
    function confirmLogout() {
        window.location.href = "../USER-VERIFICATION/index.php?logout=1";
    }
</script>
