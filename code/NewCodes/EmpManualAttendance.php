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
    </style>
</head>
<body>
<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $clock_in = mysqli_real_escape_string($conn, $_POST['clock_in']);
    $clock_out = mysqli_real_escape_string($conn, $_POST['clock_out']);
    $work_hours = mysqli_real_escape_string($conn, $_POST['work_hours']);
    $assigned_task = mysqli_real_escape_string($conn, $_POST['assigned_task']);
    $task_completion = mysqli_real_escape_string($conn, $_POST['task_completion']);
    $comments = mysqli_real_escape_string($conn, $_POST['comments']);
    
    $errors = [];
    
    $check_employee = $conn->prepare("SELECT Employee_ID FROM Employee WHERE Employee_ID = ?");
    $check_employee->bind_param("s", $employee_id);
    $check_employee->execute();
    $result = $check_employee->get_result();
    
    if ($result->num_rows == 0) {
        $errors[] = "Employee ID does not exist";
    }
    
    $check_attendance = $conn->prepare("SELECT Attendance_ID FROM Attendance WHERE Employee_ID = ? AND Date = ?");
    $check_attendance->bind_param("ss", $employee_id, $date);
    $check_attendance->execute();
    $result = $check_attendance->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Attendance record already exists for this date";
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO Attendance (Employee_ID, Date, Log_In_Time, Log_Out_Time, Work_Hours, Assigned_Task, Task_Completion, Comments) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssdsds", $employee_id, $date, $clock_in, $clock_out, $work_hours, $assigned_task, $task_completion, $comments);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Attendance record added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding attendance record: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        
        $stmt->close();
    } else {
        $_SESSION['message'] = "Error: " . implode(", ", $errors);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$search_employee_id = "";
$search_performed = false;
$search_results = [];

if (isset($_POST['search'])) {
    $search_employee_id = mysqli_real_escape_string($conn, $_POST['search_employee_id']);
    $search_performed = true;
    
    $check_employee = $conn->prepare("SELECT Employee_ID FROM Employee WHERE Employee_ID = ?");
    $check_employee->bind_param("s", $search_employee_id);
    $check_employee->execute();
    $result = $check_employee->get_result();
    
    if ($result->num_rows > 0) {
        $query = "SELECT Date, Log_In_Time, Log_Out_Time, Work_Hours, Assigned_Task, Task_Completion, Comments 
                  FROM Attendance 
                  WHERE Employee_ID = ? 
                  ORDER BY Date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $search_employee_id);
        $stmt->execute();
        $search_results = $stmt->get_result();
        $stmt->close();
    } else {
        $_SESSION['message'] = "Employee ID does not exist";
        $_SESSION['message_type'] = "error";
    }
}
?>

<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
        <div class="container-fluid">
            <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
            <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
                <a href="Dashboard.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                <a href="View Employee.php" class="d-block text-decoration-none text-dark mb-2">View Employee</a>
                <a href="attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                <a href="Project.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                <a href="Leave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                <a href="Salary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
            </div>
            <script>
                function toggleMenu() {
                    const menuList = document.getElementById('menu-list');
                    menuList.classList.toggle('d-none');
                }
                
                function logout() {
                    window.location.href = 'logout.php';
                }
            </script>
            <?php
            if(isset($_SESSION['user_data'])) {
                echo '<span class="employee-name ms-auto me-3">' . 
                     htmlspecialchars($_SESSION['user_data']['First_Name'] . ' ' . $_SESSION['user_data']['Last_Name']) . 
                     '</span>';
            }
            ?>
            <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
        </div>
    </nav>

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
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="attendanceForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Employee ID:</label>
                    <input type="text" class="form-control" name="employee_id" required 
                           value="<?php echo isset($_SESSION['user_data']['Employee_ID']) ? $_SESSION['user_data']['Employee_ID'] : ''; ?>" 
                           <?php echo isset($_SESSION['user_data']['Employee_ID']) ? 'readonly' : ''; ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date:</label>
                    <input type="date" class="form-control" name="date" required value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock In Time:</label>
                    <input type="time" class="form-control" name="clock_in" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock Out Time:</label>
                    <input type="time" class="form-control" name="clock_out" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Hours:</label>
                    <input type="number" step="0.01" class="form-control" name="work_hours" placeholder="Enter work hours" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assigned Task:</label>
                    <input type="text" class="form-control" name="assigned_task" placeholder="Enter assigned task" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Task Completion (%):</label>
                    <input type="number" class="form-control" name="task_completion" placeholder="Enter task completion %" min="0" max="100" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Comments:</label>
                    <textarea class="form-control" name="comments" rows="3" placeholder="Enter comments"></textarea>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary w-50" name="submit">Submit Attendance</button><br>
                </div>
            </div>
        </form>
    </div>

    <div class="container mt-5">
        <h2 class="text-center"><b>Search Employee Attendance</b></h2><br>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="mb-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search_employee_id" placeholder="Enter Employee ID" 
                               value="<?php echo $search_employee_id; ?>" required>
                        <button type="submit" class="btn btn-primary" name="search">Search</button>
                    </div>
                </div>
            </div>
        </form>
        
        <?php if ($search_performed): ?>
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
                    <tbody>
                        <?php
                        if ($search_results && $search_results->num_rows > 0) {
                            while($row = $search_results->fetch_assoc()) {
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
                            echo '<tr><td colspan="7">No attendance records found for this Employee ID</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelector('input[name="clock_out"]').addEventListener('change', function() {
        const clockIn = document.querySelector('input[name="clock_in"]').value;
        const clockOut = this.value;
        
        if(clockIn && clockOut) {
            const start = new Date(`2000-01-01T${clockIn}`);
            const end = new Date(`2000-01-01T${clockOut}`);
            const diff = (end - start) / (1000 * 60 * 60);
            
            if(diff > 0) {
                document.querySelector('input[name="work_hours"]').value = diff.toFixed(2);
            }
        }
    });
</script>
<?php
$conn->close();
?>
</body>
</html>