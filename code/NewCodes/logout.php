<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris_db";

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // If user is logged in (has session)
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Clear the token in database if it exists
        $stmt = $conn->prepare("UPDATE userlogin SET Token = NULL WHERE User_ID = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);  // Changed "i" to "s" since Employee_ID is VARCHAR in your schema
            if (!$stmt->execute()) {
                error_log("Failed to clear token for user: " . $user_id);
            }
            $stmt->close();
        } else {
            error_log("Prepare statement failed: " . $conn->error);
        }
    }

    // Destroy session data
    $_SESSION = array(); // Clear all session data
    
    // If it's desired to kill the session, also delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();

    // Clear all cookies
    setcookie('auto_login_token', '', time() - 3600, "/", "", false, true); // Secure, HTTP-only
    setcookie('last_login_time', '', time() - 3600, "/", "", false, true);
    
    // Close database connection
    $conn->close();

} catch (Exception $e) {
    // Log the error securely (don't expose to user)
    error_log("Logout error: " . $e->getMessage());
    
    // Still destroy session even if DB failed
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    setcookie('auto_login_token', '', time() - 3600, "/", "", false, true);
    setcookie('last_login_time', '', time() - 3600, "/", "", false, true);
}

// Always redirect to login page
header("Location: login.php");
exit();
?>