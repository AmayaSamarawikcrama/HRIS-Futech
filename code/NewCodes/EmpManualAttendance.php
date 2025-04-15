<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection function
function getDBConnection() {
    $conn = new mysqli('localhost', 'root', '', 'hris_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "Security verification failed. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: EmpManualAttendance.php");
        exit();
    }
    
    $conn = getDBConnection();
    
    // Sanitize inputs
    $employee_id = $_SESSION['user_id'];
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $clock_in = filter_input(INPUT_POST, 'clock_in', FILTER_SANITIZE_STRING);
    $clock_out = filter_input(INPUT_POST, 'clock_out', FILTER_SANITIZE_STRING);
    $assigned_task = filter_input(INPUT_POST, 'assigned_task', FILTER_SANITIZE_STRING);
    $task_completion = filter_input(INPUT_POST, 'task_completion', FILTER_SANITIZE_NUMBER_INT);
    $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);
    
    // Set defaults for nullable fields
    if (empty($assigned_task)) $assigned_task = NULL;
    if (empty($task_completion)) $task_completion = NULL;
    if (empty($comments)) $comments = NULL;
    
    // Validate inputs
    $errors = [];
    if (empty($date)) $errors[] = "Date is required";
    if (empty($clock_in)) $errors[] = "Clock in time is required";
    if (empty($clock_out)) $errors[] = "Clock out time is required";
    
    if (empty($errors)) {
        // Check for existing attendance
        $check_stmt = $conn->prepare("SELECT * FROM Attendance WHERE Employee_ID = ? AND Date = ?");
        $check_stmt->bind_param("ss", $employee_id, $date);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['message'] = "Attendance already recorded for this date.";
            $_SESSION['message_type'] = "error";
        } else {
            // Prepare SQL with only the fields that exist
            $sql = "INSERT INTO Attendance (Employee_ID, Date, Log_In_Time, Log_Out_Time";
            $types = "ssss"; // Start with the basic parameter types
            $params = array($employee_id, $date, $clock_in, $clock_out);
            
            // Add optional fields if they exist in the database
            try {
                $check_columns = $conn->query("SHOW COLUMNS FROM Attendance LIKE 'Assigned_Task'");
                if ($check_columns->num_rows > 0) {
                    $sql .= ", Assigned_Task";
                    $types .= "s";
                    $params[] = $assigned_task;
                }
                
                $check_columns = $conn->query("SHOW COLUMNS FROM Attendance LIKE 'Task_Completion'");
                if ($check_columns->num_rows > 0) {
                    $sql .= ", Task_Completion";
                    $types .= "d";
                    $params[] = $task_completion;
                }
                
                $check_columns = $conn->query("SHOW COLUMNS FROM Attendance LIKE 'Comments'");
                if ($check_columns->num_rows > 0) {
                    $sql .= ", Comments";
                    $types .= "s";
                    $params[] = $comments;
                }
                
                // Complete the SQL statement
                $sql .= ") VALUES (";
                $placeholders = array_fill(0, count($params), "?");
                $sql .= implode(", ", $placeholders) . ")";
                
                // Prepare and execute the statement
                $insert_stmt = $conn->prepare($sql);
                
                // Create the bind_param arguments dynamically
                $bindParams = array();
                $bindParams[] = &$types;
                for ($i = 0; $i < count($params); $i++) {
                    $bindParams[] = &$params[$i];
                }
                call_user_func_array(array($insert_stmt, 'bind_param'), $bindParams);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['message'] = "Attendance recorded successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error: " . $conn->error;
                    $_SESSION['message_type'] = "error";
                }
                $insert_stmt->close();
            } catch (Exception $e) {
                // Fallback to basic insert if column check fails
                $basic_insert = $conn->prepare("INSERT INTO Attendance (Employee_ID, Date, Log_In_Time, Log_Out_Time) VALUES (?, ?, ?, ?)");
                $basic_insert->bind_param("ssss", $employee_id, $date, $clock_in, $clock_out);
                
                if ($basic_insert->execute()) {
                    $_SESSION['message'] = "Attendance recorded successfully (basic record)!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error: " . $conn->error;
                    $_SESSION['message_type'] = "error";
                }
                $basic_insert->close();
            }
        }
        $check_stmt->close();
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
    }
    
    $conn->close();
    header("Location: EmpManualAttendance.php");
    exit();
}

// Fetch all attendance records for the current user
$conn = getDBConnection();
$attendance_records = [];
$employee_id = $_SESSION['user_id'];
$query = "SELECT * FROM Attendance WHERE Employee_ID = ? ORDER BY Date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendance_records[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Attendance Entry</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        #menu-list {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .attendance-table {
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
        <div class="container-fluid">
            <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
            <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                <a href="HrDashboard.html" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                <a href="HrAddEmployee.html" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                <a href="HrEmployeeDetails.html" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                <a href="Project.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                <a href="Leave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                <a href="HrSalary.html" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                <a href="Calendar.html" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
            </div>

            <span class="employee-name ms-auto me-3">
                <?php if(isset($_SESSION['user_data'])): ?>
                    <?php echo htmlspecialchars($_SESSION['user_data']['First_Name'] . ' ' . $_SESSION['user_data']['Last_Name']); ?>
                <?php else: ?>
                    User
                <?php endif; ?>
            </span>
            <button class="btn btn-primary me-2" onclick="window.location.href='logout.php'">Log Out</button>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Manual Attendance Entry</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo $_SESSION['message']; ?>
                <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="border p-4 rounded">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Employee ID</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Clock In Time</label>
                    <input type="time" name="clock_in" id="clock_in" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock Out Time</label>
                    <input type="time" name="clock_out" id="clock_out" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Assigned Task</label>
                    <input type="text" name="assigned_task" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Task Completion (%)</label>
                    <input type="number" name="task_completion" min="0" max="100" class="form-control" value="0">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Comments</label>
                    <textarea name="comments" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="submit" class="btn btn-primary px-4">Submit</button>
            </div>
        </form>

       
        <div class="attendance-table">
            <h3 class="text-center mb-3">My Attendance Records</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Work Hours</th>
                            <?php
                            $conn = getDBConnection();
                            $check_task = $conn->query("SHOW COLUMNS FROM Attendance LIKE 'Assigned_Task'");
                            $check_completion = $conn->query("SHOW COLUMNS FROM Attendance LIKE 'Task_Completion'");
                            $check_comments = $conn->query("SHOW COLUMNS FROM Attendance LIKE 'Comments'");
                            if ($check_task->num_rows > 0) echo "<th>Task</th>";
                            if ($check_completion->num_rows > 0) echo "<th>Completion %</th>";
                            if ($check_comments->num_rows > 0) echo "<th>Comments</th>";
                            $conn->close();
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendance_records)): ?>
                            <?php foreach ($attendance_records as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['Date']); ?></td>
                                    <td><?php echo htmlspecialchars($record['Log_In_Time']); ?></td>
                                    <td><?php echo htmlspecialchars($record['Log_Out_Time']); ?></td>
                                    <td><?php echo htmlspecialchars($record['Work_Hours']); ?></td>
                                    <?php if (isset($record['Assigned_Task'])): ?>
                                        <td><?php echo htmlspecialchars($record['Assigned_Task'] ?: 'N/A'); ?></td>
                                    <?php endif; ?>
                                    <?php if (isset($record['Task_Completion'])): ?>
                                        <td><?php echo htmlspecialchars($record['Task_Completion'] ?: '0') . '%'; ?></td>
                                    <?php endif; ?>
                                    <?php if (isset($record['Comments'])): ?>
                                        <td><?php echo htmlspecialchars($record['Comments'] ?: ''); ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No attendance records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle menu
    function toggleMenu() {
        const menu = document.getElementById('menu-list');
        menu.classList.toggle('d-none');
    }

    document.getElementById('clock_in').addEventListener('change', calculateHours);
    document.getElementById('clock_out').addEventListener('change', calculateHours);

    function calculateHours() {
        const inTime = document.getElementById('clock_in').value;
        const outTime = document.getElementById('clock_out').value;
        
        if (inTime && outTime) {
            const [inH, inM] = inTime.split(':').map(Number);
            const [outH, outM] = outTime.split(':').map(Number);
            
            let totalMinutes = (outH * 60 + outM) - (inH * 60 + inM);
            if (totalMinutes < 0) totalMinutes += 24 * 60; // Handle overnight
            
            console.log("Estimated work hours: " + (totalMinutes / 60).toFixed(2));
        }
    }
</script>
</body>
</html>