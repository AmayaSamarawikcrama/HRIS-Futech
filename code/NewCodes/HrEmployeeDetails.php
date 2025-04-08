<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employee Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .employee-table-container {
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            text-align: center;
        }

        .table thead {
            background-color: #abcff3;
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

        <div class="employee-table-container">
            <div class="mb-4">
                <form id="searchForm" class="d-flex justify-content-center">
                    <input type="text" id="searchInput" class="form-control w-50" placeholder="Search by Employee ID">
                    <button type="button" class="btn btn-primary ms-2" onclick="searchEmployee()">Search</button>
                </form>
            </div>

            <script>
                function searchEmployee() {
                    const input = document.getElementById('searchInput').value.toLowerCase();
                    const tables = document.querySelectorAll('.table tbody');

                    tables.forEach(tbody => {
                        const rows = tbody.querySelectorAll('tr');
                        rows.forEach(row => {
                            const employeeId = row.cells[0].textContent.toLowerCase();
                            if (employeeId.includes(input)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                }
            </script>
            <h2 class="text-center">Employee Details by Department</h2>

            <div class="table-responsive">
                <!-- HR Department Table -->
                <h4>HR Department</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example employee in HR Department -->
                        <tr>
                            <td>E12345</td>
                            <td>John Doe</td>
                            <td>HR Manager</td>
                            <td>john.doe@example.com</td>
                            <td>1234567890</td>
                            <td>$50,000</td>
                            <td>
                                <a href="viewEmployeeDetails.html" class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                        <!-- More employee rows go here -->
                    </tbody>
                </table>

                <!-- Sales Department Table -->
                <h4>Sales Department</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example employee in Sales Department -->
                        <tr>
                            <td>E12346</td>
                            <td>Jane Smith</td>
                            <td>Sales Executive</td>
                            <td>jane.smith@example.com</td>
                            <td>0987654321</td>
                            <td>$40,000</td>
                            <td>
                                <a href="viewEmployeeDetails.html" class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                        <!-- More employee rows go here -->
                    </tbody>
                </table>

                <!-- IT Department Table -->
                <h4>IT Department</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Salary</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Example employee in IT Department -->
                        <tr>
                            <td>E12347</td>
                            <td>Michael Johnson</td>
                            <td>Software Engineer</td>
                            <td>michael.johnson@example.com</td>
                            <td>1122334455</td>
                            <td>$60,000</td>
                            <td>
                                <a href="viewEmployeeDetails.html" class="btn btn-primary btn-sm">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
