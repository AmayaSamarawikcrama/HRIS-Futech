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
        <a href="HrDashboard.html" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
        <a href="HrAddEmployee.html" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
        <a href="HrEmployeeDetails.html" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
        <a href="Attendance.html" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
        <a href="HrProject.html" class="d-block text-decoration-none text-dark mb-2">Project</a>
        <a href="HrLeave.html" class="d-block text-decoration-none text-dark mb-2">Leave</a>
        <a href="HrSalary.html" class="d-block text-decoration-none text-dark mb-2">Salary</a>
        <a href="HrCompany.html" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
        <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
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

    <ul class="list-group">
      <li class="list-group-item">1. All employees must clock in before 9:00 AM.</li>
      <li class="list-group-item">2. Leave requests should be submitted at least 2 days in advance.</li>
      <li class="list-group-item">3. Professional behavior is expected at all times in the workplace.</li>
      <li class="list-group-item">4. Use only official email accounts for company communications.</li>
      <li class="list-group-item">5. Maintain confidentiality of sensitive and proprietary information.</li>
      <li class="list-group-item">6. Report any security or safety incidents immediately to HR.</li>
      <li class="list-group-item">7. Follow all COVID-19 safety guidelines and hygiene practices.</li>
      <li class="list-group-item">8. Avoid any form of discrimination or harassment in the workplace.</li>
      <li class="list-group-item">9. Abide by all company internet and software usage policies.</li>
      <li class="list-group-item">10. Regularly update your work progress in the project management system.</li>
    </ul>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
