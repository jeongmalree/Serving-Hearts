<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/asidebar.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../USER-VERIFICATION/config/db.php');

$message = '';
    // Clear requestData on page load if accessed without a request reference code
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $_SESSION['requestData'] = null; // Clear request data on page load
    }

    // Initialize requestData if it's not set
    if (!isset($_SESSION['requestData'])) {
        $_SESSION['requestData'] = null;
    }

    // Check if the request reference code was submitted
    if (isset($_POST['requestref_code'])) {
        // Sanitize and validate the input
        $referenceCode = trim($_POST['requestref_code']);
        $referenceCode = htmlspecialchars($referenceCode, ENT_QUOTES, 'UTF-8');

        // Fetch the details from the request table, including checking the status
        $stmt = $conn->prepare("SELECT * FROM request WHERE reference_code = ? AND status = 'Approved'");
        $stmt->bind_param("s", $referenceCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $requestData = $result->fetch_assoc();

        // Check if the request was found and is approved
        if ($requestData) {
            // Now check if the reference has already been processed in the handover_requests table
            $checkProcessedStmt = $conn->prepare("SELECT * FROM handover_requests WHERE reference_code = ? AND received_by IS NOT NULL");
            $checkProcessedStmt->bind_param("s", $referenceCode);
            $checkProcessedStmt->execute();
            $checkProcessedResult = $checkProcessedStmt->get_result();

            if ($checkProcessedResult->num_rows > 0) {
                $_SESSION['requestData'] = null; // Clear request data
                $message = "This reference has already been processed."; 
                $messageType = 'error'; // Set message type to error
            } else {
                // If not processed, store request data in the session
                $_SESSION['requestData'] = $requestData; // Store in session
                $message = "Request details loaded successfully.";
            }
        } else {
            // If no request found or not approved, clear requestData and set error message
            $_SESSION['requestData'] = null; // Clear request data
            $message = "No approved request found with that reference code."; 
            $messageType = 'error'; // Set message type to error
        }
    }

// Insert into handover_requests if save button is clicked
if (isset($_POST['save']) && isset($_SESSION['requestData'])) {
    $requestData = $_SESSION['requestData'];
    $receivedBy = htmlspecialchars(trim($_POST['receive']), ENT_QUOTES, 'UTF-8');

    if (empty($requestData['patientname'])) {
        $message = "Error: Patient name is empty."; 
    } else {
        // Check for duplicate entry in handover_requests
        $checkStmt = $conn->prepare("SELECT * FROM handover_requests WHERE reference_code = ?");
        $checkStmt->bind_param("s", $requestData['reference_code']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Error: Duplicate entry for reference code."; 
            $messageType = 'error'; 
        } else {
            // Check if the request is approved
            $stmtRequest = $conn->prepare("SELECT * FROM request WHERE reference_code = ? AND status = 'Approved'");
            $stmtRequest->bind_param("s", $requestData['reference_code']);
            $stmtRequest->execute();
            $requestResult = $stmtRequest->get_result();

            if ($requestResult->num_rows === 0) {
                $message = "Error: Request is not approved or does not exist."; 
                $messageType = 'error'; 
            } else {
                // Check for availability in blood_inventory (FIFO)
                $checkBloodStmt = $conn->prepare(
                    "SELECT * FROM blood_inventory 
                     WHERE blood_type = ? AND status = 'in_stock' 
                     ORDER BY expiration_date ASC 
                     LIMIT 1"
                );
                $checkBloodStmt->bind_param("s", $requestData['bloodtype']);
                $checkBloodStmt->execute();
                $bloodResult = $checkBloodStmt->get_result();
                $bloodInventory = $bloodResult->fetch_assoc();

                if (!$bloodInventory) {
                    $message = "Error: No available blood for the requested blood type."; 
                    $messageType = 'error'; 
                } else {
                    // Update the chosen blood to out_of_stock and set request_uid to the unique_number
                    $updateBloodStmt = $conn->prepare(
                        "UPDATE blood_inventory SET status = 'out_of_stock', request_uid = ? WHERE id = ?"
                    );
                    $updateBloodStmt->bind_param("si", $requestData['unique_number'], $bloodInventory['id']);
                    $updateBloodStmt->execute();

                    if ($updateBloodStmt->affected_rows > 0) {
                        // Now, proceed with saving the handover request
                        $stmt = $conn->prepare("INSERT INTO handover_requests 
                            (reference_code, requester, donor, unique_number, patientname, bloodtype, bloodcomponent, bags, hospital, physician, received_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );

                        $stmt->bind_param(
                            "sssssssssss",
                            $requestData['reference_code'], 
                            $requestData['requester'],
                            $requestData['donor'],
                            $requestData['unique_number'],
                            $requestData['patientname'], 
                            $requestData['bloodtype'],
                            $requestData['bloodcomponent'],
                            $requestData['bags'],
                            $requestData['hospital'],
                            $requestData['physician'],
                            $receivedBy
                        );

                        if ($stmt->execute()) {
                            $message = "Handover request and blood status updated successfully!";
                            $_SESSION['requestData'] = null; // Clear the session data after successful processing
                        } else {
                            $message = "Error saving handover request: " . $stmt->error;
                        }
                    } else {
                        $message = "Error updating blood inventory status.";
                    }
                }
            }
        }
    }
}

// Check if requestData exists in the session
$requestData = $_SESSION['requestData'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handover</title>
    <style>
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
        h1, h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-size: 16px;
            display: block;
            margin: 10px;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box; 
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .receive {
            border-top: 1px solid #C92A2A;
            font-weight: bold;
        }
        .refcode {
            font-weight: bold;
        }
        /* General button styles */
        input[type="submit"] {
            padding: 8px 12px; /* Smaller padding for smaller buttons */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px; /* Smaller font size */
        }

        /* Button container to align buttons to the left */
        .button-container {
            display: flex; /* Use flexbox for layout */
            justify-content: flex-start; /* Align items to the left */
            gap: 10px; /* Space between buttons */
        }

        /* Save button */
        .save-button {
            background-color: #28a745; /* Green color */
            color: white; /* Text color */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        .save-button:hover {
            background-color: #218838; /* Darker green on hover */
        }

        /* Cancel button */
        .cancel-button {
            background-color: #787878; /* Red color */
            color: white; /* Text color */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        .cancel-button:hover {
            background-color: #4a4a4a; /* Darker red on hover */
        }

        .check-button {
            background-color: #bd2020; /* Green color */
            color: white; /* Text color */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        .check-button:hover {
            background-color: #961111; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="" method="POST">
            <h2>New Handover</h2>

            <?php if ($message): ?>
                <div class="message <?= isset($messageType) && $messageType === 'error' ? 'error' : 'success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="refcode">
                <label for="requestref_code">Request Reference Code: </label>
                <input type="text" name="requestref_code" value="<?= isset($_POST['requestref_code']) ? htmlspecialchars($_POST['requestref_code']) : '' ?>" required>
                <input type="submit" class="check-button" value="Check">
            </div>
        </form>

        <?php if ($requestData): ?>
            <form action="" method="POST">
                <label for="requester">Requester: <?= htmlspecialchars($requestData['requester']) ?></label>
                <label for="donor">Donor: <?= htmlspecialchars($requestData['donor']) ?></label>
                <label for="uniqueNumber">Unique Number: <?= htmlspecialchars($requestData['unique_number']) ?></label>
                <label for="patientname">Patient Name: <?= htmlspecialchars($requestData['patientname']) ?></label>
                <label for="bloodtype">Blood Type: <?= htmlspecialchars($requestData['bloodtype']) ?></label>
                <label for="bloodcomponent">Blood Component: <?= htmlspecialchars($requestData['bloodcomponent']) ?></label>
                <label for="volume">Volume: <?= htmlspecialchars($requestData['bags']) ?></label>
                <label for="hospital">How many bags: <?= htmlspecialchars($requestData['hospital']) ?></label>
                <label for="physician">Physician: <?= htmlspecialchars($requestData['physician']) ?></label>

                <div class="receive">
                    <label for="received_by">Received By:</label>
                    <input type="text" name="receive" required>
                </div>

                <div class="button-container">
                    <input type="submit" class="save-button" name="save" value="Save"> <!-- Changed name to "save" -->
                    <input type="submit" class="cancel-button" name="cancel" value="Cancel">
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

<script>
    // Check if there's a message and set a timeout to hide it after 1.5 seconds
    window.onload = function() {
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.display = 'none';
            }, 1500); // 1500 milliseconds = 1.5 seconds
        }
    };
</script>
