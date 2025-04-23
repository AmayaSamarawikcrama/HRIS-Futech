<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if payroll ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Payroll ID is required");
}

$payroll_id = $_GET['id'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hris_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Company information - This should come from a proper company table
// For now, use hardcoded values or create a Company table
$company = [
    'Company_Name' => 'Your Company Name',
    'Address' => 'Company Address',
    'Phone' => 'Company Phone',
    'Email' => 'Company Email'
];

// Get payroll details
$query = "SELECT 
    p.*,
    e.First_Name,
    e.Last_Name,
    e.Email,
    e.Contact_Number as Phone,
    e.Employee_Type as Designation,
    'Full-Time' as Employment_Type, /* This should come from a proper field if available */
    e.Hire_Date as Join_Date,
    d.Department_Name
    FROM Payroll p
    JOIN Employee e ON p.Employee_ID = e.Employee_ID
    LEFT JOIN Department d ON e.Department_ID = d.Department_ID
    WHERE p.Payroll_ID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $payroll_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Payroll record not found");
}

$payroll = $result->fetch_assoc();

// No need to recalculate - these values are already stored in the Payroll table
// But we'll use them for reference
$epf_employee = $payroll['Employee_EPF'];
$epf_employer = $payroll['Employer_EPF'];
$etf_employer = $payroll['Employer_ETF'];

// Use the calculated values from the database
$gross_salary = $payroll['Gross_Salary'];
$total_deductions = $payroll['Total_Deductions'];
$net_salary = $payroll['Net_Salary'];

// Format the payment date
$payment_date = date('F Y', strtotime($payroll['Payment_Date']));
$print_date = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?php echo htmlspecialchars($payroll['First_Name'] . ' ' . $payroll['Last_Name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
            font-size: 12pt;
        }
        .container {
            width: 21cm;
            min-height: 29.7cm;
            padding: 1cm;
            margin: 0 auto;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .company-name {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #0d6efd;
        }
        .payslip-title {
            font-size: 18pt;
            font-weight: bold;
            margin: 15px 0;
            color: #333;
        }
        .row {
            display: flex;
            margin-bottom: 10px;
        }
        .col-6 {
            width: 50%;
            padding-right: 15px;
        }
        .employee-info {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #ddd;
        }
        .net-salary {
            font-weight: bold;
            font-size: 16pt;
            color: #0d6efd;
            margin-top: 10px;
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        .signatures {
            margin-top: 70px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 40%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                width: 100%;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name"><?php echo htmlspecialchars($company['Company_Name']); ?></div>
            <div><?php echo htmlspecialchars($company['Address']); ?></div>
            <div>Tel: <?php echo htmlspecialchars($company['Phone']); ?> | Email: <?php echo htmlspecialchars($company['Email']); ?></div>
            <div class="payslip-title">PAYSLIP - <?php echo htmlspecialchars($payment_date); ?></div>
        </div>
        
        <div class="employee-info">
            <div class="row">
                <div class="col-6">
                    <div><strong>Employee ID:</strong> <?php echo htmlspecialchars($payroll['Employee_ID']); ?></div>
                    <div><strong>Name:</strong> <?php echo htmlspecialchars($payroll['First_Name'] . ' ' . $payroll['Last_Name']); ?></div>
                    <div><strong>Department:</strong> <?php echo htmlspecialchars($payroll['Department_Name']); ?></div>
                    <div><strong>Designation:</strong> <?php echo htmlspecialchars($payroll['Designation']); ?></div>
                </div>
                <div class="col-6">
                    <div><strong>Payment Date:</strong> <?php echo htmlspecialchars(date('d-m-Y', strtotime($payroll['Payment_Date']))); ?></div>
                    <div><strong>Payment Method:</strong> <?php echo htmlspecialchars($payroll['Payment_Method']); ?></div>
                    <div><strong>Employment Type:</strong> <?php echo htmlspecialchars($payroll['Employment_Type']); ?></div>
                    <div><strong>Join Date:</strong> <?php echo htmlspecialchars(date('d-m-Y', strtotime($payroll['Join_Date']))); ?></div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Earnings</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Base Salary</td>
                        <td><?php echo number_format($payroll['Base_Salary'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Fixed Allowances</td>
                        <td><?php echo number_format($payroll['Fixed_Allowances'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Overtime Pay</td>
                        <td><?php echo number_format($payroll['Overtime_Pay'], 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gross Earnings</strong></td>
                        <td><strong><?php echo number_format($gross_salary, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Deductions</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Employee EPF (8%)</td>
                        <td><?php echo number_format($epf_employee, 2); ?></td>
                    </tr>
                    <tr>
                        <td>PAYE Tax</td>
                        <td><?php echo number_format($payroll['PAYE_Tax'], 2); ?></td>
                    </tr>
                    <?php if ($payroll['Unpaid_Leave_Deductions'] > 0): ?>
                    <tr>
                        <td>Unpaid Leave Deductions</td>
                        <td><?php echo number_format($payroll['Unpaid_Leave_Deductions'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($payroll['Loan_Recovery'] > 0): ?>
                    <tr>
                        <td>Loan Recovery</td>
                        <td><?php echo number_format($payroll['Loan_Recovery'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Total Deductions</strong></td>
                        <td><strong><?php echo number_format($total_deductions, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">Employer Contributions</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Employer EPF (12%)</td>
                        <td><?php echo number_format($epf_employer, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Employer ETF (3%)</td>
                        <td><?php echo number_format($etf_employer, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Employer Contributions</strong></td>
                        <td><strong><?php echo number_format($epf_employer + $etf_employer, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="summary">
            <div class="summary-row">
                <div><strong>Gross Salary:</strong></div>
                <div>LKR <?php echo number_format($gross_salary, 2); ?></div>
            </div>
            <div class="summary-row">
                <div><strong>Total Deductions:</strong></div>
                <div>LKR <?php echo number_format($total_deductions, 2); ?></div>
            </div>
            <div class="net-salary">
                <div>Net Salary: LKR <?php echo number_format($net_salary, 2); ?></div>
            </div>
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <div>HR Manager</div>
            </div>
            <div class="signature-box">
                <div>Employee Signature</div>
            </div>
        </div>
        
        <div class="footer">
            <p>This is a computer-generated payslip and does not require a signature.</p>
            <p>Generated on: <?php echo htmlspecialchars($print_date); ?></p>
            <p>If you have any queries regarding this payslip, please contact the HR department.</p>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print();" style="padding: 10px 20px; background-color: #0d6efd; color: white; border: none; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-print"></i> Print Payslip
            </button>
            <button onclick="window.close();" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                Close
            </button>
        </div>
    </div>
</body>
</html>