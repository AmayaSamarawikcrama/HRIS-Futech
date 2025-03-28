
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