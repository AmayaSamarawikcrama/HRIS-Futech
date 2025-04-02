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

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT e.*, d.Department_Name
    FROM Employee e
    LEFT JOIN Department d ON e.Department_ID = d.Department_ID
    WHERE e.Employee_ID = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$stmt->close();
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
                    <a href="Dashboard.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="View Employee.php" class="d-block text-decoration-none text-dark mb-2">View Employee</a>
                    <a href="Attendance.html" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="Project.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="Leave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="Salary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php
                        // Assuming $user_data is defined and contains user information
                        echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                    ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>

        <div class="container mt-4">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4 fs-5" style="height: 150px;"><b>Company Employees</b></div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4 fs-5" style="height: 150px;"><b>Present Today</b></div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4 fs-5" style="height: 150px;"><b>Absant Today</b></div>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <div class="card text-center p-4 fs-5" style="height: 150px;"><b>On Leave Today</b></div>
                </div>
            </div>
            </div>

            <div class="row mt-4 g-2">
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='View_Employee.php'">Employee Details</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Add_Employee.php'">Add Employee</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='project.php'">Project</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Attendance.html'">Attendance</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Leave.php'">Leave State</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Salary.php'">Salary Status</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Employee Performance.html'">Employee Performance</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Report.php'">Report</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Company.html'">Company</button>
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                    <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Calendar.html'">Calendar</button>
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
