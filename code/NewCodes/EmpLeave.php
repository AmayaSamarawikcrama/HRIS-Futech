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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_SESSION['user_id'];
    $leave_type = filter_input(INPUT_POST, 'leave_type', FILTER_SANITIZE_STRING);
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $leave_reason = filter_input(INPUT_POST, 'leave_reason', FILTER_SANITIZE_STRING);
    $duty_covering = filter_input(INPUT_POST, 'duty_covering', FILTER_SANITIZE_STRING);

    // Validate dates
    if (strtotime($end_date) <= strtotime($start_date)) {
        $_SESSION['error_message'] = "End date must be after start date";
    } else {
        // Insert new leave request
        $stmt = $conn->prepare("INSERT INTO Leave_Management (Employee_ID, Leave_Type, Start_Date, End_Date, Leave_Reason, Duty_Covering) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $employee_id, $leave_type, $start_date, $end_date, $leave_reason, $duty_covering);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Leave request submitted successfully!";
        } else {
            $_SESSION['error_message'] = "Error submitting leave request: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch employee's leave requests
$leave_requests = [];
$stmt = $conn->prepare("SELECT * FROM Leave_Management WHERE Employee_ID = ? ORDER BY Start_Date DESC");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_requests[] = $row;
    }
}
$stmt->close();

// Fetch employee name for display
$employee_name = array('First_Name' => '', 'Last_Name' => '');
$stmt = $conn->prepare("SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $employee_name = $result->fetch_assoc();
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Leave Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .dropdown-item:hover {
            background-color: #e9ecef;
        }
        .leave-card {
            transition: all 0.3s ease;
        }
        .leave-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-approved {
            background-color: #198754;
        }
        .badge-rejected {
            background-color: #dc3545;
        }
        .status-icon {
            font-size: 1.2rem;
            margin-right: 5px;
        }
        .user-welcome {
            font-size: 1.2rem;
            margin-right: 15px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
        <div class="container-fluid">
            <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
            <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                <a href="EmpDashboard.php" class="dropdown-item">Dashboard</a>
                <a href="EmpProfile.php" class="dropdown-item">My Profile</a>
                <a href="EmpLeave.php" class="dropdown-item">Leave Request</a>
                <a href="EmpManualAttendance.php" class="dropdown-item">My Attendance</a>
                <a href="EmpProject.php" class="dropdown-item">Projects</a>
                <a href="EmpSalary.php" class="dropdown-item">Salary Status</a>
                <a href="Report.php" class="dropdown-item">Report</a>
                <a href="Company.php" class="dropdown-item">Company Details</a>
                <a href="Calendar.php" class="dropdown-item">Calendar</a>
            </div>
            <!-- Add the welcome message here -->
            <span class="user-welcome ms-auto"><?php echo isset($employee_name['First_Name']) ? htmlspecialchars($employee_name['First_Name'] . ' ' . $employee_name['Last_Name']) : 'User'; ?></span>
            <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
        </div>
    </nav>

    <main class="container-fluid px-4 py-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-calendar-plus me-2"></i>Leave Request Form</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="employeeName" class="form-label">Employee Name</label>
                                <input type="text" class="form-control" id="employeeName" 
                                       value="<?php echo isset($employee_name['First_Name']) ? htmlspecialchars($employee_name['First_Name'] . ' ' . $employee_name['Last_Name']) : 'User not found'; ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="leaveType" class="form-label">Leave Type</label>
                                <select class="form-select" id="leaveType" name="leave_type" required>
                                    <option value="Sick Leave">Sick Leave</option>
                                    <option value="Casual Leave">Casual Leave</option>
                                    <option value="Annual Leave">Annual Leave</option>
                                    <option value="Maternity Leave">Maternity Leave</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="startDate" name="start_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="endDate" name="end_date" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="dutyCovering" class="form-label">Duty Covering Person</label>
                                <input type="text" class="form-control" id="dutyCovering" name="duty_covering" placeholder="Who will cover your duties?">
                            </div>

                            <div class="mb-3">
                                <label for="leaveReason" class="form-label">Reason for Leave</label>
                                <textarea class="form-control" id="leaveReason" name="leave_reason" rows="3" placeholder="Enter the reason for leave" required></textarea>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Leave Policy</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Sick Leave: 14 days/year</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Casual Leave: 7 days/year</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Annual Leave: 21 days/year</li>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i>Maternity Leave: 84 days</li>
                            <li class="list-group-item"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Submit requests at least 3 days in advance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title mb-0"><i class="fas fa-history me-2"></i>My Leave History</h3>
            </div>
            <div class="card-body">
                <?php if (empty($leave_requests)): ?>
                    <div class="alert alert-info">No leave requests found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leave_requests as $request): ?>
                                    <tr class="leave-card align-middle">
                                        <td><?php echo htmlspecialchars($request['Leave_Type']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($request['Start_Date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($request['End_Date'])); ?></td>
                                        <td>
                                            <?php 
                                                $start = new DateTime($request['Start_Date']);
                                                $end = new DateTime($request['End_Date']);
                                                $interval = $start->diff($end);
                                                echo $interval->days + 1 . ' days';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $badge_class = '';
                                                $icon_class = '';
                                                switch($request['Approval_Status']) {
                                                    case 'Approved':
                                                        $badge_class = 'badge-approved';
                                                        $icon_class = 'fa-check-circle';
                                                        break;
                                                    case 'Rejected':
                                                        $badge_class = 'badge-rejected';
                                                        $icon_class = 'fa-times-circle';
                                                        break;
                                                    default:
                                                        $badge_class = 'badge-pending';
                                                        $icon_class = 'fa-clock';
                                                }
                                            ?>
                                            <span class="badge rounded-pill <?php echo $badge_class; ?>">
                                                <i class="fas <?php echo $icon_class; ?> status-icon"></i>
                                                <?php echo htmlspecialchars($request['Approval_Status'] ?? 'Pending'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $request['Leave_ID']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Details Modal -->
                                    <div class="modal fade" id="detailsModal<?php echo $request['Leave_ID']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="detailsModalLabel">Leave Request Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <h6>Leave Type</h6>
                                                        <p><?php echo htmlspecialchars($request['Leave_Type']); ?></p>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <h6>Start Date</h6>
                                                            <p><?php echo date('M d, Y', strtotime($request['Start_Date'])); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>End Date</h6>
                                                            <p><?php echo date('M d, Y', strtotime($request['End_Date'])); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Duration</h6>
                                                        <p>
                                                            <?php 
                                                                $start = new DateTime($request['Start_Date']);
                                                                $end = new DateTime($request['End_Date']);
                                                                $interval = $start->diff($end);
                                                                echo $interval->days + 1 . ' days';
                                                            ?>
                                                        </p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Status</h6>
                                                        <span class="badge rounded-pill <?php echo $badge_class; ?>">
                                                            <i class="fas <?php echo $icon_class; ?> status-icon"></i>
                                                            <?php echo htmlspecialchars($request['Approval_Status'] ?? 'Pending'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Duty Covering</h6>
                                                        <p><?php echo htmlspecialchars($request['Duty_Covering'] ?? 'Not specified'); ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Reason</h6>
                                                        <p><?php echo htmlspecialchars($request['Leave_Reason']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <?php if (($request['Approval_Status'] ?? 'Pending') === 'Pending'): ?>
                                                        <button type="button" class="btn btn-danger">Cancel Request</button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
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

    // Set minimum date for start date (today)
    document.getElementById('startDate').min = new Date().toISOString().split('T')[0];
    
    // Update end date min when start date changes
    document.getElementById('startDate').addEventListener('change', function() {
        document.getElementById('endDate').min = this.value;
    });
</script>
</body>
</html>