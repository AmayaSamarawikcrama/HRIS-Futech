<?php
// Start session carefully
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "hris_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed. Please try again later.");
}

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM Employee WHERE Employee_ID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        session_destroy();
        die("Your account could not be verified. Please login again.");
    }
} catch (Exception $e) {
    die("System error. Please try again later.");
}

// Get all projects
try {
    $stmt = $conn->prepare("
        SELECT p.*, d.Department_Name, 
               CONCAT(e.First_Name, ' ', e.Last_Name) AS Manager_Name
        FROM Project p
        LEFT JOIN Department d ON p.Department_ID = d.Department_ID
        LEFT JOIN Employee e ON p.Manager_ID = e.Employee_ID
        ORDER BY p.Status, p.Start_Date
    ");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error loading projects. Please try again.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Projects - HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .project-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
            height: 100%;
        }
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
        .progress {
            height: 10px;
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
    <div class="row">
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
                    <a href="Report.php" class="dropdown-item">Report</a>
                    <a href="Company.php" class="dropdown-item">Company Details</a>
                    <a href="Calendar.php" class="dropdown-item">Calendar</a>
                </div>
                <span class="employee-name ms-auto me-3">
                    <?php echo htmlspecialchars($user_data['First_Name'] . ' ' . $user_data['Last_Name']); ?>
                </span>
                <button class="btn btn-primary me-2" onclick="logout()">Log Out</button>
                <img src="assets/image.png" alt="Profile Icon" class="rounded-circle" style="width: 40px; height: 40px; cursor: pointer;" onclick="location.href='profile.php'">
            </div>
        </nav>
        
        <main class="col-md-12 px-4 py-3">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>All Projects</h2>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </div>

            <?php if (empty($projects)): ?>
                <div class="alert alert-info">No projects found.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($projects as $project): ?>
                        <?php
                        // Calculate progress based on status
                        $progress_width = 0;
                        $progress_class = 'bg-primary';
                        
                        switch($project['Status']) {
                            case 'Completed': 
                                $progress_width = 100;
                                $progress_class = 'bg-success';
                                break;
                            case 'In Progress': 
                                $progress_width = 60;
                                break;
                            case 'Planning': 
                                $progress_width = 20;
                                $progress_class = 'bg-secondary';
                                break;
                            case 'On Hold': 
                                $progress_width = 40;
                                $progress_class = 'bg-warning';
                                break;
                            case 'Cancelled': 
                                $progress_width = 30;
                                $progress_class = 'bg-danger';
                                break;
                        }
                        ?>
                        
                        <div class="col-md-4">
                            <div class="card project-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($project['Project_Name']); ?></h5>
                                    <span class="badge status-badge 
                                        <?php 
                                        switch($project['Status']) {
                                            case 'Planning': echo 'bg-secondary'; break;
                                            case 'In Progress': echo 'bg-primary'; break;
                                            case 'On Hold': echo 'bg-warning'; break;
                                            case 'Completed': echo 'bg-success'; break;
                                            case 'Cancelled': echo 'bg-danger'; break;
                                            default: echo 'bg-light text-dark';
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($project['Status']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text text-muted">
                                        <?php echo nl2br(htmlspecialchars(substr($project['Description'] ?: 'No description', 0, 100) . '...')); ?>
                                    </p>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Department:</small>
                                        <p><?php echo htmlspecialchars($project['Department_Name'] ?: 'Not assigned'); ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Manager:</small>
                                        <p><?php echo htmlspecialchars($project['Manager_Name'] ?: 'Not assigned'); ?></p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Timeline:</small>
                                        <p>
                                            <?php echo htmlspecialchars($project['Start_Date']); ?> to 
                                            <?php echo htmlspecialchars($project['End_Date']); ?>
                                        </p>
                                    </div>
                                    
                                    <div class="progress mt-3">
                                        <div class="progress-bar <?php echo $progress_class; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $progress_width; ?>%" 
                                             aria-valuenow="<?php echo $progress_width; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="project_detail.php?id=<?php echo $project['Project_ID']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Projects</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="Planning">Planning</option>
                            <option value="In Progress">In Progress</option>
                            <option value="On Hold">On Hold</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department">
                            <option value="">All Departments</option>
                            <?php
                            try {
                                $dept_stmt = $conn->query("SELECT Department_ID, Department_Name FROM Department");
                                $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($departments as $dept) {
                                    echo '<option value="'.htmlspecialchars($dept['Department_ID']).'">'.htmlspecialchars($dept['Department_Name']).'</option>';
                                }
                            } catch (Exception $e) {
                                // Silently fail - departments won't show in filter
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Timeframe</label>
                        <select class="form-select" name="timeframe">
                            <option value="">All Timeframes</option>
                            <option value="current">Current Projects</option>
                            <option value="upcoming">Upcoming Projects</option>
                            <option value="past">Past Projects</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleMenu() {
        const menuList = document.getElementById('menu-list');
        menuList.classList.toggle('d-none');
    }
    
    function logout() {
        window.location.href = 'logout.php';
    }
    
    function applyFilters() {
        const form = document.getElementById('filterForm');
        const status = form.elements['status'].value;
        const department = form.elements['department'].value;
        const timeframe = form.elements['timeframe'].value;
        
        // Build query string
        let queryParams = [];
        if (status) queryParams.push(`status=${encodeURIComponent(status)}`);
        if (department) queryParams.push(`department=${encodeURIComponent(department)}`);
        if (timeframe) queryParams.push(`timeframe=${encodeURIComponent(timeframe)}`);
        
        // Reload page with filters
        window.location.href = window.location.pathname + (queryParams.length ? '?' + queryParams.join('&') : '');
    }
    
    // Apply filters from URL on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const form = document.getElementById('filterForm');
        
        if (urlParams.has('status')) {
            form.elements['status'].value = urlParams.get('status');
        }
        if (urlParams.has('department')) {
            form.elements['department'].value = urlParams.get('department');
        }
        if (urlParams.has('timeframe')) {
            form.elements['timeframe'].value = urlParams.get('timeframe');
        }
    });
</script>
</body>
</html>