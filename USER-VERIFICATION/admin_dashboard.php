<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Initialize the SQL queries and add error handling
$sql_total_users = "SELECT COUNT(*) as total_users FROM users";
$total_users_result = $conn->query($sql_total_users);
if ($total_users_result) {
    $total_users = $total_users_result->fetch_assoc()['total_users'] ?? 0;
} else {
    echo "Error fetching total users: " . $conn->error;
    $total_users = 0; // Default to 0 if query fails
}

$sql_new_users = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= NOW() - INTERVAL 30 DAY";
$new_users_result = $conn->query($sql_new_users);
if ($new_users_result) {
    $new_users = $new_users_result->fetch_assoc()['new_users'] ?? 0;
} else {
    echo "Error fetching new users: " . $conn->error;
    $new_users = 0;
}

$sql_today_requests = "SELECT COUNT(*) as today_requests FROM request WHERE DATE(request_date) = CURDATE()";
$today_requests_result = $conn->query($sql_today_requests);
if ($today_requests_result) {
    $today_requests = $today_requests_result->fetch_assoc()['today_requests'] ?? 0;
} else {
    echo "Error fetching today's requests: " . $conn->error;
    $today_requests = 0;
}

$sql_today_approved_requests = "SELECT COUNT(*) as approved_requests FROM handover_requests WHERE DATE(created_at) = CURDATE()";
$approved_requests_result = $conn->query($sql_today_approved_requests);
if ($approved_requests_result) {
    $approved_requests = $approved_requests_result->fetch_assoc()['approved_requests'] ?? 0;
} else {
    echo "Error fetching today's approved requests: " . $conn->error;
    $approved_requests = 0;
}

// Fetch blood counts for each type (positive and negative) with error handling
$blood_counts = [];
foreach (['A', 'B', 'AB', 'O'] as $type) {
    $sql_positive = "SELECT COUNT(*) as count FROM blood_inventory WHERE blood_type = '{$type}+'";
    $positive_result = $conn->query($sql_positive);
    $positive_count = $positive_result ? $positive_result->fetch_assoc()['count'] : 0;

    $sql_negative = "SELECT COUNT(*) as count FROM blood_inventory WHERE blood_type = '{$type}-'";
    $negative_result = $conn->query($sql_negative);
    $negative_count = $negative_result ? $negative_result->fetch_assoc()['count'] : 0;

    $blood_counts[$type] = [
        'positive' => $positive_count,
        'negative' => $negative_count
    ];

    // If there was an error with either query, output a message
    if (!$positive_result) {
        echo "Error fetching positive blood count for type {$type}: " . $conn->error;
    }
    if (!$negative_result) {
        echo "Error fetching negative blood count for type {$type}: " . $conn->error;
    }
}

// Close the database connection
$conn->close();


// Handle the forecast generation request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input for forecast steps, ensuring it's an integer
    $forecast_steps = intval($_POST['forecast_steps']);

    // Define paths for Python and the forecast script
    $python_path = "C:\\Users\\acer\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    $script_path = realpath("C:\\xampp\\htdocs\\Serving Hearts\\Predictor\\forecast_script.py"); // Absolute path to forecast script

    // Validate paths to Python and script
    if (!$script_path) {
        echo "<p>Error: Python script not found at the specified path.</p>";
        exit;
    }
    if (!file_exists($python_path)) {
        echo "<p>Error: Python executable not found at the specified path.</p>";
        exit;
    }

    // Prepare and run the Python command with forecast steps
    $command = escapeshellcmd("\"$python_path\" \"$script_path\" " . escapeshellarg($forecast_steps));
    exec($command . " 2>&1", $output, $status);


    // Handle the forecast generation request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get user input for forecast steps, ensuring it's an integer
        $forecast_steps = intval($_POST['forecast_steps']);

        // Define paths for Python and the forecast script
        $python_path = "C:\\Users\\Acer\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
        $script_path = realpath("C:\\XAMPP\\htdocs\\Serving Hearts\\Predictions\\forecast_script.py"); // Absolute path to forecast script

        // Validate paths to Python and script
        if (!$script_path) {
            echo "<p>Error: Python script not found at the specified path.</p>";
            exit;
        }
        if (!file_exists($python_path)) {
            echo "<p>Error: Python executable not found at the specified path.</p>";
            exit;
        }

        // Prepare and run the Python command with forecast steps
        $command = escapeshellcmd("\"$python_path\" \"$script_path\" " . escapeshellarg($forecast_steps));
        exec($command . " 2>&1", $output, $status);

        // If the status is successful, the message will be shown.
        // You can now directly display the result message
        if ($status === 0) {
            $message = "<div id='successMessage' class='alert alert-success'>Forecast generated successfully!</div>";
        } else {
            $message = "<div id='errorMessage' class='alert alert-danger'>Error generating forecast. Status Code: $status</div>";
            $message .= "<pre>Debug Output:\n" . htmlspecialchars(implode("\n", $output)) . "</pre>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <title>Pending Blood Requests</title>
    <style>

    /* Main dashboard layout */
    .dashboard-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        max-width: 1200px;
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        gap: 20px;
        margin-top: 20px;
        margin-left: 19rem;
    }

    /* Left side: User Data Boxes in horizontal row */
    .box-container {
        display: flex;
        justify-content: space-around;
        gap: 20px;
        width: 100%;
    }

    .box {
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        padding: 35px;
        text-align: center;
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    .icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 25px;
            color: #d9534f;
            margin-bottom: 8px;
    }

    /* Graph container below user data and blood type count sections */
    .graph {
        width: 100%;
        max-width: 700px;
        height: 450px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        padding: 20px;
        text-align: center;
    }

    /* Right side: Blood Type Counts in a horizontal row */
    .right-box-container {
        display: flex;
        justify-content: space-around;
        gap: 20px;
        width: 100%;
    }

    .right-box {
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        padding: 20px;
        text-align: center;
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .label, .count, .sub {
        margin-bottom: 8px;
        font-size: 20px;
    }

    .positive {
            color: red; /* Positive blood type in red */
            font-weight: bold;
    }

        .negative {
            color: blue; /* Negative blood type in blue */
            font-weight: bold;
        }
        .fa-square-plus {
            color: red;
        }
        .fa-square-minus {
            color: blue;
        }
</style>

</head>
<body>
    <div class="dashboard-container">
        
        <!-- Left side: User Data Boxes -->
        <div class="box-container">
            <div class="box">
                <div class="label">Total User Count:</div>
                <i class="fa-solid fa-users icon"></i>
                <div class="count"><?php echo $total_users; ?></div>
                <div class="sub">As of Today</div>
            </div>
            <div class="box">
                <div class="label">New Users:</div>
                <i class="fa-solid fa-user-plus icon"></i>
                <div class="count"><?php echo $new_users; ?></div>
                <div class="sub">30 Days Interval</div>
            </div>
            <div class="box">
                <div class="label">Today's Requests:</div>
                <i class="fa-solid fa-file-alt icon"></i>
                <div class="count"><?php echo $today_requests; ?></div>
                <div class="sub">As of Today</div>
            </div>
            <div class="box">
                <div class="label">Today's Handed Over:</div>
                <i class="fa-solid fa-check-circle icon"></i>
                <div class="count"><?php echo $approved_requests; ?></div>
            </div>
        </div>
        
        <div class="right-box-container">
            <div class="right-box">
                <div class="label">Type A <i class="fa-solid fa-square-plus"></i> <i class="fa-solid fa-square-minus"></i></span></div>
                <div class="count">
                    <span class="positive"><?php echo $blood_counts['A']['positive']; ?></span> |
                    <span class="negative"><?php echo $blood_counts['A']['negative']; ?></span>
                </div>
            </div>
            <div class="right-box">
                <div class="label">Type B <i class="fa-solid fa-square-plus"></i> <i class="fa-solid fa-square-minus"></i></div>
                <div class="count">
                    <span class="positive"><?php echo $blood_counts['B']['positive']; ?></span> |
                    <span class="negative"><?php echo $blood_counts['B']['negative']; ?></span>
                </div>
            </div>
            <div class="right-box">
                <div class="label">Type AB <i class="fa-solid fa-square-plus"></i> <i class="fa-solid fa-square-minus"></i></div>
                <div class="count">
                    <span class="positive"><?php echo $blood_counts['AB']['positive']; ?></span> |
                    <span class="negative"><?php echo $blood_counts['AB']['negative']; ?></span>
                </div>
            </div>
            <div class="right-box">
                <div class="label">Type O <i class="fa-solid fa-square-plus"></i> <i class="fa-solid fa-square-minus"></i></div>
                <div class="count">
                    <span class="positive"><?php echo $blood_counts['O']['positive']; ?></span> |
                    <span class="negative"><?php echo $blood_counts['O']['negative']; ?></span>
                </div>
            </div>
        </div>

        <!-- Center: Graph Container (between left and right boxes) -->
        <div class="graph">
            <div class="label">Blood Request Prediction</div>
            <img src="../Predictions/graphs/blood_handover_forecast.png?timestamp=<?php echo time(); ?>" alt="Blood Handover Forecast Graph">
            
            <!-- Forecast Form -->
            <div class="forecast-form">
                <form method="post">
                    <label for="forecast_steps">Enter Forecast Steps (Months):</label>
                    <input type="number" id="forecast_steps" name="forecast_steps" min="1" max="24" required>
                    <button type="submit">Generate Forecast</button>
                </form>
            </div>

            <!-- Forecast result message container (initially hidden) -->
            <div class="result-container" id="forecastResultMessage" style="display: none; margin-top: 20px;">
                <?php
                if (isset($message)) {
                    echo $message; // Display the generated message
                }
                ?>
            </div>
        </div>
        </div>

</body>
</html>

<script>
    // Show the forecast result message and hide it after 2 seconds
    window.onload = function() {
        var resultMessage = document.getElementById('forecastResultMessage');
        if (resultMessage.innerHTML.trim() !== "") {
            resultMessage.style.display = 'block';
            setTimeout(function() {
                resultMessage.style.display = 'none';
            }, 2000);  // 2 seconds timeout
        }
    };
</script>