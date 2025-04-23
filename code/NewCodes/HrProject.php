<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "hris_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM Employee WHERE Employee_ID = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle CRUD operations
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new project
    if(isset($_POST['add_project'])) {
        $project_name = $_POST['project_name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];
        $department_id = $_POST['department_id'];
        $manager_id = $_SESSION['user_id']; // Current user as manager
        
        $stmt = $conn->prepare("INSERT INTO Project (Project_Name, Description, Start_Date, End_Date, Status, Department_ID, Manager_ID) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$project_name, $description, $start_date, $end_date, $status, $department_id, $manager_id]);
        
        $_SESSION['message'] = "Project added successfully!";
        header("Location: HrProject.php");
        exit;
    }
    
    // Update project
    if(isset($_POST['update_project'])) {
        $project_id = $_POST['project_id'];
        $project_name = $_POST['project_name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE Project SET Project_Name = ?, Description = ?, Start_Date = ?, End_Date = ?, Status = ? 
                               WHERE Project_ID = ?");
        $stmt->execute([$project_name, $description, $start_date, $end_date, $status, $project_id]);
        
        $_SESSION['message'] = "Project updated successfully!";
        header("Location: HrProject.php");
        exit;
    }
}

// Delete project
if(isset($_GET['delete'])) {
    $project_id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM Project WHERE Project_ID = ?");
    $stmt->execute([$project_id]);
    
    $_SESSION['message'] = "Project deleted successfully!";
    header("Location: HrProject.php");
    exit;
}

// Get all projects
$projects = $conn->query("SELECT * FROM Project")->fetchAll(PDO::FETCH_ASSOC);

// Get departments for dropdown
$departments = $conn->query("SELECT * FROM Department")->fetchAll(PDO::FETCH_ASSOC);
?>

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
                <a href="HrDashboard.php" class="d-block text-decoration-none text-dark mb-2">Dashboard</a>
                    <a href="HrAddEmployee.php" class="d-block text-decoration-none text-dark mb-2">Add Employee</a>
                    <a href="HrEmployeeDetails.php" class="d-block text-decoration-none text-dark mb-2">Employee Details</a>
                    <a href="Attendance.php" class="d-block text-decoration-none text-dark mb-2">Attendance</a>
                    <a href="HrProject.php" class="d-block text-decoration-none text-dark mb-2">Project</a>
                    <a href="HrLeave.php" class="d-block text-decoration-none text-dark mb-2">Leave</a>
                    <a href="HrSalary.php" class="d-block text-decoration-none text-dark mb-2">Salary</a>
                    <a href="Report.php" class="d-block text-decoration-none text-dark mb-2">Report</a>
                    <a href="Company.php" class="d-block text-decoration-none text-dark mb-2">Company Details</a>
                    <a href="HrCalendar.php" class="d-block text-decoration-none text-dark mb-2">Calendar</a>
            </div>
            <script>
                function toggleMenu() {
                    const menuList = document.getElementById('menu-list');
                    menuList.classList.toggle('d-none');
                }
            </script>
            <span class="employee-name ms-auto me-3">
                <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
            </span>
            
            <button class="btn btn-primary me-2" onclick="location.href='logout.php'">Log Out</button>
            <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='profile.php'">
        </div>
    </nav>

    <!-- Main Content -->
    <main class="col-md-12 col-lg-100 px-4">
      <h2 class="my-4">Project Management</h2>
      
      <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
      <?php endif; ?>
      
      <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add Project</button>

      <!-- Add Project Modal -->
      <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
              <form class="modal-content" method="POST" action="">
                  <div class="modal-header">
                      <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <div class="mb-3">
                          <label class="form-label">Project Name</label>
                          <input type="text" name="project_name" class="form-control" placeholder="Enter project name" required />
                      </div>

                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <label class="form-label">Start Date</label>
                              <input type="date" name="start_date" class="form-control" required />
                          </div>
                          <div class="col-md-6 mb-3">
                              <label class="form-label">End Date</label>
                              <input type="date" name="end_date" class="form-control" required />
                          </div>
                      </div>

                      <div class="mb-3">
                          <label class="form-label">Department</label>
                          <select name="department_id" class="form-select" required>
                              <option value="">Select Department</option>
                              <?php foreach($departments as $dept): ?>
                                  <option value="<?php echo $dept['Department_ID']; ?>"><?php echo $dept['Department_Name']; ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>

                      <div class="mb-3">
                          <label class="form-label">Status</label>
                          <select name="status" class="form-select" required>
                              <option value="Planning">Planning</option>
                              <option value="In Progress">In Progress</option>
                              <option value="On Hold">On Hold</option>
                              <option value="Completed">Completed</option>
                              <option value="Cancelled">Cancelled</option>
                          </select>
                      </div>

                      <div class="mb-3">
                          <label class="form-label">Description</label>
                          <textarea name="description" class="form-control" rows="3" placeholder="Enter project description" required></textarea>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="add_project" class="btn btn-primary">Add Project</button>
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
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Department</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($projects as $project): 
                  // Get department name
                  $dept_stmt = $conn->prepare("SELECT Department_Name FROM Department WHERE Department_ID = ?");
                  $dept_stmt->execute([$project['Department_ID']]);
                  $dept_name = $dept_stmt->fetchColumn();
              ?>
              <tr>
                <td><?php echo htmlspecialchars($project['Project_Name']); ?></td>
                <td><?php echo htmlspecialchars($project['Start_Date']); ?></td>
                <td><?php echo htmlspecialchars($project['End_Date']); ?></td>
                <td>
                  <?php 
                  $badge_class = '';
                  switch($project['Status']) {
                      case 'Planning': $badge_class = 'bg-secondary'; break;
                      case 'In Progress': $badge_class = 'bg-primary'; break;
                      case 'On Hold': $badge_class = 'bg-warning'; break;
                      case 'Completed': $badge_class = 'bg-success'; break;
                      case 'Cancelled': $badge_class = 'bg-danger'; break;
                  }
                  ?>
                  <span class="badge <?php echo $badge_class; ?>"><?php echo $project['Status']; ?></span>
                </td>
                <td><?php echo htmlspecialchars($dept_name); ?></td>
                <td>
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $project['Project_ID']; ?>">View</button>
                  <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $project['Project_ID']; ?>">Edit</button>
                  <a href="HrProject.php?delete=<?php echo $project['Project_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                </td>
              </tr>

              <!-- View Modal for each project -->
              <div class="modal fade" id="viewModal<?php echo $project['Project_ID']; ?>" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="viewModalLabel">Project Details - <?php echo htmlspecialchars($project['Project_Name']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p><strong>Start Date:</strong> <?php echo htmlspecialchars($project['Start_Date']); ?></p>
                      <p><strong>End Date:</strong> <?php echo htmlspecialchars($project['End_Date']); ?></p>
                      <p><strong>Status:</strong> <?php echo htmlspecialchars($project['Status']); ?></p>
                      <p><strong>Department:</strong> <?php echo htmlspecialchars($dept_name); ?></p>
                      <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($project['Description'])); ?></p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Edit Modal for each project -->
              <div class="modal fade" id="editModal<?php echo $project['Project_ID']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <form class="modal-content" method="POST" action="">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editModalLabel">Edit Project - <?php echo htmlspecialchars($project['Project_Name']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="project_id" value="<?php echo $project['Project_ID']; ?>">
                      
                      <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" name="project_name" class="form-control" value="<?php echo htmlspecialchars($project['Project_Name']); ?>" required />
                      </div>

                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Start Date</label>
                          <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($project['Start_Date']); ?>" required />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">End Date</label>
                          <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($project['End_Date']); ?>" required />
                        </div>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                          <option value="Planning" <?php echo $project['Status'] == 'Planning' ? 'selected' : ''; ?>>Planning</option>
                          <option value="In Progress" <?php echo $project['Status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                          <option value="On Hold" <?php echo $project['Status'] == 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                          <option value="Completed" <?php echo $project['Status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                          <option value="Cancelled" <?php echo $project['Status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($project['Description']); ?></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="update_project" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form>
                </div>
              </div>

              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>