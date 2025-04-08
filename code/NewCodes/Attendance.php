<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'hris_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data
$stmt = $conn->prepare("SELECT Employee_ID, First_Name, Last_Name FROM Employee WHERE Employee_ID = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    $_SESSION['user_data'] = $user_data; // Store all user data in session
} else {
    $_SESSION['user_data'] = [
        'Employee_ID' => $_SESSION['user_id'],
        'First_Name' => 'Unknown',
        'Last_Name' => 'Employee'
    ];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.html" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.html" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.html" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="Project.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="Leave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.html" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Calendar.html" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>

                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($_SESSION['user_data']['First_Name'] . ' ' . $_SESSION['user_data']['Last_Name']); ?>
                </span>
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
            </div>
        </nav>

        <div class="container mt-4 text-center">
            <div class="row g-4">
                <div class="col-md-6">
                    <img src="assets/manual attendance.JPG" alt="Attendance Image 1" class="img-fluid rounded">
                    <button class="btn btn-primary w-100 mt-3 p-3 fs-5" onclick="location.href='EmpManualAttendance.php'">Add Attendance Manually</button>
                </div>
                <div class="col-md-6">
                    <img src="assets/finger print.JPG" alt="Attendance Image 2" class="img-fluid rounded">
                    <button class="btn btn-primary w-100 mt-3 p-3 fs-5">Finger Print</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            const menuList = document.getElementById('menu-list');
            menuList.classList.toggle('d-none');
        }
        
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>