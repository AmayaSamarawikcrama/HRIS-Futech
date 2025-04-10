<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hris_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data for displaying in nav
$user_id = $_SESSION['user_id'];
$query = "SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Get employee's salary details
$salary_query = "SELECT * FROM Payroll WHERE Employee_ID = ? ORDER BY Payment_Date DESC LIMIT 1";
$stmt = $conn->prepare($salary_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$salary_data = $result->fetch_assoc();

// Check if there's no salary data yet
$has_salary_data = ($salary_data !== null);

// Format currency function
function formatCurrency($amount) {
    return 'LKR ' . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Salary</title>
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
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
        }
        .no-data-message {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        .salary-breakdown {
            margin-top: 30px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .breakdown-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .breakdown-total {
            font-weight: bold;
            border-top: 2px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
        }
        .salary-title {
            position: relative;
            margin-bottom: 30px;
        }
        .salary-title:after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100px;
            height: 3px;
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3 shadow-sm">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="EmpDashboard.html" class="dropdown-item">Dashboard</a>
                    <a href="EmpProfile.html" class="dropdown-item">My Profile</a>
                    <a href="EmpLeave.html" class="dropdown-item">Leave Request</a>
                    <a href="EmpManualAttendance.html" class="dropdown-item">My Attendance</a>
                    <a href="EmpProject.html" class="dropdown-item">Projects</a>
                    <a href="EmpSalary.html" class="dropdown-item fw-bold text-primary">Salary Status</a>
                    <a href="Report.php" class="dropdown-item">Report</a>
                    <a href="Company.php" class="dropdown-item">Company Details</a>
                    <a href="Calendar.php" class="dropdown-item">Calendar</a>
                </div>
                
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="text-center mb-4">My Salary Details</h2>
            
            <?php if ($has_salary_data): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Latest Payroll Information - <?php echo date('F Y', strtotime($salary_data['Payment_Date'])); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th>Employee ID</th>
                                        <th>Base Salary</th>
                                        <th>Fixed Allowances</th>
                                        <th>Overtime Pay</th>
                                        <th>Unpaid Leave</th>
                                        <th>Loan Recovery</th>
                                        <th>PAYE Tax</th>
                                        <th>Gross Salary</th>
                                        <th>Total Deductions</th>
                                        <th>Net Salary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center">
                                        <td><?php echo htmlspecialchars($user_id); ?></td>
                                        <td><?php echo formatCurrency($salary_data['Base_Salary']); ?></td>
                                        <td><?php echo formatCurrency($salary_data['Fixed_Allowances']); ?></td>
                                        <td><?php echo formatCurrency($salary_data['Overtime_Pay']); ?></td>
                                        <td><?php echo formatCurrency($salary_data['Unpaid_Leave_Deductions']); ?></td>
                                        <td><?php echo formatCurrency($salary_data['Loan_Recovery']); ?></td>
                                        <td><?php echo formatCurrency($salary_data['PAYE_Tax']); ?></td>
                                        <td class="fw-bold"><?php echo formatCurrency($salary_data['Gross_Salary']); ?></td>
                                        <td class="fw-bold"><?php echo formatCurrency($salary_data['Total_Deductions']); ?></td>
                                        <td class="fw-bold text-success"><?php echo formatCurrency($salary_data['Net_Salary']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Earnings</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="breakdown-item">
                                            <span>Base Salary</span>
                                            <span><?php echo formatCurrency($salary_data['Base_Salary']); ?></span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span>Fixed Allowances</span>
                                            <span><?php echo formatCurrency($salary_data['Fixed_Allowances']); ?></span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span>Overtime Pay</span>
                                            <span><?php echo formatCurrency($salary_data['Overtime_Pay']); ?></span>
                                        </div>
                                        <div class="breakdown-total">
                                            <span>Gross Salary</span>
                                            <span><?php echo formatCurrency($salary_data['Gross_Salary']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Deductions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="breakdown-item">
                                            <span>Unpaid Leave Deductions</span>
                                            <span><?php echo formatCurrency($salary_data['Unpaid_Leave_Deductions']); ?></span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span>Loan Recovery</span>
                                            <span><?php echo formatCurrency($salary_data['Loan_Recovery']); ?></span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span>Employee EPF (8%)</span>
                                            <span><?php echo formatCurrency($salary_data['Employee_EPF']); ?></span>
                                        </div>
                                        <div class="breakdown-item">
                                            <span>PAYE Tax</span>
                                            <span><?php echo formatCurrency($salary_data['PAYE_Tax']); ?></span>
                                        </div>
                                        <div class="breakdown-total">
                                            <span>Total Deductions</span>
                                            <span><?php echo formatCurrency($salary_data['Total_Deductions']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Net Salary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-5">Final Take-Home Pay</span>
                                    <span class="fs-4 fw-bold"><?php echo formatCurrency($salary_data['Net_Salary']); ?></span>
                                </div>
                                <div class="small text-muted mt-2">
                                    <p>Payment Method: <?php echo htmlspecialchars($salary_data['Payment_Method']); ?></p>
                                    <p>Payment Date: <?php echo date('d F Y', strtotime($salary_data['Payment_Date'])); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Employer Contributions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="breakdown-item">
                                            <span>Employer EPF (12%)</span>
                                            <span><?php echo formatCurrency($salary_data['Employer_EPF']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="breakdown-item">
                                            <span>Employer ETF (3%)</span>
                                            <span><?php echo formatCurrency($salary_data['Employer_ETF']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">No Salary Information Available</h4>
                    <p>Your salary information has not been processed yet. Please check back later or contact the HR department for more information.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            document.getElementById('menu-list').classList.toggle('d-none');
        }
        
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>