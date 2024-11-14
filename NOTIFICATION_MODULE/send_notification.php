<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../USER-VERIFICATION/config/db.php');
include '../UI/asidebar.php';

$success = false; // Variable to track if notifications were sent successfully

if (isset($_POST['send'])) {
    $users = $_POST['users']; // Array of selected users
    $subject = htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8'); // Get subject
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8'); // Get message

    // Prepare the statement to insert subject and message
    $stmt = $conn->prepare("INSERT INTO notifications (unique_number, subject, message) VALUES (?, ?, ?)");
    foreach ($users as $user) {
        $stmt->bind_param("sss", $user, $subject, $message);
        $stmt->execute();
    }
    $stmt->close();

    $success = true; // Set success flag to true when notifications are sent
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex; /* Use flexbox for centering */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            height: 100vh; /* Full viewport height */
        }

        h2 {
            color: #333;
        }

        form {
            background-color: white;
            padding: 50px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%; /* Full width */
            max-width: 1000px; /* Set a maximum width for the form */
            margin-left: 18rem;
            margin-top: 3rem;
            height: 70%;
            overflow: auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #990F02;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #A91B0D;
        }

        /* Dropdown Checkbox Style */
        .dropdown-checkbox {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .dropdown-checkbox-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            padding: 12px;
            z-index: 1;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .dropdown-checkbox:hover .dropdown-checkbox-content {
            display: block;
        }

        .dropdown-checkbox-content label {
            display: block;
            margin: 5px 0;
        }

        #selectAllBtn {
            background-color: #990F02;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        #selectAllBtn:hover {
            background-color: #A91B0D;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .modal-content p {
            color: green;
            font-size: 18px;
            margin: 0 0 20px;
        }

        .modal-close {
            background-color: gray;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-close:hover {
            background-color: gray;
        }
    </style>
</head>
<body>
    <form method="POST" action="send_notification.php">
        <h2>Send Notification</h2>
        
        <label for="users">Select Users:</label>
        <div class="dropdown-checkbox">
            <button type="button" onclick="toggleDropdown()">Select Users <i class="fas fa-chevron-down"></i></button>
            <div class="dropdown-checkbox-content" id="userDropdown">
                <button type="button" id="selectAllBtn" onclick="selectAll()">Select All</button>
                <!-- Populate this with user data from your database -->
                <?php
                // Fetch users from the database
                $result = $conn->query("SELECT unique_number, fullname FROM users");
                while ($row = $result->fetch_assoc()) {
                    echo "<label><input type='checkbox' name='users[]' value='{$row['unique_number']}'> {$row['fullname']}</label>";
                }
                ?>
            </div>
        </div>

        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject" required>

        <label for="message">Message:</label>
        <textarea id="message" name="message" required rows="9"></textarea>
        
        <button type="submit" name="send">Send Notification</button>
    </form>

    <!-- Success Modal -->
    <div class="modal" id="successModal">
        <div class="modal-content">
            <p>Notifications sent successfully!</p>
            <button class="modal-close" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('input[name="users[]"]');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            document.getElementById('selectAllBtn').innerText = allChecked ? 'Select All' : 'Deselect All';
        }

        // Function to show the success modal
        function showModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'flex';
        }

        // Function to close the modal
        function closeModal() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'none';
        }

        // If notifications were sent, show the modal
        <?php if ($success): ?>
            showModal();
        <?php endif; ?>
    </script>
</body>
</html>
