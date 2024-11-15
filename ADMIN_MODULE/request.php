<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update the request status in the database
    $updateQuery = $conn->prepare("UPDATE request SET status = ? WHERE id = ?");
    $updateQuery->bind_param('si', $status, $id);
    
    if ($updateQuery->execute()) {
        echo "Success";
    } else {
        echo "Error";
    }

    $updateQuery->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        /* General container styling */
.container {
    padding: 20px;
    max-width: 1100px;
    margin-left: 20rem;
    margin-top: 2rem;
    background-color: #f9f9f9;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

/* Header and button alignment */
.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* Table styles */
.table-wrapper {
    overflow-x: auto;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
.table th {
    background-color: #F08080;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tbody tr:hover {
    background-color: #f1f2f6;
}

/* Status badge styles */
.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 600;
}

.badge-success {
    background-color: #2ed573;
    color: white;
}

.badge-danger {
    background-color: #ff6b6b;
    color: white;
}

.badge-secondary {
    background-color: #B90E0A;
    color: white;
}

/* Button styles */
.action-btn-container {
    display: flex;
    gap: 10px;
}

.action-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 13px;
}

.approve-btn {
    background-color: #2ed573;
}

.approve-btn:hover {
    background-color: #1eae4d;
}

.reject-btn {
    background-color: #ff6b6b;
}

.reject-btn:hover {
    background-color: #e74c3c;
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
    background-color: rgba(0, 0, 0, 0.7);
    padding-top: 100px;
    justify-content: center;
    align-items: center;
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 80%;
    max-height: 80%;
    border-radius: 10px;
}

.close {
    position: absolute;
    top: 30px;
    right: 40px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #ff6b6b;
}

/* Message styling */
.message {
    display: none;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    font-size: 14px;
}

.success {
    background-color: #2ed573;
    color: white;
}

.error {
    background-color: #ff6b6b;
    color: white;
}

    </style>

</head>
<body>

<div class="container">

    <div class="header-flex">
        <h1>List of Requests</h1>
    </div>

    <div id="message" class="message" style="display: none;"></div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference Code</th>
                    <th>Requester</th>
                    <th>Group</th>
                    <th>Donor</th>
                    <th>Unique Number</th>
                    <th>SHCIM</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Ailments</th>
                    <th>Hospital</th>
                    <th>Blood Type</th>
                    <th>Blood Component</th>
                    <th>Bags</th>
                    <th>Physician</th>
                    <th>Contact Person</th>
                    <th>Contact Number</th>
                    <th>Messenger/Viber</th>
                    <th>Request Date</th>
                    <th>Original Request Form</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $requestList = $conn->query("SELECT * FROM request ORDER BY request_date DESC");
                while($row = $requestList->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['reference_code']; ?></td>
                    <td><?php echo $row['requester']; ?></td>
                    <td><?php echo $row['group']; ?></td>
                    <td><?php echo $row['donor']; ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['shcim']; ?></td>
                    <td><?php echo $row['patientname']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['dob'])); ?></td>
                    <td><?php echo $row['ailments']; ?></td>
                    <td><?php echo $row['hospital']; ?></td>
                    <td><?php echo $row['bloodtype']; ?></td>
                    <td><?php echo $row['bloodcomponent']; ?></td>
                    <td><?php echo $row['bags']; ?></td>
                    <td><?php echo $row['physician']; ?></td>
                    <td><?php echo $row['contactperson']; ?></td>
                    <td><?php echo $row['contactnum']; ?></td>
                    <td><?php echo $row['messviber']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['request_date'])); ?></td>
                    <td>
                        <?php if (!empty($row['image_path'])): ?>
                            <img src="<?php echo $row['image_path']; ?>" alt="Image" style="width:50px;cursor:pointer;" class="modal-trigger" data-src="<?php echo $row['image_path']; ?>">
                        <?php else: ?>
                            No image
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['status'] == 'Approved'): ?>
                            <span class="badge badge-success">Approved</span>
                        <?php elseif ($row['status'] == 'Rejected'): ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btn-container">
                            <button class="action-btn approve-btn" type="button" data-id="<?php echo $row['id']; ?>">Approve</button>
                            <?php // if ($row['status'] != 'Pending') echo 'disabled'; ?><!--Approve</button>-->
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add the modal container to display the full-size image -->
<div id="imageModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
    $(document).ready(function() {
    // Initialize DataTable once with all settings
    $('#bloodInventoryTable').DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100],
        "scrollX": true,  // Enable horizontal scrolling
        "ordering": false  // Disable sorting
    });

    // Modal image handling
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImage");
    var span = document.getElementsByClassName("close")[0];

    // When an image with class modal-trigger is clicked
    $(".modal-trigger").on('click', function() {
        var imgSrc = $(this).attr('data-src'); // Get the image path from data-src attribute
        modal.style.display = "block"; // Show the modal
        modalImg.src = imgSrc; // Set the clicked image as modal content
    });

    // Close the modal when the user clicks on the "x" button
    span.onclick = function() { 
        modal.style.display = "none";
    }

    // Close the modal when the user clicks anywhere outside of the image
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Approve button functionality
    $('.approve-btn').on('click', function() {
        var requestId = $(this).data('id');
        updateRequestStatus(requestId, 'Approved');
    });

    // Reject button functionality
    $('.reject-btn').on('click', function() {
        var requestId = $(this).data('id');
        updateRequestStatus(requestId, 'Rejected');
    });

    function updateRequestStatus(requestId, status) {
        $.ajax({
            url: 'request.php', // Ensure this URL is correct
            type: 'POST',
            data: {
                id: requestId,
                status: status
            },
            success: function(response) {
                // Set the message text and classes based on status
                if (status === 'Approved') {
                    $('#message').text('Request has been approved.')
                        .removeClass('error').addClass('success') // Add success class for green
                        .show();
                } else if (status === 'Rejected') {
                    $('#message').text('Request has been rejected.')
                        .removeClass('success').addClass('error') // Add error class for red
                        .show();
                }

                // Delay reload to allow the user to read the message
                setTimeout(function() {
                    location.reload(); // Reload the page after a delay
                }, 1000); // Adjust the time as needed (3000 ms = 3 seconds)
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error); // Log the error for debugging
                $('#message').text('An error occurred. Please try again.')
                    .removeClass('success').addClass('error') // Ensure error class is added
                    .show();
                
                // Delay reload to allow the user to read the message
                setTimeout(function() {
                    location.reload(); // Reload the page after a delay
                }, 1000); // Adjust the time as needed (3000 ms = 3 seconds)
            }
        });
    }
});

</script>

</body>
</html>
