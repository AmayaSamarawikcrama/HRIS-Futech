<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hris_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $leave_id = $_POST['leave_id'];
    $action = $_POST['action'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        
        $stmt = $conn->prepare("UPDATE Leave_Management SET Approval_Status = ? WHERE Leave_ID = ?");
        $stmt->bind_param("si", $status, $leave_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Leave request has been $status!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating leave: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        $stmt->close();
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all leave requests
$leave_requests = [];
$query = "SELECT l.*, e.First_Name, e.Last_Name 
          FROM Leave_Management l
          JOIN Employee e ON l.Employee_ID = e.Employee_ID
          ORDER BY l.Start_Date DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_requests[] = $row;
    }
}

// Get current user data
$user_data = ['First_Name' => 'Unknown', 'Last_Name' => 'User'];
$stmt = $conn->prepare("SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - HR Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .menu-icon { cursor: pointer; color: #0d6efd; font-size: 25px; }
        .dropdown-item:hover { background-color: #e9ecef; }
        .badge-pending { background-color: #ffc107; color: #212529; }
        .badge-approved { background-color: #198754; }
        .badge-rejected { background-color: #dc3545; }
        .status-icon { font-size: 1.2rem; margin-right: 5px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.html" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.html" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.html" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.html" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.html" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.html" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.html" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
            </div>
        </nav>

        <main class="col-md-12 col-lg-12 px-5">
            <h2 class="my-4">Leave Management</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Leave Requests</h5>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leave_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['First_Name'] . ' ' . $request['Last_Name']); ?></td>
                                <td><?php echo htmlspecialchars($request['Leave_Type']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($request['Start_Date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($request['End_Date'])); ?></td>
                                <td>
                                    <?php 
                                        $badge_class = '';
                                        switch($request['Approval_Status']) {
                                            case 'Approved': $badge_class = 'badge-approved'; break;
                                            case 'Rejected': $badge_class = 'badge-rejected'; break;
                                            default: $badge_class = 'badge-pending';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($request['Approval_Status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($request['Approval_Status'] === 'Pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="leave_id" value="<?php echo $request['Leave_ID']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="leave_id" value="<?php echo $request['Leave_ID']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="viewDetails(
                                                '<?php echo htmlspecialchars($request['First_Name'] . ' ' . $request['Last_Name']); ?>',
                                                '<?php echo htmlspecialchars($request['Leave_Type']); ?>',
                                                '<?php echo date('M d, Y', strtotime($request['Start_Date'])); ?>',
                                                '<?php echo date('M d, Y', strtotime($request['End_Date'])); ?>',
                                                '<?php echo htmlspecialchars($request['Leave_Reason']); ?>'
                                            )">
                                        View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Leave Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Details will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleMenu() {
        const menuList = document.getElementById('menu-list');
        menuList.classList.toggle('d-none');
    }
    
    function logout() {
        window.location.href = 'logout.php';
    }

    function viewDetails(name, leaveType, startDate, endDate, reason) {
        const details = `
            <div>
                <p><strong>Employee:</strong> ${name}</p>
                <p><strong>Type:</strong> ${leaveType}</p>
                <p><strong>Dates:</strong> ${startDate} to ${endDate}</p>
                <p><strong>Reason:</strong> ${reason}</p>
            </div>
        `;
        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        document.getElementById('modalBody').innerHTML = details;
        modal.show();
    }
</script>
</body>
</html>