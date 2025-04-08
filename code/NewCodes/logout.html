<?php
session_start(); // Start session

// Check if user is logged in (Session)
if (isset($_SESSION['user_id'])) {
    // Database connection (same as your login page)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hris_db";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user ID from session and delete the token from the database
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE userlogin SET Token = NULL WHERE User_ID = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Destroy session and clear cookies
    session_unset();
    session_destroy();
    
    setcookie('auto_login_token', '', time() - 3600, "/"); // Clear cookie
    setcookie('last_login_time', '', time() - 3600, "/"); // Clear cookie
    
    // Redirect to login page
    header("Location: login.php");
    exit();
} else {
    // If no session, just redirect to login
    header("Location: login.php");
    exit();
}
?>
