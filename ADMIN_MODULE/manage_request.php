<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Fetch user info from the users table
$userId = $_SESSION['id'];
$sql = "SELECT fullname, unique_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement (user info): " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['fullname'];
    $uniqueNumber = $user['unique_number'];
} else {
    $name = '';
    $uniqueNumber = '';
}

$message = '';  // Variable to store success or error messages

function generateReferenceCode($length = 10) {
    return substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", $length)), 0, $length);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare and sanitize form data
    $requester = htmlspecialchars(trim($_POST['requester']));
    $group = htmlspecialchars(trim($_POST['group']));
    $donor = htmlspecialchars(trim($_POST['donor']));
    $unique_number = htmlspecialchars(trim($_POST['unique_number']));
    $shcim = htmlspecialchars(trim($_POST['shcim']));
    $patientname = htmlspecialchars(trim($_POST['patientname']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    $ailments = htmlspecialchars(trim($_POST['ailments']));
    $hospital = htmlspecialchars(trim($_POST['hospital']));
    $bloodtype = htmlspecialchars(trim($_POST['bloodtype']));
    $bloodcomponent = htmlspecialchars(trim($_POST['bloodcomponent']));
    $bags = filter_var(trim($_POST['bags']), FILTER_VALIDATE_INT);
    $physician = htmlspecialchars(trim($_POST['physician']));
    $contactperson = htmlspecialchars(trim($_POST['contactperson']));
    $contactnum = htmlspecialchars(trim($_POST['contactnum']));
    $messviber = htmlspecialchars(trim($_POST['messviber']));

    // Check if bags is valid
    if ($bags === false || $bags < 1) {
        $message = "Invalid number of bags.";
    } else {
        // Generate reference code
        $reference_code = generateReferenceCode();

        // Check for duplicate booking
        $checkSql = "SELECT * FROM request WHERE requester = ? AND hospital = ? AND patientname = ? AND dob = ?";
        $checkStmt = $conn->prepare($checkSql);
        if (!$checkStmt) {
            die("Error preparing statement (duplicate check): " . $conn->error);
        }
        $checkStmt->bind_param("ssss", $requester, $hospital, $patientname, $dob);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Duplicate booking detected! This request has already been submitted.";
        } else {
            // Handle the image upload
            $target_dir = "uploads/";
            $image_name = basename($_FILES["image"]["name"]);
            $target_file = $target_dir . time() . '_' . $image_name; // To avoid file name conflicts
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is an actual image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $message = "File is not an image.";
                $uploadOk = 0;
            }

            // Check file size (limit: 5MB)
            if ($_FILES["image"]["size"] > 5000000) {
                $message = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow only specific image formats
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                $message = "Sorry, only JPG, JPEG, and PNG files are allowed.";
                $uploadOk = 0;
            }

            // If everything is ok, try to upload file
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Updated SQL query with escaping `group` keyword
                    $sql = "INSERT INTO request 
                    (requester, `group`, donor, unique_number, shcim, patientname, dob, ailments, hospital, bloodtype, bloodcomponent, bags, physician, contactperson, contactnum, messviber, image_path, reference_code) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {
                    die("Error preparing statement (insert): " . $conn->error); // This should give you a more detailed error message now.
                    }

                    // Bind parameters
                    $stmt->bind_param("sssssssssssissssss", $requester, $group, $donor, $unique_number, $shcim, $patientname, $dob, $ailments, $hospital, $bloodtype, $bloodcomponent, $bags, $physician, $contactperson, $contactnum, $messviber, $target_file, $reference_code);

                    // Execute and check for success
                    if ($stmt->execute()) {
                        $message = "Request submitted successfully! Reference Code: $reference_code";
                    } else {
                        $message = "Error executing statement: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $message = "Sorry, there was an error uploading your file.";
                }
            }
        }

        $checkStmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Donation</title>
    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin-top: 100px;
            margin-left: 30rem;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
        }
        h3 {
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 18px;
            border-bottom: 1px solid #C92A2A;
            padding-bottom: 5px;
        }
        label {
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box; /* Ensures padding is included in width */
        }
        input[type="submit"] {
            background-color: #C92A2A;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%; /* Make the button full width */
            font-size: 16px;
        }
        .red-text {
            color: #C92A2A;
        }
        .reminder-label {
            margin: 10px 0;
            font-size: 13px;
        }
        .image-preview {
            width: 100%;
            height: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background-color: #f0f0f0;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain; 
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Blood Request Form</h2>

        <!-- Display success or error messages -->
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Duplicate booking detected') !== false ? 'error' : 'success' ?>" id="message">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <h3>Reminders</h3>
            <label class="reminder-label">
                <span class="red-text">PAUNAWA:</span> Pasagutan po lahat ng detalye at ihanda ang ORIGINAL na BLOOD REQUEST FORM <br> ng pasyente na dapat ay nasa bantay na tatangap ng dadaling dugo sa ospital.
                <br><br>
                <span class="red-text">PAUNAWA:</span> Ang dugo po ay LIBRE na ibabahagi at dadalin ng BLOOD  RIDER ng Serving Hearts Charity Inc. wala pong sinuman tao o grupo ang  maaring humingi ng kahit ano man kabayaran.
                <br><br>
                Pakiusap po na paki lagyan ng sagot lalo na ang numero ng tatangap sa dadalin na libreng dugo.
            </label>

            <h3>Request Information</h3>

            <label for="requester">Requester</label>
            <input type="text" name="requester" required>

            <label for="group">Group</label>
            <input type="text" name="group" required>

            <label for="donor">Donor</label>
            <input type="text" name="donor" value="<?= htmlspecialchars($name) ?>" readonly>

            <label for="unique_number">Unique Number</label>
            <input type="text" name="unique_number" value="<?= htmlspecialchars($uniqueNumber) ?>" readonly>

            <label for="shcim">SHCIM</label>
            <input type="text" name="shcim" value="Serving Heart Charity Inc Members" readonly>

            <h3>Patients Information</h3>

            <label for="patientname">Patient's Name</label>
            <input type="text" name="patientname" required>

            <label for="dob">Date of Birth</label>
            <input type="date" name="dob" required>

            <label for="ailments">Ailments</label>
            <input type="text" name="ailments" required>

            <label for="hospital">Hospital</label>
            <input type="text" name="hospital" required>

            <label for="bloodtype">Blood Type:</label>
            <select name="bloodtype" id="bloodtype" required>
                <option value="" disabled selected>Select Blood Type</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>

            <label for="bloodcomponent">Blood Component:</label>
            <select name="bloodcomponent" id="bloodcomponent" required>
                <option value="" disabled selected>Select Blood Component</option>
                <option value="rbc">RBC</option>
                <option value="plasma">Plasma</option>
                <option value="platelets">Platelets</option>
                <option value="cryoprecipitate">Cryoprecipitate</option>
            </select>

            <label for="bags">How many bags?</label>
            <input type="number" name="bags" required min="1" value="1">

            <label for="physician">Physician or Attending Physician</label>
            <input type="text" name="physician" required>

            <h3>Contact Person Information</h3>

            <label for="contactperson">Contact Person Name</label>
            <input type="text" name="contactperson" required>

            <label for="contactnum">Contact Number</label>
            <input type="text" name="contactnum" required>

            <label for="messviber">Messenger/Viber</label>
            <input type="text" name="messviber" required>

            <h3>Upload Original Request Form</h3>

            <label for="image">Request Form from Hospital</label>

            <label class="reminder-label">
                <span class="red-text">PAUNAWA:</span> Paki-upload ang maayos na larawan ng orihinal na request from mula sa ospital.
            </label>
            
            <input type="file" id="imageUpload" name="image" accept="image/*" required>
            <div class="image-preview" id="imagePreview">
                <p>No image selected</p>
            </div>

            <input type="submit" value="Submit Request Form">
        </form>
    </div>

    <script>
        const imageUpload = document.getElementById('imageUpload');
        const imagePreview = document.getElementById('imagePreview');

        imageUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                imagePreview.innerHTML = ''; // Clear previous preview
                reader.addEventListener('load', function() {
                    const img = document.createElement('img');
                    img.src = this.result;
                    imagePreview.appendChild(img);
                });
                reader.readAsDataURL(file);
            } else {
                imagePreview.innerHTML = '<p>No image selected</p>';
            }
        });

        // Hide the message after 2 seconds
        setTimeout(function() {
            const messageDiv = document.querySelector('.message');
            if (messageDiv) {
                messageDiv.style.display = 'none';
            }
        }, 2000);
    </script>
</body>
</html>