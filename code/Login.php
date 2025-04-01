<?php 
session_start();

// Database connection
$host = 'localhost';
$dbname = 'hris_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database error occurred: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $input_password = trim($_POST['password']);
    
    try {
        // Check Employee table for login credentials
        // User can log in with Employee_ID or Email
        $stmt = $pdo->prepare("SELECT Employee_ID, Password, Employee_Type FROM Employee 
                              WHERE (Employee_ID = :username OR Email = :username)");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // For simple password verification (if passwords are stored as plain text)
        // In production, you should use password_hash and password_verify
        if ($employee && $input_password == $employee['Password']) {
            // Login successful
            $_SESSION['user_id'] = $employee['Employee_ID'];
            $_SESSION['user_type'] = $employee['Employee_Type'];
            
            // Role-based redirection
            if ($employee['Employee_Type'] == 'HumanResource Manager') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dash.php");
            }
            exit();
        } else {
            $error = "Invalid Username or Password";
        }
    } catch(PDOException $e) {
        $error = "Database error occurred: " . $e->getMessage();
    }
}
?>

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
            <form action="" method="post">
                <div class="input-group">
                    <div class="input-icon user-icon"></div>
                    <input type="text" name="username" placeholder="Employee ID or Email" class="input-field" required>
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
            
            <?php if (isset($error)) { echo "<p style='color:red; text-align:center;'>$error</p>"; } ?>
        </div>
    </div>
</body>
</html>