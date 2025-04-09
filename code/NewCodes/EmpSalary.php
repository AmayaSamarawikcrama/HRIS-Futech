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
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="EmpDashboard.html" class="dropdown-item">Dashboard</a>
                    <a href="EmpProfile.html" class="dropdown-item">My Profile</a>
                    <a href="EmpLeave.html" class="dropdown-item">Leave Request</a>
                    <a href="EmpManualAttendance.html" class="dropdown-item">My Attendance</a>
                    <a href="EmpProject.html" class="dropdown-item"> Projects</a>
                    <a href="EmpSalary.html" class="dropdown-item">Salary Status</a>
                    <a href="Report.php" class="dropdown-item">Report</a>
                    <a href="Company.php" class="dropdown-item">Company Details</a>
                    <a href="Calendar.php" class="dropdown-item">Calendar</a>
                </div>
                <script>
                    function toggleMenu() {
                        document.getElementById('menu-list').classList.toggle('d-none');
                    }
                </script>
            <button class="btn btn-primary me-2 ms-auto" onclick="logout()">Log Out</button>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="text-center">My Salary Details</h2>
            <table class="table table-bordered mt-3">
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
                        <td>EMP123</td>
                        <td>$3000</td>
                        <td>$200</td>
                        <td>$150</td>
                        <td>$50</td>
                        <td>$100</td>
                        <td>$300</td>
                        <td>$3350</td>
                        <td>$450</td>
                        <td>$2900</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>
