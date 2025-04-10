<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function getDBConnection() {
    $conn = new mysqli('localhost', 'root', '', 'hris_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Handle PDF generation request
if (isset($_GET['generate_pdf'])) {
    require('./fdpdf186/fpdf.php');
    
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get complete employee data
    $sql = "SELECT e.*, d.Department_Name 
            FROM Employee e
            LEFT JOIN Department d ON e.Department_ID = d.Department_ID
            WHERE e.Employee_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee_data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Create PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    
    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'EMPLOYEE DETAILS', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Employee Information
    $pdf->SetFont('Arial', '', 12);
    
    $fields = [
        'First Name' => $employee_data['First_Name'],
        'Last Name' => $employee_data['Last_Name'],
        'Email' => $employee_data['Email'],
        'Phone Number' => $employee_data['Contact_Number'],
        'Address' => $employee_data['Address'],
        'Date of Birth' => $employee_data['Date_of_Birth'],
        'Gender' => $employee_data['Gender'],
        'Nationality' => $employee_data['Nationality'],
        'Department' => $employee_data['Department_Name'],
        'Position' => $employee_data['Employee_Type'],
        'Salary' => '$' . number_format($employee_data['Salary'], 2),
        'Hire Date' => $employee_data['Hire_Date'],
        'Insurance' => $employee_data['Insurance_Info'],
        'Blood Type' => $employee_data['Blood_Type'],
        'Marital Status' => $employee_data['Marital_Status']
    ];
    
    foreach ($fields as $label => $value) {
        $pdf->Cell(50, 10, $label . ':', 0, 0);
        $pdf->Cell(0, 10, $value, 0, 1);
    }
    
    $pdf->Output('D', 'employee_details_' . $user_id . '.pdf');
    exit();
}

// Normal page rendering
$conn = getDBConnection();

// Get current user data
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();
$stmt_user->close();

// Get all departments
$sql_departments = "SELECT Department_ID, Department_Name FROM Department ORDER BY Department_Name";
$departments_result = $conn->query($sql_departments);

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "Security verification failed. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: HrEmployeeDetails.php");
        exit();
    }
    // Handle form submissions here
}
?>

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
        
        .department-heading {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .employee-name {
            font-weight: bold;
            color: #dc3545;
        }
        
        .pdf-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="HrDashboard.php" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="window.location.href='?logout=true'">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='profile.php'">
            </div>
        </nav>

        <div class="employee-table-container">
            <div class="mb-4">
                <form id="searchForm" class="d-flex justify-content-center">
                    <input type="text" id="searchInput" class="form-control w-50" placeholder="Search by Employee ID, Name or Position">
                    <button type="button" class="btn btn-primary ms-2" onclick="searchEmployee()">Search</button>
                    <button type="button" class="btn btn-secondary ms-2" onclick="resetSearch()">Reset</button>
                </form>
            </div>

            <h2 class="text-center mb-4">Employee Details by Department</h2>

            <div class="table-responsive">
                <?php
                if ($departments_result->num_rows > 0) {
                    while ($department = $departments_result->fetch_assoc()) {
                        $dept_id = $department['Department_ID'];
                        $dept_name = htmlspecialchars($department['Department_Name']);
                        
                        $sql_employees = "SELECT e.Employee_ID, e.First_Name, e.Last_Name, 
                                         e.Employee_Type as Position, e.Email, e.Contact_Number, e.Salary
                                      FROM Employee e
                                      WHERE e.Department_ID = ?
                                      ORDER BY e.Last_Name, e.First_Name";
                        
                        $stmt = $conn->prepare($sql_employees);
                        $stmt->bind_param("i", $dept_id);
                        $stmt->execute();
                        $employees_result = $stmt->get_result();
                        
                        if ($employees_result->num_rows > 0) {
                            echo "<div class='department-heading'>{$dept_name} Department</div>";
                            echo "<table class='table table-bordered table-striped mb-5'>";
                            echo "<thead><tr>
                                    <th>Employee ID</th>
                                    <th>Full Name</th>
                                    <th>Position</th>
                                    <th>Email</th>
                                    <th>Contact Number</th>
                                    <th>Salary</th>
                                    <th>Action</th>
                                </tr></thead><tbody>";
                            
                            while ($employee = $employees_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($employee['Employee_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['First_Name'] . ' ' . $employee['Last_Name']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['Position']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['Email']) . "</td>";
                                echo "<td>" . htmlspecialchars($employee['Contact_Number']) . "</td>";
                                echo "<td>$" . number_format($employee['Salary'], 2) . "</td>";
                                echo "<td>
                                        <a href='viewEmployeeDetails.php?id=" . $employee['Employee_ID'] . "' class='btn btn-primary btn-sm'>View</a>
                                        <a href='editEmployee.php?id=" . $employee['Employee_ID'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                    </td>";
                                echo "</tr>";
                            }
                            
                            echo "</tbody></table>";
                        }
                        $stmt->close();
                    }
                } else {
                    echo "<div class='alert alert-info'>No departments found.</div>";
                }
                
                $conn->close();
                ?>
            </div>
        </div>
        
        <!-- Floating PDF button -->
        <a href="?generate_pdf=true" class="btn btn-danger pdf-btn">
            <i class="bi bi-file-earmark-pdf"></i> Generate PDF
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchEmployee() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const tables = document.querySelectorAll('.table tbody');
            let anyResultsFound = false;

            tables.forEach(tbody => {
                const rows = tbody.querySelectorAll('tr');
                let foundInTable = false;
                
                rows.forEach(row => {
                    const employeeId = row.cells[0].textContent.toLowerCase();
                    const name = row.cells[1].textContent.toLowerCase();
                    const position = row.cells[2].textContent.toLowerCase();
                    
                    if (employeeId.includes(input) || name.includes(input) || position.includes(input)) {
                        row.style.display = '';
                        foundInTable = true;
                        anyResultsFound = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const departmentHeading = tbody.closest('.table').previousElementSibling;
                if (departmentHeading && departmentHeading.classList.contains('department-heading')) {
                    departmentHeading.style.display = foundInTable ? '' : 'none';
                }
                
                const table = tbody.closest('.table');
                table.style.display = foundInTable ? '' : 'none';
            });

            if (!anyResultsFound && input !== '') {
                alert('No employees found matching your search criteria.');
            }
        }

        function resetSearch() {
            document.getElementById('searchInput').value = '';
            const tables = document.querySelectorAll('.table');
            const headings = document.querySelectorAll('.department-heading');
            
            tables.forEach(table => {
                table.style.display = '';
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    row.style.display = '';
                });
            });
            
            headings.forEach(heading => {
                heading.style.display = '';
            });
        }

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchEmployee();
            }
        });
    </script>
</body>
</html>