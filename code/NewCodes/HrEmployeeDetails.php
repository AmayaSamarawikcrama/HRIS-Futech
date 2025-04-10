<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function getDBConnection() {
    $conn = new mysqli('localhost', 'root', '', 'hris_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle PDF generation request
if (isset($_GET['generate_pdf']) && isset($_GET['employee_id'])) {
    require('./fdpdf186/fpdf.php');
    
    $conn = getDBConnection();
    $employee_id = $_GET['employee_id'];
    
    // Get complete employee data
    $sql = "SELECT e.*, d.Department_Name 
            FROM Employee e
            LEFT JOIN Department d ON e.Department_ID = d.Department_ID
            WHERE e.Employee_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $employee_id); // Changed to string parameter
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "Employee not found.";
        exit();
    }
    
    $employee_data = $result->fetch_assoc();
    $stmt->close();
    
    // Get performance data
    $sql_performance = "SELECT Performance_Rating, Strengths, Recommendations 
                        FROM Employee_Performance 
                        WHERE Employee_ID = ? 
                        ORDER BY Performance_ID DESC LIMIT 1";
    $stmt_performance = $conn->prepare($sql_performance);
    $stmt_performance->bind_param("s", $employee_id);
    $stmt_performance->execute();
    $performance_result = $stmt_performance->get_result();
    $performance_data = $performance_result->num_rows > 0 ? $performance_result->fetch_assoc() : null;
    $stmt_performance->close();
    
    // Get attendance data (most recent)
    $sql_attendance = "SELECT Date, Log_In_Time, Log_Out_Time, Work_Hours 
                      FROM Attendance 
                      WHERE Employee_ID = ? 
                      ORDER BY Date DESC LIMIT 5";
    $stmt_attendance = $conn->prepare($sql_attendance);
    $stmt_attendance->bind_param("s", $employee_id);
    $stmt_attendance->execute();
    $attendance_result = $stmt_attendance->get_result();
    $attendance_data = [];
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $stmt_attendance->close();
    
    // Get leave data
    $sql_leave = "SELECT Leave_Type, Start_Date, End_Date, Approval_Status 
                 FROM Leave_Management 
                 WHERE Employee_ID = ? 
                 ORDER BY Start_Date DESC LIMIT 5";
    $stmt_leave = $conn->prepare($sql_leave);
    $stmt_leave->bind_param("s", $employee_id);
    $stmt_leave->execute();
    $leave_result = $stmt_leave->get_result();
    $leave_data = [];
    while ($row = $leave_result->fetch_assoc()) {
        $leave_data[] = $row;
    }
    $stmt_leave->close();
    
    // Get payroll data
    $sql_payroll = "SELECT Base_Salary, Fixed_Allowances, Overtime_Pay, Total_Deductions, Net_Salary, Payment_Date 
                   FROM Payroll 
                   WHERE Employee_ID = ? 
                   ORDER BY Payment_Date DESC LIMIT 1";
    $stmt_payroll = $conn->prepare($sql_payroll);
    $stmt_payroll->bind_param("s", $employee_id);
    $stmt_payroll->execute();
    $payroll_result = $stmt_payroll->get_result();
    $payroll_data = $payroll_result->num_rows > 0 ? $payroll_result->fetch_assoc() : null;
    $stmt_payroll->close();
    
    $conn->close();

    // Create PDF with FPDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    // Header with company logo and title
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, 'EMPLOYEE DETAILS REPORT', 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 5, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Employee Basic Information
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(0, 10, 'PERSONAL INFORMATION', 1, 1, 'L', true);
    
    $pdf->SetFont('Arial', '', 11);
    
    // Personal Information
    $pdf->Cell(60, 8, 'Employee ID:', 1);
    $pdf->Cell(130, 8, $employee_data['Employee_ID'], 1, 1);
    
    $pdf->Cell(60, 8, 'Full Name:', 1);
    $pdf->Cell(130, 8, $employee_data['First_Name'] . ' ' . $employee_data['Last_Name'], 1, 1);
    
    $pdf->Cell(60, 8, 'Date of Birth:', 1);
    $pdf->Cell(130, 8, $employee_data['Date_of_Birth'], 1, 1);
    
    $pdf->Cell(60, 8, 'Gender:', 1);
    $pdf->Cell(130, 8, $employee_data['Gender'], 1, 1);
    
    $pdf->Cell(60, 8, 'Blood Type:', 1);
    $pdf->Cell(130, 8, $employee_data['Blood_Type'] ?? 'Not Specified', 1, 1);
    
    $pdf->Cell(60, 8, 'Marital Status:', 1);
    $pdf->Cell(130, 8, $employee_data['Marital_Status'], 1, 1);
    
    $pdf->Cell(60, 8, 'Address:', 1);
    $pdf->Cell(130, 8, $employee_data['Address'], 1, 1);
    
    $pdf->Cell(60, 8, 'Contact Number:', 1);
    $pdf->Cell(130, 8, $employee_data['Contact_Number'], 1, 1);
    
    $pdf->Cell(60, 8, 'Email:', 1);
    $pdf->Cell(130, 8, $employee_data['Email'], 1, 1);
    
    // Work Information
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'EMPLOYMENT INFORMATION', 1, 1, 'L', true);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(60, 8, 'Employee Type:', 1);
    $pdf->Cell(130, 8, $employee_data['Employee_Type'], 1, 1);
    
    $pdf->Cell(60, 8, 'Department:', 1);
    $pdf->Cell(130, 8, $employee_data['Department_Name'] ?? 'Not Assigned', 1, 1);
    
    $pdf->Cell(60, 8, 'Hire Date:', 1);
    $pdf->Cell(130, 8, $employee_data['Hire_Date'], 1, 1);
    
    $pdf->Cell(60, 8, 'Salary:', 1);
    $pdf->Cell(130, 8, '$' . number_format($employee_data['Salary'], 2), 1, 1);
    
    $pdf->Cell(60, 8, 'Insurance:', 1);
    $pdf->Cell(130, 8, $employee_data['Insurance'] ?? 'Not Specified', 1, 1);
    
    $pdf->Cell(60, 8, 'Qualification:', 1);
    $pdf->Cell(130, 8, $employee_data['Qualification'] ?? 'Not Specified', 1, 1);
    
    // Performance Information (if available)
    if ($performance_data) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'PERFORMANCE EVALUATION', 1, 1, 'L', true);
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, 'Performance Rating:', 1);
        $pdf->Cell(130, 8, $performance_data['Performance_Rating'], 1, 1);
        
        $pdf->Cell(60, 8, 'Strengths:', 1);
        $pdf->Cell(130, 8, $performance_data['Strengths'], 1, 1);
        
        $pdf->Cell(60, 8, 'Recommendations:', 1);
        $pdf->Cell(130, 8, $performance_data['Recommendations'], 1, 1);
    }
    
    // Attendance Information (if available)
    if (count($attendance_data) > 0) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'RECENT ATTENDANCE', 1, 1, 'L', true);
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 8, 'Date', 1);
        $pdf->Cell(40, 8, 'Log In Time', 1);
        $pdf->Cell(40, 8, 'Log Out Time', 1);
        $pdf->Cell(70, 8, 'Work Hours', 1, 1);
        
        $pdf->SetFont('Arial', '', 11);
        foreach ($attendance_data as $attendance) {
            $pdf->Cell(40, 8, $attendance['Date'], 1);
            $pdf->Cell(40, 8, $attendance['Log_In_Time'], 1);
            $pdf->Cell(40, 8, $attendance['Log_Out_Time'] ?? 'Not Logged Out', 1);
            $pdf->Cell(70, 8, $attendance['Work_Hours'] ?? 'N/A', 1, 1);
        }
    }
    
    // Leave Information (if available)
    if (count($leave_data) > 0) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'LEAVE RECORDS', 1, 1, 'L', true);
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 8, 'Leave Type', 1);
        $pdf->Cell(40, 8, 'Start Date', 1);
        $pdf->Cell(40, 8, 'End Date', 1);
        $pdf->Cell(70, 8, 'Status', 1, 1);
        
        $pdf->SetFont('Arial', '', 11);
        foreach ($leave_data as $leave) {
            $pdf->Cell(40, 8, $leave['Leave_Type'], 1);
            $pdf->Cell(40, 8, $leave['Start_Date'], 1);
            $pdf->Cell(40, 8, $leave['End_Date'], 1);
            $pdf->Cell(70, 8, $leave['Approval_Status'], 1, 1);
        }
    }
    
    // Payroll Information (if available)
    if ($payroll_data) {
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'RECENT PAYROLL', 1, 1, 'L', true);
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, 'Base Salary:', 1);
        $pdf->Cell(130, 8, '$' . number_format($payroll_data['Base_Salary'], 2), 1, 1);
        
        $pdf->Cell(60, 8, 'Fixed Allowances:', 1);
        $pdf->Cell(130, 8, '$' . number_format($payroll_data['Fixed_Allowances'], 2), 1, 1);
        
        $pdf->Cell(60, 8, 'Overtime Pay:', 1);
        $pdf->Cell(130, 8, '$' . number_format($payroll_data['Overtime_Pay'], 2), 1, 1);
        
        $pdf->Cell(60, 8, 'Total Deductions:', 1);
        $pdf->Cell(130, 8, '$' . number_format($payroll_data['Total_Deductions'], 2), 1, 1);
        
        $pdf->Cell(60, 8, 'Net Salary:', 1);
        $pdf->Cell(130, 8, '$' . number_format($payroll_data['Net_Salary'], 2), 1, 1);
        
        $pdf->Cell(60, 8, 'Payment Date:', 1);
        $pdf->Cell(130, 8, $payroll_data['Payment_Date'], 1, 1);
    }
    
    // Footer
    $pdf->SetY(-15);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . ' - This is a confidential document.', 0, 0, 'C');
    
    // Output PDF
    $pdf->Output('D', 'Employee_Details_' . $employee_data['Employee_ID'] . '.pdf');
    exit();
}

// Normal page rendering
$conn = getDBConnection();

// Get current user data
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user_id); // Changed to string parameter
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();
$stmt_user->close();

// Get all departments
$sql_departments = "SELECT Department_ID, Department_Name FROM Department ORDER BY Department_Name";
$departments_result = $conn->query($sql_departments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .employee-table-container {
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        .table thead {
            background-color: #abcff3;
        }

        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        
        .department-heading {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .employee-name {
            font-weight: bold;
            color: #dc3545;
        }
        
        .btn-pdf {
            background-color: #dc3545;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-pdf:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 5px;
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
            opacity: 0;
            transition: all 0.3s;
        }
        
        .loading-overlay.show {
            visibility: visible;
            opacity: 1;
        }
        
        .spinner-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3">Generating PDF, please wait...</p>
        </div>
    </div>

    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.php" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="window.location.href='?logout=true'">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='profile.php'">
            </div>
        </nav>

        <div class="employee-table-container">
            <div class="mb-4">
                <form id="searchForm" class="d-flex justify-content-center">
                    <input type="text" id="searchInput" class="form-control w-50" placeholder="Search by Employee ID, Name or Position">
                    <button type="button" class="btn btn-primary ms-2" onclick="searchEmployee()">Search</button>
                    <button type="button" class="btn btn-secondary ms-2" onclick="resetSearch()">Reset</button>
                </form>
            </div>

            <h2 class="text-center mb-4">Employee Details by Department</h2>

            <div class="table-responsive">
                <?php
                if ($departments_result->num_rows > 0) {
                    while ($department = $departments_result->fetch_assoc()) {
                        $dept_id = $department['Department_ID'];
                        $dept_name = htmlspecialchars($department['Department_Name']);
                        
                        $sql_employees = "SELECT e.Employee_ID, e.First_Name, e.Last_Name, 
                                         e.Employee_Type as Position, e.Email, e.Contact_Number, e.Salary
                                      FROM Employee e
                                      WHERE e.Department_ID = ?
                                      ORDER BY e.Last_Name, e.First_Name";
                        
                        $stmt = $conn->prepare($sql_employees);
                        $stmt->bind_param("i", $dept_id);
                        $stmt->execute();
                        $employees_result = $stmt->get_result();
                        
                        if ($employees_result->num_rows > 0) {
                            echo "<div class='department-heading'>{$dept_name} Department</div>";
                            echo "<table class='table table-bordered table-striped mb-5'>";
                            echo "<thead><tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Position</th>
                                    <th>Email</th>
                                    <th>Contact Number</th>
                                    <th>Salary</th>
                                    <th>Action</th>
                                </tr></thead><tbody>";
                            
                            while ($employee = $employees_result->fetch_assoc()) {
                                $emp_id = htmlspecialchars($employee['Employee_ID']);
                                echo "<tr>";
                                echo "<td>" . $emp_id . "</td>";
                                echo "<td>" . htmlspecialchars($employee['First_Name'] . ' ' . $employee['Last_Name']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['Position']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['Email']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['Contact_Number']) . "</td>";
                                echo "<td>$" . number_format($employee['Salary'], 2) . "</td>";
                                echo "<td class='action-buttons'>
                                        <button onclick='generatePDF(\"" . $emp_id . "\")' class='btn btn-pdf btn-sm'>
                                            <i class='bi bi-file-earmark-pdf'></i> PDF
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table>";
                        }
                        $stmt->close();
                    }
                } else {
                    echo "<div class='alert alert-info'>No departments found.</div>";
                }
                
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generatePDF(employeeId) {
            document.getElementById('loadingOverlay').classList.add('show');
            
            // Create a hidden iframe to download the PDF without leaving the page
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            
            iframe.src = `?generate_pdf=true&employee_id=${employeeId}`;
            
            // Hide loading overlay after download starts
            iframe.onload = function() {
                setTimeout(function() {
                    document.getElementById('loadingOverlay').classList.remove('show');
                    document.body.removeChild(iframe);
                }, 1000);
            };
        }

        function searchEmployee() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const tables = document.querySelectorAll('.table tbody');
            let anyResultsFound = false;

            tables.forEach(tbody => {
                const rows = tbody.querySelectorAll('tr');
                let foundInTable = false;
                
                rows.forEach(row => {
                    const employeeId = row.cells[0].textContent.toLowerCase();
                    const name = row.cells[1].textContent.toLowerCase();
                    const position = row.cells[2].textContent.toLowerCase();
                    
                    if (employeeId.includes(input) || name.includes(input) || position.includes(input)) {
                        row.style.display = '';
                        foundInTable = true;
                        anyResultsFound = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const departmentHeading = tbody.closest('.table').previousElementSibling;
                if (departmentHeading && departmentHeading.classList.contains('department-heading')) {
                    departmentHeading.style.display = foundInTable ? '' : 'none';
                }
                
                const table = tbody.closest('.table');
                table.style.display = foundInTable ? '' : 'none';
            });

            if (!anyResultsFound && input !== '') {
                alert('No employees found matching your search criteria.');
            }
        }

        function resetSearch() {
            document.getElementById('searchInput').value = '';
            const tables = document.querySelectorAll('.table');
            const headings = document.querySelectorAll('.department-heading');
            
            tables.forEach(table => {
                table.style.display = '';
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
            });
            
            headings.forEach(heading => {
                heading.style.display = '';
            });
        }

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchEmployee();
            }
        });
    </script>
</body>
</html>