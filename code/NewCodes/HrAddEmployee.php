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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact_no = $_POST['contact_no'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $qualification = $_POST['qualification'];
    $insurance = $_POST['insurance'];
    $blood_type = $_POST['blood_type'];
    $marital_status = $_POST['marital_status'];
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    $department_id = $_POST['department_id'];
    $manager_id = !empty($_POST['manager_id']) ? $_POST['manager_id'] : null;
    $employee_type = $_POST['employee_type'];

    // Generate Employee_ID
    if ($employee_type == 'HumanResource Manager') {
        $prefix = 'HM';
    } elseif ($employee_type == 'Manager MAN') {
        $prefix = 'MAN';
    } else {
        $prefix = 'EMP';
    }

    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(Employee_ID, 3) AS UNSIGNED)) AS max_id FROM Employee WHERE Employee_ID LIKE '$prefix%'");
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] + 1 : 1;

    // Generate the final Employee_ID
    $employee_id = $prefix . str_pad($max_id, 4, '0', STR_PAD_LEFT);

    // Handle file upload
    $file_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $file_name;
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload file']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Prepare and execute the SQL statement for employee
        $query = "INSERT INTO Employee (
    Employee_ID, Password, Employee_Type, First_Name, Last_Name, 
    Date_of_Birth, Gender, Address, Contact_Number, Email, 
    Qualification, Insurance, Blood_Type, Marital_Status, Hire_Date, 
    Salary, Department_ID, Manager_ID, file_name)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssssssssdisss", $employee_id, $password, $employee_type, $first_name, $last_name,
                 $dob, $gender, $address, $contact_no, $email, $qualification, $insurance, $blood_type,
                 $marital_status, $hire_date, $salary, $department_id, $manager_id, $file_name);

        
        if ($stmt->execute()) {
            // Get the employee ID that was just created
            $new_employee_id = $employee_id; // Directly use generated ID
            
            // If the employee type is Manager, insert into Manager table
            if ($employee_type === 'Manager MAN') {
                $manager_query = "INSERT INTO Manager (Manager_ID, Employee_ID, Department_ID) 
                                  VALUES (?, ?, ?)";
                $manager_stmt = $conn->prepare($manager_query);
                $manager_stmt->bind_param("ssi", $new_employee_id, $new_employee_id, $department_id);
                
                if (!$manager_stmt->execute()) {
                    throw new Exception("Failed to add manager record: " . $manager_stmt->error);
                }
            }
            
            // Commit transaction
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Employee added successfully with ID: ' . $new_employee_id]);
        } else {
            // Rollback on failure
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to add employee: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        // Rollback on exception
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}
?>


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
        
        #message {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
                    <a href="HrDashboard.php" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="Calendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <span class="employee-name ms-auto me-3">
                    <?php
                        echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                    ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>

        <div class="container">
            <h2 class="text-center mt-4 mb-4">Add New Employee</h2>
            
            <div id="message" class="alert" role="alert"></div>

            <form id="addEmployeeForm" method="post" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-3 field-label">Profile Picture:</div>
                    <div class="col-md-9">
                        <input type="file" class="form-control" id="image" name="image" required>
                        <small class="text-muted">Upload a profile picture (JPG, PNG, or GIF)</small>
                    </div>
                </div>

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
                            <option value="Single" selected>Single</option>
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
                        <input type="number" class="form-control" id="salary" name="salary" placeholder="Enter Salary" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Department</div>
                    <div class="col-md-9">
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="" selected>Select Department</option>
                            <?php
                            // Fetch departments from database
                            $dept_query = "SELECT Department_ID, Department_Name FROM Department ORDER BY Department_Name";
                            $dept_result = $conn->query($dept_query);
                            while ($dept = $dept_result->fetch_assoc()) {
                                echo "<option value='" . $dept['Department_ID'] . "'>" . htmlspecialchars($dept['Department_Name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Manager</div>
                    <div class="col-md-9">
                        <select class="form-select" id="manager_id" name="manager_id">
                            <option value="" selected>Select Manager (Optional)</option>
                            <?php
                            // Fetch managers from database
                            $manager_query = "SELECT Employee_ID, First_Name, Last_Name FROM Employee 
                                             WHERE Employee_Type = 'Manager' OR Employee_Type = 'HumanResource Manager'
                                             ORDER BY First_Name, Last_Name";
                            $manager_result = $conn->query($manager_query);
                            while ($manager = $manager_result->fetch_assoc()) {
                                echo "<option value='" . $manager['Employee_ID'] . "'>" . 
                                     htmlspecialchars($manager['First_Name'] . ' ' . $manager['Last_Name']) . 
                                     " (" . $manager['Employee_ID'] . ")</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 field-label">Employee Type</div>
                    <div class="col-md-9">
                        <select class="form-select" id="employee_type" name="employee_type" required>
                            <option value="" selected>Select Employee Type</option>
                            <option value="HumanResource Manager">Human Resource Manager</option>
                            <option value="Manager">Manager</option>
                            <option value="Employee">Employee</option>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4 mb-5">
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                    <button type="reset" class="btn btn-secondary ms-2">Reset Form</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleMenu() {
        const menuList = document.getElementById('menu-list');
        menuList.classList.toggle('d-none');
    }
    
    function logout() {
        // Redirect to logout script
        window.location.href = 'logout.php';
    }
    
    // Form submission handling with AJAX
    document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('message');
            messageDiv.style.display = 'block';
            
            if (data.status === 'success') {
                messageDiv.className = 'alert alert-success';
                messageDiv.textContent = data.message;
                // Reset the form on success
                document.getElementById('addEmployeeForm').reset();
            } else {
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = data.message;
            }
            
            // Scroll to the message
            messageDiv.scrollIntoView({ behavior: 'smooth' });
            
            // Hide the message after 5 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        })
        .catch(error => {
            console.error('Error:', error);
            const messageDiv = document.getElementById('message');
            messageDiv.style.display = 'block';
            messageDiv.className = 'alert alert-danger';
            messageDiv.textContent = 'An error occurred during form submission. Please try again.';
        });
    });
    </script>
</body>
</html>