<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "hris_db");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $conn->real_escape_string($_POST['emp_id']);
    $leave_type = $conn->real_escape_string($_POST['leave_type']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $reason = $conn->real_escape_string($_POST['reason']);
    $duty_covering = $conn->real_escape_string($_POST['duty_covering'] ?? ''); // Added optional field

    // Validate form fields
    if (empty($employee_id) || empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit();
    }

    // Validate dates
    if (strtotime($end_date) <= strtotime($start_date)) {
        echo "<script>alert('End Date must be later than Start Date!'); window.history.back();</script>";
        exit();
    }

    // Check if Employee_ID exists
    $check_employee = "SELECT Employee_ID FROM Employee WHERE Employee_ID = ?";
    $stmt = $conn->prepare($check_employee);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<script>alert('Invalid Employee ID!'); window.history.back();</script>";
        exit();
    }
    $stmt->close();

    // Insert data into Leave_Management using prepared statement
    $sql = "INSERT INTO Leave_Management (Employee_ID, Leave_Type, Start_Date, End_Date, Leave_Reason, Duty_Covering, Approval_Status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $employee_id, $leave_type, $start_date, $end_date, $reason, $duty_covering);
    
    if ($stmt->execute()) {
        echo "<script>alert('Leave request submitted successfully!'); window.location.href=window.location.href;</script>";
    } else {
        echo "<script>alert('Error: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
    $stmt->close();
}

// Fetch remaining leave balance - you can uncomment and modify this if needed
/*
$leave_balance = 40; // Default value
if (isset($_SESSION['employee_id'])) {
    $emp_id = $_SESSION['employee_id'];
    $leave_query = "SELECT COUNT(*) as used_leaves FROM Leave_Management 
                    WHERE Employee_ID = ? AND Approval_Status = 'Approved' 
                    AND YEAR(Start_Date) = YEAR(CURRENT_DATE())";
    $stmt = $conn->prepare($leave_query);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $leave_balance = 40 - $row['used_leaves'];
    }
    $stmt->close();
}
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Leave Request</title>
  <link rel="stylesheet" href="EmpLeave.css" />
  <script>
    function validateForm() {
        let emp_id = document.getElementById("emp_id").value.trim();
        let leave_type = document.getElementById("leave_type").value;
        let start_date = document.getElementById("start_date").value;
        let end_date = document.getElementById("end_date").value;
        let reason = document.getElementById("reason").value.trim();

        if (!emp_id || !leave_type || !start_date || !end_date || !reason) {
            alert("All fields are required!");
            return false;
        }

        if (new Date(end_date) <= new Date(start_date)) {
            alert("End Date must be later than Start Date!");
            return false;
        }

        return true;
    }
  </script>
</head>
<body>
  <div class="container">
    <header>
      <div class="menu-icon">&#9776;</div>
      <button class="logout">Log Out</button>
      <img class="profile-pic" src="assets/profile.png" alt="Employee Details">
    </header>
    <main>
      <section class="left-panel">
        <h1>Leave Request</h1>
        <div class="info-box">
            <div class="status accepted">Accepted Your Request</div>
            <div class="leave-count">You Have Only 40 Leaves</div>
        </div>
        <a href="#" class="leave-history">Leave History</a>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return validateForm()">
            <label for="emp_id">Employee ID:</label>
            <input type="text" id="emp_id" name="emp_id" placeholder="Enter your Employee ID" required>

            <label for="leave_type">Leave Type:</label>
            <select id="leave_type" name="leave_type" required>
                <option value="">Select Leave Type</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Casual Leave">Casual Leave</option>
                <option value="Annual Leave">Annual Leave</option>
                <option value="Short Leave">Short Leave</option>
                <option value="Duty Out">Duty Out</option>
                <option value="Special Leave">Special Leave</option>
                <option value="Lieu Leave">Lieu Leave</option>
                <option value="Election Leave">Election Leave</option>
                <option value="Maternity Leave">Maternity Leave</option>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="reason">Reason:</label>
            <textarea id="reason" name="reason" rows="3" required></textarea>

            <label for="duty_covering">Duty Covering (Optional):</label>
            <input type="text" id="duty_covering" name="duty_covering" placeholder="Who will cover your duties?">

            <button type="submit" class="submit">SUBMIT</button>
        </form>
      </section>
      <section class="right-panel">
        <img src="assets/leveDash.jpg" alt="Businessman Climbing Graph" />
      </section>
    </main>
  </div>
</body>
</html>