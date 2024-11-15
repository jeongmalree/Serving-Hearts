<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

// Include the database connection (adjust the path as necessary)
require_once('../USER-VERIFICATION/config/db.php');

// Get the unique number of the logged-in user
$user = $_SESSION['unique_number'];

// Query to count unread notifications for the logged-in user
$unreadQuery = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE unique_number = ? AND is_read = 0");
$unreadQuery->bind_param("s", $user);
$unreadQuery->execute();
$unreadResult = $unreadQuery->get_result();
$unreadData = $unreadResult->fetch_assoc();
$unreadCount = $unreadData['unread_count'] ?? 0; // Set to 0 if unread count is not found
?>

<link rel="stylesheet" href="../CSS/top-bar.css">

<header class="main-header">
    <div class="logo-container">
        <img src="../WEB/images/logo.png" alt="Serving Hearts Logo" class="logo">
    </div>
    <div class="notification-container" onclick="toggleDropdown()">
        <i class="fa-solid fa-bell notification-icon"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="badge"><?php echo $unreadCount; ?></span> <!-- Display unread count only if greater than zero -->
        <?php endif; ?>

        <div class="dropdown" id="notification-dropdown">
            <?php
            // Fetch and display recent unread notifications
            $recentNotifications = $conn->prepare("SELECT id, subject, timestamp FROM notifications WHERE unique_number = ? AND is_read = 0 ORDER BY timestamp DESC LIMIT 5");
            $recentNotifications->bind_param("s", $user);
            $recentNotifications->execute();
            $recentResults = $recentNotifications->get_result();

            if ($recentResults->num_rows > 0) {
                while ($row = $recentResults->fetch_assoc()) {
                    echo "<div class='dropdown-item' onclick=\"window.location.href='/Serving%20Hearts/NOTIFICATION_MODULE/view_notification.php?id={$row['id']}';\">";
                    echo "<strong>{$row['subject']}</strong>";
                    echo "<span class='timestamp'>{$row['timestamp']}</span>";
                    echo "</div>";
                }
            } else {
                echo "<div class='dropdown-item'>No new notifications</div>";
            }
            ?>
        </div>
    </div>
</header>

<script>
    function toggleDropdown() {
        var dropdown = document.getElementById('notification-dropdown');
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    window.onclick = function(event) {
        if (!event.target.matches('.notification-container, .notification-icon')) {
            var dropdown = document.getElementById('notification-dropdown');
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            }
        }
    }
</script>
