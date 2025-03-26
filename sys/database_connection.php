<?php
$servername = "localhost";  // Database server (e.g., localhost or IP)
$db_username = "root";      // Database username
$db_password = "";          // Database password (for XAMPP, the default is usually empty)
$dbname = "hris_db";        // Your database name

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
