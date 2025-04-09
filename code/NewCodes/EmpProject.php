<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Project Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
     .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
</style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
                    <a href="EmpDashboard.html" class="dropdown-item">Dashboard</a>
                    <a href="EmpProfile.html" class="dropdown-item">My Profile</a>
                    <a href="EmpLeave.html" class="dropdown-item">Leave Request</a>
                    <a href="EmpManualAttendance.html" class="dropdown-item">My Attendance</a>
                    <a href="EmpProject.html" class="dropdown-item"> Projects</a>
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
        <main class="col-md-12  col-lg-100 px-4">
            <h2 class="my-4">Project Overview: [Project Name]</h2>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text">This is a brief overview of the project, its objectives, and the expected outcomes. It provides insight into the project's scope, goals, and timeline.</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Project Relevant Employees</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            John Doe
                            <span class="badge bg-primary rounded-pill">Project Manager</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Jane Smith
                            <span class="badge bg-secondary rounded-pill">Lead Developer</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Emily Johnson
                            <span class="badge bg-success rounded-pill">UI/UX Designer</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Michael Brown
                            <span class="badge bg-warning rounded-pill">QA Engineer</span>
                        </li>
                    </ul>
                </div>
            </div>

           
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Project Timeline</h5>
                    <ul class="list-group">
                        <li class="list-group-item">Start Date - End Date</li>
                      
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Project Status</h5>
                    <p class="card-text">Current progress: 60% complete. The project is on track to meet the deadlines, with some tasks in progress and others completed.</p>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
