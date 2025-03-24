<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dash</title>
    <link rel="stylesheet" href="CSS/Dash.css">
</head>
<body>
    <div class="header">
        <button onclick="logout()" class="logout-button">Log Out</button>
        <img class="profile" src="assets/image.png" alt="Employee Details" width="40px" height="40px">
    </div>
    <div class="dashboard">
        <div class="card">
            <div class="grid">
                <div class="button" onclick="EmpProfile()">My Profile</div>
                <div class="button">My Performance</div>
                <div class="button">Reports</div>
                <div class="button">Related Project</div>
                <div class="button">My Salary Status</div>
                <div class="button">Company Details</div>
                <div class="button">Leave Request</div>
                <div class="button">My Attendance</div>
                <div class="button">Calendar</div>
            </div>
        </div>
    </div>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
        function EmpProfile() {
            window.location.href = "EmpProfile.php";
        }
    </script>
</body>
</html>