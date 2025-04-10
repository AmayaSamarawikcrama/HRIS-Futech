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
        SUM(Work_Hours) as total_hours
        FROM Attendance 
        WHERE Employee_ID = ? 
        AND DATE_FORMAT(Date, '%Y-%m') = ?";
    $attendance_stmt = $conn->prepare($attendance_query);
    $attendance_stmt->bind_param("ss", $employee_id, $current_month);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    $attendance_data = $attendance_result->fetch_assoc();

    // Get leave data for current month
    $leave_query = "SELECT 
        COUNT(*) as leave_days,
        SUM(CASE WHEN Leave_Type != 'Annual Leave' THEN 1 ELSE 0 END) as unpaid_leave_days
        FROM Leave_Management 
        WHERE Employee_ID = ? 
        AND Approval_Status = 'Approved'
        AND (
            DATE_FORMAT(Start_Date, '%Y-%m') = ? OR 
            DATE_FORMAT(End_Date, '%Y-%m') = ?
        )";
    $leave_stmt = $conn->prepare($leave_query);
    $leave_stmt->bind_param("sss", $employee_id, $current_month, $current_month);
    $leave_stmt->execute();
    $leave_result = $leave_stmt->get_result();
    $leave_data = $leave_result->fetch_assoc();

    // Calculate unpaid leave deduction
    $working_days_in_month = date('t'); // Total days in current month
    $daily_rate = $employee['Salary'] / $working_days_in_month;
    $unpaid_leave_deduction = $leave_data['unpaid_leave_days'] * $daily_rate;

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
        'overtime_hours' => 0, // Calculate if needed
        'overtime_pay' => 0 // Calculate if needed
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .employee-name {
            font-weight: bold;
            color: red;
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
        }
        .salary-calculator {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2 navigation" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.html" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.html" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.html" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.html" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.html" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.html" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.html" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.html" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>
        
        <!-- Messages Section -->
        <?php if (!empty($message)): ?>
            <div class="container mt-3">
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="container mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card payroll-card">
                        <div class="card-header bg-primary text-white">
                            <h2 class="text-center mb-0">Payroll Management</h2>
                        </div>
                        <div class="card-body">
                            <!-- Payroll Entry Form -->
                            <form class="row g-3" method="POST" id="payrollForm">
                                <div class="col-md-6">
                                    <label for="employee_id" class="form-label">Employee:</label>
                                    <select class="form-select" name="employee_id" id="employee_id" required onchange="fetchEmployeeDetails()">
                                        <option value="">Select Employee</option>
                                        <?php while ($employee = $employees_result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($employee['Employee_ID']); ?>">
                                                <?php echo htmlspecialchars($employee['Employee_ID'] . ' - ' . $employee['FullName']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="payment_date" class="form-label">Payment Date:</label>
                                    <input type="date" class="form-control" name="payment_date" value="<?php echo $current_month; ?>" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="base_salary" class="form-label">Base Salary (LKR):</label>
                                    <input type="number" class="form-control" step="0.01" name="base_salary" id="base_salary" required onchange="calculateSalary()">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="fixed_allowances" class="form-label">Fixed Allowances (LKR):</label>
                                    <input type="number" class="form-control" step="0.01" name="fixed_allowances" id="fixed_allowances" value="0" onchange="calculateSalary()">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="overtime_pay" class="form-label">Overtime Pay (LKR):</label>
                                    <input type="number" class="form-control" step="0.01" name="overtime_pay" id="overtime_pay" value="0" onchange="calculateSalary()">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="unpaid_leave_deductions" class="form-label">Unpaid Leave Deductions (LKR):</label>
                                    <input type="number" class="form-control" step="0.01" name="unpaid_leave_deductions" id="unpaid_leave_deductions" value="0" onchange="calculateSalary()">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="loan_recovery" class="form-label">Loan Recovery (LKR):</label>
                                    <input type="number" class="form-control" step="0.01" name="loan_recovery" id="loan_recovery" value="0" onchange="calculateSalary()">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="paye_tax" class="form-label">PAYE Tax (LKR):</label>
                                    <input type="number" class="form-control" step="0.01" name="paye_tax" id="paye_tax" value="0" onchange="calculateSalary()">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Payment Method:</label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                </div>
                                
                                <!-- Salary Calculator Preview -->
                                <div class="col-md-6">
                                    <div class="salary-calculator">
                                        <h5>Salary Calculation Preview</h5>
                                        <div class="summary-item">
                                            <span>Employee EPF (8%):</span>
                                            <span id="employee_epf_preview">0.00</span>
                                        </div>
                                        <div class="summary-item">
                                            <span>Employer EPF (12%):</span>
                                            <span id="employer_epf_preview">0.00</span>
                                        </div>
                                        <div class="summary-item">
                                            <span>Employer ETF (3%):</span>
                                            <span id="employer_etf_preview">0.00</span>
                                        </div>
                                        <div class="summary-item">
                                            <span>Gross Salary:</span>
                                            <span id="gross_salary_preview">0.00</span>
                                        </div>
                                        <div class="summary-item">
                                            <span>Total Deductions:</span>
                                            <span id="total_deductions_preview">0.00</span>
                                        </div>
                                        <div class="summary-item">
                                            <strong>Net Salary:</strong>
                                            <strong id="net_salary_preview">0.00</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" class="btn btn-primary">Submit Payroll</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mt-4 mb-5">
            <div class="card payroll-card">
                <div class="card-header bg-primary text-white">
                    <h2 class="text-center mb-0">Employee Payroll Records</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Employee Name</th>
                                    <th>Base Salary</th>
                                    <th>Fixed Allowances</th>
                                    <th>Overtime Pay</th>
                                    <th>Unpaid Leave</th>
                                    <th>EPF (8%)</th>
                                    <th>PAYE Tax</th>
                                    <th>Loan Recovery</th>
                                    <th>Gross Salary</th>
                                    <th>Net Salary</th>
                                    <th>Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payroll_records && $payroll_records->num_rows > 0): ?>
                                    <?php while ($record = $payroll_records->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['Employee_ID']); ?></td>
                                            <td><?php echo htmlspecialchars($record['FullName']); ?></td>
                                            <td><?php echo number_format($record['Base_Salary'], 2); ?></td>
                                            <td><?php echo number_format($record['Fixed_Allowances'], 2); ?></td>
                                            <td><?php echo number_format($record['Overtime_Pay'], 2); ?></td>
                                            <td><?php echo number_format($record['Unpaid_Leave_Deductions'], 2); ?></td>
                                            <td><?php echo number_format($record['Employee_EPF'], 2); ?></td>
                                            <td><?php echo number_format($record['PAYE_Tax'], 2); ?></td>
                                            <td><?php echo number_format($record['Loan_Recovery'], 2); ?></td>
                                            <td><?php echo number_format($record['Gross_Salary'], 2); ?></td>
                                            <td><?php echo number_format($record['Net_Salary'], 2); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($record['Payment_Date'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center">No payroll records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            const menuList = document.getElementById('menu-list');
            menuList.classList.toggle('d-none');
        }
        
        function logout() {
            window.location.href = "logout.php";
        }
        
        function fetchEmployeeDetails() {
            const employeeId = document.getElementById('employee_id').value;
            if (!employeeId) return;
            
            // Fetch employee details using AJAX - now directly to this same file
            fetch('salary.php?employee_id=' + employeeId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('base_salary').value = data.salary;
                        document.getElementById('unpaid_leave_deductions').value = data.unpaid_leave_deduction;
                        // Set other fields based on returned data if needed
                        calculateSalary();
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching employee details:', error));
        }
        
        function calculateSalary() {
            // Get values
            const baseSalary = parseFloat(document.getElementById('base_salary').value) || 0;
            const fixedAllowances = parseFloat(document.getElementById('fixed_allowances').value) || 0;
            const overtimePay = parseFloat(document.getElementById('overtime_pay').value) || 0;
            const unpaidLeaveDeductions = parseFloat(document.getElementById('unpaid_leave_deductions').value) || 0;
            const loanRecovery = parseFloat(document.getElementById('loan_recovery').value) || 0;
            const payeTax = parseFloat(document.getElementById('paye_tax').value) || 0;
            
            // Calculate components
            const employeeEpf = baseSalary * 0.08;
            const employerEpf = baseSalary * 0.12;
            const employerEtf = baseSalary * 0.03;
            const grossSalary = baseSalary + fixedAllowances + overtimePay - unpaidLeaveDeductions;
            const totalDeductions = employeeEpf + payeTax + loanRecovery;
            const netSalary = grossSalary - totalDeductions;
            
            // Update preview
            document.getElementById('employee_epf_preview').textContent = employeeEpf.toFixed(2);
            document.getElementById('employer_epf_preview').textContent = employerEpf.toFixed(2);
            document.getElementById('employer_etf_preview').textContent = employerEtf.toFixed(2);
            document.getElementById('gross_salary_preview').textContent = grossSalary.toFixed(2);
            document.getElementById('total_deductions_preview').textContent = totalDeductions.toFixed(2);
            document.getElementById('net_salary_preview').textContent = netSalary.toFixed(2);
        }
        
        // Initialize calculation on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateSalary();
        });
    </script>
</body>
</html>