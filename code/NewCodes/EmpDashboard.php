<?php
// Start the session if not already started
session_start();

// Check if user is logged in and retrieve user data
if (isset($_SESSION['user_id'])) {
    // Connect to database
    $conn = new mysqli('localhost', 'root', '', 'hris_db');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Fetch user data based on employee ID
    $stmt = $conn->prepare("SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        // Fallback if employee not found
        $user_data = [
            'First_Name' => 'Unknown',
            'Last_Name' => 'Employee'
        ];
    }
    
    $stmt->close();
    $conn->close();
} else {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
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
                    <a href="EmpProfile.php" class="dropdown-item">My Profile</a>
                    <a href="EmpLeave.php" class="dropdown-item">Leave Request</a>
                    <a href="Attendance.php" class="dropdown-item">My Attendance</a>
                    <a href="EmpProject.php" class="dropdown-item">Projects</a>
                    <a href="EmpSalary.php" class="dropdown-item">Salary Status</a>
                    <!-- <a href="Report.php" class="dropdown-item">Report</a> -->
                    <a href="Company.php" class="dropdown-item">Company Details</a>
                    <a href="EmpCalender.php" class="dropdown-item">Calendar</a>
                </div>
                
                <!-- Added employee name display here -->
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
               
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='EmpProfile.php'">
            </div>
        </nav>

        <div class="container-fluid mt-4 text-center">
            <img src="assets/EmpDash.jpg" alt="Dashboard Overview" class="img-fluid w-100" style="height: auto;">
        </div>

        <div class="row mt-4 g-2">
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpProfile.php'">My Profile</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpLeave.php'">Leave Request</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Attendance.php'">My Attendance</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpProject.php'">Our Projects</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpSalary.php'">Salary Status</button>
            </div>
            <!-- <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Report.php'">Report</button>
            </div> -->
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpCompany.php'">Company Details</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpCalender.php'">Calendar</button>
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