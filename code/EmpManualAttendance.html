<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance Entry</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
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
                    <a href="Dashboard.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="View Employee.php" class="d-block text-decoration-none text-dark mb-2">View Employee</a>
                    <a href="Attendance.html" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="Project.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="Leave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="Salary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                </div>
                <script>
                    function toggleMenu() {
                        const menuList = document.getElementById('menu-list');
                        menuList.classList.toggle('d-none');
                    }
                </script>
                <span class="employee-name ms-auto me-3">
                    <?php
                        // Assuming $user_data is defined and contains user information
                        echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']);
                    ?>
                </span>
                
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='assets/image.png'">
            </div>
        </nav>
    <!-- Attendance Entry Form -->
    <div class="container mt-4">
        <h2 class="text-center"><b>Enter Your Attendance</b></h2><br>
        <form id="attendanceForm">
            <div class="row g-3">
               
                <div class="col-md-6">
                    <label class="form-label">Date:</label>
                    <input type="date" class="form-control" id="date" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock In Time:</label>
                    <input type="time" class="form-control" id="clockIn" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clock Out Time:</label>
                    <input type="time" class="form-control" id="clockOut" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Hours:</label>
                    <input type="number" class="form-control" id="workHours" placeholder="Enter work hours" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assigned Task:</label>
                    <input type="text" class="form-control" id="assignedTask" placeholder="Enter assigned task" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Task Completion (%):</label>
                    <input type="number" class="form-control" id="taskCompletion" placeholder="Enter task completion %" min="0" max="100" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Comments:</label>
                    <textarea class="form-control" id="comments" rows="3" placeholder="Enter comments"></textarea>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary w-50">Submit Attendance</button><br>
                </div>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="container mt-4">
        <h2 class="text-center"><b>My Attendance Records</b></h2><br>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Date</th>
                        <th>Clock In Time</th>
                        <th>Clock Out Time</th>
                        <th>Work Hours</th>
                        <th>Assigned Task</th>
                        <th>Task Completion (%)</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <!-- Employee entered data will appear here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('attendanceForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Get form values
     
        const date = document.getElementById('date').value;
        const clockIn = document.getElementById('clockIn').value;
        const clockOut = document.getElementById('clockOut').value;
        const workHours = document.getElementById('workHours').value;
        const assignedTask = document.getElementById('assignedTask').value;
        const taskCompletion = document.getElementById('taskCompletion').value;
        const comments = document.getElementById('comments').value;

        // Validate input
        if (!employeeName || !department || !date || !clockIn || !clockOut || !workHours || !assignedTask || !taskCompletion) {
            alert("Please fill in all required fields.");
            return;
        }

        // Append new row to the table
        const tableBody = document.getElementById('attendanceTableBody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${employeeName}</td>
            <td>${department}</td>
            <td>${date}</td>
            <td>${clockIn}</td>
            <td>${clockOut}</td>
            <td>${workHours} hours</td>
            <td>${assignedTask}</td>
            <td>${taskCompletion}%</td>
            <td>${comments}</td>
        `;
        tableBody.appendChild(newRow);

        // Reset the form after submission
        document.getElementById('attendanceForm').reset();
    });
</script>
</body>
</html>
