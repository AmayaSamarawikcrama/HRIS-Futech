<?php
session_start(); // Start session

$host = "localhost";
$dbname = "hris_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM UserLogin WHERE Username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and password matches
    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id'] = $user['Emp_ID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Role'];

        // Redirect based on username prefix
        if (strpos($username, 'E') === 0) {
            header("Location: ../frontend/employee_dashboard.html");
        } elseif (strpos($username, 'MN') === 0) {
            header("Location: ../frontend/manager_dashboard.html");
        } elseif (strpos($username, 'HR') === 0) {
            header("Location: ../frontend/hr_dashboard.html");
        } else {
            echo "Invalid user type!";
        }
        exit();
    } else {
        echo "Invalid username or password!";
    }
}
?>
