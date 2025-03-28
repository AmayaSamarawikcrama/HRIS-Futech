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
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Login Processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    // Debug: Print entered credentials
    error_log("Login Attempt - Employee ID: $employee_id");

    try {
        // Prepare SQL to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM Employee WHERE Employee_ID = :employee_id");
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debug: Check if user exists
        if (!$user) {
            error_log("No user found with Employee ID: $employee_id");
            $error = "Invalid Employee ID";
        } else {
            // Debug: Print stored password details
            error_log("Stored Password Hash: " . $user['Password']);

            // Note: Direct comparison instead of password_verify
            if ($input_password === $user['Password']) {
                // Login successful
                $_SESSION['user_id'] = $user['Employee_ID'];
                $_SESSION['user_role'] = $user['Job_Role'];

                // Conditional Redirection based on username prefix
                if (strpos($employee_id, 'EMP') === 0) {
                    // If username starts with 'EMP', redirect to dash.php
                    header("Location: dash.php");
                    exit();
                } elseif (strpos($employee_id, 'HM') === 0) {
                    // If username starts with 'HM', redirect to hm_dash.php
                    header("Location: hr_Dashboard.php");
                    exit();
                }
                else if(strpos($employee_id, 'MN') === 0)
                {
                    header("Location: hr_Dashboard.php");
                    exit();
                }
                 else 
                {
                    // Default redirection if no specific prefix matches
                    exit();
                }
            } else {
                error_log("Password mismatch for Employee ID: $employee_id");
                $error = "Invalid Password";
            }
        }
    } catch(PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error = "Database error occurred";
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

            <?php
            // Display error if there's any login issue
            if (isset($error)) {
                echo "<p style='color:red; text-align:center;'>$error</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>