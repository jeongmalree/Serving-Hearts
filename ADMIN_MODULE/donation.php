<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="donationstyle.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        
        .container {
            width: 78%;
            max-width: 1200px;
            margin-top: 100px;
            margin-left: 299px;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        .table th {
            background-color: #F08080;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        .table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        .table td {
            font-weight: 400;
            color: #555;
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
        
        .new-entry-btn {
            background-color: darkred;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            width: 100%;
            max-width: 9rem;
        }
        .new-entry-btn:hover{
            background-color: darkred;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header-flex">
        <h1>Blood Inventory</h1>
        <a href="manage_donation.php" id="new_blood_entry" class="new-entry-btn">
            <i class="fa fa-plus"></i> New Entry
        </a>
    </div>

    <div class="message-container" id="message-container">
        <p id="message-text"></p>
    </div>

    <table class="table" id="bloodInventoryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Unique Number</th>
                <th>Donor Name</th>
                <th>Blood Type</th>
                <th>Status</th>
                <th>Request UID</th>
                <th>Collection Date</th>
                <th>Expiration Date</th>
                <th>Volume (ml)</th>
                <th>Remarks</th>
                <th>Additives</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            $bloodInventory = $conn->query("SELECT * FROM blood_inventory ORDER BY expiration_date ASC");
            while($row = $bloodInventory->fetch_assoc()): 
            ?>
            <tr data-id="<?php echo $row['id']; ?>">
                <td><?php echo $i++ ?></td>
                <td><?php echo $row['unique_number']; ?></td>
                <td><?php echo $row['fullname']; ?></td>
                <td><?php echo $row['blood_type']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['request_uid']; ?></td>
                <td><?php echo date('M d, Y', strtotime($row['collection_date'])); ?></td>
                <td><?php echo date('M d, Y', strtotime($row['expiration_date'])); ?></td>
                <td><?php echo $row['volume']; ?></td>
                <td><?php echo $row['remarks']; ?></td>
                <td><?php echo $row['additives']; ?></td>
                <td>
                    <div class="action-btn-container">
                        <button class="action-btn edit-btn edit_blood" type="button" data-id="<?php echo $row['id']; ?>">Edit</button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Structure -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-close" id="closeModal">&times;</span>
            <h5 class="modal-title">Edit Blood Inventory</h5>
        </div>
        <div class="modal-body">
            <form id="editBloodForm">
                <input type="hidden" id="edit_id" name="id">

                <div id="message-container" style="display:none;">
                    <p id="message-text"></p>
                </div>
                
                <div class="mb-3">
                    <label for="edit_unique_number" class="form-label">Unique Number:</label>
                    <input type="text" class="form-control" id="edit_unique_number" name="unique_number" required>
                </div>

                <div class="mb-3">
                    <label for="edit_fullname" class="form-label">Donor Name:</label>
                    <input type="text" class="form-control" id="edit_fullname" name="fullname">
                </div>

                <div class="mb-3">
                    <label for="edit_blood_type" class="form-label">Blood Type:</label>
                    <select name="blood_type" id="edit_blood_type" class="form-control" required>
                        <option value="N/A" selected disabled>-I do not know my blood type-</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="edit_collection_date" class="form-label">Collection Date:</label>
                    <input type="datetime-local" class="form-control" id="edit_collection_date" name="collection_date" required>
                </div>

                <div class="mb-3">
                    <label for="edit_expiration_date" class="form-label">Expiration Date:</label>
                    <input type="date" class="form-control" id="edit_expiration_date" name="expiration_date" required readonly>
                </div>

                <div class="mb-3">
                    <label for="edit_volume" class="form-label">Volume (ml):</label>
                    <input type="number" class="form-control" id="edit_volume" name="volume" required>
                </div>

                <div class="mb-3">
                    <label for="edit_remarks" class="form-label">Remarks:</label>
                    <input type="text" class="form-control" id="edit_remarks" name="remarks"></input>
                </div>

                <div class="mb-3">
                    <label for="edit_additives" class="form-label">Additives:</label>
                    <select name="additives" id="edit_additives" class="form-control" required>
                        <option value="" selected disabled>-Select Additive-</option>
                        <option value="CDPA-1">CDPA-1</option>
                        <option value="AS-1">AS-1</option>
                        <option value="AS-3">AS-3</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="edit_status" class="form-label">Status:</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="in_stock">In Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const additiveSelect = document.getElementById("edit_additives");
                        const expirationDateInput = document.getElementById("edit_expiration_date");
                        const collectionDateInput = document.getElementById("edit_collection_date");

                        const expirationPeriods = {
                            'CDPA-1': 35,
                            'AS-1': 42,
                            'AS-3': 42,
                        };

                        // Function to calculate and set the expiration date
                        function calculateExpirationDate() {
                            const selectedAdditive = additiveSelect.value;
                            const collectionDate = new Date(collectionDateInput.value); // Get collection date from the input

                            if (expirationPeriods[selectedAdditive] && !isNaN(collectionDate)) {
                                let daysToExpire = expirationPeriods[selectedAdditive];
                                const expirationDate = new Date(collectionDate);
                                expirationDate.setDate(collectionDate.getDate() + daysToExpire);
                                expirationDateInput.value = expirationDate.toISOString().split('T')[0]; // Set value in 'YYYY-MM-DD'
                            } else {
                                expirationDateInput.value = ''; // Clear the expiration date if no additive is selected or collection date is invalid
                            }
                        }

                        // Listen for changes in the additive selection
                        additiveSelect.addEventListener("change", calculateExpirationDate);

                        // Listen for changes in the collection date input
                        collectionDateInput.addEventListener("change", calculateExpirationDate);
                    });
                </script>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable without sorting
    var table = $('#bloodInventoryTable').DataTable({
        ordering: false // Disable sorting
    });

    // Show modal and pre-fill fields when the edit button is clicked
    $(document).on('click', ".edit_blood", function() {
        var bloodId = $(this).data('id');

        // Use AJAX to fetch data and populate the form
        $.ajax({
            url: 'fetch_updateinventory.php', // Fetch record data by ID
            type: 'POST',
            data: { id: bloodId },
            success: function(response) {
                try {
                    var data = JSON.parse(response);

                    if (data.success) {
                        $('#edit_id').val(data.record.id);
                        $('#edit_unique_number').val(data.record.unique_number);
                        $('#edit_blood_type').val(data.record.blood_type);
                        $('#edit_fullname').val(data.record.fullname);

                        var collectionDate = new Date(data.record.collection_date);
                        var formattedCollectionDate = collectionDate.toISOString().slice(0, 19);
                        $('#edit_collection_date').val(formattedCollectionDate);

                        var expirationDate = new Date(data.record.expiration_date);
                        var formattedExpirationDate = expirationDate.toISOString().slice(0, 10);
                        $('#edit_expiration_date').val(formattedExpirationDate);

                        $('#edit_volume').val(data.record.volume);
                        $('#edit_remarks').val(data.record.remarks);
                        $('#edit_additives').val(data.record.additives);
                        $('#edit_status').val(data.record.status);

                        $('#editModal').fadeIn();
                    }
                } catch (e) {
                    console.error("Error parsing JSON response:", e);
                }
            }
        });
    });

    // Handle form submission
    $("#editBloodForm").submit(function(event) {
        event.preventDefault();

        const formData = new FormData(this);

        fetch('fetch_updateinventory.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageContainer = document.getElementById("message-container");
            const messageType = data.success ? 'success' : 'error';

            messageContainer.innerHTML = `<div class="message ${messageType}">${data.message}</div>`;
            messageContainer.style.display = 'block';

            // Hide the message after 1.2 seconds
            setTimeout(function() {
                messageContainer.style.display = 'none';

                // Reload the page after hiding the message
                location.reload();
            }, 1200);

            // Hide modal
            $('#editModal').fadeOut();
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });

    // Close modal when clicking the close button
    $("#closeModal").click(function() {
        $('#editModal').fadeOut();
    });
});

</script>

</body>
</html>
