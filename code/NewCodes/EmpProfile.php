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

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM Employee WHERE Employee_ID = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user_data = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $contact_no = filter_input(INPUT_POST, 'contact_no', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $qualification = filter_input(INPUT_POST, 'qualification', FILTER_SANITIZE_STRING);
    $insurance = filter_input(INPUT_POST, 'insurance', FILTER_SANITIZE_STRING);
    $blood_type = filter_input(INPUT_POST, 'blood_type', FILTER_SANITIZE_STRING);
    $marital_status = filter_input(INPUT_POST, 'marital_status', FILTER_SANITIZE_STRING);

    // Prepare update query
    $update_fields = [
        'First_Name' => $first_name,
        'Last_Name' => $last_name,
        'Date_of_Birth' => $dob,
        'Gender' => $gender,
        'Address' => $address,
        'Contact_Number' => $contact_no,
        'Email' => $email,
        'Qualification' => $qualification,
        'Insurance' => $insurance,
        'Blood_Type' => $blood_type,
        'Marital_Status' => $marital_status
    ];

    // Only update password if provided
    if ($password) {
        $update_fields['Password'] = $password;
    }

    // Handle profile photo upload
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "uploads/profile_photos/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $user_id . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                $update_fields['Profile_Photo'] = $target_file;
            }
        }
    }

    // Build the update query
    $set_clause = [];
    $params = [];
    $types = '';
    
    foreach ($update_fields as $field => $value) {
        $set_clause[] = "$field = ?";
        $params[] = $value;
        $types .= 's'; // All fields are strings in this case
    }
    
    $params[] = $user_id;
    $types .= 's';
    
    $query = "UPDATE Employee SET " . implode(', ', $set_clause) . " WHERE Employee_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        // Refresh user data
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating profile: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
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
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .profile-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #0d6efd;
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
                    <a href="EmpProfile.html" class="dropdown-item">My Profile</a>
                    <a href="EmpLeave.html" class="dropdown-item">Leave Request</a>
                    <a href="EmpManualAttendance.html" class="dropdown-item">My Attendance</a>
                    <a href="EmpProject.html" class="dropdown-item">Projects</a>
                    <a href="EmpSalary.html" class="dropdown-item">Salary Status</a>
                    <a href="Report.php" class="dropdown-item">Report</a>
                    <a href="Company.php" class="dropdown-item">Company Details</a>
                    <a href="Calendar.php" class="dropdown-item">Calendar</a>
                </div>
                <button class="btn btn-primary me-2 ms-auto" onclick="logout()">Log Out</button>
                <img src="<?php echo isset($user_data['Profile_Photo']) ? htmlspecialchars($user_data['Profile_Photo']) : 'assets/image.png'; ?>" 
                     alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;">
            </div>
        </nav>

        <div class="container">
            <div class="profile-container">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="profile-header">
                    <h2><i class="fas fa-user-circle me-2"></i>My Profile</h2>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row profile-field align-items-center">
                        <div class="col-md-3 field-label">Profile Photo</div>
                        <div class="col-md-9">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo isset($user_data['Profile_Photo']) ? htmlspecialchars($user_data['Profile_Photo']) : 'assets/image.png'; ?>" 
                                     alt="Profile Photo" class="profile-photo me-4" id="profilePhotoPreview">
                                <div>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('profilePhotoInput').click()">
                                        <i class="fas fa-camera me-2"></i>Change Photo
                                    </button>
                                    <input type="file" id="profilePhotoInput" name="profile_photo" class="d-none" accept="image/*" onchange="previewProfilePhoto(event)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">First Name</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user_data['First_Name']); ?>" required>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Last Name</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user_data['Last_Name']); ?>" required>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Date of Birth</div>
                        <div class="col-md-9">
                            <input type="date" class="form-control" name="dob" value="<?php echo htmlspecialchars($user_data['Date_of_Birth']); ?>" required>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Gender</div>
                        <div class="col-md-9">
                            <select class="form-select" name="gender" required>
                                <option value="Male" <?php echo ($user_data['Gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($user_data['Gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($user_data['Gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Address</div>
                        <div class="col-md-9">
                            <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user_data['Address']); ?></textarea>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Contact Number</div>
                        <div class="col-md-9">
                            <input type="tel" class="form-control" name="contact_no" pattern="[0-9]{10,15}" 
                                   value="<?php echo htmlspecialchars($user_data['Contact_Number']); ?>" required>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Email</div>
                        <div class="col-md-9">
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user_data['Email']); ?>" required>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Password</div>
                        <div class="col-md-9">
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Leave blank to keep current">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Qualification</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="qualification" value="<?php echo htmlspecialchars($user_data['Qualification']); ?>">
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Insurance</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="insurance" value="<?php echo htmlspecialchars($user_data['Insurance']); ?>">
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Blood Type</div>
                        <div class="col-md-9">
                            <select class="form-select" name="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="A+" <?php echo ($user_data['Blood_Type'] === 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($user_data['Blood_Type'] === 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($user_data['Blood_Type'] === 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($user_data['Blood_Type'] === 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="O+" <?php echo ($user_data['Blood_Type'] === 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($user_data['Blood_Type'] === 'O-') ? 'selected' : ''; ?>>O-</option>
                                <option value="AB+" <?php echo ($user_data['Blood_Type'] === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($user_data['Blood_Type'] === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            </select>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Marital Status</div>
                        <div class="col-md-9">
                            <select class="form-select" name="marital_status">
                                <option value="Single" <?php echo ($user_data['Marital_Status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($user_data['Marital_Status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                                <option value="Divorced" <?php echo ($user_data['Marital_Status'] === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Widowed" <?php echo ($user_data['Marital_Status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Read-only fields (for display only) -->
                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Employee ID</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['Employee_ID']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Hire Date</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['Hire_Date']); ?>" readonly>
                        </div>
                    </div>

                    <div class="row profile-field">
                        <div class="col-md-3 field-label">Department</div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['Department_ID']); ?>" readonly>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            const menuList = document.getElementById('menu-list');
            menuList.classList.toggle('d-none');
        }
        
        function logout() {
            window.location.href = 'logout.php';
        }
        
        function previewProfilePhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePhotoPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
        
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>