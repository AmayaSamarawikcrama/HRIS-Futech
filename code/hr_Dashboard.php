


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="CSS/hr_Dashboard.css">
</head>
<body>
    <div class="container">
        <nav>
            <div class="menu-icon">&#9776;</div>
            <button onclick="logout()" class="logout">Log Out</button>
        </nav>
        <div class="dashboard">
            <div class="card">Total Employee</div>
            <div class="card">Present Today</div>
            <div class="card">Total Absent</div>
            <div class="card">On Leave Today</div>
        </div>
        <div class="buttons">
            <button>Employee Details</button>
            <button onclick="window.location.href = 'Add_Employee.php';">Add Employee</button>
            <button>Project</button>
            <button>Attendance</button>
            <button>Leave State</button>
            <button>Salary Status</button>
            <button>Employee Performance</button>
            <button>Report</button>
            <button>Company</button>
            <button>Calendar</button>
        </div>
    </div>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
        function EmpProfile() {
            window.location.href = "EmpProfile.php";
        }
    </script>
</body>
</html>
