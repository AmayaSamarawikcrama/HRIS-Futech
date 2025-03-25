<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris_db";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get logged-in user's ID from session
$user_id = $_SESSION['user_id'];

// 🔹 Fetch Emp_ID from `userlogin` table
$stmt = $conn->prepare("SELECT Emp_ID FROM userlogin WHERE User_ID = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $emp_id = $row['Emp_ID'];
} else {
    die("<p style='color:red;'>Error: Employee details not found in userlogin table.</p>");
}

// 🔹 Fetch Employee Details based on the database schema
$stmt = $conn->prepare("
    SELECT e.First_Name, e.Last_Name, e.Email, j.Job_Title, d.Dept_Name 
    FROM Employee e
    LEFT JOIN EmployeeJob ej ON e.Emp_ID = ej.Emp_ID
    LEFT JOIN JobProfile j ON ej.Job_ID = j.Job_ID
    LEFT JOIN Department d ON e.Department_ID = d.Dept_ID
    WHERE e.Emp_ID = ?
");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
} else {
    die("<p style='color:red;'>Error: Employee details not found in Employee table.</p>");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile</title>
    <link rel="stylesheet" href="CSS/EmpProfile.css">
</head>
<body>
    <nav class="navbar">
        <button onclick="logout()" class="logout">Log Out</button>
        <img class="profile" src="profile.png" alt="Employee Profile" width="40px" height="40px">
    </nav>
    
    <div class="container">
        <div class="sidebar">
            <h2>Profile</h2>
            <img class="profile-pic" src="profile.png" alt="Employee Picture">
        </div>
        <main class="content">
            <h2>Employee Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($employee['First_Name'] . ' ' . $employee['Last_Name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($employee['Email']); ?></p>
            <p><strong>Position:</strong> <?php echo htmlspecialchars($employee['Job_Title'] ?? 'Not Assigned'); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($employee['Dept_Name'] ?? 'Not Assigned'); ?></p>
        </main>
    </div>
    
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>