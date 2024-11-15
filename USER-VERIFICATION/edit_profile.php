<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

$message = ""; // For displaying success or error messages
$messageType = ""; // For message type (success/error)

// Fetch current user data from the database
$user_id = $_SESSION['id'];
$query = $conn->prepare("SELECT password, profile_picture, phonenumber, email, address FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Check if the profile picture exists
$profile_picture_path = !empty($user['profile_picture']) && file_exists($user['profile_picture']) 
    ? htmlspecialchars($user['profile_picture']) . '?' . time() 
    : '../USER-VERIFICATION/uploads/profile_picture/default-placeholder.png'; // Update with your default placeholder path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle password update
    if (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'] ?? null;
        $new_password = $_POST['new_password'] ?? null;
        $confirm_password = $_POST['confirm_password'] ?? null;

        // Check if the old password matches the current password
        if ($old_password && password_verify($old_password, $user['password'])) {
            // Check if the new passwords match
            if ($new_password && $new_password === $confirm_password) {
                // Hash the new password
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in the database
                $update_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_query->bind_param("si", $new_password_hashed, $user_id);

                // Execute password update
                if ($update_query->execute()) {
                    $message = "Password updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update password.";
                    $messageType = "error";
                }
            } elseif (!empty($new_password) || !empty($confirm_password)) {
                $message = "New passwords do not match.";
                $messageType = "error";
            }
        } elseif (!empty($old_password)) {
            $message = "Current password is incorrect.";
            $messageType = "error";
        }
    }

    // Handle profile picture update
    if (isset($_FILES['profile_picture'])) {
        $profile_picture = $_FILES['profile_picture'];

        // Path to the target directory for profile pictures
        $target_dir = "../USER-VERIFICATION/uploads/profile_picture/";

        // Check if a new file has been uploaded
        if ($profile_picture && $profile_picture["error"] === UPLOAD_ERR_OK) {
            // Delete old profile picture if it exists
            if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                unlink($user['profile_picture']); // Delete the old picture
            }
        
            // Set the target file name
            $fileName = pathinfo($profile_picture["name"], PATHINFO_FILENAME);
            $fileExtension = pathinfo($profile_picture["name"], PATHINFO_EXTENSION);
            $newFileName = $fileName . '_' . time() . '.' . $fileExtension; // Ensure unique file name
            $target_file = $target_dir . $newFileName; // New target file name
        
            // Move the uploaded file
            if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                // Update the profile picture path in the database
                $update_picture_query = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $update_picture_query->bind_param("si", $target_file, $user_id);
                if ($update_picture_query->execute()) {
                    $message .= " Profile picture updated successfully!";
                    $messageType = "success";
                } else {
                    $message .= " Failed to update profile picture in the database.";
                    $messageType = "error";
                }
            } else {
                $message .= " Failed to upload new profile picture.";
                $messageType = "error";
            }
        } else {
            // If no image is uploaded, set a message indicating no change
            $message .= " No changes were made to the profile picture.";
            $messageType = "success";
        }
    }

    // Handle contact info update
    if (isset($_POST['update_contact'])) {
        $new_phonenumber = $_POST['new_phonenumber'] ?? null;
        $new_email = $_POST['new_email'] ?? null;
        $new_address = $_POST['new_address'] ?? null;

        // Initialize an array to hold the updated values
        $updated_values = [];
        $field_updates = []; // To track the specific fields being updated
        $updated_fields = []; // To keep track of the field names that are updated

        // Check each field and only update if it's not empty
        if (!empty($new_phonenumber)) {
            $updated_values['phonenumber'] = $new_phonenumber;
            $field_updates[] = "phonenumber = ?";
            $updated_fields[] = "Phone Number"; // Add field name to updated fields
        }
        if (!empty($new_email)) {
            $updated_values['email'] = $new_email;
            $field_updates[] = "email = ?";
            $updated_fields[] = "Email"; // Add field name to updated fields
        }
        if (!empty($new_address)) {
            $updated_values['address'] = $new_address;
            $field_updates[] = "address = ?";
            $updated_fields[] = "Address"; // Add field name to updated fields
        }

        // If no fields are filled, set an error message
        if (empty($updated_values)) {
            $message = "No fields were filled to update.";
            $messageType = "error";
        } else {
            // Build the update query dynamically
            $update_query_str = "UPDATE users SET " . implode(', ', $field_updates) . " WHERE id = ?";
            $update_contact_query = $conn->prepare($update_query_str);

            // Prepare the parameters for binding
            $update_query_params = array_values($updated_values); // Get the new values
            $update_query_params[] = $user_id; // Add user_id to the parameters

            // Bind parameters dynamically
            $types = str_repeat('s', count($updated_values)) . 'i'; // 's' for strings and 'i' for integer (user_id)
            $update_contact_query->bind_param($types, ...$update_query_params);

            // Execute the update
            if ($update_contact_query->execute()) {
                // Create a message identifying the updated fields
                $updated_field_list = implode(', ', $updated_fields);
                $message = "Successfully updated the $updated_field_list field.";
                $messageType = "success";
            } else {
                $message = "Failed to update contact information.";
                $messageType = "error";
            }
        }
    }
}

// Fetch the updated user data including profile picture path
$query = $conn->prepare("SELECT profile_picture, phonenumber, email, address FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EBEBEB;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #EBEBEB;
            margin-left: 5rem;
            padding: 30px;
            width: 800px;
        }
        .form-container h1{
            font-size: 30px;
            margin-bottom: 30px;
            color: #333;
        }
        .form-container h2 {
            font-size: 20px;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            color: #555;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 17px;
        }
        .form-group input[type="file"],
        .form-group input[type="password"],
        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .image-preview-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            overflow: hidden;
            margin: 10px auto 20px;
            border: 2px solid #ddd;
        }
        .image-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .change-picture-container {
            flex-grow: 1;
            margin-left: 30px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between; /* Space between buttons */
        }
        .btn-submit {
            background-color: darkred;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: red;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Edit Profile</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>" style="display: block;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Picture Update Form -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group" style="display: flex; align-items: center;">
                <div class="image-preview-container" style="margin-right: 20px;">
                    <img src="<?php echo $profile_picture_path; ?>" alt="Profile Picture">
                </div>

                <div class="change-picture-container">
                    <label for="profile_picture">Change Profile Picture:</label>
                    <input type="file" name="profile_picture" accept="image/*">
                    <button type="submit" name="update_picture" class="btn-submit">Update Profile Picture</button>
                </div>
            </div>
        </form>

        <!-- Password Update Form -->
        <form method="POST">
            <h2>Update Password</h2>
            <div class="form-group">
                <label for="old_password">Current Password:</label>
                <input type="password" name="old_password" placeholder="Enter your current password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password">
            </div>
            <div class="btn-container">
                <button type="submit" name="update_password" class="btn-submit">Update Password</button>
            </div>
        </form>

        <!-- Contact Information Update Form -->
        <form method="POST">
            <h2>Update Contact Information</h2>
            <div class="form-group">
                <label for="new_phonenumber">Phone Number:</label>
                <input type="text" name="new_phonenumber">
            </div>
            <div class="form-group">
                <label for="new_email">Email:</label>
                <input type="email" name="new_email">
            </div>
            <div class="form-group">
                <label for="new_address">Address:</label>
                <input type="text" name="new_address">
            </div>
            <div class="btn-container">
                <button type="submit" name="update_contact" class="btn-submit">Update Contact Info</button>
            </div>
        </form>
    </div>
</body>
</html>

<script>
    // Image preview function
    function previewImage(event) {
        const imagePreview = document.getElementById('imagePreview');
        imagePreview.src = URL.createObjectURL(event.target.files[0]);
    }

    // Automatically hide message after 2 seconds
    const message = document.querySelector('.message');
    if (message) {
        setTimeout(() => {
            message.style.display = 'none';
        }, 2000);
    }
</script>
