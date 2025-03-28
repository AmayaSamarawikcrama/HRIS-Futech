<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'hris_db';
$username = 'root';
$password = '';

// Create mysqli connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch employee details based on the logged-in user
$user_id = $_SESSION['user_id'];

// Use prepared statement for secure query
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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="hr_Dashboard.css">
    <style>
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f4f4f4;
        }
        .employee-name {
            margin-left: auto;
            padding-right: 20px;
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <div class="menu-icon">&#9776;</div>
            <span class="employee-name">
                <?php 
                    // Display the logged-in user's full name
                    echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                ?>
            </span>
            <button onclick="logout()" class="logout">Log Out</button>
        </nav>
        <div class="dashboard">
            <div class="card">Total Employee</div>
            <div class="card">Present Today</div>
            <div class="card">Total Absent</div>
            <div class="card">On Leave Today</div>
        </div>
        <div class="buttons">
            <button onclick="location.href='View_Employee.php'">Employee Details</button>
            <button onclick="window.location.href = 'Add_Employee.php';">Add Employee</button>
            <button>Project</button>
            <button onclick="location.href='Attendance.php'">Attendance</button>
            <button onclick="location.href='Leave.php'">Leave State</button>
            <button onclick="location.href='Salary.php'">Salary Status</button>
            <button>Employee Performance</button>
            <button>Report</button>
            <button onclick="location.href='Company.html'">Company</button>
            <button onclick="location.href='Calendar.html'">Calendar</button>
        </div>
    </div>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>

