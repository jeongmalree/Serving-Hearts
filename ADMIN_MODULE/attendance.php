<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        .container{
            width: 1100px;
            margin-left: 20rem;
            margin-top: 2rem;
        }
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            table-layout: auto;
            word-wrap: break-word;
        }
        .table th {
            background-color: #F08080;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge {
            padding: 5px 10px;
            color: white;
            border-radius: 5px;
            font-size: 0.9em;
            text-align: center;
        }
        .badge-consented { background-color: green; }
        .badge-not-consented { background-color: red; }
        .badge-processed { background-color: green; }
        .badge-not-processed { background-color: orange; }

        .new-entry-btn {
            background-color: darkred;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            width: 100%;
            max-width: 8.5rem;
        }
        .new-entry-btn:hover{
            background-color: darkred;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header-flex">
        <h1>List of Confirmed Attendance</h1>
        <a href="request.php" id="new_blood_entry" class="new-entry-btn">
            <i class="fa fa-plus"></i> New Entry
        </a>
    </div>

    <div id="message" class="message" style="display: none;"></div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event ID</th>
                    <th>Event Name</th>
                    <th>Event Address</th>
                    <th>Unique Number</th>
                    <th>Donor Name</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Check In Time</th>
                    <th>Consent</th>
                    <th>Blood Processed </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                // Query only rows with confirmed status
                $requestList = $conn->query("SELECT * FROM booking WHERE status = 'confirmed' ORDER BY booking_date DESC");
                while($row = $requestList->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['event_id']; ?></td>
                    <td><?php echo $row['event_name']; ?></td>
                    <td><?php echo $row['event_address']; ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['fullname']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['check_in_time'])); ?></td>
                    <td>
                        <?php if ($row['consent'] == 1): ?>
                            <span class="badge badge-consented">Consented</span>
                        <?php else: ?>
                            <span class="badge badge-not-consented">Not Consented</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['blood_processed'] == 1): ?>
                            <span class="badge badge-processed">Processed</span>
                        <?php else: ?>
                            <span class="badge badge-not-processed">Not Yet Processed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#bloodInventoryTable').DataTable({
        "ordering": false // Disable column sorting
    });
});
</script>

</body>
</html>