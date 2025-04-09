<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hris_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id = $_POST['employee_id'];
    $base_salary = $_POST['base_salary'];
    $fixed_allowances = $_POST['fixed_allowances'];
    $overtime_pay = $_POST['overtime_pay'];
    $unpaid_leave_deductions = $_POST['unpaid_leave_deductions'];
    $loan_recovery = $_POST['loan_recovery'];
    $paye_tax = $_POST['paye_tax'];

    // Insert into Payroll table
    $sql = "INSERT INTO Payroll (Employee_ID, Base_Salary, Fixed_Allowances, Overtime_Pay, Unpaid_Leave_Deductions, Loan_Recovery, PAYE_Tax) 
            VALUES ('$employee_id', '$base_salary', '$fixed_allowances', '$overtime_pay', '$unpaid_leave_deductions', '$loan_recovery', '$paye_tax')";

    if ($conn->query($sql) === TRUE) {
        $message = "Payroll data inserted successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Fetch payroll records
$sql = "SELECT * FROM Payroll";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Payroll Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #f4f4f4; }
        .container { max-width: 600px; margin: auto; }
        input[type="text"], input[type="number"], input[type="submit"] { width: 100%; padding: 8px; margin: 5px 0; }
        input[type="submit"] { background-color:rgb(55, 21, 207); color: white; border: none; cursor: pointer; }
        input[type="submit"]:hover { background-color: #218838; }
    </style>
</head>
<body>

<div class="container">
    <h2>Payroll Management</h2>

    <?php if (!empty($message)) { echo "<p style='color:green;'>$message</p>"; } ?>

    <form action="" method="POST">
        <label for="employee_id">Employee ID:</label>
        <input type="text" name="employee_id" required>

        <label for="base_salary">Base Salary:</label>
        <input type="number" step="0.01" name="base_salary" required>

        <label for="fixed_allowances">Fixed Allowances:</label>
        <input type="number" step="0.01" name="fixed_allowances">

        <label for="overtime_pay">Overtime Pay:</label>
        <input type="number" step="0.01" name="overtime_pay">

        <label for="unpaid_leave_deductions">Unpaid Leave Deductions:</label>
        <input type="number" step="0.01" name="unpaid_leave_deductions">

        <label for="loan_recovery">Loan Recovery:</label>
        <input type="number" step="0.01" name="loan_recovery">

        <label for="paye_tax">PAYE Tax:</label>
        <input type="number" step="0.01" name="paye_tax">

        <input type="submit" value="Submit Payroll">
    </form>
</div>

<h2>Employee Payroll Records</h2>
<table>
    <tr>
        <th>Employee ID</th>
        <th>Base Salary</th>
        <th>Fixed Allowances</th>
        <th>Overtime Pay</th>
        <th>Unpaid Leave Deductions</th>
        <th>Loan Recovery</th>
        <th>PAYE Tax</th>
        <th>Gross Salary</th>
        <th>Total Deductions</th>
        <th>Net Salary</th>
    </tr>
    <?php if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row["Employee_ID"]; ?></td>
                <td><?php echo $row["Base_Salary"]; ?></td>
                <td><?php echo $row["Fixed_Allowances"]; ?></td>
                <td><?php echo $row["Overtime_Pay"]; ?></td>
                <td><?php echo $row["Unpaid_Leave_Deductions"]; ?></td>
                <td><?php echo $row["Loan_Recovery"]; ?></td>
                <td><?php echo $row["PAYE_Tax"]; ?></td>
                <td><?php echo $row["Gross_Salary"]; ?></td>
                <td><?php echo $row["Total_Deductions"]; ?></td>
                <td><?php echo $row["Net_Salary"]; ?></td>
            </tr>
    <?php } } else { ?>
        <tr><td colspan="10">No payroll records found.</td></tr>
    <?php } ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
