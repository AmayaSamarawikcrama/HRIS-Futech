<?php
// Database connection configuration
$servername = "localhost";
$username = "root";  // Default XAMPP MySQL username
$password = "";      // Default XAMPP MySQL password (usually blank)
$dbname = "hris_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Connection failed. Please check your database settings. " . $conn->connect_error);
}

// SQL query to fetch employee details with department name
$sql = "SELECT 
            e.Employee_ID, 
            e.First_Name, 
            e.Last_Name, 
            e.Date_of_Birth, 
            e.Gender, 
            e.Contact_Number, 
            e.Email, 
            e.Address, 
            d.Department_Name, 
            e.Employee_Type as Job_Title, 
            e.Hire_Date, 
            e.Qualification, 
            e.Insurance, 
            e.Blood_Type, 
            e.Salary, 
            e.Status
        FROM 
            Employee e
        LEFT JOIN 
            Department d ON e.Department_ID = d.Department_ID
        ORDER BY 
            e.Last_Name, e.First_Name";

$result = $conn->query($sql);

if ($result === false) {
    error_log("Query Failed: " . $conn->error);
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details</title>
    <link rel="stylesheet" href="View_Employee.css">
    <style>
        .edit-btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
            border: none;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="menu-icon">&#9776;</div>
        <button class="logout-btn">Log Out</button>
        <img class="profile" src="assets/profile.png" alt="Profile">
    </nav>

    <h2>Employee Details</h2>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>B Day</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Department</th>
                    <th>Emp ID</th>
                    <th>Role</th>
                    <th>Join Date</th>
                    <th>Qualification</th>
                    <th>Insurance</th>
                    <th>Blood Type</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody id="employeeTable">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><img src='assets/profile.png' alt='Profile' class='employee-profile' style='width:50px; height:50px;'></td>";
                        echo "<td>" . htmlspecialchars($row['First_Name'] . " " . $row['Last_Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Date_of_Birth']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Gender']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Contact_Number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Department_Name'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Employee_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Job_Title'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Hire_Date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Qualification']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Insurance']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Blood_Type']) . "</td>";
                        echo "<td>$" . number_format($row['Salary'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "<td><a href='edit_employee.php?id=" . $row['Employee_ID'] . "' class='edit-btn'>Edit</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='17'>No employees found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>
