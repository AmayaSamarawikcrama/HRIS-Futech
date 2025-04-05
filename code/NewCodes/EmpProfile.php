<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Information</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .profile-field {
            margin-bottom: 15px;
        }
        
        .field-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .field-value {
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }

        @media (max-width: 576px) {
            .profile-container {
                padding: 15px;
            }
            .profile-field {
                flex-direction: column;
            }
            .field-label {
                margin-bottom: 10px;
            }
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
                    <a href="EmpProfile.php" class="dropdown-item">My Profile</a>
                    <a href="EmplLeave.php" class="dropdown-item">Leave Request</a>
                    <a href="EmpManualAttendance.html" class="dropdown-item">My Attendance</a>
                    <a href="Performance.php" class="dropdown-item">My Performance</a>
                    <a href="EmpSalary.html" class="dropdown-item">Salary Status</a>
                    <a href="Report.php" class="dropdown-item">Report</a>
                    <a href="Company.php" class="dropdown-item">Company Details</a>
                    <a href="Calendar.php" class="dropdown-item">Calendar</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                    
                    function logout() {
                        window.location.href = 'logout.php';
                    }
                </script>
                <button class="btn btn-primary me-2 ms-auto" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>
        <div class="container ">
            <div class="profile-header">
                <h2>Profile</h2>
            </div>
            <div class="row profile-field align-items-center">
                <div class="col-md-3 field-label">Profile Photo</div>
                <div class="col-md-9">
                    <div class="d-flex align-items-center">
                        <img src="assets/image.png" alt="Profile Photo" class="rounded-circle me-3" style="width: 80px; height: 80px; object-fit: cover;">
                        <button class="btn btn-primary" onclick="document.getElementById('profilePhotoInput').click()">Change Photo</button>
                        <input type="file" id="profilePhotoInput" class="d-none" accept="image/*" onchange="previewProfilePhoto(event)">
                    </div>
                </div>
            </div>
            <script>
                function previewProfilePhoto(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.querySelector('.profile-field img');
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            </script>
            <div class="row profile-field">
                <div class="col-md-3 field-label">First Name</div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="first_name" name="first_name" value="John" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Last Name</div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="last_name" name="last_name" value="Doe" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Date of Birth</div>
                <div class="col-md-9">
                    <input type="date" class="form-control" id="dob" name="dob" value="1990-01-01" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Gender</div>
                <div class="col-md-9">
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="Male" selected>Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Address</div>
                <div class="col-md-9">
                    <textarea class="form-control" id="address" name="address">123 Main Street</textarea>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Contact Number</div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="contact_no" name="contact_no" pattern="[0-9]{10,15}" value="1234567890" placeholder="Enter 10-15 digits">
                </div>
            </div>

           

            <div class="row profile-field">
                <div class="col-md-3 field-label">Email</div>
                <div class="col-md-9">
                    <input type="email" class="form-control" id="email" name="email" value="john.doe@example.com" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Password</div>
                <div class="col-md-9">
                    <input type="password" class="form-control" id="password" name="password" value="password123" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Qualification</div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="qualification" name="qualification" value="Bachelor's Degree">
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Insurance</div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="insurance" name="insurance" value="Health Insurance">
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Blood Type</div>
                <div class="col-md-9">
                    <select class="form-select" id="blood_type" name="blood_type">
                        <option value="">Select Blood Type</option>
                        <option value="A+" selected>A+</option>
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

            <div class="row profile-field">
                <div class="col-md-3 field-label">Marital Status</div>
                <div class="col-md-9">
                    <select class="form-select" id="marital_status" name="marital_status">
                        <option value="Single" selected>Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Hire Date</div>
                <div class="col-md-9">
                    <input type="date" class="form-control" id="hire_date" name="hire_date" value="2022-01-01" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Salary</div>
                <div class="col-md-9">
                    <input type="number" class="form-control" id="salary" name="salary" value="50000" min="0" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Department</div>
                <div class="col-md-9">
                    <input type="number" class="form-control" id="department_id" name="department_id" value="101" required>
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Manager ID</div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="manager_id" name="manager_id" value="MGR123">
                </div>
            </div>

            <div class="row profile-field">
                <div class="col-md-3 field-label">Employee Type</div>
                <div class="col-md-9">
                    <select class="form-select" id="employee_type" name="employee_type" required>
                        <option value="HumanResource Manager">Human Resource Manager</option>
                        <option value="Employee" selected>Employee</option>
                    </select>
                </div>
            </div>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-primary" onclick="saveCustomerDetails()">Save Changes</button>
            </div>
            <script>
                function saveCustomerDetails() {
                    const customerDetails = {
                        name: document.getElementById('customerName').value,
                        age: document.getElementById('customerAge').value,
                        admiral: document.getElementById('customerAdmiral').value,
                        phone: document.getElementById('customerPhone').value,
                        status: document.getElementById('customerStatus').value,
                        timeType: document.getElementById('customerTimeType').value,
                        nz: document.getElementById('customerNZ').value,
                        totalDateNo: document.getElementById('customerTotalDateNo').value
                    };
                    console.log('Customer Details Saved:', customerDetails);
                    alert('Customer details have been saved successfully!');
                }
            </script>
        </div>
    </div>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>