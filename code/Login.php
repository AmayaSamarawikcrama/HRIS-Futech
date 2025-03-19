<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture the form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    if(!empty($username) && !empty($password)){
        $sql = "SELECT * FROM userlogin WHERE Username = '$username' AND Password = '$password'";
        $result = mysqli_query($conn, $sql);

        if($result){
            
                if($result && mysqli_num_rows($result) > 0)
                {
                    $user_data =mysqli_fetch_assoc($result);

                    if($user_data['Password'] == $password)
                    {
                        header("location: Dash.php");
                        die;
                    }
                }
            

        }
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
            <!-- Form for login -->
            <form action="login.php" method="post"> <!-- Action points to the same file -->
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
                <button action="login.php" type="submit" class="login-submit">LOGIN</button>
            </form>
        </div>
    </div>
</body>
</html>
