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
    <title>Handed Over Requests</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1100px;
            margin-top: 80px;
            margin-left: 20rem;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }


        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
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

        .table th, .table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .table th {
            background-color: #F08080;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        #bloodInventoryTable_filter {
            margin-bottom: 15px;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            display: none;
        }
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
<body>

<div class="container">

    <div class="header-flex">
        <h1>List of Handed Over Requests</h1>
        <a href="manage_handover.php" id="new_blood_entry" class="new-entry-btn">
            <i class="fa fa-plus"></i> New Entry
        </a>
    </div>

    <div id="message" class="message" style="display: none;"></div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference Code</th>
                    <th>Requester</th>
                    <th>Donor</th>
                    <th>Unique Number</th>
                    <th>Patient Name</th>
                    <th>Hospital</th>
                    <th>Blood Type</th>
                    <th>Blood Component</th>
                    <th>Bags</th>
                    <th>Physician</th>
                    <th>Received By</th>
                    <th>Receive Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $requestList = $conn->query("SELECT * FROM handover_requests ORDER BY created_at DESC");
                while($row = $requestList->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['reference_code']; ?></td>
                    <td><?php echo $row['requester']; ?></td>
                    <td><?php echo $row['donor']; ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['patientname']; ?></td>
                    <td><?php echo $row['hospital']; ?></td>
                    <td><?php echo $row['bloodtype']; ?></td>
                    <td><?php echo $row['bloodcomponent']; ?></td>
                    <td><?php echo $row['bags']; ?></td>
                    <td><?php echo $row['physician']; ?></td>
                    <td><?php echo $row['received_by']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>            
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<script>
    $(document).ready(function() {
        // Initialize DataTable with ordering disabled
        $('#bloodInventoryTable').DataTable({
            "paging": true,
            "pageLength": 5,
            "lengthMenu": [5, 10, 25, 50, 100],
            "scrollX": true, // Enable horizontal scrolling in the DataTable
            "ordering": false // Disable column sorting
        });
    });
</script>
