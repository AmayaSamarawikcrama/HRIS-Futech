<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { 
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    try {
        // Fetch user details
        $stmt = $conn->prepare("SELECT * FROM Employee WHERE Employee_ID  = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) { // Ensure password is hashed in DB
            $_SESSION['user_id'] = $user['User_ID'];

            // Redirect based on username prefix
            if (strpos($username, 'EMP') === 0) {
                header("Location: dash.php");
            } elseif (strpos($username, 'HR') === 0) {
                header("Location: hrDash.php");
            } elseif (strpos($username, 'MN') === 0) {
                header("Location: managerDash.php"); // Changed from hrDash.php for MN users
            } else {
                header("Location: dashboard.php"); // Default fallback
            }
            exit();
        } else {
            $error = "Invalid Username or Password";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .divider:after,
        .divider:before {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
        }
    </style>
</head>
<body>
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex align-items-center justify-content-center h-100">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <img src="assets/login.png" class="img-fluid" alt="Login Image">
                </div>
                <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">
                    <form method="POST">
                        <h2 class="text-center mb-4"><b>Login</b></h2>

                        <div class="form-outline mb-4">
                            <input type="text" name="username" class="form-control form-control-lg" style="border: 2px solid rgb(32, 122, 232);" required />
                            <label class="form-label">User ID</label>
                        </div>

                        <div class="form-outline mb-4">
                            <input type="password" name="password" class="form-control form-control-lg" style="border: 2px solid rgb(32, 122, 232);" required />
                            <label class="form-label">Password</label>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger text-center"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-around align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" checked />
                                <label class="form-check-label">Remember me</label>
                            </div>
                            <a href="#!">Forgot password?</a>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary btn-lg btn-block w-100">Sign in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
