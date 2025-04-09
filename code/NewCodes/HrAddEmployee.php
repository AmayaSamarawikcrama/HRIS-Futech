<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
       

        .field-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
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
                    <a href="Calendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
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

        <div class="container ">
            <h2 class="text-center">Add New Employee</h2>

            <form id="addEmployeeForm">
                <div class="row mb-3">
                    <div class="col-md-3 field-label">First Name</div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter First Name" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Last Name</div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter Last Name" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Date of Birth</div>
                    <div class="col-md-9">
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Gender</div>
                    <div class="col-md-9">
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Address</div>
                    <div class="col-md-9">
                        <textarea class="form-control" id="address" name="address" placeholder="Enter Address"></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Contact Number</div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="contact_no" name="contact_no" pattern="[0-9]{10,15}" placeholder="Enter 10-15 digits">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Email</div>
                    <div class="col-md-9">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Password</div>
                    <div class="col-md-9">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Qualification</div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="qualification" name="qualification" placeholder="Enter Qualification">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Insurance</div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="insurance" name="insurance" placeholder="Enter Insurance Details">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Blood Type</div>
                    <div class="col-md-9">
                        <select class="form-select" id="blood_type" name="blood_type">
                            <option value="" selected>Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Marital Status</div>
                    <div class="col-md-9">
                        <select class="form-select" id="marital_status" name="marital_status">
                            <option value="" selected>Select Marital Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Hire Date</div>
                    <div class="col-md-9">
                        <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Salary</div>
                    <div class="col-md-9">
                        <input type="number" class="form-control" id="salary" name="salary" placeholder="Enter Salary" min="0" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Department</div>
                    <div class="col-md-9">
                        <input type="number" class="form-control" id="department_id" name="department_id" placeholder="Enter Department ID" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Manager ID</div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="manager_id" name="manager_id" placeholder="Enter Manager ID">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Employee Type</div>
                    <div class="col-md-9">
                        <select class="form-select" id="employee_type" name="employee_type" required>
                            <option value="" selected>Select Employee Type</option>
                            <option value="HumanResource Manager">Human Resource Manager</option>
                            <option value="Employee">Employee</option>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
