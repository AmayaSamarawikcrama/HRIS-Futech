<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Connection failed. Please check your database settings. " . $conn->connect_error);
}

// Process approval/rejection actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['leave_id'])) {
    $leave_id = intval($_POST['leave_id']);
    $status = $_POST['action'];
    
    // Validate status value
    if ($status === 'Approve') {
        $new_status = 'Approved';
    } elseif ($status === 'Reject') {
        $new_status = 'Rejected';
    } elseif ($status === 'Pending') {
        $new_status = 'Pending';
    } else {
        echo "<script>alert('Invalid action');</script>";
        exit;
    }
    
    // Update leave request status
    $stmt = $conn->prepare("UPDATE Leave_Management SET Approval_Status = ? WHERE Leave_ID = ?");
    $stmt->bind_param("si", $new_status, $leave_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Leave request has been " . strtolower($new_status) . "');
              window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Error updating record: " . $stmt->error . "');</script>";
    }
    
    $stmt->close();
}

// Fetch leave requests with employee details using JOIN
$sql = "SELECT l.Leave_ID, l.Employee_ID, CONCAT(e.First_Name, ' ', e.Last_Name) AS Name, 
        l.Leave_Type, l.Start_Date, l.End_Date, l.Leave_Reason, e.Contact_Number, 
        l.Duty_Covering, l.Approval_Status 
        FROM Leave_Management l
        JOIN Employee e ON l.Employee_ID = e.Employee_ID
        ORDER BY l.Start_Date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request Management</title>
    <link rel="stylesheet" href="Leave.css">
    <style>
        /* Additional CSS to style buttons based on status */
        .approved {
            background-color: #4CAF50;
            color: white;
        }
        .rejected {
            background-color: #f44336;
            color: white;
        }
        .pending {
            background-color: #ff9800;
            color: white;
        }
        
        /* Highlight row based on status */
        tr.approved-row {
            background-color: rgba(76, 175, 80, 0.1);
        }
        tr.rejected-row {
            background-color: rgba(244, 67, 54, 0.1);
        }
        tr.pending-row {
            background-color: rgba(255, 152, 0, 0.1);
        }
    </style>
</head>
<body>
    <nav>
        <div class="menu-icon">&#9776;</div>
        <button class="logout-btn">Log Out</button>
        <img class="profile" src="assets/profile.png" alt="Employee Details" width="40px" height="40px">
    </nav>
    
    <div class="container">
        <h2>Leave Request Management</h2>
        <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Contact Number</th>
                    <th>Duty Covering</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): 
                    $row_class = strtolower($row['Approval_Status']) . '-row';
                ?>
                <tr class="<?php echo $row_class; ?>">
                    <td><?php echo htmlspecialchars($row['Employee_ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Leave_Type']); ?></td>
                    <td><?php echo htmlspecialchars($row['Start_Date']); ?></td>
                    <td><?php echo htmlspecialchars($row['End_Date']); ?></td>
                    <td><?php echo htmlspecialchars($row['Leave_Reason']); ?></td>
                    <td><?php echo htmlspecialchars($row['Contact_Number']); ?></td>
                    <td><?php echo htmlspecialchars($row['Duty_Covering']); ?></td>
                    <td><?php echo htmlspecialchars($row['Approval_Status']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="leave_id" value="<?php echo $row['Leave_ID']; ?>">
                            <button type="submit" name="action" value="Approve" class="action <?php echo $row['Approval_Status'] == 'Approved' ? 'approved' : ''; ?>">Approve</button>
                            <button type="submit" name="action" value="Reject" class="action <?php echo $row['Approval_Status'] == 'Rejected' ? 'rejected' : ''; ?>">Reject</button>
                            <button type="submit" name="action" value="Pending" class="action <?php echo $row['Approval_Status'] == 'Pending' ? 'pending' : ''; ?>">Pending</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No leave requests found</p>
        <?php endif; ?>
    </div>

    <script>
        // Highlight the current status button
        document.addEventListener('DOMContentLoaded', function() {
        });
    </script>
</body>
</html>