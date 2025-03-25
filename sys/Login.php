<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) 
    {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_COOKIE['auto_login_token']) && isset($_COOKIE['last_login_time']) && (time() - $_COOKIE['last_login_time']) <= 300) {
        $token = $_COOKIE['auto_login_token'];
    
        $stmt = $conn->prepare("SELECT Employee_ID, Password FROM Employee WHERE Token = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
    
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) 
        {
            $user_data = $result->fetch_assoc();
            $_SESSION['user_id'] = $user_data['Employee_ID'];
            redirectToDashboard($user_data['Employee_ID']);
        }
    }
    
    // Handle login form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']); // Emp_ID
        $password = trim($_POST['password']);
    
        if (!empty($username) && !empty($password)) {
            $stmt = $conn->prepare("SELECT Employee_ID, Password FROM Employee WHERE Employee_ID = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
    
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result && $result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                
                // Verify hashed password
                if (password_verify($password, $user_data['Password'])) {
                    $_SESSION['user_id'] = $user_data['Employee_ID'];
    
                    // Generate and store token for auto-login
                    $token = bin2hex(random_bytes(16));
                    $update_stmt = $conn->prepare("UPDATE Employee SET Token = ? WHERE Employee_ID = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("ss", $token, $user_data['Employee_ID']);
                        $update_stmt->execute();
                    }
    
                    setcookie('auto_login_token', $token, time() + 300, "/");
                    setcookie('last_login_time', time(), time() + 300, "/");
    
                    redirectToDashboard($username);
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
    
    function redirectToDashboard($username)
    {
        if (strpos($username, 'E') === 0) {
            header("Location: Dash.php");
        } elseif (strpos($username, 'M') === 0 || strpos($username, 'H') === 0) {
            header("Location: hr_Dashboard.php");
        }
        exit;
    }
}
    ?>
    

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="CSS/Login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="assets/logo.png" alt="Logo" class="logo" style="width: 80px; height: auto;">
            </div>
            <form action="login.php" method="post">
                <div class="input-group">
                    <div class="input-icon user-icon"></div>
                    <input id="username" type="text" name="username" placeholder="Username" class="input-field" required>
                </div>
                <div class="input-group">
                    <div class="input-icon lock-icon"></div>
                    <input id="password" type="password" name="password" placeholder="Password" class="input-field" required>
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
