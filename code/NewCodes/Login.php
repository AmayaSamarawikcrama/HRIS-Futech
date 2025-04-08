<?php
session_start();

$host = 'localhost';
$dbname = 'hris_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error occurred: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // Get user input password

    try {
        // Fetch employee record
        $stmt = $pdo->prepare("SELECT Employee_ID, Password, Employee_Type FROM Employee WHERE Employee_ID = :username OR Email = :username");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee && $password === $employee['Password']) { // Directly compare plaintext passwords
            session_regenerate_id(true);

            $_SESSION['user_id'] = $employee['Employee_ID'];
            $_SESSION['user_type'] = $employee['Employee_Type'];

            $redirect_page = ($employee['Employee_Type'] == 'HumanResource Manager' || $employee['Employee_Type'] == 'Manager' || $employee['Employee_Type'] == 'Employee') 
    ? "HrDashboard.php" 
    : "EmpDashboard.php";


            header("Location: $redirect_page");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        die("Database error occurred: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HRIS System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <section class="vh-100">
        <div class="container py-5 h-100">
            <div class="row d-flex align-items-center justify-content-center h-100">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <img src="assets/login.png" class="img-fluid" alt="Login Image">
                </div>
                <div class="col-md-7 col-lg-5 col-xl-5 offset-xl-1">
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <h2 class="text-center mb-4"><b>HRIS Login</b></h2>
                        
                        <div class="form-outline mb-4">
                            <label for="username" class="form-label">Employee ID or Email</label>
                            <input type="text" id="username" name="username" class="form-control form-control-lg" required />
                        </div>
                        
                        <div class="form-outline mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control form-control-lg" required />
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" />
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="forgot_password.php">Forgot password?</a>
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
