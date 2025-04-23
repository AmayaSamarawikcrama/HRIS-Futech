<?php
session_start();
$host = 'localhost';
$dbname = 'hris_db';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit;
}

// Get logged-in user data
$user_data = [];
$user_id = $_SESSION['user_id'];
$sql = "SELECT First_Name, Last_Name FROM Employee WHERE Employee_ID = '$user_id'";
$result = $conn->query($sql);
if($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    // If user data not found, use session data if available
    if(isset($_SESSION['First_Name']) && isset($_SESSION['Last_Name'])) {
        $user_data['First_Name'] = $_SESSION['First_Name'];
        $user_data['Last_Name'] = $_SESSION['Last_Name'];
    } else {
        $user_data['First_Name'] = 'Unknown';
        $user_data['Last_Name'] = 'User';
    }
}

// Fetch events from database
$events = array();
$sql = "SELECT * FROM Event ORDER BY Event_Date, Event_Time";
$result = $conn->query($sql);

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Calendar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .menu-icon {
            cursor: pointer;
            color: #0d6efd;
            font-size: 25px;
        }
        .event-list {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .event-card {
            margin-bottom: 10px;
            border-left: 4px solid #0d6efd;
        }
        .event-type {
            font-weight: bold;
            color: #0d6efd;
        }
        #calendar {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .fc-event {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light p-3">
            <div class="container-fluid">
                <span class="menu-icon" onclick="toggleMenu()">&#9776;</span>
                <div id="menu-list" class="d-none position-absolute bg-light border rounded p-2" style="top: 50px; left: 10px; z-index: 1000;">
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
                <button class="btn btn-primary" onclick="logout()">Log Out</button>
            </div>
        </nav>

        <div class="container mt-4">
            <h2 class="text-center"><b>HR Team Calendar</b></h2>
            
            <div class="row">
                <div class="col-md-8">
                    <div id="calendar" class="mb-4"></div>
                </div>
                <div class="col-md-4">
                    <h4 class="mt-4">Upcoming Events</h4>
                    <div class="event-list">
                        <?php if(empty($events)): ?>
                            <div class="alert alert-info">No upcoming events scheduled.</div>
                        <?php else: ?>
                            <?php foreach($events as $event): ?>
                                <div class="card event-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['Event_Name']); ?></h5>
                                        <p class="card-text">
                                            <span class="event-type"><?php echo htmlspecialchars($event['Event_Type']); ?></span><br>
                                            <?php echo htmlspecialchars($event['Event_Description']); ?>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <strong>Date:</strong> <?php echo date('M j, Y', strtotime($event['Event_Date'])); ?><br>
                                                <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['Event_Time'])); ?><br>
                                                <?php if($event['Location']): ?>
                                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['Location']); ?><br>
                                                <?php endif; ?>
                                                <?php if($event['Organizer']): ?>
                                                    <strong>Organizer:</strong> <?php echo htmlspecialchars($event['Organizer']); ?>
                                                <?php endif; ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php foreach($events as $event): ?>
                    {
                        title: '<?php echo addslashes($event['Event_Name']); ?>',
                        start: '<?php echo $event['Event_Date'] . ($event['Event_Time'] ? 'T' . $event['Event_Time'] : ''); ?>',
                        description: '<?php echo addslashes($event['Event_Description']); ?>',
                        extendedProps: {
                            type: '<?php echo addslashes($event['Event_Type']); ?>',
                            location: '<?php echo addslashes($event['Location']); ?>',
                            organizer: '<?php echo addslashes($event['Organizer']); ?>'
                        },
                        color: getEventColor('<?php echo $event['Event_Type']; ?>')
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    // Display event details when clicked
                    alert(
                        'Event: ' + info.event.title + '\n' +
                        'Type: ' + info.event.extendedProps.type + '\n' +
                        'Description: ' + info.event.extendedProps.description + '\n' +
                        'Date: ' + info.event.start.toLocaleString() + '\n' +
                        'Location: ' + info.event.extendedProps.location + '\n' +
                        'Organizer: ' + info.event.extendedProps.organizer
                    );
                }
            });
            calendar.render();
        });
        
        // Assign different colors based on event type
        function getEventColor(type) {
            switch(type) {
                case 'Meeting': return '#3788d8';
                case 'Training': return '#6c757d';
                case 'Workshop': return '#20c997';
                case 'Seminar': return '#6610f2';
                case 'Conference': return '#fd7e14';
                case 'Team Building': return '#dc3545';
                default: return '#6f42c1';
            }
        }

        function logout() {
            // Implement logout functionality
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>