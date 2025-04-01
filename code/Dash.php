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
    <link rel="stylesheet" href="Dash.css">
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
    <div class="header">
        <div class="profile-container">
            <img class="profile" src="assets/image.png" alt="Employee Details" width="40px" height="40px">
            <span class="employee-name">
                <?php 
                    echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                ?>
            </span>
            <button onclick="logout()" class="logout-button">Log Out</button>
        </div>
    </div>

    <div class="dashboard">
        <div class="card">
            <div class="grid">
                <div class="button" onclick="navigate('EmpProfile.php')">My Profile</div>
                <div class="button">My Performance</div>
                <div class="button">Reports</div>
                <div class="button">Related Projects</div>
                <div class="button">My Salary Status</div>
                <div class="button">Company Details</div>
                <div class="button" onclick="navigate('EmplLeave.php')">Leave Request</div>
                <div class="button">My Attendance</div>
                <div class="button">Calendar</div>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            window.location.href = "logout.php";
        }

        function navigate(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>
