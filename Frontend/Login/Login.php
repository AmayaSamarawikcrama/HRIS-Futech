<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="Login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="assets/logo.png" alt="Logo" class="logo" style="width: 80px; height: auto;">
            </div>
            <!-- Form for login -->
            <form action="" method="post">  <!-- Action points to the same page for processing -->
                <div class="input-group">
                    <div class="input-icon user-icon"></div>
                    <input type="text" name="username" placeholder="User name" class="input-field" required>
                </div>
                <div class="input-group">
                    <div class="input-icon lock-icon"></div>
                    <input type="password" name="password" placeholder="Password" class="input-field" required>
                </div>
                <div class="options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                <button type="submit" class="login-submit">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>

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
    // Sanitize inputs
    $username = htmlspecialchars($_POST['username']);
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
