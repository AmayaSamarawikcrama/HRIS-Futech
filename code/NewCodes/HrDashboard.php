<?php
session_start();

$host = 'localhost';
$dbname = 'hris_db';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if($conn->connect_error)
{
    die("Connection failed: " . $conn->connect_error);
}

// if(!isset($_SESSION['user_id']))    
// {
//     hedder("Location: login.php");
//     exit();
// }

$user_id = $_SESSION['user_id'] ?? '';

// Get user data
if($user_id) {
    $stmt = $conn->prepare("
        SELECT e.*, d.Department_Name
        FROM Employee e
        LEFT JOIN Department d ON e.Department_ID = d.Department_ID
        WHERE e.Employee_ID = ?
    ");

    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
}

// Get employee count
$employee_count = 0;
$query = "SELECT COUNT(*) as total FROM Employee";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $employee_count = $row['total'];
}

// Get present today count
$present_count = 0;
$today = date('Y-m-d');
$query = "SELECT COUNT(DISTINCT Employee_ID) as present FROM Attendance WHERE Date = '$today'";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $present_count = $row['present'];
}

// Get absent count (all employees minus present)
$absent_count = $employee_count - $present_count;

// Get on leave count
$leave_count = 0;
$query = "SELECT COUNT(DISTINCT Employee_ID) as on_leave FROM Leave_Management 
          WHERE '$today' BETWEEN Start_Date AND End_Date 
          AND Approval_Status = 'Approved'";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $leave_count = $row['on_leave'];
}

// Adjust absent count (subtract those on approved leave)
$absent_count -= $leave_count;
if($absent_count < 0) $absent_count = 0;

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .employee-name {
            font-weight: bold;
            color: red;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .stat-label {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company</a>
                    <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php
                        // Displaying user name if available
                        if(isset($user_data)) {
                            echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                        }
                    ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>

        <div class="container mt-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4" style="height: 150px;">
                        <div class="stat-number"><?php echo $employee_count; ?></div>
                        <div class="stat-label">Company Employees</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4" style="height: 150px;">
                        <div class="stat-number"><?php echo $present_count; ?></div>
                        <div class="stat-label">Present Today</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4" style="height: 150px;">
                        <div class="stat-number"><?php echo $absent_count; ?></div>
                        <div class="stat-label">Absent Today</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4" style="height: 150px;">
                        <div class="stat-number"><?php echo $leave_count; ?></div>
                        <div class="stat-label">On Leave Today</div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 g-2">
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrEmployeeDetails.php'">Employee Details</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrAddEmployee.php'">Add Employee</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrProject.php'">Project</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Attendance.php'">Attendance</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrLeave.php'">Leave State</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrSalary.php'">Salary Status</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrProject.php'"> Our Projects</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Report.php'">Report</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Company.php'">Company</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='HrCalendar.php'">Calendar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>