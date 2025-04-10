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
        .table th{
            background-color: #abcff3;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
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
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php
                        // Assuming $user_data is defined and contains user information
                        echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                    ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>
        
        <div class="container mt-4">
            <h2 class="text-center">Payroll Management</h2>
            <form class="row g-3" method="POST">
                <div class="col-md-6">
                    <label for="employee_id" class="form-label">Employee ID:</label>
                    <input type="text" class="form-control" name="employee_id" required>
                </div>
                <div class="col-md-6">
                    <label for="base_salary" class="form-label">Base Salary:</label>
                    <input type="number" class="form-control" step="0.01" name="base_salary" required>
                </div>
                <div class="col-md-6">
                    <label for="fixed_allowances" class="form-label">Fixed Allowances:</label>
                    <input type="number" class="form-control" step="0.01" name="fixed_allowances">
                </div>
                <div class="col-md-6">
                    <label for="overtime_pay" class="form-label">Overtime Pay:</label>
                    <input type="number" class="form-control" step="0.01" name="overtime_pay">
                </div>
                <div class="col-md-6">
                    <label for="unpaid_leave_deductions" class="form-label">Unpaid Leave Deductions:</label>
                    <input type="number" class="form-control" step="0.01" name="unpaid_leave_deductions">
                </div>
                <div class="col-md-6">
                    <label for="loan_recovery" class="form-label">Loan Recovery:</label>
                    <input type="number" class="form-control" step="0.01" name="loan_recovery">
                </div>
                <div class="col-md-6">
                    <label for="paye_tax" class="form-label">PAYE Tax:</label>
                    <input type="number" class="form-control" step="0.01" name="paye_tax">
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary">Submit Payroll</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="container mt-4">
            <h2 class="text-center">Employee Payroll Records</h2>
            <table class="table table-striped table-bordered text-center">
                
                <thead class="table-light">
                    <tr>
                        <th>Employee ID</th>
                        <th>Base Salary</th>
                        <th>Fixed Allowances</th>
                        <th>Overtime Pay</th>
                        <th>Unpaid Leave Deductions</th>
                        <th>Loan Recovery</th>
                        <th>PAYE Tax</th>
                        <th>Gross Salary</th>
                        <th>Total Deductions</th>
                        <th>Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>101</td>
                        <td>$3000</td>
                        <td>$200</td>
                        <td>$150</td>
                        <td>$100</td>
                        <td>$50</td>
                        <td>$300</td>
                        <td>$3350</td>
                        <td>$450</td>
                        <td>$2900</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>
