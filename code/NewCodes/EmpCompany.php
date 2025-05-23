<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Company Rules and Regulations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
        <div class="container-fluid">
            <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
            <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                <a href="EmpDashboard.php" class="dropdown-item">Dashboard</a>
                <a href="EmpProfile.php" class="dropdown-item">My Profile</a>
                <a href="EmpLeave.php" class="dropdown-item">Leave Request</a>
                <a href="EmpManualAttendance.php" class="dropdown-item">My Attendance</a>
                <a href="EmpProject.php" class="dropdown-item">Projects</a>
                <a href="EmpSalary.php" class="dropdown-item">Salary Status</a>
                <a href="Company.php" class="dropdown-item">Company Details</a>
                <a href="Calendar.php" class="dropdown-item">Calendar</a>
            </div>

            <script>
                function toggleMenu() {
                    const menuList = document.getElementById('menu-list');
                    menuList.classList.toggle('d-none');
                }

                function logout() {
                    window.location.href = 'logout.php';
                }
            </script>

            <button class="btn btn-primary me-2 ms-auto" onclick="logout()">Log Out</button>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4 text-center">Company Rules and Regulations</h2>
        <ul class="list-group" id="employeeRulesList">
        </ul>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const rules = [
            "1. All employees must clock in before 9:00 AM.",
            "2. Leave requests should be submitted at least 2 days in advance.",
            "3. Professional behavior is expected at all times in the workplace.",
            "4. Use only official email accounts for company communications.",
            "5. Maintain confidentiality of sensitive and proprietary information.",
            "6. Report any security or safety incidents immediately to HR.",
            "7. Follow all COVID-19 safety guidelines and hygiene practices.",
            "8. Avoid any form of discrimination or harassment in the workplace.",
            "9. Abide by all company internet and software usage policies.",
            "10. Regularly update your work progress in the project management system."
        ];

        const list = document.getElementById('employeeRulesList');
        rules.forEach(rule => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = rule;
            list.appendChild(li);
        });
    </script>
</body>
</html>
