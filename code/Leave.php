<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <link rel="stylesheet" href="Leave.css">
</head>
<body>
    <nav>
        <div class="menu-icon">&#9776;</div>
        <button class="logout-btn">Log Out</button>
        <img class="profile" src="assets/profile.png" alt="Employee Details" width="40px" height="40px">
    </nav>

    <div class="container">
        <h2>Leave Request</h2>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Contact Number</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>101</td>
                    <td>John Doe</td>
                    <td>Sick Leave</td>
                    <td>2023-10-01</td>
                    <td>2023-10-05</td>
                    <td>Flu</td>
                    <td>123-456-7890</td>
                    <td><button class="action">Approve</button> <button class="action">Reject</button> <button class="action">Pending</button></td>
                </tr>
               
            </tbody>
        </table>
    </div>
</body>
</html>
