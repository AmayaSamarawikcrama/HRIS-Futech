<?php
// Start session and include database connection
session_start();

// Function to establish database connection
function getDBConnection() {
    // We'll use direct connection instead of relying on potentially missing config file
    $host = 'localhost';
    $dbname = 'hris_db';
    $username = 'root';
    $password = '';
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Check if user is logged in, redirect if not
if (!isset($_SESSION['user_data'])) {
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "Security verification failed. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: attendance.php");
        exit();
    }
    
    $conn = getDBConnection();
    
    // Get form data and sanitize
    $employee_id = filter_var($_POST['employee_id'], FILTER_SANITIZE_STRING);
    $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
    $clock_in = filter_var($_POST['clock_in'], FILTER_SANITIZE_STRING);
    $clock_out = filter_var($_POST['clock_out'], FILTER_SANITIZE_STRING);
    $work_hours = filter_var($_POST['work_hours'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $assigned_task = filter_var($_POST['assigned_task'], FILTER_SANITIZE_STRING);
    $task_completion = filter_var($_POST['task_completion'], FILTER_SANITIZE_NUMBER_INT);
    $comments = filter_var($_POST['comments'], FILTER_SANITIZE_STRING);
    
    // Validate data
    if (empty($employee_id) || empty($date) || empty($clock_in) || empty($clock_out) || 
        empty($work_hours) || empty($assigned_task) || empty($task_completion)) {
        $_SESSION['message'] = "All fields are required except comments.";
        $_SESSION['message_type'] = "error";
        header("Location: attendance.php");
        exit();
    }
    
    // Additional validation
    if ($task_completion < 0 || $task_completion > 100) {
        $_SESSION['message'] = "Task completion must be between 0 and 100 percent.";
        $_SESSION['message_type'] = "error";
        header("Location: attendance.php");
        exit();
    }
    
    if ($work_hours <= 0) {
        $_SESSION['message'] = "Work hours must be greater than zero.";
        $_SESSION['message_type'] = "error";
        header("Location: attendance.php");
        exit();
    }
    
    // Check if attendance already exists
    $check_query = "SELECT * FROM Attendance WHERE Employee_ID = ? AND Date = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ss", $employee_id, $date);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Attendance already recorded for this date.";
        $_SESSION['message_type'] = "error";
        header("Location: attendance.php");
        exit();
    }
    
    // Insert new attendance record
    $insert_query = "INSERT INTO Attendance 
                    (Employee_ID, Date, Log_In_Time, Log_Out_Time, Work_Hours, Assigned_Task, Task_Completion, Comments) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssssdsds", $employee_id, $date, $clock_in, $clock_out, 
                            $work_hours, $assigned_task, $task_completion, $comments);
    
    if ($insert_stmt->execute()) {
        $_SESSION['message'] = "Attendance recorded successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error recording attendance: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    
    $insert_stmt->close();
    $check_stmt->close();
    $conn->close();
    
    header("Location: attendance.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Entry</title>
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
        .error { color: red; }
        .success { color: green; }
        #menu-list {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .menu-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
        <div class="container-fluid">
            <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
            <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                <a href="Dashboard.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">Add Employee</a>
                <a href="View_Employee.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">View Employee</a>
                <a href="attendance.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">Attendance</a>
                <a href="Project.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">Project</a>
                <a href="Leave.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">Leave</a>
                <a href="Salary.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">Salary</a>
                <a href="Report.php" class="d-block text-decoration-none text-dark mb-2 p-2 menu-item">Report</a>
            </div>
            <?php
            if(isset($_SESSION['user_data'])) {
                echo '<span class="employee-name ms-auto me-3">' . 
                     htmlspecialchars($_SESSION['user_data']['First_Name'] . ' ' . $_SESSION['user_data']['Last_Name']) . 
                     '</span>';
            }
            ?>
            <a href="attendance.php?logout=1" class="btn btn-primary me-2">Log Out</a>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='profile.php'">
        </div>
    </nav>

    <!-- Attendance Entry Form -->
    <div class="container mt-4">
        <h2 class="text-center"><b>Enter Your Attendance</b></h2><br>
        <?php
        if(isset($_SESSION['message'])) {
            echo '<div class="alert ' . ($_SESSION['message_type'] == 'success' ? 'alert-success' : 'alert-danger') . '">' . 
                 $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <form method="POST" action="attendance.php" id="attendanceForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Employee ID:</label>
                    <input type="text" class="form-control" name="employee_id" required 
                           value="<?php echo isset($_SESSION['user_data']['Employee_ID']) ? htmlspecialchars($_SESSION['user_data']['Employee_ID']) : ''; ?>" 
                           <?php echo isset($_SESSION['user_data']['Employee_ID']) ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date:</label>
                    <input type="date" class="form-control" name="date" required value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock In Time:</label>
                    <input type="time" class="form-control" name="clock_in" id="clock_in" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock Out Time:</label>
                    <input type="time" class="form-control" name="clock_out" id="clock_out" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Hours:</label>
                    <input type="number" step="0.01" class="form-control" name="work_hours" id="work_hours" placeholder="Enter work hours" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assigned Task:</label>
                    <input type="text" class="form-control" name="assigned_task" placeholder="Enter assigned task" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Task Completion (%):</label>
                    <input type="number" class="form-control" name="task_completion" placeholder="Enter task completion %" min="0" max="100" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Comments:</label>
                    <textarea class="form-control" name="comments" rows="3" placeholder="Enter comments" maxlength="500"></textarea>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary w-50" name="submit">Submit Attendance</button><br>
                </div>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="container mt-4">
        <h2 class="text-center"><b>My Attendance Records</b></h2><br>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Date</th>
                        <th>Clock In Time</th>
                        <th>Clock Out Time</th>
                        <th>Work Hours</th>
                        <th>Assigned Task</th>
                        <th>Task Completion (%)</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <?php
                    // Display existing attendance records
                    if(isset($_SESSION['user_data']['Employee_ID'])) {
                        $conn = getDBConnection();
                        $employee_id = $_SESSION['user_data']['Employee_ID'];
                        $query = "SELECT Date, Log_In_Time, Log_Out_Time, Work_Hours, Assigned_Task, Task_Completion, Comments 
                                  FROM Attendance 
                                  WHERE Employee_ID = ? 
                                  ORDER BY Date DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("s", $employee_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['Date']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Log_In_Time']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Log_Out_Time']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Work_Hours']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Assigned_Task']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Task_Completion']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['Comments']) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">No attendance records found</td></tr>';
                        }
                        
                        $stmt->close();
                        $conn->close();
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Function to toggle the menu
    function toggleMenu() {
        const menuList = document.getElementById('menu-list');
        menuList.classList.toggle('d-none');
        
        // Close menu when clicking outside
        if (!menuList.classList.contains('d-none')) {
            document.addEventListener('click', function closeMenu(e) {
                if (!e.target.closest('#menu-list') && e.target.className !== 'menu-icon') {
                    menuList.classList.add('d-none');
                    document.removeEventListener('click', closeMenu);
                }
            });
        }
    }

    // Calculate work hours automatically when clock times change
    document.getElementById('clock_in').addEventListener('change', calculateHours);
    document.getElementById('clock_out').addEventListener('change', calculateHours);
    
    function calculateHours() {
        const clockIn = document.getElementById('clock_in').value;
        const clockOut = document.getElementById('clock_out').value;
        
        if(clockIn && clockOut) {
            // Parse the times
            const [inHours, inMins] = clockIn.split(':').map(Number);
            const [outHours, outMins] = clockOut.split(':').map(Number);
            
            // Convert to minutes
            let inMinutes = inHours * 60 + inMins;
            let outMinutes = outHours * 60 + outMins;
            
            // Handle overnight shift
            if (outMinutes < inMinutes) {
                outMinutes += 24 * 60; // Add 24 hours
            }
            
            // Calculate difference in hours
            const diff = (outMinutes - inMinutes) / 60;
            
            if(diff > 0) {
                document.getElementById('work_hours').value = diff.toFixed(2);
            } else {
                document.getElementById('work_hours').value = '';
            }
        }
    }
    
    // Form validation
    document.getElementById('attendanceForm').addEventListener('submit', function(e) {
        const workHours = parseFloat(document.getElementById('work_hours').value);
        const taskCompletion = parseInt(document.querySelector('input[name="task_completion"]').value);
        
        let isValid = true;
        let errorMessage = '';
        
        if (isNaN(workHours) || workHours <= 0) {
            errorMessage = 'Work hours must be greater than zero.';
            isValid = false;
        } else if (workHours > 24) {
            errorMessage = 'Work hours cannot exceed 24 hours per day.';
            isValid = false;
        }
        
        if (isNaN(taskCompletion) || taskCompletion < 0 || taskCompletion > 100) {
            errorMessage = 'Task completion must be between 0 and 100 percent.';
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
</script>
</body>
</html>