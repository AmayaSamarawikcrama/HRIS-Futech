<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>HR Project Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />

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
    <!-- Sidebar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
        <div class="container-fluid">
            <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
            <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; nav-left: 10px; z-index: 1000;">
                <a href="HrDashboard.html" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.html" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.html" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.html" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.html" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.html" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.html" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.html" class="d-block text-decoration-none text-dark mb-2">Calendar</a>

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

    <!-- Main Content -->
    <main class="col-md-12  col-lg-100 px-4">
      <h2 class="my-4">Project Management</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add Project</button>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" class="form-control" placeholder="Enter project name" required />
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" required />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" required>
                            <option selected disabled>Select status</option>
                            <option>Active</option>
                            <option>Completed</option>
                            <option>On Hold</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Employees</label>
                        <div id="employee-list">
                            <div class="d-flex align-items-center mb-2">
                                <input type="text" class="form-control me-2" placeholder="Enter employee name" required />
                                <input type="text" class="form-control me-2" placeholder="Role in Project" required />
                                <button type="button" class="btn btn-danger" onclick="removeEmployee(this)">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addEmployee()">Add Employee</button>
                    </div>

                    <script>
                        function addEmployee() {
                            const employeeList = document.getElementById('employee-list');
                            const newEmployee = document.createElement('div');
                            newEmployee.className = 'd-flex align-items-center mb-2';
                            newEmployee.innerHTML = `
                                <input type="text" class="form-control me-2" placeholder="Enter employee name" required />
                                <input type="text" class="form-control me-2" placeholder="Role in Project" required />
                                <button type="button" class="btn btn-danger" onclick="removeEmployee(this)">Remove</button>
                            `;
                            employeeList.appendChild(newEmployee);
                        }

                        function removeEmployee(button) {
                            button.parentElement.remove();
                        }
                    </script>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" placeholder="Enter project description" required></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Project</button>
                </div>
            </form>
        </div>
    </div>
      <!-- Projects Table -->
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">All Projects</h5>
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>Project Name</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Employees</th>
                <th>Progress</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>HRIS Dashboard</td>
                <td>2025-03-01</td>
                <td>2025-05-15</td>
                <td><span class="badge bg-primary">Active</span></td>
                <td>5</td>
                <td>
                  <div class="progress">
                    <div class="progress-bar bg-success" style="width: 60%">60%</div>
                  </div>
                </td>
                <td>
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal">View</button>
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewModalLabel">Project Details - HRIS Dashboard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Start Date:</strong> 2025-03-01</p>
        <p><strong>End Date:</strong> 2025-05-15</p>
        <p><strong>Status:</strong> Active</p>
        <p><strong>Assigned Employees:</strong></p>
        <ul>
            <li>Jane - Developer</li>
            <li>Alex - Designer</li>
            <li>Sam - Project Manager</li>
        </ul>
        <p><strong>Progress:</strong> 60%</p>
        <p><strong>Description:</strong> This project focuses on building a new HR dashboard to manage employee records, leaves, and payroll integration.</p>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit Project - HRIS Dashboard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
          <label class="form-label">Project Name</label>
          <input type="text" class="form-control" value="HRIS Dashboard" />
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" value="2025-03-01" />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" value="2025-05-15" />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Status</label>
          <select class="form-select">
            <option selected>Active</option>
            <option>Completed</option>
            <option>On Hold</option>
          </select>
        </div>

    <div class="mb-3"></div>
        <label class="form-label">Assigned Employees</label>
        <div id="edit-employee-list">
            <div class="d-flex align-items-center mb-2">
                <input type="text" class="form-control me-2" value="Jane" required />
                <input type="text" class="form-control me-2" value="Developer" required />
                <button type="button" class="btn btn-danger" onclick="removeEmployee(this)">Remove</button>
            </div>
            <div class="d-flex align-items-center mb-2">
                <input type="text" class="form-control me-2" value="Alex" required />
                <input type="text" class="form-control me-2" value="Designer" required />
                <button type="button" class="btn btn-danger" onclick="removeEmployee(this)">Remove</button>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mt-2" onclick="addEmployee()">Add Employee</button>
    </div>

    <script>
        function addEmployee() {
            const employeeList = document.getElementById('edit-employee-list');
            const newEmployee = document.createElement('div');
            newEmployee.className = 'd-flex align-items-center mb-2';
            newEmployee.innerHTML = `
                <input type="text" class="form-control me-2" placeholder="Enter employee name" required />
                <input type="text" class="form-control me-2" placeholder="Role in Project" required />
                <button type="button" class="btn btn-danger" onclick="removeEmployee(this)">Remove</button>
            `;
            employeeList.appendChild(newEmployee);
        }

        function removeEmployee(button) {
            button.parentElement.remove();
        }
    </script>


        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="3">This project focuses on building a new HR dashboard to manage employee records, leaves, and payroll integration.</textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
