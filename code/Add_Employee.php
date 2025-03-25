<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "hris_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $contact_no = isset($_POST['contact_no']) ? $_POST['contact_no'] : NULL;  // Ensure contact_no is set
    $email = $_POST['email'];
    $marital_status = $_POST['marital_status'];
    $qualification = $_POST['qualification'];
    $experience = $_POST['experience'];
    $blood_type = $_POST['blood_type'];
    $insurance = $_POST['insurance'];
    $joining_date = $_POST['joining_date'];
    $leave_balance = $_POST['leave_balance'];
    $department_id = $_POST['department_id'];
    $manager_id = $_POST['manager_id'];
    $job_type = $_POST['job_type'];

    // Auto-generate Emp_ID (example: E001, E002, ...)
    $result = $conn->query("SELECT MAX(Emp_ID) AS max_emp_id FROM Employee");
    $row = $result->fetch_assoc();
    $max_emp_id = $row['max_emp_id'];
    $emp_id = 'E' . str_pad(substr($max_emp_id, 1) + 1, 3, '0', STR_PAD_LEFT);

    // Prepare SQL query to insert employee data
    $sql = $conn->prepare("INSERT INTO Employee 
        (Emp_ID, First_Name, Last_Name, DOB, Gender, Address, Contact_No, Email, Marital_Status, Qualification, 
        Experience, Blood_Type, Insurance, Joining_Date, Leave_Balance, Department_ID, Manager_ID, Job_Type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($sql === false) {
        die("Error preparing query: " . $conn->error);
    }

    // Bind parameters
    $sql->bind_param("ssssssssssssssssss", $emp_id, $first_name, $last_name, $dob, $gender, $address, $contact_no, $email, $marital_status, $qualification, $experience, $blood_type, $insurance, $joining_date, $leave_balance, $department_id, $manager_id, $job_type);

    // Execute the query
    if ($sql->execute()) {
        echo "Employee added successfully!";
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
    <title>Add Employee</title>
    <link rel="stylesheet" href="Add_Employee.css">
</head>
<body>
    <h2>Add New Employee</h2>
    <form method="post">
        <label for="first_name">First Name:</label><br>
        <input type="text" id="first_name" name="first_name" required><br>

        <label for="last_name">Last Name:</label><br>
        <input type="text" id="last_name" name="last_name" required><br>

        <label for="dob">Date of Birth:</label><br>
        <input type="date" id="dob" name="dob"><br>

        <label for="gender">Gender:</label><br>
        <select id="gender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select><br>

        <label for="address">Address:</label><br>
        <textarea id="address" name="address"></textarea><br>

        <label for="contact_no">Contact Number:</label><br>
        <input type="text" id="contact_no" name="contact_no"><br> <!-- This can be left empty or optional -->

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>

        <label for="marital_status">Marital Status:</label><br>
        <select id="marital_status" name="marital_status" required>
            <option value="Single">Single</option>
            <option value="Married">Married</option>
            <option value="Divorced">Divorced</option>
            <option value="Widowed">Widowed</option>
        </select><br>

        <label for="qualification">Qualification:</label><br>
        <input type="text" id="qualification" name="qualification"><br>

        <label for="experience">Experience:</label><br>
        <input type="number" id="experience" name="experience"><br>

        <label for="blood_type">Blood Type:</label><br>
        <input type="text" id="blood_type" name="blood_type"><br>

        <label for="insurance">Insurance:</label><br>
        <input type="text" id="insurance" name="insurance"><br>

        <label for="joining_date">Joining Date:</label><br>
        <input type="date" id="joining_date" name="joining_date"><br>

        <label for="leave_balance">Leave Balance:</label><br>
        <input type="number" id="leave_balance" name="leave_balance" value="0"><br>

        <label for="department_id">Department:</label><br>
        <input type="text" id="department_id" name="department_id" required><br>

        <label for="manager_id">Manager ID:</label><br>
        <input type="text" id="manager_id" name="manager_id"><br>

        <label for="job_type">Job Type:</label>
        <br>
        <select id="job_type" name="job_type" required>
            <option value="Manager">Manager</option>
            <option value="HR">HR</option>
            <option value="Employee">Employee</option>
        </select><br>

        <input type="submit" value="Add Employee">
    </form>
</body>
</html>
