<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .employee-name {
            font-weight: bold;
            color: red;
        }
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
                   <a href="EmpProfile.html" class="dropdown-item">My Profile</a>
                   <a href="EmplLeave.php" class="dropdown-item">Leave Request</a>
                   <a href="EmpManualAttendance.html" class="dropdown-item">My Attendance</a>
                   <a href="Performance.php" class="dropdown-item">My Performance</a>
                   <a href="EmpSalary.html" class="dropdown-item">Salary Status</a>
                 <a href="Report.php" class="dropdown-item">Report</a>
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

        <div class="container-fluid mt-4 text-center">
            <img src="assets/EmpDash.jpg" alt="Dashboard Overview" class="img-fluid w-100" style="height: auto;">
        </div>

        <div class="row mt-4 g-2">
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpProfile.html'">My Profile</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmplLeave.html'">Leave Request</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpManualAttendance.html'">My Attendance</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Performance.html'">My Performance</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='EmpSalary.html'">Salary Status</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Report.html'">Report</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Company.html'">Company Details</button>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <button class="btn btn-primary w-100 p-3 fs-6" onclick="location.href='Calendar.html'">Calendar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logout() {
            window.location.href = "logout.php";
        }
    </script>
</body>
</html>
