<?php
// Check if session is already active before starting a new one
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['unique_number'])) {
    header('Location:../USER-VERIFICATION/index.php');
    exit();
}

include "../UI/sidebar.php";

// Get the logged-in user's unique number
$loggedInUserNumber = $_SESSION['unique_number'];

// Path to the ID card
$idCardPath = '../ID_GENERATOR/ids/' . $loggedInUserNumber . '_ID_Card.png';

// Check if the ID card exists
$idCardExists = file_exists($idCardPath);

// Fetch blood type from the database
$bloodType = "To be determined"; // Default value
$query = "SELECT blood_type FROM users WHERE unique_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserNumber);
$stmt->execute();
$stmt->bind_result($bloodType);
$stmt->fetch();
$stmt->close();

// Set bloodType to "To be determined" if it is NULL or empty
if (is_null($bloodType) || $bloodType === '') {
    $bloodType = "To be determined";
}

// Fetch confirmed bookings for the logged-in user
$bookingCount = 0; // Default value
$query = "SELECT COUNT(*) FROM booking WHERE unique_number = ? AND status = 'confirmed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserNumber);
$stmt->execute();
$stmt->bind_result($bookingCount);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../CSS/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EBEBEB;
            margin-left: 20rem;
            padding: 20px;
        }

        h1 {
            font-size: 2rem;
            margin-top: 5rem;
            color: #333;
        }

        .flex-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .id-card, .bloodtype, .milestone-info {
            width: 310px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
        }

        .id-card img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .bloodtype h2 {
            margin: 10px 0;
            color: DarkRed;
        }

        .bloodtype-display {
            font-size: 40px;
            font-weight: bold;
            color: #333;
        }

        .milestone-info h3 {
            color: #333;
            margin-bottom: 30px;
        }

        .milestone-info ul {
            padding-left: 0;
            list-style-type: none;
            margin: 0;
        }

        .milestone-info li {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .milestone-info .color-box {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 8px;
        }

        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .generate-button, .download-button {
            background-color: #28a745;
        }

        .generate-button:hover, .download-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <h1>Dashboard</h1>

    <div class="flex-container">
        <div class="id-card">
            <?php if ($idCardExists): ?>
                <img src="<?php echo $idCardPath; ?>" alt="ID Card">
            <?php else: ?>
                <p>No ID Card generated yet.</p>
            <?php endif; ?>
            <form action="../ID_GENERATOR/generate_id.php" method="get" target="_blank">
                <button type="submit" class="generate-button">Generate your Virtual ID</button>
            </form>
            <a href="<?php echo $idCardPath; ?>" download>
                <button class="download-button">Download your Virtual ID</button>
            </a>
        </div>

        <div class="milestone-info">
            <h3>ID Milestone Information</h3>
            <ul>
                <li><span class="color-box" style="background-color: #fcf047;"></span>0-3 bookings: First Tier (Yellow)</li>
                <li><span class="color-box" style="background-color: #ff9800;"></span>4-7 bookings: Second Tier (Orange)</li>
                <li><span class="color-box" style="background-color: #f44336;"></span>8-10 bookings: Third Tier (Red)</li>
                <li><span class="color-box" style="background-color: #4169E1;"></span>10+ bookings: Expert Level (Blue)</li>
            </ul>
        </div>

        <div class="bloodtype">
            <h2>Blood Type:</h2>
            <span class="bloodtype-display">
                <?php echo htmlspecialchars($bloodType); ?>
            </span>
        </div>
    </div>
</body>
</html>
