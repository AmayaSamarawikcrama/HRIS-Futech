<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance and Task Details</title>
    <link rel="stylesheet" href="Attendance.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="menu-icon">&#9776;</div>
            <button class="logout">Log Out</button>
            <img class="profile" src="assets/profile.png" alt="Employee Details" width="40px" height="40px">
        </nav>
        <div class="search-bar">
            <input type="text" id="departmentSearch" placeholder="Search Departments">
            <button onclick="searchDepartments()">Search</button>
        </div>
        <script>
            function searchDepartments() {
            const input = document.getElementById('departmentSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.table-container tbody tr');
            
            rows.forEach(row => {
                const department = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                if (department.includes(input)) {
                row.style.display = '';
                } else {
                row.style.display = 'none';
                }
            });
            }
        </script>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>Logout Time</th>
                        <th>Work Hours</th>
                        <th>Assigned Task</th>
                        <th>Task Completion (%)</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <img src="assets/profile.png" alt="Profile" class="profile-pic">
                            John Doe
                        </td>
                        <td>Engineering</td>
                        <td>July 08, 2023</td>
                        <td>09:00 AM</td>
                        <td>05:30 PM</td>
                        <td>8.5 hours</td>
                        <td>Develop New Feature</td>
                        <td>80%</td>
                        <td>Completed most of the tasks, pending testing.</td>
                    </tr>
                    <tr>
                        <td>
                            <img src="assets/profile.png" alt="Profile" class="profile-pic">
                            Jane Smith
                        </td>
                        <td>Marketing</td>
                        <td>July 08, 2023</td>
                        <td>10:00 AM</td>
                        <td>06:00 PM</td>
                        <td>06 </td>
                        <td>UI Design for Dashboard</td>
                        <td>100%</td>
                        <td>Task completed successfully, no issues.</td>
                    </tr>
                    <tr>
                        <td>
                            <img src="assets/profile.png" alt="Profile" class="profile-pic">
                            Mike Johnson
                        </td>
                        <td>Engineering</td>
                        <td>July 09, 2023</td>
                        <td>08:30 AM</td>
                        <td>04:30 PM</td>
                        <td>08 hours</td>
                        <td>Database Optimization</td>
                        <td>60%</td>
                        <td>Some tasks were delayed due to data inconsistencies.</td>
                    </tr>
                    <tr>
                        <td>
                            <img src="assets/profile.png" alt="Profile" class="profile-pic">
                            Linda Johnson
                        </td>
                        <td>Marketing</td>
                        <td>July 09, 2023</td>
                        <td>09:30 AM</td>
                        <td>05:30 PM</td>
                        <td>07 hours</td>
                        <td>API Integration</td>
                        <td>90%</td>
                        <td>API work is almost complete, testing pending.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
