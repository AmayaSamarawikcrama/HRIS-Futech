<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "hris_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    // Sanitize and validate input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $contact_no = !empty($_POST['contact_no']) ? trim($_POST['contact_no']) : NULL;
    $email = trim($_POST['email']);
    $qualification = trim($_POST['qualification']);
    $insurance = trim($_POST['insurance']);
    $hire_date = $_POST['hire_date'];
    $salary = $_POST['salary'];
    $department_id = $_POST['department_id'];
    $employee_type = $_POST['employee_type'];
    $blood_type = !empty($_POST['blood_type']) ? $_POST['blood_type'] : NULL;
    $marital_status = !empty($_POST['marital_status']) ? $_POST['marital_status'] : 'Single';
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password securely

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Generate Employee_ID
    $prefix = ($employee_type == 'HumanResource Manager') ? 'HM' : 'EMP';
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(Employee_ID, 3) AS UNSIGNED)) AS max_id FROM Employee WHERE Employee_ID LIKE '$prefix%'");
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] + 1 : 1;
    $employee_id = $prefix . str_pad($max_id, 4, '0', STR_PAD_LEFT);

<<<<<<< HEAD
    // Prepare SQL query (Removed Manager_ID from the query)
    $sql = $conn->prepare("INSERT INTO Employee 
    (Employee_ID, Password, Employee_Type, First_Name, Last_Name, Date_of_Birth, Gender, Address, Contact_Number, Email, Qualification, 
    Insurance, Blood_Type, Marital_Status, Hire_Date, Salary, Department_ID) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
=======
    // Handle image upload
    $targetDir = "uploads/";  // Directory to store images
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allowed file formats
    $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];
    if (!in_array($fileType, $allowedTypes)) {
        die("Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.");
    }

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        die("Failed to upload the image.");
    }

    // Prepare SQL query
    $sql = $conn->prepare("INSERT INTO Employee 
    (Employee_ID, Password, Employee_Type, First_Name, Last_Name, Date_of_Birth, Gender, Address, Contact_Number, Email, Qualification, 
    Insurance, Blood_Type, Marital_Status, Hire_Date, Salary, Department_ID, Manager_ID, file_name) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
>>>>>>> fa25ae0a7123ef1cdfd7d69c9a1a75535bb8a0b3

    if ($sql === false) {
        die("Error preparing query: " . $conn->error);
    }

<<<<<<< HEAD
    // Bind parameters (Removed Manager_ID from bind_param)
    $sql->bind_param("sssssssssssssssds", 
        $employee_id, $password, $employee_type, $first_name, $last_name, $dob, $gender, 
        $address, $contact_no, $email, $qualification, $insurance, $blood_type, $marital_status, 
        $hire_date, $salary, $department_id
=======
    // Bind parameters
    $sql->bind_param("sssssssssssssssdsss", 
        $employee_id, $password, $employee_type, $first_name, $last_name, $dob, $gender, 
        $address, $contact_no, $email, $qualification, $insurance, $blood_type, $marital_status, 
        $hire_date, $salary, $department_id, $manager_id, $fileName
>>>>>>> fa25ae0a7123ef1cdfd7d69c9a1a75535bb8a0b3
    );

    // Execute the query
    if ($sql->execute()) {
        echo "<script>alert('Employee added successfully!'); window.location.href='add_employee.php';</script>";
    } else {
        echo "Error: " . $sql->error;
    }

    // Close the statement and connection
    $sql->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <link rel="stylesheet" href="Add_Employee.css">
</head>
<body>
    <nav class="navbar">
        <div class="menu-icon">&#9776;</div>
        <button class="logout-btn">Log Out</button>
        <img class="profile" src="assets/profile.png" alt="Profile">
    </nav>
    <div class="container">
        <h2>Add New Employee</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="left">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>

                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <label for="address">Address:</label>
                <textarea id="address" name="address"></textarea>

                <label for="contact_no">Contact Number:</label>
                <input type="text" id="contact_no" name="contact_no" pattern="[0-9]{10,15}" placeholder="Enter 10-15 digits">

                <label for="Profile_pic">Enter Your Picture:</label>
                <input type="file" name="image" required/>

            </div>

            <div class="right">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="qualification">Qualification:</label>
                <input type="text" id="qualification" name="qualification">

                <label for="insurance">Insurance:</label>
                <input type="text" id="insurance" name="insurance">

                <label for="blood_type">Blood Type:</label>
                <select id="blood_type" name="blood_type">
                    <option value="">Select Blood Type</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>

                <label for="marital_status">Marital Status:</label>
                <select id="marital_status" name="marital_status">
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                    <option value="Widowed">Widowed</option>
                </select>

                <label for="hire_date">Hire Date:</label>
                <input type="date" id="hire_date" name="hire_date" required>

                <label for="salary">Salary:</label>
                <input type="number" id="salary" name="salary" min="0" required>
            </div>

            <div class="full">
                <label for="department_id">Department:</label>
                <input type="number" id="department_id" name="department_id" required>

                <label for="manager_id">Manager ID:</label>
                <input type="text" id="manager_id" name="manager_id">

                <label for="employee_type">Employee Type:</label>
                <select id="employee_type" name="employee_type" required>
                    <option value="Manager">Manager</option>
                    <option value="HumanResource Manager">Human Resource Manager</option>
                    <option value="Employee">Employee</option>
                </select>
            </div>

            <input type="submit" class="submit" value="Add Employee">
        </form>
    </div>
</body>
</html>
