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
    <title>Dash</title>
    <link rel="stylesheet" href="Dash.css">
</head>
<body>
    <div class="header">
        <button onclick="logout()" class="logout-button">Log Out</button>
        <img class="profile" src="assets/image.png" alt="Employee Details" width="40px" height="40px">
        <span class="employee-name">
            <?php 
                // Display the logged-in user's full name
                echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
            ?>
        </span>
    </div>

    <div class="dashboard">
        <div class="card">
            <div class="grid">
                <div class="button" onclick="EmpProfile()">
                    My Profile
                </div>
                <div class="button">
                    My Performance
                </div>
                <div class="button">
                    Reports
                </div>
                <div class="button">
                    Related Projects
                </div>
                <div class="button">
                    My Salary Status
                </div>
                <div class="button">
                    Company Details
                </div>
                <div class="button">
                    Leave Request
                </div>
                <div class="button">
                    My Attendance
                </div>
                <div class="button">
                    Calendar
                </div>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            window.location.href = "logout.php";
        }

        function EmpProfile() {
            window.location.href = "EmpProfile.php";
        }
    </script>
</body>
</html>
