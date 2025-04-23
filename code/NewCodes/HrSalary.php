<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Check if this is an AJAX request for employee details
    if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hris_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process AJAX request for employee details if applicable
if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
    $employee_id = $_GET['employee_id'];
    
    // Get employee details
    $query = "SELECT 
        e.Employee_ID,
        e.First_Name,
        e.Last_Name,
        e.Salary,
        e.Department_ID,
        d.Department_Name
        FROM Employee e
        LEFT JOIN Department d ON e.Department_ID = d.Department_ID
        WHERE e.Employee_ID = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Employee not found']);
        exit();
    }

    $employee = $result->fetch_assoc();

    // Get attendance data for current month
    $current_month = date('Y-m');
    $attendance_query = "SELECT 
        COUNT(DISTINCT Date) as worked_days,
        SUM(Work_Hours) as total_hours,
        AVG(Task_Completion) as avg_task_completion
        FROM Attendance 
        WHERE Employee_ID = ? 
        AND DATE_FORMAT(Date, '%Y-%m') = ?";
    $attendance_stmt = $conn->prepare($attendance_query);
    $attendance_stmt->bind_param("ss", $employee_id, $current_month);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    $attendance_data = $attendance_result->fetch_assoc();

    // Get overtime hours
    $standard_hours_per_day = 8;
    $total_standard_hours = $attendance_data['worked_days'] * $standard_hours_per_day;
    $overtime_hours = ($attendance_data['total_hours'] ?? 0) - $total_standard_hours;
    $overtime_hours = max(0, $overtime_hours); // Ensure it's not negative
    
    // Calculate overtime pay (1.5x hourly rate)
    $hourly_rate = $employee['Salary'] / (22 * $standard_hours_per_day); // Assuming 22 working days per month
    $overtime_pay = $overtime_hours * $hourly_rate * 1.5;

    // Get leave data for current month
    $leave_query = "SELECT 
        COUNT(*) as leave_days,
        SUM(CASE WHEN Leave_Type != 'Annual Leave' THEN 1 ELSE 0 END) as unpaid_leave_days
        FROM Leave_Management 
        WHERE Employee_ID = ? 
        AND Approval_Status = 'Approved'
        AND (
            (Start_Date BETWEEN ? AND LAST_DAY(?)) OR 
            (End_Date BETWEEN ? AND LAST_DAY(?)) OR
            (Start_Date <= ? AND End_Date >= LAST_DAY(?))
        )";
    $first_day_of_month = date('Y-m-01');
    $leave_stmt = $conn->prepare($leave_query);
    $leave_stmt->bind_param("sssssss", $employee_id, $first_day_of_month, $first_day_of_month, 
                           $first_day_of_month, $first_day_of_month, $first_day_of_month, $first_day_of_month);
    $leave_stmt->execute();
    $leave_result = $leave_stmt->get_result();
    $leave_data = $leave_result->fetch_assoc();

    // Calculate unpaid leave deduction
    $working_days_in_month = date('t'); // Total days in current month
    $daily_rate = $employee['Salary'] / 22; // Assuming 22 working days per month
    $unpaid_leave_deduction = ($leave_data['unpaid_leave_days'] ?? 0) * $daily_rate;

    // Calculate performance-based bonus (example logic)
    $performance_bonus = 0;
    if (($attendance_data['avg_task_completion'] ?? 0) > 90) {
        $performance_bonus = $employee['Salary'] * 0.05; // 5% bonus for high performers
    }

    // Prepare response data
    $response = [
        'success' => true,
        'employee_id' => $employee['Employee_ID'],
        'name' => $employee['First_Name'] . ' ' . $employee['Last_Name'],
        'salary' => $employee['Salary'],
        'department' => $employee['Department_Name'],
        'worked_days' => $attendance_data['worked_days'] ?? 0,
        'total_hours' => $attendance_data['total_hours'] ?? 0,
        'leave_days' => $leave_data['leave_days'] ?? 0,
        'unpaid_leave_days' => $leave_data['unpaid_leave_days'] ?? 0,
        'unpaid_leave_deduction' => round($unpaid_leave_deduction, 2),
        'overtime_hours' => round($overtime_hours, 2),
        'overtime_pay' => round($overtime_pay, 2),
        'performance_bonus' => round($performance_bonus, 2),
        'task_completion' => round($attendance_data['avg_task_completion'] ?? 0, 2)
    ];

    // Send response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get user data for displaying in nav
$user_id = $_SESSION['user_id'];
$query = "SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Initialize variables for messages
$message = "";
$messageType = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $employee_id = $_POST['employee_id'];
    $base_salary = floatval($_POST['base_salary']);
    $fixed_allowances = !empty($_POST['fixed_allowances']) ? floatval($_POST['fixed_allowances']) : 0;
    $overtime_pay = !empty($_POST['overtime_pay']) ? floatval($_POST['overtime_pay']) : 0;
    $unpaid_leave_deductions = !empty($_POST['unpaid_leave_deductions']) ? floatval($_POST['unpaid_leave_deductions']) : 0;
    $loan_recovery = !empty($_POST['loan_recovery']) ? floatval($_POST['loan_recovery']) : 0;
    $paye_tax = !empty($_POST['paye_tax']) ? floatval($_POST['paye_tax']) : 0;
    $payment_method = $_POST['payment_method'];
    $payment_date = $_POST['payment_date'];
    $performance_bonus = !empty($_POST['performance_bonus']) ? floatval($_POST['performance_bonus']) : 0;
    
    // Validate employee exists
    $check_employee = $conn->prepare("SELECT Employee_ID FROM Employee WHERE Employee_ID = ?");
    $check_employee->bind_param("s", $employee_id);
    $check_employee->execute();
    $employee_result = $check_employee->get_result();
    
    if ($employee_result->num_rows === 0) {
        $message = "Employee ID does not exist!";
        $messageType = "danger";
    } else {
        // Check if payroll record already exists for this employee this month
        $payroll_month = date('Y-m', strtotime($payment_date));
        $check_payroll = $conn->prepare("SELECT Payroll_ID FROM Payroll WHERE Employee_ID = ? AND DATE_FORMAT(Payment_Date, '%Y-%m') = ?");
        $check_payroll->bind_param("ss", $employee_id, $payroll_month);
        $check_payroll->execute();
        $payroll_result = $check_payroll->get_result();
        
        if ($payroll_result->num_rows > 0) {
            $message = "Payroll record already exists for this employee for the selected month. Please update the existing record.";
            $messageType = "warning";
        } else {
            // Get attendance data (work days, overtime hours)
            $attendance_query = "SELECT 
                COUNT(DISTINCT Date) as worked_days,
                SUM(Work_Hours) as total_hours
                FROM Attendance 
                WHERE Employee_ID = ? 
                AND DATE_FORMAT(Date, '%Y-%m') = ?";
            $attendance_stmt = $conn->prepare($attendance_query);
            $attendance_stmt->bind_param("ss", $employee_id, $payroll_month);
            $attendance_stmt->execute();
            $attendance_result = $attendance_stmt->get_result();
            $attendance_data = $attendance_result->fetch_assoc();
            
            // Get leave data (unpaid leave days)
            $leave_query = "SELECT 
                COUNT(*) as leave_days
                FROM Leave_Management 
                WHERE Employee_ID = ? 
                AND Leave_Type != 'Annual Leave'
                AND Approval_Status = 'Approved'
                AND DATE_FORMAT(Start_Date, '%Y-%m') = ?";
            $leave_stmt = $conn->prepare($leave_query);
            $leave_stmt->bind_param("ss", $employee_id, $payroll_month);
            $leave_stmt->execute();
            $leave_result = $leave_stmt->get_result();
            $leave_data = $leave_result->fetch_assoc();
            
            // Insert payroll record
            $insert_query = "INSERT INTO Payroll (
                Employee_ID, Base_Salary, Fixed_Allowances, Overtime_Pay, 
                Unpaid_Leave_Deductions, Loan_Recovery, PAYE_Tax, 
                Payment_Method, Payment_Date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param(
                "sddddddss", 
                $employee_id, $base_salary, $fixed_allowances, $overtime_pay,
                $unpaid_leave_deductions, $loan_recovery, $paye_tax,
                $payment_method, $payment_date
            );
            
            if ($stmt->execute()) {
                $message = "Payroll record created successfully!";
                $messageType = "success";
            } else {
                $message = "Error creating payroll record: " . $conn->error;
                $messageType = "danger";
            }
        }
    }
}

// Get current month and year for default payment date
$current_month = date('Y-m-01');

// Get list of all employees for dropdown
$employees_query = "SELECT Employee_ID, CONCAT(First_Name, ' ', Last_Name) as FullName FROM Employee ORDER BY First_Name";
$employees_result = $conn->query($employees_query);

// Get payroll records for display
$payroll_records_query = "SELECT 
    p.Payroll_ID,
    p.Employee_ID,
    CONCAT(e.First_Name, ' ', e.Last_Name) as FullName,
    p.Base_Salary,
    p.Fixed_Allowances,
    p.Overtime_Pay,
    p.Unpaid_Leave_Deductions,
    p.Loan_Recovery,
    p.PAYE_Tax,
    p.Employee_EPF,
    p.Employer_EPF,
    p.Employer_ETF,
    p.Gross_Salary,
    p.Total_Deductions,
    p.Net_Salary,
    p.Payment_Method,
    p.Payment_Date
    FROM Payroll p
    JOIN Employee e ON p.Employee_ID = e.Employee_ID
    ORDER BY p.Payment_Date DESC, e.First_Name";
$payroll_records = $conn->query($payroll_records_query);

// Get employee statistics
$employee_stats_query = "SELECT 
    COUNT(*) as total_employees,
    AVG(Salary) as avg_salary,
    SUM(Salary) as total_salary_expense
    FROM Employee";
$employee_stats = $conn->query($employee_stats_query)->fetch_assoc();

// Get department payroll statistics
$dept_stats_query = "SELECT 
    d.Department_Name,
    COUNT(e.Employee_ID) as employee_count,
    AVG(e.Salary) as avg_salary,
    SUM(e.Salary) as total_salary
    FROM Department d
    JOIN Employee e ON d.Department_ID = e.Department_ID
    GROUP BY d.Department_ID
    ORDER BY total_salary DESC";
$dept_stats = $conn->query($dept_stats_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .employee-name {
            font-weight: bold;
            color: #dc3545;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .table th {
            background-color: #abcff3;
        }
        .navigation a {
            text-decoration: none;
            padding: 10px;
            margin: 5px;
            color: #3a3a3a;
            border-radius: 5px;
            display: block;
            transition: background-color 0.3s;
        }
        .navigation a:hover {
            background-color: #f0f0f0;
        }
        .payroll-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .payroll-card:hover {
            transform: translateY(-5px);
        }
        .payroll-summary {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-total {
            font-weight: bold;
            border-top: 2px solid #dee2e6;
            padding-top: 10px;
        }
        .salary-calculator {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.1);
        }
        .stats-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .stats-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        .stats-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .stats-value {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
        }
        .stats-label {
            font-size: 14px;
            color: #6c757d;
        }
        .form-section {
            background-color: #fff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-section-title {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #495057;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
            position: sticky;
            top: 0;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 5px 8px;
            border-radius: 5px;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
            padding: 5px 8px;
            border-radius: 5px;
        }
        .badge-primary {
            background-color: #0d6efd;
            color: white;
            padding: 5px 8px;
            border-radius: 5px;
        }
        .input-group-text {
            background-color: #e9ecef;
        }
        .employee-info {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()"><i class="fas fa-bars"></i></span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2 navigation" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-home me-2"></i>Dashboard</a>
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-user-plus me-2"></i>Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-users me-2"></i>Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-clock me-2"></i>Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-project-diagram me-2"></i>Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-calendar-minus me-2"></i>Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-money-bill-wave me-2"></i>Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-chart-bar me-2"></i>Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-building me-2"></i>Company Details</a>
                    <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2"><i class="fas fa-calendar-alt me-2"></i>Calendar</a>
                </div>
                <span class="employee-name ms-auto me-3">
                    <i class="fas fa-user-circle me-1"></i>
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()"><i class="fas fa-sign-out-alt me-1"></i>Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>
        
        <!-- Messages Section -->
        <?php if (!empty($message)): ?>
            <div class="container mt-3">
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas <?php echo ($messageType == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="container mt-4">
            <div class="row">
                <!-- Statistics Cards -->
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <i class="fas fa-users fa-2x mb-3 text-primary"></i>
                        <div class="stats-value"><?php echo number_format($employee_stats['total_employees']); ?></div>
                        <div class="stats-label">Total Employees</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <i class="fas fa-money-bill-wave fa-2x mb-3 text-success"></i>
                        <div class="stats-value">LKR <?php echo number_format($employee_stats['avg_salary'], 2); ?></div>
                        <div class="stats-label">Average Salary</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <i class="fas fa-wallet fa-2x mb-3 text-danger"></i>
                        <div class="stats-value">LKR <?php echo number_format($employee_stats['total_salary_expense'], 2); ?></div>
                        <div class="stats-label">Monthly Salary Expense</div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card payroll-card">
                        <div class="card-header bg-primary text-white">
                            <h2 class="text-center mb-0"><i class="fas fa-money-check-alt me-2"></i>Payroll Management</h2>
                        </div>
                        <div class="card-body">
                            <!-- Employee Selection and Info -->
                            <div class="form-section">
                                <h4 class="form-section-title"><i class="fas fa-user me-2"></i>Employee Selection</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="employee_id" class="form-label">Select Employee:</label>
                                            <select class="form-select" name="employee_id" id="employee_id" onchange="fetchEmployeeDetails()">
                                                <option value="">-- Select Employee --</option>
                                                <?php while ($employee = $employees_result->fetch_assoc()): ?>
                                                    <option value="<?php echo htmlspecialchars($employee['Employee_ID']); ?>">
                                                        <?php echo htmlspecialchars($employee['Employee_ID'] . ' - ' . $employee['FullName']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_date" class="form-label">Payment Date:</label>
                                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?php echo $current_month; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Employee Information Display -->
                                <div id="employee-info" class="employee-info d-none">
                                    <h5><i class="fas fa-id-card me-2"></i>Employee Information</h5>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <span class="info-label">Name:</span>
                                                <span class="info-value" id="emp-name">-</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">Department:</span>
                                                <span class="info-value" id="emp-department">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <span class="info-label">Base Salary:</span>
                                                <span class="info-value" id="emp-salary">-</span>
                                            </div>
                                            <div class="info-row">
                                                <span class="info-label">Work Performance:</span>
                                                <span class="info-value" id="emp-performance">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-4">
                                            <div class="info-row">
                                                <span class="info-label">Days Worked:</span>
                                                <span class="info-value" id="emp-days-worked">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-row">
                                                <span class="info-label">Leave Days:</span>
                                                <span class="info-value" id="emp-leave-days">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-row">
                                                <span class="info-label">Overtime Hours:</span>
                                                <span class="info-value" id="emp-overtime">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payroll Form -->
                            <form method="POST" id="payrollForm">
                                <input type="hidden" name="employee_id" id="form_employee_id">
                                <input type="hidden" name="payment_date" id="form_payment_date">
                                
                                <div class="form-section">
                                    <h4 class="form-section-title"><i class="fas fa-plus-circle me-2"></i>Earnings</h4>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="base_salary" class="form-label">Base Salary (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-money-bill"></i></span>
                                                    <input type="number" class="form-control" step="0.01" name="base_salary" id="base_salary" required onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="fixed_allowances" class="form-label">Fixed Allowances (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-plus"></i></span>
                                                    <input type="number" class="form-control" step="0.01" name="fixed_allowances" id="fixed_allowances" value="0" onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="overtime_pay" class="form-label">Overtime Pay (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                    <input type="number" class="form-
                                                    <input type="number" class="form-control" step="0.01" name="overtime_pay" id="overtime_pay" value="0" onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="performance_bonus" class="form-label">Performance Bonus (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-award"></i></span>
                                                    <input type="number" class="form-control" step="0.01" name="performance_bonus" id="performance_bonus" value="0" onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h4 class="form-section-title"><i class="fas fa-minus-circle me-2"></i>Deductions</h4>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="unpaid_leave_deductions" class="form-label">Unpaid Leave Deductions (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-calendar-minus"></i></span>
                                                    <input type="number" class="form-control" step="0.01" name="unpaid_leave_deductions" id="unpaid_leave_deductions" value="0" onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="loan_recovery" class="form-label">Loan Recovery (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-hand-holding-usd"></i></span>
                                                    <input type="number" class="form-control" step="0.01" name="loan_recovery" id="loan_recovery" value="0" onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="paye_tax" class="form-label">PAYE Tax (LKR):</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                                    <input type="number" class="form-control" step="0.01" name="paye_tax" id="paye_tax" value="0" onchange="calculateSalary()">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h4 class="form-section-title"><i class="fas fa-cog me-2"></i>Payment Details</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="payment_method" class="form-label">Payment Method:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                                    <select class="form-select" name="payment_method" id="payment_method" required>
                                                        <option value="Bank Transfer">Bank Transfer</option>
                                                        <option value="Cheque">Cheque</option>
                                                        <option value="Cash">Cash</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Salary Summary -->
                                <div class="form-section">
                                    <h4 class="form-section-title"><i class="fas fa-calculator me-2"></i>Salary Summary</h4>
                                    <div class="salary-calculator">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="payroll-summary">
                                                    <h5 class="text-primary mb-3">Earnings</h5>
                                                    <div class="summary-item">
                                                        <span>Base Salary</span>
                                                        <span id="summary-base-salary">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span>Fixed Allowances</span>
                                                        <span id="summary-fixed-allowances">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span>Overtime Pay</span>
                                                        <span id="summary-overtime">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span>Performance Bonus</span>
                                                        <span id="summary-bonus">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item summary-total">
                                                        <span>Gross Salary</span>
                                                        <span id="summary-gross">LKR 0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="payroll-summary">
                                                    <h5 class="text-danger mb-3">Deductions</h5>
                                                    <div class="summary-item">
                                                        <span>Employee EPF (8%)</span>
                                                        <span id="summary-epf">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span>PAYE Tax</span>
                                                        <span id="summary-paye">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span>Unpaid Leave Deductions</span>
                                                        <span id="summary-unpaid-leave">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item">
                                                        <span>Loan Recovery</span>
                                                        <span id="summary-loan">LKR 0.00</span>
                                                    </div>
                                                    <div class="summary-item summary-total">
                                                        <span>Total Deductions</span>
                                                        <span id="summary-deductions">LKR 0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="payroll-summary bg-light">
                                                    <div class="summary-item summary-total">
                                                        <span class="h5">Net Salary</span>
                                                        <span class="h5 text-success" id="summary-net">LKR 0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Create Payroll Record</button>
                                </div>
                            </form>
                            
                            <!-- Payroll Records Table -->
                            <div class="form-section mt-5">
                                <h4 class="form-section-title"><i class="fas fa-history me-2"></i>Recent Payroll Records</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Employee</th>
                                                <th>Payment Date</th>
                                                <th>Gross Salary</th>
                                                <th>Deductions</th>
                                                <th>Net Salary</th>
                                                <th>Method</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($payroll_records->num_rows > 0): ?>
                                                <?php while($record = $payroll_records->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($record['Payroll_ID']); ?></td>
                                                    <td><?php echo htmlspecialchars($record['FullName']); ?></td>
                                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($record['Payment_Date']))); ?></td>
                                                    <td>LKR <?php echo number_format($record['Gross_Salary'], 2); ?></td>
                                                    <td>LKR <?php echo number_format($record['Total_Deductions'], 2); ?></td>
                                                    <td>LKR <?php echo number_format($record['Net_Salary'], 2); ?></td>
                                                    <td><span class="badge-primary"><?php echo htmlspecialchars($record['Payment_Method']); ?></span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info" onclick="viewPayslip(<?php echo $record['Payroll_ID']; ?>)"><i class="fas fa-eye"></i></button>
                                                        <button class="btn btn-sm btn-primary" onclick="printPayslip(<?php echo $record['Payroll_ID']; ?>)"><i class="fas fa-print"></i></button>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No payroll records found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Department Statistics -->
                            <div class="form-section mt-5">
                                <h4 class="form-section-title"><i class="fas fa-chart-pie me-2"></i>Department Payroll Statistics</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Employee Count</th>
                                                <th>Average Salary</th>
                                                <th>Total Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($dept_stats->num_rows > 0): ?>
                                                <?php while($dept = $dept_stats->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($dept['Department_Name']); ?></td>
                                                    <td><?php echo htmlspecialchars($dept['employee_count']); ?></td>
                                                    <td>LKR <?php echo number_format($dept['avg_salary'], 2); ?></td>
                                                    <td>LKR <?php echo number_format($dept['total_salary'], 2); ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No department statistics available</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Menu
        function toggleMenu() {
            document.getElementById('menu-list').classList.toggle('d-none');
        }
        
        // Logout Function
        function logout() {
            window.location.href = 'logout.php';
        }
        
        // Fetch Employee Details
        function fetchEmployeeDetails() {
            const employeeId = document.getElementById('employee_id').value;
            const paymentDate = document.getElementById('payment_date').value;
            
            if (!employeeId) {
                document.getElementById('employee-info').classList.add('d-none');
                return;
            }
            
            // Set form hidden fields
            document.getElementById('form_employee_id').value = employeeId;
            document.getElementById('form_payment_date').value = paymentDate;
            
            // AJAX request to fetch employee details
            fetch(`HrSalary.php?employee_id=${employeeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show employee info section
                        document.getElementById('employee-info').classList.remove('d-none');
                        
                        // Update employee information
                        document.getElementById('emp-name').textContent = data.name;
                        document.getElementById('emp-department').textContent = data.department;
                        document.getElementById('emp-salary').textContent = `LKR ${parseFloat(data.salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                        document.getElementById('emp-performance').textContent = `${data.task_completion}%`;
                        document.getElementById('emp-days-worked').textContent = data.worked_days;
                        document.getElementById('emp-leave-days').textContent = data.leave_days;
                        document.getElementById('emp-overtime').textContent = `${data.overtime_hours} hours`;
                        
                        // Update form fields with calculated values
                        document.getElementById('base_salary').value = data.salary;
                        document.getElementById('overtime_pay').value = data.overtime_pay;
                        document.getElementById('unpaid_leave_deductions').value = data.unpaid_leave_deduction;
                        document.getElementById('performance_bonus').value = data.performance_bonus;
                        
                        // Calculate initial summary
                        calculateSalary();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to fetch employee details. Please try again.');
                });
        }
        
        // Calculate Salary Summary
        function calculateSalary() {
            // Get form values
            const baseSalary = parseFloat(document.getElementById('base_salary').value) || 0;
            const fixedAllowances = parseFloat(document.getElementById('fixed_allowances').value) || 0;
            const overtimePay = parseFloat(document.getElementById('overtime_pay').value) || 0;
            const performanceBonus = parseFloat(document.getElementById('performance_bonus').value) || 0;
            const unpaidLeaveDeductions = parseFloat(document.getElementById('unpaid_leave_deductions').value) || 0;
            const loanRecovery = parseFloat(document.getElementById('loan_recovery').value) || 0;
            const payeTax = parseFloat(document.getElementById('paye_tax').value) || 0;
            
            // Calculate EPF
            const employeeEpf = baseSalary * 0.08;
            
            // Calculate gross salary
            const grossSalary = baseSalary + fixedAllowances + overtimePay + performanceBonus - unpaidLeaveDeductions;
            
            // Calculate total deductions
            const totalDeductions = employeeEpf + payeTax + loanRecovery;
            
            // Calculate net salary
            const netSalary = grossSalary - totalDeductions;
            
            // Update summary values
            document.getElementById('summary-base-salary').textContent = `LKR ${baseSalary.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-fixed-allowances').textContent = `LKR ${fixedAllowances.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-overtime').textContent = `LKR ${overtimePay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-bonus').textContent = `LKR ${performanceBonus.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-gross').textContent = `LKR ${grossSalary.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            document.getElementById('summary-epf').textContent = `LKR ${employeeEpf.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-paye').textContent = `LKR ${payeTax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-unpaid-leave').textContent = `LKR ${unpaidLeaveDeductions.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-loan').textContent = `LKR ${loanRecovery.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('summary-deductions').textContent = `LKR ${totalDeductions.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            document.getElementById('summary-net').textContent = `LKR ${netSalary.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
        
        // View Payslip
        function viewPayslip(payrollId) {
            window.open(`payslip.php?id=${payrollId}`, '_blank');
        }
        
        // Print Payslip
        function printPayslip(payrollId) {
            window.open(`payslip_print.php?id=${payrollId}`, '_blank');
        }
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure all required elements exist before calling calculateSalary
            if (document.getElementById('base_salary') && document.getElementById('fixed_allowances') && document.getElementById('overtime_pay')) {
                // Set initial form values
                calculateSalary();
            }
            
            // Close alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>