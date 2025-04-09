<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance and Task Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap">
    <style>
        .menu-icon {
            font-size: 25px;
            cursor: pointer;
            color: #0d6efd;
        }
        .profile-pic {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        .table th {
            background-color: #abcff3;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
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
                </script>                <div class="ms-auto d-flex align-items-center">
                    <button class="btn btn-primary me-3" onclick="logout()">Log Out</button>
                    <img class="profile rounded-circle" src="assets/image.png" alt="Profilr img" width="40" height="40" onclick="location.href='assets/image.png'">
                </div>
            </div>
        </nav>

        <div class="container mt-3">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="departmentSearch" placeholder="Search Departments">
                        <button class="btn btn-primary" onclick="searchDepartments()">Search</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>Logout Time</th>
                            <th>Work Hours</th>
                            <th>Assigned Task</th>
                            <th>Task Completion (%)</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><img src="assets/image.png" alt="Profile" class="profile-pic"> John Doe</td>
                            <td>Engineering</td>
                            <td>July 08, 2023</td>
                            <td>09:00 AM</td>
                            <td>05:30 PM</td>
                            <td>8.5 hours</td>
                            <td>Develop New Feature</td>
                            <td>80%</td>
                            <td>Completed most of the tasks, pending testing.</td>
                        </tr>
                        <tr>
                            <td><img src="assets/image.png" alt="Profile" class="profile-pic"> Jane Smith</td>
                            <td>Marketing</td>
                            <td>July 08, 2023</td>
                            <td>10:00 AM</td>
                            <td>06:00 PM</td>
                            <td>6 hours</td>
                            <td>UI Design for Dashboard</td>
                            <td>100%</td>
                            <td>Task completed successfully, no issues.</td>
                        </tr>
                        <tr>
                            <td><img src="assets/image.png" alt="Profile" class="profile-pic"> Mike Johnson</td>
                            <td>Engineering</td>
                            <td>July 09, 2023</td>
                            <td>08:30 AM</td>
                            <td>04:30 PM</td>
                            <td>8 hours</td>
                            <td>Database Optimization</td>
                            <td>60%</td>
                            <td>Some tasks were delayed due to data inconsistencies.</td>
                        </tr>
                      
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchDepartments() {
            const input = document.getElementById('departmentSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            
            rows.forEach(row => {
                const department = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                row.style.display = department.includes(input) ? '' : 'none';
            });
        }
        
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>

