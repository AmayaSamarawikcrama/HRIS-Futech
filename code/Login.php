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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    try {
        // Fetch user data from User_Account table
        $stmt = $pdo->prepare("SELECT * FROM User_Account WHERE Username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid Username";
        } else {
            // Verify password
            if (password_verify($input_password, $user['Password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['Employee_ID'];
                $_SESSION['user_role'] = $user['User_Type'];
                
                // Role-based redirection
                switch ($user['User_Type']) {
                    case 'Admin':
                        header("Location: admin_dashboard.php");
                        break;
                    case 'Manager':
                        header("Location: manager_dashboard.php");
                        break;
                    case 'Employee':
                        header("Location: dash.php");
                        break;
                    default:
                        header("Location: index.php");
                        break;
                }
                exit();
            } else {
                $error = "Invalid Password";
            }
        }
    } catch(PDOException $e) {
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
                    <input type="text" name="username" placeholder="Username" class="input-field" required>
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
